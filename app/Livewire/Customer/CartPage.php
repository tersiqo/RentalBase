<?php

namespace App\Livewire\Customer;

use App\Models\Client;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\Component;

class CartPage extends Component
{
    public $client;

    public $cart = [];

    public $selectedItems = [];

    public $selectAll = false;

    public $showEditDateModal = false;
    public $editingHash = null;
    public $editingItem = null;
    public $editingStartDate = '';
    public $editingEndDate = '';
    public $editingAvailableUnits = 0;
    public $editingErrorMessage = null;

    public function mount($subdomain)
    {
        $this->client = Client::where('subdomain', $subdomain)
            ->where('status', 'aktif')
            ->firstOrFail();

        session()->put('active_client_id', $this->client->id);
        session()->put('active_client_subdomain', $this->client->subdomain);

        $this->loadCart();
    }

    public function loadCart()
    {
        $this->cart = session()->get('cart', []);

        // Ensure selectedItems only contains valid hashes and is sequentially indexed
        $validHashes = array_keys($this->cart);
        $this->selectedItems = array_values(array_intersect($this->selectedItems, $validHashes));
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedItems = array_keys($this->cart);
        } else {
            $this->selectedItems = [];
        }
    }

    public function removeItem($hash)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$hash])) {
            unset($cart[$hash]);
            session()->put('cart', $cart);
        }

        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function updateQuantity($hash, $newQuantity)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$hash])) {
            $quantity = max(1, (int) $newQuantity);
            $cart[$hash]['quantity'] = $quantity;
            $cart[$hash]['subtotal'] = $cart[$hash]['price'] * $cart[$hash]['duration_days'] * $quantity;
            session()->put('cart', $cart);
        }
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function incrementQuantity($hash)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$hash])) {
            $cart[$hash]['quantity']++;
            $cart[$hash]['subtotal'] = $cart[$hash]['price'] * $cart[$hash]['duration_days'] * $cart[$hash]['quantity'];
            session()->put('cart', $cart);
        }
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function decrementQuantity($hash)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$hash]) && $cart[$hash]['quantity'] > 1) {
            $cart[$hash]['quantity']--;
            $cart[$hash]['subtotal'] = $cart[$hash]['price'] * $cart[$hash]['duration_days'] * $cart[$hash]['quantity'];
            session()->put('cart', $cart);
        }
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function openEditDateModal($hash)
    {
        $cart = session()->get('cart', []);
        if (! isset($cart[$hash])) {
            return;
        }

        $this->editingHash = $hash;
        $this->editingItem = $cart[$hash];
        $this->editingStartDate = $this->editingItem['start_date'];
        $this->editingEndDate = $this->editingItem['end_date'];
        $this->editingErrorMessage = null;

        $product = Product::find($this->editingItem['product_id']);
        $this->editingAvailableUnits = $product
            ? $product->getAvailableUnitsForPeriod($this->editingStartDate, $this->editingEndDate)
            : 0;

        $this->showEditDateModal = true;
    }

    public function checkEditingAvailability()
    {
        $this->editingErrorMessage = null;
        if (! $this->editingItem || ! $this->editingStartDate || ! $this->editingEndDate) {
            return;
        }

        $product = Product::find($this->editingItem['product_id']);
        if ($product) {
            $this->editingAvailableUnits = $product->getAvailableUnitsForPeriod($this->editingStartDate, $this->editingEndDate);
        }
    }

    public function closeEditDateModal()
    {
        $this->showEditDateModal = false;
        $this->editingHash = null;
        $this->editingItem = null;
        $this->editingErrorMessage = null;
    }

    public function saveEditedDate()
    {
        if (! $this->editingHash || ! $this->editingItem || ! $this->editingStartDate || ! $this->editingEndDate) {
            return;
        }

        $cart = session()->get('cart', []);
        if (! isset($cart[$this->editingHash])) {
            $this->closeEditDateModal();
            return;
        }

        $start = Carbon::parse($this->editingStartDate);
        $end = Carbon::parse($this->editingEndDate);

        if ($end->lt($start)) {
            $this->editingErrorMessage = 'Tanggal selesai harus setelah tanggal mulai.';
            return;
        }

        $product = Product::find($this->editingItem['product_id']);
        if (! $product) {
            $this->editingErrorMessage = 'Produk tidak ditemukan.';
            return;
        }

        $available = $product->getAvailableUnitsForPeriod($start, $end);
        $this->editingAvailableUnits = $available;
        $qty = (int) $this->editingItem['quantity'];

        if ($qty > $available) {
            $this->editingErrorMessage = "Stok unit tidak mencukupi untuk rentang tanggal tersebut (sisa {$available} unit).";
            return;
        }

        $newDuration = max(1, $start->diffInDays($end) + 1);
        $newHash = md5($product->id . $start->format('Y-m-d') . $end->format('Y-m-d'));

        // If changed to dates that already exist for this product in cart
        if ($newHash !== $this->editingHash && isset($cart[$newHash])) {
            $mergedQty = $cart[$newHash]['quantity'] + $qty;
            if ($mergedQty > $available) {
                $this->editingErrorMessage = "Penggabungan dengan item di tanggal yang sama melebihi stok tersedia (tersisa {$available} unit).";
                return;
            }
            $cart[$newHash]['quantity'] = $mergedQty;
            $cart[$newHash]['subtotal'] = $product->rental_price_per_day * $newDuration * $mergedQty;
            unset($cart[$this->editingHash]);
        } else {
            $updatedItem = $cart[$this->editingHash];
            $updatedItem['hash'] = $newHash;
            $updatedItem['start_date'] = $start->format('Y-m-d');
            $updatedItem['end_date'] = $end->format('Y-m-d');
            $updatedItem['duration_days'] = $newDuration;
            $updatedItem['subtotal'] = $product->rental_price_per_day * $newDuration * $qty;

            unset($cart[$this->editingHash]);
            $cart[$newHash] = $updatedItem;
        }

        // Update selectedItems array if old hash was selected
        if (in_array($this->editingHash, $this->selectedItems)) {
            $this->selectedItems = array_values(array_unique(array_map(
                fn($h) => $h === $this->editingHash ? $newHash : $h,
                $this->selectedItems
            )));
        }

        session()->put('cart', $cart);
        $this->loadCart();
        $this->dispatch('cart-updated');
        $this->closeEditDateModal();
        session()->flash('success', 'Tanggal sewa berhasil diperbarui!');
    }

    public function proceedToCheckout()
    {
        $cart = session()->get('cart', []);

        if (empty($this->selectedItems)) {
            session()->flash('error', 'Pilih minimal satu item sebelum melanjutkan ke checkout.');

            return;
        }

        $validHashes = array_keys($cart);
        $selected = array_values(array_intersect($this->selectedItems, $validHashes));

        if (empty($selected)) {
            session()->flash('error', 'Item yang dipilih tidak valid. Silakan pilih ulang.');

            return;
        }

        $periods = [];
        foreach ($selected as $hash) {
            $item = $cart[$hash];
            $periods[$item['start_date'].'|'.$item['end_date']] = true;
        }

        if (count($periods) > 1) {
            session()->flash('error', 'Item yang dipilih harus dalam satu periode tanggal sewa yang sama. Pilih item dengan periode yang sama untuk lanjut checkout.');

            return;
        }

        session()->put('checkout_items', $selected);

        return redirect()->route('customer.checkout', ['subdomain' => $this->client->subdomain]);
    }

    public function render()
    {
        $grandTotal = 0;
        $totalDeposit = 0;
        $selectedCount = 0;

        foreach ($this->cart as $hash => $item) {
            if (in_array($hash, $this->selectedItems)) {
                $grandTotal += $item['subtotal'];
                $totalDeposit += ($item['deposit_fee'] * $item['quantity']);
                $selectedCount += $item['quantity'];
            }
        }

        $cartWithHash = collect($this->cart)->map(function ($item, $key) {
            $item['hash'] = $key;

            return $item;
        });

        $groupedCart = $cartWithHash->groupBy(function ($item) {
            return $item['start_date'].'|'.$item['end_date'];
        });

        return view('livewire.customer.cart-page', [
            'groupedCart' => $groupedCart,
            'grandTotal' => $grandTotal,
            'totalDeposit' => $totalDeposit,
            'selectedCount' => $selectedCount,
        ])->extends('layouts.customer', ['client' => $this->client])->section('content');
    }
}

<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Product;
use Carbon\Carbon;

class AddToCartModal extends Component
{
    public Product $product;
    public int $availableUnits = 0;
    public ?string $errorMessage = null;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->availableUnits = $this->product->getAvailableUnitsForPeriod();
    }

    public function checkAvailability($startDate, $endDate)
    {
        $this->errorMessage = null;

        if (! $startDate || ! $endDate) {
            $this->availableUnits = $this->product->getAvailableUnitsForPeriod();
            return;
        }

        $this->availableUnits = $this->product->getAvailableUnitsForPeriod($startDate, $endDate);
    }

    public function confirm($startDate, $endDate, $quantity)
    {
        $this->errorMessage = null;

        if (!$startDate || !$endDate) {
            return;
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            if ($start->isPast() && !$start->isToday()) {
                return;
            }

            if ($end->lt($start)) {
                return;
            }

            $maxUnits = $this->product->getAvailableUnitsForPeriod($start, $end);
            $this->availableUnits = $maxUnits;

            $cart = session()->get('cart', []);
            $hash = md5($this->product->id . $start->format('Y-m-d') . $end->format('Y-m-d'));
            $existingQty = isset($cart[$hash]) ? (int) $cart[$hash]['quantity'] : 0;

            if ($maxUnits < 1) {
                $this->errorMessage = 'Stok penuh pada rentang tanggal yang dipilih. Silakan pilih tanggal lain.';
                return;
            }

            if ($quantity < 1 || ($existingQty + $quantity) > $maxUnits) {
                $this->errorMessage = "Jumlah unit melebihi stok tersedia pada tanggal tersebut (tersisa {$maxUnits} unit).";
                return;
            }

            $durationDays = $start->diffInDays($end) + 1;
            $subtotal = $this->product->rental_price_per_day * $durationDays * $quantity;
            
            if (isset($cart[$hash])) {
                $cart[$hash]['quantity'] += $quantity;
                $cart[$hash]['subtotal'] = $this->product->rental_price_per_day * $cart[$hash]['duration_days'] * $cart[$hash]['quantity'];
            } else {
                $cart[$hash] = [
                    'hash' => $hash,
                    'product_id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->rental_price_per_day,
                    'deposit_fee' => $this->product->deposit_fee,
                    'main_image' => $this->product->main_image,
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'duration_days' => $durationDays,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }
            
            session()->put('cart', $cart);
            
            // Beri tahu halaman untuk memperbarui icon keranjang (nanti)
            $this->dispatch('cart-updated');
            
            // Beri tahu Alpine.js untuk menutup modal tanpa me-refresh halaman
            $this->dispatch('close-cart-modal');
            
            session()->flash('success', 'Barang berhasil ditambahkan ke keranjang!');

        } catch (\Exception $e) {
            // Abaikan, biarkan UI menangani
        }
    }

    public function confirmAndCheckout($startDate, $endDate, $quantity)
    {
        $this->errorMessage = null;

        if (!$startDate || !$endDate) {
            return;
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            if ($start->isPast() && !$start->isToday()) {
                return;
            }

            if ($end->lt($start)) {
                return;
            }

            $maxUnits = $this->product->getAvailableUnitsForPeriod($start, $end);
            $this->availableUnits = $maxUnits;

            $cart = session()->get('cart', []);
            $hash = md5($this->product->id . $start->format('Y-m-d') . $end->format('Y-m-d'));

            if ($maxUnits < 1) {
                $this->errorMessage = 'Stok penuh pada rentang tanggal yang dipilih. Silakan pilih tanggal lain.';
                return;
            }

            if ($quantity < 1 || $quantity > $maxUnits) {
                $this->errorMessage = "Jumlah unit melebihi stok tersedia pada tanggal tersebut (tersisa {$maxUnits} unit).";
                return;
            }

            $durationDays = $start->diffInDays($end) + 1;
            $subtotal = $this->product->rental_price_per_day * $durationDays * $quantity;
            
            $cart[$hash] = [
                'hash' => $hash,
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->rental_price_per_day,
                'deposit_fee' => $this->product->deposit_fee,
                'main_image' => $this->product->main_image,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'duration_days' => $durationDays,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
            
            session()->put('cart', $cart);
            session()->put('checkout_items', [$hash]);
            $this->dispatch('cart-updated');
            
            return redirect()->route('customer.checkout', ['subdomain' => $this->product->client->subdomain]);

        } catch (\Exception $e) {
            // Abaikan
        }
    }

    public function render()
    {
        return view('livewire.customer.add-to-cart-modal');
    }
}

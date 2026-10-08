<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Client;

class CartPage extends Component
{
    public $client;
    public $cart = [];
    public $selectedItems = [];
    public $selectAll = false;

    public function mount($subdomain)
    {
        $this->client = Client::where('subdomain', $subdomain)
            ->where('status', 'aktif')
            ->firstOrFail();
            
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
            $quantity = max(1, (int)$newQuantity);
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
        
        $groupedCart = $cartWithHash->groupBy(function($item) {
            return $item['start_date'] . '|' . $item['end_date'];
        });

        return view('livewire.customer.cart-page', [
            'groupedCart' => $groupedCart,
            'grandTotal' => $grandTotal,
            'totalDeposit' => $totalDeposit,
            'selectedCount' => $selectedCount,
        ])->extends('layouts.customer', ['client' => $this->client])->section('content');
    }
}

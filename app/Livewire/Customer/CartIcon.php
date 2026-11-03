<?php

namespace App\Livewire\Customer;

use Livewire\Component;

class CartIcon extends Component
{
    public $client;
    public $count = 0;
    
    protected $listeners = ['cart-updated' => 'updateCount'];

    public function mount($client)
    {
        $this->client = $client;
        $this->updateCount();
    }

    public function updateCount()
    {
        $cart = session()->get('cart', []);
        $this->count = collect($cart)->filter(function ($item) {
            if ($this->client && isset($item['client_id'])) {
                return (int) $item['client_id'] === (int) $this->client->id;
            }
            if ($this->client && isset($item['product_id'])) {
                $prod = \App\Models\Product::find($item['product_id']);
                return $prod && (int) $prod->client_id === (int) $this->client->id;
            }
            return true;
        })->sum('quantity');
    }

    public function render()
    {
        return view('livewire.customer.cart-icon');
    }
}

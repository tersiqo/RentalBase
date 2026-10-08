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
        $this->count = collect($cart)->sum('quantity');
    }

    public function render()
    {
        return view('livewire.customer.cart-icon');
    }
}

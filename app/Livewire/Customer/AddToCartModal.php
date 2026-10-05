<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Product;
use Carbon\Carbon;

class AddToCartModal extends Component
{
    public Product $product;

    public function confirm($startDate, $endDate, $quantity)
    {
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

            $maxUnits = $this->product->units->count();
            if ($quantity < 1 || $quantity > $maxUnits) {
                return;
            }

            // TODO: Simpan ke tabel Carts atau Session
            
            // Beri tahu halaman untuk memperbarui icon keranjang (nanti)
            $this->dispatch('cart-updated');
            
            // Beri tahu Alpine.js untuk menutup modal tanpa me-refresh halaman
            $this->dispatch('close-cart-modal');
            
            session()->flash('success', 'Barang berhasil ditambahkan ke keranjang!');

        } catch (\Exception $e) {
            // Abaikan, biarkan UI menangani
        }
    }

    public function render()
    {
        return view('livewire.customer.add-to-cart-modal');
    }
}

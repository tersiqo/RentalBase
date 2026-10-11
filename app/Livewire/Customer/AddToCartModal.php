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

            $durationDays = $start->diffInDays($end) + 1;
            $subtotal = $this->product->rental_price_per_day * $durationDays * $quantity;
            
            $cart = session()->get('cart', []);
            $hash = md5($this->product->id . $start->format('Y-m-d') . $end->format('Y-m-d'));
            
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

    public function render()
    {
        return view('livewire.customer.add-to-cart-modal');
    }
}

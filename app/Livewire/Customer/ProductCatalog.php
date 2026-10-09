<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\Product;

class ProductCatalog extends Component
{
    public $client;
    public $categories;
    
    public $availableOnly = false;
    public $selectedCategory = null;
    public $minPrice = 0;
    public $maxPrice = 1000000;
    public $absoluteMaxPrice = 1000000;
    public $search = '';
    public $sort = 'terbaru';

    public $allProductsCount = 0;
    public $categoryCounts = [];

    public function mount($client, $categories)
    {
        $this->client = $client;
        $this->categories = $categories;

        $max = Product::where('client_id', $this->client->id)->max('rental_price_per_day');
        if ($max) {
            $this->maxPrice = (int)$max;
            $this->absoluteMaxPrice = (int)$max;
        }

        $this->allProductsCount = Product::where('client_id', $this->client->id)->where('status', 'aktif')->count();

        $this->categoryCounts = Product::where('client_id', $this->client->id)
            ->where('status', 'aktif')
            ->selectRaw('category_id, count(*) as count')
            ->groupBy('category_id')
            ->pluck('count', 'category_id')
            ->toArray();
    }

    public function resetFilters()
    {
        $this->reset(['availableOnly', 'selectedCategory', 'search', 'sort', 'minPrice', 'maxPrice']);
        $this->maxPrice = $this->absoluteMaxPrice;
    }

    #[Computed]
    public function products()
    {
        $productsQuery = Product::where('client_id', $this->client->id)
            ->where('status', 'aktif')
            ->with('category')
            ->withCount(['units as available_units_count' => function ($query) {
                $query->where('status', 'tersedia');
            }]);

        if ($this->search) {
            $productsQuery->where(function($query) {
                $query->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhereHas('category', function($q) {
                          $q->where('name', 'ilike', '%' . $this->search . '%');
                      });
            });
        }

        if ($this->availableOnly) {
            $productsQuery->whereHas('units', function ($query) {
                $query->where('status', 'tersedia');
            });
        }

        if ($this->selectedCategory) {
            $productsQuery->where('category_id', $this->selectedCategory);
        }

        $productsQuery->whereBetween('rental_price_per_day', [(int) $this->minPrice, (int) $this->maxPrice]);

        if ($this->sort === 'termurah') {
            $productsQuery->orderBy('rental_price_per_day', 'asc');
        } elseif ($this->sort === 'termahal') {
            $productsQuery->orderBy('rental_price_per_day', 'desc');
        } elseif ($this->sort === 'abjad') {
            $productsQuery->orderBy('name', 'asc');
        } else {
            $productsQuery->orderBy('created_at', 'desc');
        }

        return $productsQuery->get();
    }

    public function render()
    {
        return view('livewire.customer.product-catalog');
    }
}

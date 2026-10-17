<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;

class ProductCatalog extends Component
{
    use WithPagination;

    public $client;
    public $categories;
    
    public $availableOnly = false;
    public $selectedCategory = null;
    public $minPrice = 0;
    public $maxPrice = 1000000;
    public $absoluteMaxPrice = 1000000;
    public $search = '';
    public $sort = 'terbaru';
    public $startDate = '';
    public $endDate = '';

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

    public function updatedSearch() { $this->resetPage(); }
    public function updatedSelectedCategory() { $this->resetPage(); }
    public function updatedAvailableOnly() { $this->resetPage(); }
    public function updatedMinPrice() { $this->resetPage(); }
    public function updatedMaxPrice() { $this->resetPage(); }
    public function updatedSort() { $this->resetPage(); }

    public function updatedStartDate()
    {
        if ($this->startDate && $this->endDate && $this->endDate < $this->startDate) {
            $this->endDate = $this->startDate;
        }
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        if ($this->startDate && $this->endDate && $this->endDate < $this->startDate) {
            $this->startDate = $this->endDate;
        }
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['availableOnly', 'selectedCategory', 'search', 'sort', 'minPrice', 'maxPrice', 'startDate', 'endDate']);
        $this->maxPrice = $this->absoluteMaxPrice;
        $this->resetPage();
    }

    public function quickAddToCart($productId)
    {
        $product = Product::where('id', $productId)
            ->where('client_id', $this->client->id)
            ->where('status', 'aktif')
            ->first();

        if (!$product) {
            session()->flash('error', 'Produk tidak ditemukan.');
            return;
        }

        $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->addDay()->startOfDay();
        $end = $this->endDate ? Carbon::parse($this->endDate)->startOfDay() : $start->copy()->addDays(1);

        if ($end->lt($start)) {
            $end = $start->copy()->addDays(1);
        }

        $durationDays = max(1, $start->diffInDays($end) + 1);
        $subtotal = $product->rental_price_per_day * $durationDays;

        $cart = session()->get('cart', []);
        $hash = md5($product->id . $start->format('Y-m-d') . $end->format('Y-m-d'));

        if (isset($cart[$hash])) {
            $cart[$hash]['quantity'] += 1;
            $cart[$hash]['subtotal'] = $product->rental_price_per_day * $cart[$hash]['duration_days'] * $cart[$hash]['quantity'];
        } else {
            $cart[$hash] = [
                'hash' => $hash,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->rental_price_per_day,
                'deposit_fee' => $product->deposit_fee,
                'main_image' => $product->main_image,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'duration_days' => $durationDays,
                'quantity' => 1,
                'subtotal' => $subtotal,
            ];
        }

        session()->put('cart', $cart);
        $this->dispatch('cart-updated');

        $periodStr = $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y');
        session()->flash('cart_success', "{$product->name} berhasil ditambahkan ke keranjang ({$periodStr})!");
    }

    #[Computed]
    public function products()
    {
        $productsQuery = Product::where('client_id', $this->client->id)
            ->where('status', 'aktif')
            ->with('category');

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();

            $productsQuery->select('products.*')
                ->withCount(['units as total_units_count' => function ($query) {
                    $query->where('status', '!=', 'rusak');
                }])
                ->selectRaw('(
                    SELECT COALESCE(SUM(oi.quantity), 0)
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    WHERE oi.product_id = products.id
                      AND o.client_id = products.client_id
                      AND o.status NOT IN (\'batal\', \'dibatalkan\', \'ditolak\')
                      AND o.rental_start_date <= ?
                      AND o.rental_end_date >= ?
                ) as booked_units_count', [$end, $start]);

            if ($this->availableOnly) {
                $productsQuery->havingRaw('(
                    (SELECT COUNT(*) FROM equipment_units eu WHERE eu.product_id = products.id AND eu.status != \'rusak\')
                    -
                    (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.product_id = products.id AND o.client_id = products.client_id AND o.status NOT IN (\'batal\', \'dibatalkan\', \'ditolak\') AND o.rental_start_date <= ? AND o.rental_end_date >= ?)
                ) > 0', [$end, $start]);
            }
        } else {
            $productsQuery->withCount(['units as available_units_count' => function ($query) {
                $query->where('status', 'tersedia');
            }]);

            if ($this->availableOnly) {
                $productsQuery->whereHas('units', function ($query) {
                    $query->where('status', 'tersedia');
                });
            }
        }

        if ($this->search) {
            $productsQuery->where(function($query) {
                $query->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhereHas('category', function($q) {
                          $q->where('name', 'ilike', '%' . $this->search . '%');
                      });
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

        $paginated = $productsQuery->paginate(9);

        if ($this->startDate && $this->endDate) {
            $paginated->getCollection()->transform(function ($item) {
                $item->available_units_count = max(0, (int)($item->total_units_count ?? 0) - (int)($item->booked_units_count ?? 0));
                return $item;
            });
        }

        return $paginated;
    }

    public function render()
    {
        return view('livewire.customer.product-catalog');
    }
}

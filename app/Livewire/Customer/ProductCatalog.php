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

    public $showModal = false;
    public $modalProduct = null;
    public $modalStartDate = '';
    public $modalEndDate = '';
    public $modalQuantity = 1;
    public $modalAvailableUnits = 0;
    public $modalErrorMessage = null;

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

    public function openAddToCartModal($productId)
    {
        $product = Product::where('id', $productId)
            ->where('client_id', $this->client->id)
            ->where('status', 'aktif')
            ->first();

        if (! $product) {
            return;
        }

        $this->modalProduct = $product;
        $this->modalStartDate = $this->startDate ?: Carbon::now()->addDay()->format('Y-m-d');
        $this->modalEndDate = $this->endDate ?: Carbon::now()->addDays(3)->format('Y-m-d');
        $this->modalErrorMessage = null;

        $this->modalAvailableUnits = $product->getAvailableUnitsForPeriod($this->modalStartDate, $this->modalEndDate);
        $this->modalQuantity = $this->modalAvailableUnits > 0 ? 1 : 1;
        $this->showModal = true;
    }

    public function checkModalAvailability()
    {
        $this->modalErrorMessage = null;
        if ($this->modalProduct && $this->modalStartDate && $this->modalEndDate) {
            $this->modalAvailableUnits = $this->modalProduct->getAvailableUnitsForPeriod($this->modalStartDate, $this->modalEndDate);
            if ($this->modalQuantity > $this->modalAvailableUnits && $this->modalAvailableUnits > 0) {
                $this->modalQuantity = $this->modalAvailableUnits;
            }
        }
    }

    public function closeAddToCartModal()
    {
        $this->showModal = false;
        $this->modalProduct = null;
        $this->modalErrorMessage = null;
    }

    public function confirmModalAddToCart()
    {
        if (! $this->modalProduct || ! $this->modalStartDate || ! $this->modalEndDate) {
            return;
        }

        $start = Carbon::parse($this->modalStartDate);
        $end = Carbon::parse($this->modalEndDate);

        if ($end->lt($start)) {
            $this->modalErrorMessage = 'Tanggal selesai harus setelah tanggal mulai.';
            return;
        }

        $maxUnits = $this->modalProduct->getAvailableUnitsForPeriod($start, $end);
        $this->modalAvailableUnits = $maxUnits;

        $cart = session()->get('cart', []);
        $hash = md5($this->modalProduct->id . $start->format('Y-m-d') . $end->format('Y-m-d'));
        $existingQty = isset($cart[$hash]) ? (int) $cart[$hash]['quantity'] : 0;

        if ($maxUnits < 1) {
            $this->modalErrorMessage = 'Stok penuh pada rentang tanggal yang dipilih. Silakan pilih tanggal lain.';
            return;
        }

        if ($this->modalQuantity < 1 || ($existingQty + $this->modalQuantity) > $maxUnits) {
            $this->modalErrorMessage = "Jumlah unit melebihi stok tersedia pada tanggal tersebut (tersisa {$maxUnits} unit).";
            return;
        }

        $durationDays = $start->diffInDays($end) + 1;
        $subtotal = $this->modalProduct->rental_price_per_day * $durationDays * $this->modalQuantity;

        if (isset($cart[$hash])) {
            $cart[$hash]['quantity'] += $this->modalQuantity;
            $cart[$hash]['subtotal'] = $this->modalProduct->rental_price_per_day * $cart[$hash]['duration_days'] * $cart[$hash]['quantity'];
        } else {
            $cart[$hash] = [
                'hash' => $hash,
                'product_id' => $this->modalProduct->id,
                'name' => $this->modalProduct->name,
                'price' => $this->modalProduct->rental_price_per_day,
                'deposit_fee' => $this->modalProduct->deposit_fee,
                'main_image' => $this->modalProduct->main_image,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'duration_days' => $durationDays,
                'quantity' => $this->modalQuantity,
                'subtotal' => $subtotal,
            ];
        }

        session()->put('cart', $cart);
        $this->dispatch('cart-updated');
        $this->showModal = false;

        $periodStr = $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y');
        session()->flash('cart_success', "{$this->modalProduct->name} berhasil ditambahkan ke keranjang ({$periodStr})!");
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

        $availableUnits = $product->getAvailableUnitsForPeriod($start, $end);
        $cart = session()->get('cart', []);
        $hash = md5($product->id . $start->format('Y-m-d') . $end->format('Y-m-d'));
        $currentQty = isset($cart[$hash]) ? (int) $cart[$hash]['quantity'] : 0;

        if ($availableUnits < 1 || ($currentQty + 1) > $availableUnits) {
            session()->flash('error', "Stok {$product->name} tidak mencukupi untuk periode tanggal tersebut (tersisa {$availableUnits} unit).");
            return;
        }

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
                      AND o.status NOT IN (\'batal\', \'dibatalkan\', \'ditolak\', \'selesai\')
                      AND o.rental_start_date <= ?
                      AND o.rental_end_date >= ?
                ) as booked_units_count', [$end, $start]);

            if ($this->availableOnly) {
                $productsQuery->havingRaw('(
                    (SELECT COUNT(*) FROM equipment_units eu WHERE eu.product_id = products.id AND eu.status != \'rusak\')
                    -
                    (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.product_id = products.id AND o.client_id = products.client_id AND o.status NOT IN (\'batal\', \'dibatalkan\', \'ditolak\', \'selesai\') AND o.rental_start_date <= ? AND o.rental_end_date >= ?)
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $client = $this->resolveClient();
        $subscription = $client->activeSubscription()->first();

        $query = Product::with(['category'])
            ->withCount('units')
            ->withCount(['units as available_units_count' => function ($q) {
                $q->where('status', 'tersedia');
            }])
            ->where('client_id', $client->id);

        // Filter: Pencarian Nama atau Deskripsi
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Filter: Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Filter: Status (aktif / nonaktif)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $categories = Category::where('client_id', $client->id)
            ->orderBy('name')
            ->get();

        $limit = $this->planProductLimit($subscription?->plan_name);
        $used = Product::where('client_id', $client->id)->count();

        return view('admin.products.index', compact('client', 'subscription', 'products', 'categories', 'limit', 'used'));
    }

    public function store(Request $request)
    {
        $client = $this->resolveClient();

        $limit = $this->planProductLimit($client->activeSubscription()->first()?->plan_name);
        $used = Product::where('client_id', $client->id)->count();

        if ($limit !== null && $used >= $limit) {
            throw ValidationException::withMessages([
                'name' => "Batas kuota produk paket tercapai ({$limit} jenis produk). Hapus produk yang tidak terpakai atau upgrade paket langganan Anda.",
            ]);
        }

        $validated = $this->validateProduct($request);

        $category = Category::where('id', $validated['category_id'])
            ->where('client_id', $client->id)
            ->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => 'Kategori yang dipilih tidak valid atau bukan milik toko Anda.',
            ]);
        }

        $mainImagePath = $this->handleImageUpload($request);

        $productData = [
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($client->id, $validated['name']),
            'description' => $validated['description'] ?? null,
            'rental_price_per_day' => $validated['rental_price_per_day'],
            'deposit_fee' => $validated['deposit_fee'] ?? 0,
            'late_fee_per_hour' => $validated['late_fee_per_hour'],
            'main_image' => $mainImagePath,
            'identity_guarantee_requirements' => $validated['identity_guarantee_requirements'] ?? null,
            'status' => $validated['status'],
        ];

        // Sinkronkan sequence PostgreSQL jika driver adalah pgsql agar tidak terjadi duplicate key products_pkey
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            try {
                \Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence('products', 'id'), COALESCE((SELECT MAX(id) FROM products), 1))");
            } catch (\Throwable $t) {
                // Abaikan jika bukan PostgreSQL standar
            }
        }

        try {
            $product = Product::create($productData);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            if (str_contains($e->getMessage(), 'products_pkey')) {
                \Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence('products', 'id'), COALESCE((SELECT MAX(id) FROM products), 1))");
                $product = Product::create($productData);
            } else {
                throw $e;
            }
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk "' . $product->name . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $client = $this->resolveClient();
        $this->authorizeProduct($client, $product);

        $validated = $this->validateProduct($request, $product->id);

        $category = Category::where('id', $validated['category_id'])
            ->where('client_id', $client->id)
            ->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => 'Kategori yang dipilih tidak valid atau bukan milik toko Anda.',
            ]);
        }

        $mainImagePath = $this->handleImageUpload($request);

        $updateData = [
            'category_id' => $category->id,
            'name' => $validated['name'],
            'slug' => $product->name === $validated['name'] 
                ? $product->slug 
                : $this->uniqueSlug($client->id, $validated['name'], $product->id),
            'description' => $validated['description'] ?? null,
            'rental_price_per_day' => $validated['rental_price_per_day'],
            'deposit_fee' => $validated['deposit_fee'] ?? 0,
            'late_fee_per_hour' => $validated['late_fee_per_hour'],
            'identity_guarantee_requirements' => $validated['identity_guarantee_requirements'] ?? null,
            'status' => $validated['status'],
        ];

        if ($mainImagePath) {
            if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                Storage::disk('public')->delete($product->main_image);
            }
            $updateData['main_image'] = $mainImagePath;
        }

        $product->update($updateData);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk "' . $product->name . '" berhasil diperbarui.');
    }

    public function toggleStatus(Product $product)
    {
        $client = $this->resolveClient();
        $this->authorizeProduct($client, $product);

        $newStatus = $product->status === 'aktif' ? 'nonaktif' : 'aktif';
        $product->update(['status' => $newStatus]);

        $label = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk "' . $product->name . '" berhasil ' . $label . '.');
    }

    public function destroy(Product $product)
    {
        $client = $this->resolveClient();
        $this->authorizeProduct($client, $product);

        // Cek constraint ke tabel order_items (RESTRICT)
        $hasOrders = OrderItem::where('product_id', $product->id)->exists();

        if ($hasOrders) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', "Produk \"{$product->name}\" tidak dapat dihapus karena sudah memiliki riwayat pesanan. Silakan ubah status produk menjadi 'Nonaktif' agar tidak muncul lagi di katalog.");
        }

        // Hapus unit fisik terkait jika ada
        $product->units()->delete();

        // Hapus file gambar jika ada di public storage
        if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
            Storage::disk('public')->delete($product->main_image);
        }

        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk "' . $name . '" berhasil dihapus.');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|integer',
            'rental_price_per_day' => 'required|numeric|min:0',
            'late_fee_per_hour' => 'required|numeric|min:0',
            'deposit_fee' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:3000',
            'identity_guarantee_requirements' => 'nullable|string|max:1000',
            'status' => 'required|in:aktif,nonaktif',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 150 karakter.',
            'category_id.required' => 'Silakan pilih kategori produk.',
            'rental_price_per_day.required' => 'Harga sewa per hari wajib diisi.',
            'rental_price_per_day.numeric' => 'Harga sewa harus berupa angka.',
            'rental_price_per_day.min' => 'Harga sewa minimal Rp 0.',
            'late_fee_per_hour.required' => 'Tarif denda per jam wajib diisi.',
            'late_fee_per_hour.numeric' => 'Tarif denda per jam harus berupa angka.',
            'late_fee_per_hour.min' => 'Tarif denda per jam minimal Rp 0.',
            'status.required' => 'Status produk wajib dipilih.',
            'status.in' => 'Status produk hanya boleh Aktif atau Nonaktif.',
            'image.image' => 'File foto harus berupa gambar valid.',
            'image.mimes' => 'Format foto yang didukung: JPEG, PNG, JPG, WEBP.',
            'image.max' => 'Ukuran foto maksimal 2MB per gambar.',
            'images.*.image' => 'Setiap file foto harus berupa gambar valid.',
            'images.*.mimes' => 'Format foto yang didukung: JPEG, PNG, JPG, WEBP.',
            'images.*.max' => 'Ukuran foto maksimal 2MB per gambar.',
        ]);
    }

    private function handleImageUpload(Request $request): ?string
    {
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            if (! empty($files) && isset($files[0]) && $files[0]->isValid()) {
                return $files[0]->store('products', 'public');
            }
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            return $request->file('image')->store('products', 'public');
        }

        return null;
    }

    private function uniqueSlug(int $clientId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $counter = 2;

        while (Product::where('client_id', $clientId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function planProductLimit(?string $planName): ?int
    {
        $plan = mb_strtolower((string) $planName);

        if ($plan === '') {
            return config('packages.starter.limits.products', 10);
        }

        if (str_contains($plan, 'starter')) {
            return config('packages.starter.limits.products', 10);
        }

        if (str_contains($plan, 'business')) {
            return config('packages.business.limits.products', 50);
        }

        if (str_contains($plan, 'professional') || str_contains($plan, 'pro') || str_contains($plan, 'enterprise')) {
            return config('packages.professional.limits.products', null);
        }

        return config('packages.starter.limits.products', 10);
    }

    private function resolveClient(): Client
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko rental manapun.');
        }

        return $client;
    }

    private function authorizeProduct(Client $client, Product $product): void
    {
        if ($product->client_id !== $client->id) {
            abort(403, 'Anda tidak memiliki hak akses ke data produk toko ini.');
        }
    }
}

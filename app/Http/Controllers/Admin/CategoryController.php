<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        $client = $this->resolveClient();
        $subscription = $client->activeSubscription()->first();

        $categories = Category::withCount('products')
            ->where('client_id', $client->id)
            ->orderBy('name')
            ->get();

        $limit = $this->planCategoryLimit($subscription?->plan_name);
        $used = $categories->count();

        return view('admin.categories.index', compact('client', 'subscription', 'categories', 'limit', 'used'));
    }

    public function store(Request $request)
    {
        $client = $this->resolveClient();

        $data = $this->validated($request);

        $limit = $this->planCategoryLimit($client->activeSubscription()->first()?->plan_name);
        $used = Category::where('client_id', $client->id)->count();

        if ($limit !== null && $used >= $limit) {
            throw ValidationException::withMessages([
                'name' => "Batas paket tercapai ({$limit} kategori). Hapus kategori yang tidak dipakai atau upgrade paket.",
            ]);
        }

        $this->ensureNameIsUnique($client->id, $data['name']);

        Category::create([
            'client_id' => $client->id,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($client->id, $data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori "'.$data['name'].'" berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $client = $this->resolveClient();
        $this->authorizeCategory($client, $category);

        $data = $this->validated($request);
        $this->ensureNameIsUnique($client->id, $data['name'], $category->id);

        $category->update([
            'name' => $data['name'],
            'slug' => $category->name === $data['name']
                ? $category->slug
                : $this->uniqueSlug($client->id, $data['name'], $category->id),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori "'.$category->name.'" berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $client = $this->resolveClient();
        $this->authorizeCategory($client, $category);

        $products = $category->products()->count();

        if ($products > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "Kategori \"{$category->name}\" masih digunakan oleh {$products} produk. Pindahkan atau hapus produk tersebut terlebih dahulu.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori "'.$name.'" telah dihapus.');
    }

    /**
     * @return array{name: string, description: ?string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:60',
            'description' => 'nullable|string|max:200',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 60 karakter.',
            'description.max' => 'Deskripsi maksimal 200 karakter.',
        ]);
    }

    private function ensureNameIsUnique(int $clientId, string $name, ?int $ignoreId = null): void
    {
        $exists = Category::where('client_id', $clientId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => "Kategori \"{$name}\" sudah ada di akun Anda. Gunakan nama lain.",
            ]);
        }
    }

    private function uniqueSlug(int $clientId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $counter = 2;

        while (Category::where('client_id', $clientId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function planCategoryLimit(?string $planName): ?int
    {
        $plan = mb_strtolower((string) $planName);

        if ($plan === '') {
            return config('packages.business.limits.categories', 20);
        }

        if (str_contains($plan, 'starter')) {
            return config('packages.starter.limits.categories', 5);
        }

        if (str_contains($plan, 'business')) {
            return config('packages.business.limits.categories', 20);
        }

        if (str_contains($plan, 'professional') || str_contains($plan, 'pro') || str_contains($plan, 'enterprise')) {
            return config('packages.professional.limits.categories', null);
        }

        return config('packages.business.limits.categories', 20);
    }

    private function resolveClient(): Client
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko.');
        }

        return $client;
    }

    private function authorizeCategory(Client $client, Category $category): void
    {
        if ($category->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke kategori ini.');
        }
    }
}

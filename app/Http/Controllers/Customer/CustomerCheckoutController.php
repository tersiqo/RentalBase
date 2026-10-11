<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerCheckoutController extends Controller
{
    public function create(Request $request, string $subdomain)
    {
        $client = $this->resolveClient($subdomain);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('customer.home', $subdomain)
                ->with('error', 'Keranjang kamu masih kosong. Pilih alat dulu sebelum checkout.');
        }

        $requestedHashes = $request->query('items');
        $selectedHashes = $this->resolveSelectedHashes($cart, $requestedHashes);

        $selected = $this->resolveItems($cart, $selectedHashes);

        if ($selected->isEmpty()) {
            return redirect()->route('customer.home', $subdomain)
                ->with('error', 'Item yang dipilih tidak valid. Silakan pilih ulang dari keranjang.');
        }

        $period = $this->singlePeriod($selected);

        if (! $period) {
            return back()->withErrors([
                'period' => 'Semua item yang dipilih harus memiliki periode tanggal sewa yang sama. Kembali ke keranjang dan pilih item dalam satu periode.',
            ]);
        }

        $items = $this->decorate($selected, $period);
        $total = $items->sum('subtotal');

        session()->put('checkout_items', $selectedHashes);

        return view('customer.checkout', compact('client', 'items', 'period', 'total'));
    }

    public function direct(string $subdomain, Product $product)
    {
        $client = $this->resolveClient($subdomain);

        if ($product->client_id !== $client->id || $product->status !== 'aktif') {
            abort(404);
        }

        $available = $product->units()->where('status', 'tersedia')->count();

        if ($available < 1) {
            return redirect()->route('customer.product.show', ['subdomain' => $subdomain, 'product' => $product->id])
                ->with('error', 'Maaf, saat ini tidak ada unit yang tersedia untuk disewa.');
        }

        $start = now()->addDay()->startOfDay();
        $end = $start->copy()->addDays(1);
        $hash = md5($product->id.$start->format('Y-m-d').$end->format('Y-m-d'));

        $cart = session()->get('cart', []);

        $cart[$hash] = [
            'hash' => $hash,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->rental_price_per_day,
            'deposit_fee' => $product->deposit_fee,
            'main_image' => $product->main_image,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'duration_days' => 2,
            'quantity' => 1,
            'subtotal' => $product->rental_price_per_day * 2,
        ];

        session()->put('cart', $cart);

        return redirect()->route('customer.checkout', ['subdomain' => $subdomain]);
    }

    public function store(Request $request, string $subdomain)
    {
        $client = $this->resolveClient($subdomain);

        $request->validate([
            'selected_hashes' => 'nullable|array',
            'items' => 'nullable|array',
            'items.*.quantity' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shipping_address' => 'required|string|min:10',
            'full_name' => 'required|string|max:255',
            'identity_number' => 'required|string|min:8|max:30',
            'identity_address' => 'required|string|min:10',
            'identity_document_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'face_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'terms' => 'required|accepted',
        ], [
            'selected_hashes.required' => 'Item yang dipilih tidak ditemukan. Silakan ulangi checkout.',
            'items.*.quantity.required' => 'Jumlah unit sewa wajib diisi.',
            'items.*.quantity.min' => 'Jumlah unit sewa minimal 1.',
            'start_date.required' => 'Tanggal mulai sewa wajib diisi.',
            'end_date.required' => 'Tanggal selesai sewa wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
            'shipping_address.required' => 'Alamat pengiriman / rincian lokasi wajib diisi.',
            'shipping_address.min' => 'Alamat pengiriman terlalu singkat.',
            'full_name.required' => 'Nama lengkap sesuai KTP wajib diisi.',
            'identity_number.required' => 'Nomor KTP / NIK wajib diisi.',
            'identity_number.min' => 'Nomor KTP minimal 8 digit.',
            'identity_address.required' => 'Alamat sesuai KTP wajib diisi.',
            'identity_address.min' => 'Alamat sesuai KTP minimal 10 karakter.',
            'identity_document_image.required' => 'Foto KTP wajib diunggah.',
            'identity_document_image.image' => 'Foto KTP harus berupa gambar.',
            'face_image.required' => 'Foto swafoto (selfie memegang KTP) wajib diunggah.',
            'face_image.image' => 'Foto swafoto harus berupa gambar.',
            'terms.accepted' => 'Anda harus menyetujui ketentuan dan jaminan sewa.',
        ]);

        $cart = session()->get('cart', []);
        $selectedHashes = $request->input('selected_hashes', []);

        // Filter out empty/null/undefined values
        $selectedHashes = array_values(array_filter((array) $selectedHashes, fn ($h) => !empty($h) && $h !== 'undefined'));

        if (empty($selectedHashes)) {
            $selectedHashes = (array) session()->get('checkout_items', []);
            $selectedHashes = array_values(array_filter($selectedHashes, fn ($h) => !empty($h) && $h !== 'undefined'));
        }

        if (empty($selectedHashes)) {
            $selectedHashes = array_keys($cart);
        }

        $selected = $this->resolveItems($cart, $selectedHashes);

        if ($selected->isEmpty() && !empty($cart)) {
            $selected = collect($cart);
            $selectedHashes = array_keys($cart);
        }

        if ($selected->isEmpty()) {
            return back()->withErrors(['items' => 'Item tidak cocok dengan keranjang. Harap ulangi checkout dari keranjang.']);
        }

        $period = $this->singlePeriod($selected);

        if (! $period) {
            return back()->withErrors(['period' => 'Semua item yang dipilih harus memiliki periode tanggal sewa yang sama.']);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $durationDays = max(1, $startDate->diffInDays($endDate) + 1);

        $requested = $request->input('items', []);
        $lines = [];
        $totalAmount = 0;

        foreach ($selected as $hash => $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('client_id', $client->id)
                ->where('status', 'aktif')
                ->first();

            if (! $product) {
                return back()->withErrors(['items' => 'Produk "'.$item['name'].'" tidak tersedia lagi.']);
            }

            $available = $product->units()->where('status', 'tersedia')->count();
            $itemHash = $item['hash'] ?? $hash;
            $quantity = (int) ($requested[$hash]['quantity'] ?? $requested[$itemHash]['quantity'] ?? $item['quantity'] ?? 1);

            if ($quantity < 1) {
                $quantity = 1;
            }

            if ($quantity > $available) {
                return back()->withErrors([
                    'items' => 'Stok "'.$item['name'].'" tidak mencukupi. Sisa unit tersedia: '.$available.'.',
                ]);
            }

            $subtotal = $durationDays * $item['price'] * $quantity;
            $totalAmount += $subtotal;

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price_per_day' => $item['price'],
                'subtotal' => $subtotal,
            ];
        }

        DB::beginTransaction();
        try {
            $docPath = $request->file('identity_document_image')->store('identity_guarantees', 'public');
            $facePath = $request->file('face_image')->store('identity_guarantees', 'public');

            $orderNumber = 'ORD-'.strtoupper(Str::random(5)).'-'.date('Ymd');

            $order = Order::create([
                'client_id' => $client->id,
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'rental_start_date' => $startDate,
                'rental_end_date' => $endDate,
                'duration_days' => $durationDays,
                'total_amount' => $totalAmount,
                'shipping_address' => $request->shipping_address,
                'status' => 'menunggu_konfirmasi',
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'price_per_day' => $line['price_per_day'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            IdentityGuarantee::create([
                'order_id' => $order->id,
                'customer_id' => auth()->id(),
                'full_name' => $request->full_name,
                'identity_number' => $request->identity_number,
                'identity_document_image' => $docPath,
                'face_image' => $facePath,
                'identity_address' => $request->identity_address,
                'status' => 'menunggu',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal membuat pesanan: '.$e->getMessage()]);
        }

        // Remove processed items from cart
        foreach ($selectedHashes as $hash) {
            unset($cart[$hash]);
        }
        session()->put('cart', $cart);
        session()->forget('checkout_items');

        return redirect()->route('customer.orders')
            ->with('success', 'Pesanan #'.$orderNumber.' berhasil dibuat! Jaminan identitas Anda sedang diverifikasi admin. Pembayaran dibuka setelah verifikasi disetujui.');
    }

    protected function resolveClient(string $subdomain): Client
    {
        return Client::where('subdomain', $subdomain)
            ->where('status', 'aktif')
            ->firstOrFail();
    }

    protected function resolveSelectedHashes(array $cart, $requestedHashes): array
    {
        $keys = array_keys($cart);

        if (is_array($requestedHashes)) {
            $intersect = array_values(array_intersect($requestedHashes, $keys));
        } else {
            $intersect = [];
        }

        if (empty($intersect)) {
            $fromSession = session()->get('checkout_items', []);
            $intersect = array_values(array_intersect($fromSession, $keys));
        }

        return $intersect ?: $keys;
    }

    protected function resolveItems(array $cart, array $hashes): Collection
    {
        return collect($cart)->filter(fn ($item, $hash) => in_array($hash, $hashes, true));
    }

    protected function singlePeriod(Collection $items): ?array
    {
        $first = $items->first();

        foreach ($items as $item) {
            if ($item['start_date'] !== $first['start_date'] || $item['end_date'] !== $first['end_date']) {
                return null;
            }
        }

        $start = Carbon::parse($first['start_date']);
        $end = Carbon::parse($first['end_date']);

        return [
            'start_date' => $first['start_date'],
            'end_date' => $first['end_date'],
            'days' => max(1, $start->diffInDays($end) + 1),
        ];
    }

    protected function decorate(Collection $items, array $period): Collection
    {
        return $items->map(function ($item, $hash) use ($period) {
            $product = Product::where('id', $item['product_id'])->first();
            $available = $product ? $product->units()->where('status', 'tersedia')->count() : 0;
            $quantity = $available < 1 ? 0 : min((int) $item['quantity'], $available);

            $imageUrl = ! empty($item['main_image'])
                ? Storage::url($item['main_image'])
                : null;

            $itemHash = ! empty($item['hash']) ? $item['hash'] : (is_string($hash) && strlen($hash) > 10 ? $hash : md5($item['product_id'].$item['start_date'].$item['end_date']));

            return array_merge($item, [
                'hash' => $itemHash,
                'image_url' => $imageUrl,
                'available_units' => $available,
                'quantity' => $quantity,
                'subtotal' => $item['price'] * $period['days'] * $quantity,
            ]);
        })->values();
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Events\NewOrderPlaced;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerCheckoutController extends Controller
{
    public function create(Request $request, string $subdomain)
    {
        if (Auth::check() && Auth::user()?->role !== 'customer') {
            return redirect()->route('customer.home', $subdomain)
                ->with('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');
        }

        $client = $this->resolveClient($subdomain);

        $cart = session()->get('cart', []);

        // Filter item keranjang khusus milik tenant ini
        $cart = collect($cart)->filter(function ($item) use ($client) {
            if (isset($item['client_id'])) {
                return (int) $item['client_id'] === (int) $client->id;
            }
            if (isset($item['product_id'])) {
                $prod = Product::find($item['product_id']);

                return $prod && (int) $prod->client_id === (int) $client->id;
            }

            return false;
        })->all();

        if (empty($cart)) {
            return redirect()->route('customer.home', $subdomain)
                ->with('error', 'Keranjang belanja kamu di toko ini masih kosong.');
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

        /** @var User|null $user */
        $user = Auth::user();
        $profileAddress = $user?->address ? trim($user->address) : null;

        $lastOrder = null;
        if ($user) {
            $lastOrder = Order::where('user_id', $user->id)
                ->where('shipping_method', Order::SHIPPING_KURIR)
                ->whereNotNull('shipping_address')
                ->where('shipping_address', '!=', '')
                ->where('shipping_address', 'not like', 'Ambil di toko%')
                ->latest('id')
                ->first();
        }

        $lastOrderAddress = ($lastOrder && trim($lastOrder->shipping_address) !== $profileAddress)
            ? trim($lastOrder->shipping_address)
            : null;

        $lastIdentity = $user
            ? IdentityGuarantee::where('customer_id', $user->id)->latest('id')->first()
            : null;

        return view('customer.checkout', compact(
            'client',
            'items',
            'period',
            'total',
            'profileAddress',
            'lastOrderAddress',
            'lastIdentity'
        ));
    }

    public function direct(string $subdomain, Product $product)
    {
        if (Auth::check() && Auth::user()?->role !== 'customer') {
            return redirect()->route('customer.home', $subdomain)
                ->with('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');
        }

        $client = $this->resolveClient($subdomain);

        if ($product->client_id !== $client->id || $product->status !== 'aktif') {
            abort(404);
        }

        $start = now()->addDay()->startOfDay();
        $end = $start->copy()->addDays(1);
        $available = $product->getAvailableUnitsForPeriod($start, $end);

        if ($available < 1) {
            $found = false;
            for ($i = 1; $i <= 30; $i++) {
                $testStart = now()->addDays($i)->startOfDay();
                $testEnd = $testStart->copy()->addDays(1);
                if ($product->getAvailableUnitsForPeriod($testStart, $testEnd) >= 1) {
                    $start = $testStart;
                    $end = $testEnd;
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                return redirect()->route('customer.product.show', ['subdomain' => $subdomain, 'product' => $product->id])
                    ->with('error', 'Maaf, seluruh unit produk ini sedang penuh disewa untuk 30 hari ke depan.');
            }
        }

        $hash = md5($product->id.$start->format('Y-m-d').$end->format('Y-m-d'));

        $cart = session()->get('cart', []);

        $durationDays = max(1, (int) $start->diffInDays($end));

        $cart[$hash] = [
            'hash' => $hash,
            'client_id' => $client->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->rental_price_per_day,
            'deposit_fee' => $product->deposit_fee,
            'main_image' => $product->main_image,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'duration_days' => $durationDays,
            'quantity' => 1,
            'subtotal' => $product->rental_price_per_day * $durationDays,
        ];

        session()->put('cart', $cart);
        session()->put('checkout_items', [$hash]);

        return redirect()->route('customer.checkout', ['subdomain' => $subdomain]);
    }

    public function store(Request $request, string $subdomain)
    {
        if (Auth::check() && Auth::user()?->role !== 'customer') {
            return redirect()->route('customer.home', $subdomain)
                ->with('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');
        }

        $client = $this->resolveClient($subdomain);

        $useExistingIdentity = $request->boolean('use_existing_identity');
        /** @var User|null $user */
        $user = Auth::user();

        $lastIdentity = $user
            ? IdentityGuarantee::where('customer_id', $user->id)
                ->whereNotNull('identity_document_image')
                ->whereNotNull('face_image')
                ->latest('id')
                ->first()
            : null;

        $docFile = $lastIdentity?->identity_document_image;
        $faceFile = $lastIdentity?->face_image;

        $canReuseIdentity = $useExistingIdentity && $lastIdentity && ! empty($docFile) && ! empty($faceFile);

        if ($canReuseIdentity) {
            if (! $request->filled('full_name') && ! empty($lastIdentity->full_name)) {
                $request->merge(['full_name' => $lastIdentity->full_name]);
            }
            if (! $request->filled('identity_number') && ! empty($lastIdentity->identity_number)) {
                $request->merge(['identity_number' => $lastIdentity->identity_number]);
            }
            if (! $request->filled('identity_address') && ! empty($lastIdentity->identity_address)) {
                $request->merge(['identity_address' => $lastIdentity->identity_address]);
            }
        }

        $request->validate([
            'selected_hashes' => 'nullable|array',
            'items' => 'nullable|array',
            'items.*.quantity' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'rental_time' => ['nullable', 'date_format:H:i'],
            'shipping_address' => 'required|string|min:10',
            'shipping_method' => ['required', Rule::in([Order::SHIPPING_KURIR, Order::SHIPPING_AMBIL_TOKO])],
            'whatsapp_number' => 'nullable|string|max:30',
            'save_address_to_profile' => 'nullable',
            'full_name' => 'required|string|max:255',
            'identity_number' => 'required|string|min:8|max:30',
            'identity_address' => 'required|string|min:10',
            'identity_document_image' => $canReuseIdentity ? 'nullable|image|mimes:jpeg,png,jpg|max:2048' : 'required|image|mimes:jpeg,png,jpg|max:2048',
            'face_image' => $canReuseIdentity ? 'nullable|image|mimes:jpeg,png,jpg|max:2048' : 'required|image|mimes:jpeg,png,jpg|max:2048',
            'terms' => 'required|accepted',
        ], [
            'selected_hashes.required' => 'Item yang dipilih tidak ditemukan. Silakan ulangi checkout.',
            'items.*.quantity.required' => 'Jumlah unit sewa wajib diisi.',
            'items.*.quantity.min' => 'Jumlah unit sewa minimal 1.',
            'start_date.required' => 'Tanggal mulai sewa wajib diisi.',
            'end_date.required' => 'Tanggal selesai sewa wajib diisi.',
            'end_date.after' => 'Tanggal selesai sewa minimal 1 hari (H+1) setelah tanggal mulai sewa.',
            'rental_time.date_format' => 'Format jam sewa harus berupa jam:menit (contoh 14:00).',
            'shipping_address.required' => 'Alamat pengiriman / rincian lokasi wajib diisi.',
            'shipping_address.min' => 'Alamat pengiriman terlalu singkat.',
            'shipping_method.required' => 'Metode penerimaan wajib dipilih.',
            'shipping_method.in' => 'Metode penerimaan tidak valid.',
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

        $startDay = Carbon::parse($request->start_date)->startOfDay();
        if ($startDay->isToday() && $request->filled('rental_time')) {
            $selectedDateTime = Carbon::parse($request->start_date.' '.$request->rental_time);
            // Beri toleransi 15 menit agar saat customer memilih jam sekarang atau proses isi form beberapa menit tidak memicu false-positive error
            if ($selectedDateTime->lt(now()->subMinutes(15))) {
                return back()->withErrors([
                    'rental_time' => 'Jam mulai sewa tidak boleh menggunakan waktu yang sudah lewat untuk penyewaan hari ini.',
                ])->withInput();
            }
        }

        $rawCart = session()->get('cart', []);
        $cart = collect($rawCart)->filter(function ($item) use ($client) {
            if (isset($item['client_id'])) {
                return (int) $item['client_id'] === (int) $client->id;
            }
            if (isset($item['product_id'])) {
                $prod = Product::find($item['product_id']);

                return $prod && (int) $prod->client_id === (int) $client->id;
            }

            return false;
        })->all();

        $selectedHashes = $request->input('selected_hashes', []);

        // Filter out empty/null/undefined values
        $selectedHashes = array_values(array_filter((array) $selectedHashes, fn ($h) => ! empty($h) && $h !== 'undefined'));

        if (empty($selectedHashes)) {
            $selectedHashes = (array) session()->get('checkout_items', []);
            $selectedHashes = array_values(array_filter($selectedHashes, fn ($h) => ! empty($h) && $h !== 'undefined'));
        }

        if (empty($selectedHashes)) {
            $selectedHashes = array_keys($cart);
        }

        $selected = $this->resolveItems($cart, $selectedHashes);

        if ($selected->isEmpty() && ! empty($cart)) {
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

        $rentalTime = $request->filled('rental_time')
            ? (string) $request->input('rental_time')
            : now()->format('H:i');

        $endDay = Carbon::parse($request->end_date)->startOfDay();
        $durationDays = max(1, (int) $startDay->diffInDays($endDay));

        $startDate = Carbon::parse($request->start_date)->setTimeFromTimeString($rentalTime);
        $endDate = Carbon::parse($request->end_date)->setTimeFromTimeString($rentalTime);

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

            $available = $product->getAvailableUnitsForPeriod($startDate, $endDate);
            $itemHash = $item['hash'] ?? $hash;
            $quantity = (int) ($requested[$hash]['quantity'] ?? $requested[$itemHash]['quantity'] ?? $item['quantity'] ?? 1);

            if ($quantity < 1) {
                $quantity = 1;
            }

            if ($quantity > $available) {
                return back()->withErrors([
                    'items' => 'Stok "'.$item['name'].'" pada periode tanggal tersebut tidak mencukupi. Sisa unit tersedia: '.$available.'.',
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

        $order = null;
        $orderNumber = '';

        DB::beginTransaction();
        try {
            $disk = config('filesystems.default', 'public');

            if ($canReuseIdentity && ! $request->hasFile('identity_document_image')) {
                $docPath = $docFile;
            } else {
                $docPath = $request->file('identity_document_image')->store('identity_guarantees', $disk);
            }

            if ($canReuseIdentity && ! $request->hasFile('face_image')) {
                $facePath = $faceFile;
            } else {
                $facePath = $request->file('face_image')->store('identity_guarantees', $disk);
            }

            do {
                $orderNumber = 'ORD-'.strtoupper(Str::random(6)).'-'.date('Ymd');
            } while (Order::where('order_number', $orderNumber)->exists());

            $order = Order::create([
                'client_id' => $client->id,
                'user_id' => Auth::id(),
                'order_number' => $orderNumber,
                'rental_start_date' => $startDate,
                'rental_end_date' => $endDate,
                'duration_days' => $durationDays,
                'total_amount' => $totalAmount,
                'shipping_address' => $request->shipping_address,
                'shipping_method' => $request->shipping_method,
                'status' => 'menunggu_pembayaran',
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

            $guaranteeStatus = ($canReuseIdentity && in_array($lastIdentity->status, ['diverifikasi', 'dikembalikan', 'dititipkan']))
                ? 'diverifikasi'
                : 'menunggu';

            IdentityGuarantee::create([
                'order_id' => $order->id,
                'customer_id' => Auth::id(),
                'full_name' => $request->full_name,
                'identity_number' => $request->identity_number,
                'identity_document_image' => $docPath,
                'face_image' => $facePath,
                'identity_address' => $request->identity_address,
                'status' => $guaranteeStatus,
            ]);

            $user = Auth::user();
            if ($user instanceof User) {
                $userUpdates = [];

                $isKurir = $request->shipping_method === Order::SHIPPING_KURIR;
                $userAddressEmpty = empty(trim($user->address ?? ''));
                $shouldSaveAddress = $request->boolean('save_address_to_profile') || $userAddressEmpty;

                if ($isKurir && $shouldSaveAddress && ! empty($request->shipping_address)) {
                    $userUpdates['address'] = $request->shipping_address;
                }

                $waNumber = $request->input('whatsapp_number');
                if (! empty($waNumber) && empty(trim($user->phone ?? ''))) {
                    $userUpdates['phone'] = $waNumber;
                }

                if (! empty($userUpdates)) {
                    $user->update($userUpdates);
                }
            }

            DB::commit();

            if (class_exists(NewOrderPlaced::class)) {
                event(new NewOrderPlaced($order));
            }

            Cache::forget('admin_dashboard_counts_'.$client->id);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal membuat pesanan: '.$e->getMessage()]);
        }

        // Remove processed items from cart
        $globalCart = session()->get('cart', []);
        foreach ($selectedHashes as $hash) {
            unset($globalCart[$hash]);
        }
        session()->put('cart', $globalCart);
        session()->forget('checkout_items');

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Pesanan #'.$orderNumber.' berhasil dibuat! Silakan selesaikan pembayaran di bawah ini.');
    }

    protected function resolveClient(string $subdomain): Client
    {
        $client = Client::where('subdomain', $subdomain)->firstOrFail();

        if (in_array($client->status, ['suspended', 'nonaktif'])) {
            abort(response()->view('errors.tenant-suspended', compact('client'), 503));
        }

        if ($client->status !== 'aktif') {
            abort(404, 'Toko sedang tidak aktif.');
        }

        session()->put('active_client_id', $client->id);

        return $client;
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
            'days' => max(1, (int) $start->diffInDays($end)),
        ];
    }

    protected function decorate(Collection $items, array $period): Collection
    {
        return $items->map(function ($item, $hash) use ($period) {
            $product = Product::where('id', $item['product_id'])->first();
            $available = $product ? $product->getAvailableUnitsForPeriod($period['start_date'], $period['end_date']) : 0;
            $quantity = $available < 1 ? 0 : min((int) $item['quantity'], $available);

            $imageUrl = ! empty($item['main_image'])
                ? storage_url($item['main_image'])
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

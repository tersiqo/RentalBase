<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\EquipmentUnit;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first() ?? \App\Models\User::first();
        }

        $client = $user?->client ?? \App\Models\Client::first();

        if (! $client) {
            abort(403, 'Akun tidak terhubung ke toko.');
        }

        $clientId = $client->id;
        $today = Carbon::today();

        // 1. Dashboard Summary Counts (dengan Cache)
        $dashboardCounts = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_counts_' . $clientId, 15, function () use ($clientId, $today) {
            $totalBookingBaru = Order::where('client_id', $clientId)
                ->whereIn('status', ['menunggu_konfirmasi', 'menunggu_pembayaran'])
                ->count();

            $bookingHariIni = Order::where('client_id', $clientId)
                ->whereDate('created_at', $today)
                ->count();

            $menungguVerifikasi = Payment::whereHas('order', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })->whereIn('status', ['menunggu_konfirmasi', 'menunggu_verifikasi'])->count();

            $totalDisewa = Order::where('client_id', $clientId)
                ->where('status', 'sedang_disewa')
                ->count();

            $pendapatanBulanIni = (float) Order::where('client_id', $clientId)
                ->whereIn('status', ['selesai', 'sedang_disewa', 'dikonfirmasi', 'siap_kirim', 'pembayaran_terverifikasi'])
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount');

            $totalProduk  = Product::where('client_id', $clientId)->count();
            $totalUnit    = EquipmentUnit::where('client_id', $clientId)->count();

            return compact(
                'totalBookingBaru',
                'bookingHariIni',
                'menungguVerifikasi',
                'totalDisewa',
                'pendapatanBulanIni',
                'totalProduk',
                'totalUnit'
            );
        });

        // 2. Tren Pendapatan 30 Hari Terakhir ($revenueChartData)
        $startDate = Carbon::today()->subDays(29)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $dailyRevenues = Order::where('client_id', $clientId)
            ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as order_date, SUM(total_amount) as total')
            ->groupBy('order_date')
            ->pluck('total', 'order_date')
            ->all();

        $revenueLabels = [];
        $revenueValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $dateKey = $day->format('Y-m-d');
            $revenueLabels[] = $day->translatedFormat('d M');
            $revenueValues[] = (float) ($dailyRevenues[$dateKey] ?? 0);
        }

        $revenueChartData = [
            'labels'        => $revenueLabels,
            'data'          => $revenueValues,
            'total_30_days' => array_sum($revenueValues),
        ];

        // 3. Utilisasi Unit Fisik / Fleet Status ($unitStatusData)
        $rawUnitCounts = EquipmentUnit::where('client_id', $clientId)
            ->selectRaw('LOWER(status) as unit_status, COUNT(*) as count')
            ->groupBy('unit_status')
            ->pluck('count', 'unit_status')
            ->all();

        $unitTersedia = (int) ($rawUnitCounts['tersedia'] ?? 0);
        $unitDisewa = (int) ($rawUnitCounts['disewa'] ?? 0);
        $unitMaintenance = (int) ($rawUnitCounts['maintenance'] ?? 0)
            + (int) ($rawUnitCounts['dicuci'] ?? 0)
            + (int) ($rawUnitCounts['perbaikan'] ?? 0)
            + (int) ($rawUnitCounts['sterilisasi'] ?? 0);
        $unitRusak = (int) ($rawUnitCounts['rusak'] ?? 0);
        $totalUnits = $unitTersedia + $unitDisewa + $unitMaintenance + $unitRusak;
        $utilizationRate = $totalUnits > 0 ? round(($unitDisewa / $totalUnits) * 100) : 0;

        $unitStatusData = [
            'tersedia'         => $unitTersedia,
            'disewa'           => $unitDisewa,
            'maintenance'      => $unitMaintenance,
            'rusak'            => $unitRusak,
            'total'            => $totalUnits,
            'utilization_rate' => $utilizationRate,
            'labels'           => ['Tersedia', 'Disewa', 'Sterilisasi / Cuci', 'Rusak'],
            'data'             => [$unitTersedia, $unitDisewa, $unitMaintenance, $unitRusak],
        ];

        // 4. Top 5 Produk Paling Laris ($topProducts)
        $topProductsQuery = OrderItem::whereHas('order', function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak'])
                  ->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
            })
            ->selectRaw('product_id, SUM(quantity) as total_qty, COUNT(id) as rent_frequency')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with(['product.category'])
            ->get();

        if ($topProductsQuery->isEmpty()) {
            $topProductsQuery = OrderItem::whereHas('order', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId)
                      ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak']);
                })
                ->selectRaw('product_id, SUM(quantity) as total_qty, COUNT(id) as rent_frequency')
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->with(['product.category'])
                ->get();
        }

        $maxRented = (int) ($topProductsQuery->max('total_qty') ?? 1);
        if ($maxRented <= 0) {
            $maxRented = 1;
        }

        $topProducts = $topProductsQuery->map(function ($item) use ($maxRented) {
            $totalRented = (int) $item->total_qty;
            $percentage = round(($totalRented / $maxRented) * 100);

            return (object) [
                'id'            => $item->product_id,
                'product'       => $item->product,
                'name'          => $item->product?->name ?? 'Produk',
                'thumbnail'     => $item->product?->cover_image_url ?? 'https://placehold.co/100x100?text=Produk',
                'total_rented'  => $totalRented,
                'rental_count'  => (int) $item->rent_frequency,
                'category_name' => $item->product?->category?->name ?? 'Peralatan',
                'percentage'    => $percentage,
            ];
        });

        // 5. Jadwal Operasional Hari Ini ($todayShipments & $todayReturns)
        // Maksimal 4 tindakan teratas untuk tampilan dashboard yang proporsional
        $todayShipmentsQuery = Order::with(['user', 'orderItems.product'])
            ->where('client_id', $clientId)
            ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak', 'selesai', 'dikembalikan'])
            ->where(function ($q) use ($today) {
                $q->where('status', 'siap_kirim')
                  ->orWhereDate('rental_start_date', $today);
            })
            ->orderBy('rental_start_date', 'asc');

        $totalPengirimanHariIni = (clone $todayShipmentsQuery)->count();
        $todayShipments = $todayShipmentsQuery->limit(4)->get();

        $todayReturnsQuery = Order::with(['user', 'orderItems.product'])
            ->where('client_id', $clientId)
            ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak', 'selesai'])
            ->where(function ($q) use ($today) {
                $q->whereDate('rental_end_date', $today)
                  ->orWhere(function ($sub) use ($today) {
                      $sub->whereIn('status', ['sedang_disewa', 'dikirim'])
                          ->whereDate('rental_end_date', '<=', $today);
                  });
            })
            ->orderBy('rental_end_date', 'asc');

        $totalPengembalianHariIni = (clone $todayReturnsQuery)->count();
        $todayReturns = $todayReturnsQuery->limit(4)->get();

        // Subscription & Quotas (Single Source of Truth: config/packages.php)
        $subscription = $client->activeSubscription()->first() ?? $client->latestSubscription;
        $planKey      = strtolower(trim((string) ($subscription?->plan_name ?? 'starter')));
        $pkgConfig    = config("packages.{$planKey}") ?? config('packages.starter');
        $maxProduk    = array_key_exists('products', $pkgConfig['limits'] ?? []) ? $pkgConfig['limits']['products'] : null;
        $maxUnit      = $pkgConfig['limits']['units'] ?? null;
        $maxKategori  = $pkgConfig['limits']['categories'] ?? null;
        $maxAdmin     = $pkgConfig['limits']['admins'] ?? 1;

        $totalKategori = \App\Models\Category::where('client_id', $clientId)->count();
        $totalAdmin    = \App\Models\User::where('client_id', $clientId)->where('role', 'admin_rental')->count();

        // Pending Request jika ada pengajuan upgrade/perpanjang
        $pendingSubscription = Subscription::where('client_id', $clientId)
            ->whereIn('status', ['menunggu_konfirmasi', 'menunggu'])
            ->latest('id')
            ->first();

        // Hitung sisa hari aktif
        $daysRemaining = 0;
        $isExpired = false;
        if ($subscription && $subscription->end_date) {
            $endDate = Carbon::parse($subscription->end_date);
            if ($endDate->isPast()) {
                $isExpired = true;
                $daysRemaining = 0;
            } else {
                $daysRemaining = (int) Carbon::now()->diffInDays($endDate, false);
            }
        }

        $recentOrders = Order::with(['user', 'orderItems.product'])
            ->where('client_id', $clientId)
            ->latest()
            ->limit(7)
            ->get();

        return view('admin.dashboard', array_merge([
            'client'                   => $client,
            'user'                     => $user,
            'subscription'             => $subscription,
            'pkgConfig'                => $pkgConfig,
            'pendingSubscription'      => $pendingSubscription,
            'daysRemaining'            => $daysRemaining,
            'isExpired'                => $isExpired,
            'maxProduk'                => $maxProduk,
            'maxUnit'                  => $maxUnit,
            'maxKategori'              => $maxKategori,
            'maxAdmin'                 => $maxAdmin,
            'totalKategori'            => $totalKategori,
            'totalAdmin'               => $totalAdmin,
            'recentOrders'             => $recentOrders,
            'revenueChartData'         => $revenueChartData,
            'unitStatusData'           => $unitStatusData,
            'topProducts'              => $topProducts,
            'todayShipments'           => $todayShipments,
            'todayReturns'             => $todayReturns,
            'totalPengirimanHariIni'   => $totalPengirimanHariIni,
            'totalPengembalianHariIni' => $totalPengembalianHariIni,
        ], $dashboardCounts));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

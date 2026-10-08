<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\EquipmentUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();

        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first() ?? \App\Models\User::first();
        }

        $client = $user?->client ?? \App\Models\Client::first();

        if (! $client) {
            abort(403, 'Akun tidak terhubung ke toko.');
        }

        $clientId = $client->id;

        $dashboardCounts = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_counts_' . $clientId, 15, function () use ($clientId) {
            $today = Carbon::today();

            $totalBookingBaru = Order::where('client_id', $clientId)
                ->where('status', 'menunggu_pembayaran')
                ->count();

            $menungguVerifikasi = Payment::whereHas('order', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })->where('status', 'menunggu_konfirmasi')->count();

            $totalDisewa = Order::where('client_id', $clientId)
                ->where('status', 'sedang_disewa')
                ->count();

            $pendapatanBulanIni = Order::where('client_id', $clientId)
                ->whereIn('status', ['selesai', 'sedang_disewa', 'dikonfirmasi', 'siap_kirim', 'pembayaran_terverifikasi'])
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount');

            $totalProduk  = Product::where('client_id', $clientId)->count();
            $totalUnit    = EquipmentUnit::where('client_id', $clientId)->count();

            return compact(
                'totalBookingBaru',
                'menungguVerifikasi',
                'totalDisewa',
                'pendapatanBulanIni',
                'totalProduk',
                'totalUnit'
            );
        });

        $subscription = $client->activeSubscription()->first();
        $maxProduk    = $subscription ? (int) $subscription->max_products : null;

        $recentOrders = Order::with(['user', 'orderItems.product'])
            ->where('client_id', $clientId)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', array_merge([
            'client'       => $client,
            'user'         => $user,
            'subscription' => $subscription,
            'maxProduk'    => $maxProduk,
            'recentOrders' => $recentOrders,
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

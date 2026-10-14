<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function landing()
    {
        // Dummy data untuk Pricing
        $pricingPlans = [
            [
                'name' => 'Starter',
                'price' => 'Rp 150.000',
                'features' => ['Maks 50 transaksi', 'Manajemen barang', 'Katalog standar'],
                'highlight' => false,
            ],
            [
                'name' => 'Business',
                'price' => 'Rp 350.000',
                'features' => ['Transaksi tanpa batas', 'Manajemen pelanggan', 'Kustomisasi toko', 'Laporan dasar'],
                'highlight' => true,
            ],
            [
                'name' => 'Professional',
                'price' => 'Rp 750.000',
                'features' => ['Semua fitur Business', 'Laporan lanjutan', 'Dukungan prioritas', 'Multi cabang'],
                'highlight' => false,
            ],
        ];

        return view('welcome', compact('pricingPlans'));
    }

    public function myOrders(Request $request)
    {
        // 1. Resolve $client with a clear priority chain
        $client = null;

        // Priority 1: explicit ?client= query parameter
        if ($request->filled('client')) {
            $client = Client::where('subdomain', $request->query('client'))
                ->where('status', 'aktif')
                ->first();

            if ($client) {
                session()->put('active_client_id', $client->id);
                session()->put('active_client_subdomain', $client->subdomain);
            }
        }

        // Priority 2: session active_client_id or active_client_subdomain
        if (! $client && session()->has('active_client_id')) {
            $client = Client::find(session('active_client_id'));
        }
        if (! $client && session()->has('active_client_subdomain')) {
            $client = Client::where('subdomain', session('active_client_subdomain'))->first();
        }

        // Priority 3: inspect HTTP referer URL (e.g. navigated from /client/{subdomain})
        if (! $client && ($referer = $request->headers->get('referer'))) {
            if (preg_match('#/client/([^/?#]+)#', $referer, $matches)) {
                $client = Client::where('subdomain', $matches[1])->first();
                if ($client) {
                    session()->put('active_client_id', $client->id);
                    session()->put('active_client_subdomain', $client->subdomain);
                }
            }
        }

        // Priority 4: authenticated user's own client
        if (! $client && Auth::check() && Auth::user()->client_id) {
            $client = Auth::user()->client;
        }

        // Priority 5: first active client in DB (if available)
        if (! $client) {
            try {
                $client = Client::where('status', 'aktif')->first() ?? Client::first();
            } catch (\Throwable $e) {
                $client = null;
            }
        }

        // Priority 6: fallback object so view never crashes
        $activeSubdomain = session('active_client_subdomain') ?? ($client?->subdomain ?? 'babyrental');
        $activeNamaUsaha = $client?->nama_usaha ?? ($client?->name ?? 'BabyRent Malang');
        $client ??= (object) [
            'id'                => 1,
            'name'              => $activeNamaUsaha,
            'nama_usaha'        => $activeNamaUsaha,
            'subdomain'         => $activeSubdomain,
            'address'           => 'Jl. Contoh No. 12, Malang',
            'phone'             => '08123456789',
            'operational_hours' => '08:00 - 20:00',
        ];

        // Fetch real orders if user is authenticated and Order model exists
        if (Auth::check()) {
            try {
                $orders = Order::where('user_id', Auth::id())
                    ->with(['orderItems.product', 'client'])
                    ->latest()
                    ->get();
            } catch (\Throwable $e) {
                $orders = collect();
            }
        } else {
            $orders = [
                [
                    'id' => 'ID ORD-94821',
                    'date' => '10-12 Okt 2026',
                    'status' => 'Belum Dibayar',
                    'status_color' => 'bg-red-600 text-white',
                    'total' => 'Rp 320.000',
                    'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=200',
                    'items_count' => 3, 
                ],
                [
                    'id' => 'ID ORD-94710',
                    'date' => '05-08 Okt 2026',
                    'status' => 'Sedang Disewa',
                    'status_color' => 'bg-blue-600 text-white',
                    'total' => 'Rp 150.000',
                    'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&q=80&w=200',
                    'items_count' => 1,
                ],
                [
                    'id' => 'ID ORD-94503',
                    'date' => '28 Sep - 1 Okt 2026',
                    'status' => 'Selesai',
                    'status_color' => 'bg-green-600 text-white',
                    'total' => 'Rp 75.000',
                    'image' => 'https://images.unsplash.com/photo-1527011045970-132ed6dc480d?auto=format&fit=crop&q=80&w=200',
                    'items_count' => 1,
                ]
            ];
        }

        return view('customer.orders.my-orders', compact('orders', 'client'));
    }
}
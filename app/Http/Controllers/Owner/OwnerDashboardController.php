<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use Illuminate\Support\Carbon;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Otomatis ubah status subscription aktif yang sudah melewati End Date menjadi kadaluarsa
        Subscription::where('status', 'aktif')
            ->where('end_date', '<', $now)
            ->update(['status' => 'kadaluarsa']);

        // Kartu 1: Total tenant terverifikasi (sama dengan di halaman Client / Tenant)
        $totalTenant = Client::where('status', '!=', 'menunggu_verifikasi')->count();

        // Kartu 2: Tenant berstatus aktif
        $tenantAktif = Client::where('status', 'aktif')->count();

        // Kartu 3: Tenant berstatus suspended
        $tenantSuspended = Client::where('status', 'suspended')->count();

        // Kartu 4: Langganan segera berakhir (rentang 7 hari ke depan)
        $subscriptionSegera = Subscription::where('status', 'aktif')
            ->whereHas('client', function ($q) {
                $q->where('status', '!=', 'menunggu_verifikasi');
            })
            ->where('end_date', '<=', $now->copy()->addDays(7))
            ->where('end_date', '>=', $now)
            ->count();

        // Pendaftaran menunggu verifikasi (tambahan pemantauan platform)
        $pendaftaranMenunggu = ClientRegistration::where('status', 'menunggu_verifikasi')->count();

        // Tabel ringkasan tenant: hanya yang sudah diverifikasi, eager load latestSubscription
        $tenants = Client::where('status', '!=', 'menunggu_verifikasi')
            ->with(['latestSubscription', 'activeSubscription'])
            ->select(['id', 'business_name', 'subdomain', 'status', 'logo', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Data pendaftaran menunggu verifikasi terbaru
        $pendingRegistrations = ClientRegistration::where('status', 'menunggu_verifikasi')
            ->with(['client'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('owner.dashboard', compact(
            'totalTenant',
            'tenantAktif',
            'tenantSuspended',
            'subscriptionSegera',
            'pendaftaranMenunggu',
            'tenants',
            'pendingRegistrations'
        ));
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

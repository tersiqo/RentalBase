<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi customer yang sedang login.
     * Dibatasi strictly per-tenant toko aktif.
     */
    public function index(Request $request, ?string $subdomain = null)
    {
        $user = Auth::user();

        // 1. Resolve client toko aktif untuk konsistensi layout dan isolasi data per-tenant
        $client = null;
        if ($subdomain) {
            $client = Client::where('subdomain', $subdomain)->first();
        }
        if (! $client && $request->route('subdomain')) {
            $client = Client::where('subdomain', $request->route('subdomain'))->first();
        }
        if (! $client && $request->filled('client')) {
            $client = Client::where('subdomain', $request->query('client'))->first();
        }
        if (! $client && session()->has('active_client_id')) {
            $client = Client::find(session('active_client_id'));
        }
        if (! $client && session()->has('active_client_subdomain')) {
            $client = Client::where('subdomain', session('active_client_subdomain'))->first();
        }
        if (! $client && ($referer = $request->headers->get('referer'))) {
            if (preg_match('#/client/([^/?#]+)#', $referer, $matches)) {
                $client = Client::where('subdomain', $matches[1])->first();
            }
        }
        if (! $client && $user->client_id) {
            $client = Client::find($user->client_id);
        }
        if (! $client) {
            $client = Client::where('status', 'aktif')->first() ?? Client::first();
        }

        if ($client) {
            session()->put('active_client_id', $client->id);
            session()->put('active_client_subdomain', $client->subdomain);
        }

        // 2. Query dasar: WAJIB dibatasi hanya notifikasi milik user customer dan toko aktif!
        $query = Notification::where('user_id', $user->id);
        if ($client && isset($client->id)) {
            $query->where('client_id', $client->id);
        }

        // 3. Filter berdasarkan tab kategori
        $filter = $request->query('filter', 'all');

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'orders') {
            $query->whereIn('type', ['order', 'payment', 'identity']);
        } elseif ($filter === 'logistics') {
            $query->whereIn('type', ['shipment', 'return', 'damage']);
        }

        // 4. Hitung statistik untuk badge counter tab (strictly per-tenant)
        $unreadQuery = Notification::where('user_id', $user->id)->where('is_read', false);
        $allQuery    = Notification::where('user_id', $user->id);
        if ($client && isset($client->id)) {
            $unreadQuery->where('client_id', $client->id);
            $allQuery->where('client_id', $client->id);
        }

        $totalUnread = $unreadQuery->count();
        $totalAll    = $allQuery->count();

        // 5. Pagination 10 baris per halaman
        $notifications = $query->latest()->paginate(10)->withQueryString();

        return view('customer.notifications.index', compact(
            'notifications',
            'client',
            'filter',
            'totalUnread',
            'totalAll'
        ));
    }

    /**
     * Menandai satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Otorisasi: Customer tidak boleh membaca notifikasi milik customer lain!
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Kamu tidak memiliki hak akses ke notifikasi ini.');
        }

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pemberitahuan telah ditandai dibaca.',
            ]);
        }

        $targetUrl = $request->input('target_url') ?? $request->query('target') ?? route('customer.notifications.index');
        return redirect($targetUrl)->with('success', 'Pemberitahuan telah ditandai dibaca.');
    }

    /**
     * Menandai semua notifikasi customer sebagai telah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $query = Notification::where('user_id', Auth::id())
            ->where('is_read', false);

        $client = null;
        if ($request->filled('client_id')) {
            $client = Client::find($request->client_id);
        } elseif ($request->filled('client')) {
            $client = Client::where('subdomain', $request->client)->first();
        }

        if ($client && isset($client->id)) {
            $query->where('client_id', $client->id);
        }

        $query->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua pemberitahuan berhasil ditandai sebagai dibaca.',
            ]);
        }

        return redirect()->back()->with('success', 'Semua pemberitahuan berhasil ditandai sebagai dibaca.');
    }
}

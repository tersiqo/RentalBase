<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Tampilkan halaman pusat notifikasi operasional admin.
     */
    public function index(Request $request)
    {
        $client = $this->resolveClient();
        $currentTab = $request->query('tab', 'all');

        $baseQuery = Notification::where('client_id', $client->id);

        // Hitung total untuk badge setiap tab
        $countAll          = (clone $baseQuery)->count();
        $countUnread       = (clone $baseQuery)->where('is_read', false)->count();
        $countOrders       = (clone $baseQuery)->where('type', 'order')->count();
        $countVerification = (clone $baseQuery)->whereIn('type', ['payment', 'identity'])->count();
        $countReturns      = (clone $baseQuery)->whereIn('type', ['return', 'damage', 'shipment'])->count();
        $countSystem       = (clone $baseQuery)->whereIn('type', ['system', 'quota', 'info'])->count();

        // Terapkan filter berdasarkan tab yang aktif
        $query = clone $baseQuery;
        match ($currentTab) {
            'unread'       => $query->where('is_read', false),
            'orders'       => $query->where('type', 'order'),
            'verification' => $query->whereIn('type', ['payment', 'identity']),
            'returns'      => $query->whereIn('type', ['return', 'damage', 'shipment']),
            'system'       => $query->whereIn('type', ['system', 'quota', 'info']),
            default        => null,
        };

        $notifications = $query->latest()->paginate(10)->withQueryString();

        return view('admin.notifications.index', compact(
            'client',
            'notifications',
            'currentTab',
            'countAll',
            'countUnread',
            'countOrders',
            'countVerification',
            'countReturns',
            'countSystem'
        ));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $client = $this->resolveClient();
        $this->authorizeNotification($client, $notification);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            $unreadCount = Notification::where('client_id', $client->id)->where('is_read', false)->count();

            return response()->json([
                'success' => true,
                'message' => 'Pemberitahuan telah ditandai sebagai sudah dibaca.',
                'unread_count' => $unreadCount,
            ]);
        }

        if ($request->filled('redirect_to')) {
            $redirectTo = $request->input('redirect_to');
            // Pastikan URL aman mengarah ke internal panel admin
            if (str_starts_with($redirectTo, '/admin') || str_starts_with($redirectTo, url('/admin'))) {
                return redirect($redirectTo)->with('success', 'Pemberitahuan telah ditandai sebagai dibaca.');
            }
        }

        return redirect()->back()->with('success', 'Pemberitahuan berhasil ditandai sebagai sudah dibaca.');
    }

    /**
     * Tandai seluruh notifikasi toko sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $client = $this->resolveClient();

        Notification::where('client_id', $client->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua pemberitahuan operasional toko telah ditandai sebagai sudah dibaca.',
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Semua pemberitahuan toko berhasil ditandai sebagai sudah dibaca.');
    }

    /**
     * Dapatkan toko/klien aktif yang dikelola oleh admin login.
     */
    private function resolveClient(): Client
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun admin tidak terhubung ke toko manapun.');
        }

        return $client;
    }

    /**
     * Pastikan notifikasi yang diakses benar-benar milik toko admin ini (Isolasi Multi-Tenant).
     */
    private function authorizeNotification(Client $client, Notification $notification): void
    {
        if ((int) $notification->client_id !== (int) $client->id) {
            abort(403, 'Akses ditolak: Pemberitahuan ini bukan milik toko Anda.');
        }
    }
}

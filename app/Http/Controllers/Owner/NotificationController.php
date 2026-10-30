<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Tampilkan halaman pusat notifikasi platform untuk Owner.
     */
    public function index(Request $request)
    {
        $owner = Auth::user();
        if (! $owner || $owner->role !== 'owner') {
            abort(403, 'Akses ditolak: Halaman ini hanya untuk Owner Platform.');
        }

        $currentTab = $request->query('tab', 'all');

        // Query dasar hanya notifikasi milik Owner login
        $baseQuery = Notification::where('user_id', $owner->id)->with('client');

        // Hitung total untuk badge tab
        $countAll    = (clone $baseQuery)->count();
        $countUnread = (clone $baseQuery)->unread()->count();
        $countAlert  = (clone $baseQuery)->whereIn('type', ['alert', 'quota', 'registration', 'subscription', 'tenant_registration', 'subscription_renewal'])->count();

        // Terapkan filter tab
        $query = clone $baseQuery;
        match ($currentTab) {
            'unread' => $query->unread(),
            'alert'  => $query->whereIn('type', ['alert', 'quota', 'registration', 'subscription', 'tenant_registration', 'subscription_renewal']),
            default  => null,
        };

        $notifications = $query->latest()->paginate(10)->withQueryString();

        return view('owner.notifications.index', compact(
            'notifications',
            'currentTab',
            'countAll',
            'countUnread',
            'countAlert'
        ));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca dan arahkan ke deep link jika ada.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $owner = Auth::user();
        if (! $owner || $owner->role !== 'owner') {
            abort(403, 'Akses ditolak: Halaman ini hanya untuk Owner Platform.');
        }

        $this->authorizeOwnerNotification($notification);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            $unreadCount = Notification::where('user_id', $owner->id)->unread()->count();

            return response()->json([
                'success'      => true,
                'message'      => 'Notifikasi telah ditandai sebagai sudah dibaca.',
                'unread_count' => $unreadCount,
            ]);
        }

        if ($request->filled('redirect_to')) {
            $redirectTo = $request->input('redirect_to');
            if (str_starts_with($redirectTo, '/owner') || str_starts_with($redirectTo, url('/owner'))) {
                return redirect($redirectTo)->with('success', 'Notifikasi telah ditandai sebagai sudah dibaca.');
            }
        }

        return redirect()->back()->with('success', 'Notifikasi berhasil ditandai sebagai sudah dibaca.');
    }

    /**
     * Tandai seluruh notifikasi Owner sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $owner = Auth::user();
        if (! $owner || $owner->role !== 'owner') {
            abort(403, 'Akses ditolak: Halaman ini hanya untuk Owner Platform.');
        }

        Notification::where('user_id', $owner->id)
            ->unread()
            ->update(['is_read' => true]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Semua notifikasi platform telah ditandai sebagai sudah dibaca.',
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi platform berhasil ditandai sebagai sudah dibaca.');
    }

    /**
     * Pastikan notifikasi benar-benar milik Owner yang sedang login.
     */
    private function authorizeOwnerNotification(Notification $notification): void
    {
        if ((int) $notification->user_id !== (int) Auth::id()) {
            abort(403, 'Akses ditolak: Notifikasi ini bukan milik Anda.');
        }
    }
}

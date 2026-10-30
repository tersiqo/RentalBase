<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan daftar jejak rekam aktivitas internal toko rental.
     */
    public function index(Request $request)
    {
        $client = $this->resolveClient();

        // 1. Ambil input filter
        $filters = [
            'from'    => $request->query('from'),
            'to'      => $request->query('to'),
            'user_id' => $request->query('user_id'),
            'module'  => $request->query('module'),
            'search'  => $request->query('search'),
        ];

        // 2. Query dasar TERISOLASI KHUSUS untuk client toko ini (Multi-Tenant Isolation)
        $query = ActivityLog::where('client_id', $client->id)
            ->with(['user']);

        // 3. Terapkan filter pencarian
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                  ->orWhere('action', 'ILIKE', "%{$search}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(15)->withQueryString();

        // 4. Data pendukung untuk dropdown filter
        $staffMembers = User::where('client_id', $client->id)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $modules = ActivityLog::where('client_id', $client->id)
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        // 5. Metrik statistik cepat
        $totalLogs = ActivityLog::where('client_id', $client->id)->count();
        $logsToday = ActivityLog::where('client_id', $client->id)->whereDate('created_at', today())->count();

        return view('admin.activity-logs.index', compact(
            'client',
            'logs',
            'filters',
            'staffMembers',
            'modules',
            'totalLogs',
            'logsToday'
        ));
    }

    /**
     * Mengembalikan data log detail untuk modal pop-up Alpine.js.
     */
    public function show(ActivityLog $activityLog)
    {
        $client = $this->resolveClient();
        $this->authorizeActivityLog($client, $activityLog);

        $activityLog->load(['user']);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'          => $activityLog->id,
                    'created_at'  => $activityLog->created_at ? $activityLog->created_at->translatedFormat('l, d F Y - H:i:s') . ' WIB' : '-',
                    'action'      => $activityLog->action,
                    'module'      => $activityLog->module,
                    'description' => $activityLog->description,
                    'ip_address'  => $activityLog->ip_address ?? 'Tidak tercatat',
                    'user_agent'  => $activityLog->user_agent ?? 'Tidak tercatat',
                    'actor_name'  => $activityLog->user?->name ?? 'Sistem Otomatis',
                    'actor_role'  => $activityLog->user?->role ?? 'system',
                    'actor_email' => $activityLog->user?->email ?? '-',
                ],
            ]);
        }

        return redirect()->route('admin.activity-logs.index');
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
     * Pastikan log yang diakses benar-benar milik toko ini (Isolasi Multi-Tenant Mutlak).
     */
    private function authorizeActivityLog(Client $client, ActivityLog $activityLog): void
    {
        if ((int) $activityLog->client_id !== (int) $client->id) {
            abort(403, 'Akses ditolak: Rekaman log ini bukan milik toko Anda.');
        }
    }
}

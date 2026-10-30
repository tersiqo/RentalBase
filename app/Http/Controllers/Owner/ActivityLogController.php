<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan daftar jejak audit platform lintas tenant.
     */
    public function index(Request $request)
    {
        // 1. Ambil input filter dari URL query string
        $filters = [
            'from'      => $request->query('from'),
            'to'        => $request->query('to'),
            'client_id' => $request->query('client_id'),
            'user_id'   => $request->query('user_id'),
            'module'    => $request->query('module'),
            'search'    => $request->query('search'),
        ];

        // 2. Query log dengan Eager Loading relasi client dan user agar hemat memori (N+1 Problem)
        $logs = ActivityLog::with(['client', 'user'])
            ->filter($filters)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        // 3. Ambil data master untuk opsi dropdown filter
        $tenants = Client::orderBy('business_name')->get(['id', 'business_name', 'subdomain']);
        
        $actors = User::whereIn('role', ['owner', 'admin_rental'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $modules = ActivityLog::select('module')
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        // 4. Hitung ringkasan metrik audit log
        $totalLogs          = ActivityLog::count();
        $logsToday          = ActivityLog::whereDate('created_at', today())->count();
        $totalTenantsActive = Client::where('status', 'aktif')->count();

        return view('owner.activity-logs.index', compact(
            'logs',
            'filters',
            'tenants',
            'actors',
            'modules',
            'totalLogs',
            'logsToday',
            'totalTenantsActive'
        ));
    }

    /**
     * Mengembalikan detail data log teknis untuk Modal Alpine.js.
     */
    public function show(ActivityLog $activityLog)
    {
        // Eager load relasi pendukung
        $activityLog->load(['client', 'user']);

        // Jika diminta via AJAX / fetch API
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
                    'client_name' => $activityLog->client?->business_name ?? 'Platform Pusat (Non-Tenant)',
                    'subdomain'   => $activityLog->client?->subdomain ?? '-',
                    'actor_name'  => $activityLog->user?->name ?? 'Sistem Otomatis',
                    'actor_role'  => $activityLog->user?->role ?? 'system',
                    'actor_email' => $activityLog->user?->email ?? '-',
                ],
            ]);
        }

        return redirect()->route('owner.activity-logs.index');
    }
}

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Mail\TenantApproved;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OwnerRegistrationController extends Controller
{
    /**
     * Menampilkan daftar pendaftaran tenant baru.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = ClientRegistration::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('admin_name', 'like', "%{$search}%")
                  ->orWhere('admin_email', 'like', "%{$search}%")
                  ->orWhere('admin_phone', 'like', "%{$search}%");
            });
        }

        $sortable = [
            'business_name' => 'business_name',
            'admin_name'    => 'admin_name',
            'admin_phone'   => 'admin_phone',
            'plan_name'     => 'plan_name',
            'status'        => 'status',
            'created_at'    => 'created_at',
        ];

        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!array_key_exists($sort, $sortable)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $registrations = $query->with(['client'])
            ->orderBy($sortable[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        // Hitung total berdasarkan status untuk tab badge
        $counts = [
            'total'    => ClientRegistration::count(),
            'menunggu' => ClientRegistration::where('status', 'menunggu_verifikasi')->count(),
            'disetujui' => ClientRegistration::where('status', 'disetujui')->count(),
            'ditolak'  => ClientRegistration::where('status', 'ditolak')->count(),
        ];

        return view('owner.registrations.index', compact(
            'registrations',
            'status',
            'search',
            'counts',
            'sort',
            'direction'
        ));
    }

    /**
     * Memverifikasi pendaftaran tenant dan mengaktifkan akun user, client, dan subscription.
     */
    public function verify($id)
    {
        $reg = ClientRegistration::findOrFail($id);

        // Cegah pengiriman notifikasi ganda apabila request persetujuan diulang
        if ($reg->status === 'disetujui') {
            return redirect()->back()->with('info', 'Pendaftaran tenant ini sudah pernah diverifikasi sebelumnya.');
        }

        $targetUser = null;

        // Eksekusi transaksi database secara atomik
        DB::transaction(function () use ($reg, &$targetUser) {
            // Update status registrasi
            $reg->update([
                'status' => 'disetujui',
                'payment_status' => 'diverifikasi',
                'rejection_reason' => null,
                'reviewed_at' => Carbon::now(),
                'reviewed_by' => auth()->id(),
            ]);

            // Update status client menjadi aktif
            Client::where('subdomain', $reg->subdomain)->update([
                'status' => 'aktif',
            ]);

            // Update status user (admin rental) menjadi aktif
            User::where('email', $reg->admin_email)->update([
                'status' => 'aktif',
            ]);
            $targetUser = User::where('email', $reg->admin_email)->first();

            // Update status subscription menjadi aktif dan atur masa aktif mulai hari ini
            $client = $reg->client ?? Client::where('subdomain', $reg->subdomain)->first();
            $subscription = $client ? Subscription::where('client_id', $client->id)->latest()->first() : null;

            if ($subscription) {
                $now = Carbon::now();
                $subscription->update([
                    'status' => 'aktif',
                    'payment_status' => 'lunas',
                    'start_date' => $now->toDateString(),
                    'end_date' => $now->copy()->addMonth()->toDateString(),
                ]);
            }
        });

        // Kirim email pemberitahuan di luar transaksi database
        // Tangani kegagalan pengiriman email secara terpisah agar tidak membatalkan transaksi yang telah commit
        $emailSent = true;
        try {
            Mail::to($reg->admin_email)->send(
                new TenantApproved($reg, $targetUser)
            );
        } catch (\Throwable $e) {
            $emailSent = false;
            Log::error("Gagal mengirim email persetujuan pendaftaran tenant [ID: {$reg->id}, Email: {$reg->admin_email}]: " . $e->getMessage(), [
                'registration_id' => $reg->id,
                'business_name'   => $reg->business_name,
                'email'           => $reg->admin_email,
                'exception'       => $e,
            ]);
        }

        if ($emailSent) {
            return redirect()->back()->with('success', "Tenant {$reg->business_name} berhasil diverifikasi dan diaktifkan! Email pemberitahuan telah dikirimkan ke {$reg->admin_email}.");
        }

        return redirect()->back()->with('warning', "Tenant {$reg->business_name} berhasil diverifikasi dan diaktifkan, namun email pemberitahuan ke {$reg->admin_email} gagal dikirim. Silakan hubungi PIC secara langsung.");
    }

    /**
     * Menolak pendaftaran tenant dengan mencatat alasan penolakan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $reg = ClientRegistration::findOrFail($id);

        // Cegah tolak pendaftaran yang sudah disetujui dan aktif
        if ($reg->status === 'disetujui') {
            return redirect()->back()->with('error', 'Pendaftaran tenant yang sudah disetujui tidak dapat ditolak.');
        }

        DB::transaction(function () use ($reg, $request) {
            $reg->update([
                'status' => 'ditolak',
                'payment_status' => 'ditolak',
                'rejection_reason' => $request->input('rejection_reason'),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            Client::where('subdomain', $reg->subdomain)->update([
                'status' => 'ditolak',
            ]);

            User::where('email', $reg->admin_email)->update([
                'status' => 'ditolak',
            ]);

            $clientId = $reg->client?->id ?? Client::where('subdomain', $reg->subdomain)->value('id');
            if ($clientId) {
                Subscription::where('client_id', $clientId)->update([
                    'status' => 'ditolak',
                    'payment_status' => 'ditolak',
                ]);
            }
        });

        return redirect()->back()->with('success', "Pendaftaran tenant {$reg->business_name} telah ditolak.");
    }
}

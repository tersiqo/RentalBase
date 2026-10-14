<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        $registrations = $query->with(['client'])->orderByDesc('created_at')->paginate(10)->withQueryString();

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
            'counts'
        ));
    }

    /**
     * Memverifikasi pendaftaran tenant dan mengaktifkan akun user, client, dan subscription.
     */
    public function verify($id)
    {
        $reg = ClientRegistration::findOrFail($id);

        if ($reg->status === 'disetujui') {
            return redirect()->back()->with('info', 'Pendaftaran tenant ini sudah pernah diverifikasi sebelumnya.');
        }

        DB::transaction(function () use ($reg) {
            // Update status registrasi
            $reg->update([
                'status' => 'disetujui',
                'rejection_reason' => null,
            ]);

            // Update status client menjadi aktif
            if ($reg->client_id) {
                Client::where('id', $reg->client_id)->update([
                    'status' => 'aktif',
                ]);
            } else {
                Client::where('subdomain', $reg->subdomain)->update([
                    'status' => 'aktif',
                ]);
            }

            // Update status user (admin rental) menjadi aktif
            if ($reg->user_id) {
                User::where('id', $reg->user_id)->update([
                    'status' => 'aktif',
                ]);
            } else {
                User::where('email', $reg->admin_email)->update([
                    'status' => 'aktif',
                ]);
            }

            // Update status subscription menjadi aktif dan atur start/end date jika belum
            $subscription = null;
            if ($reg->subscription_id) {
                $subscription = Subscription::find($reg->subscription_id);
            } elseif ($reg->client_id) {
                $subscription = Subscription::where('client_id', $reg->client_id)->latest()->first();
            }

            if ($subscription) {
                $now = Carbon::now();
                $updateData = ['status' => 'aktif'];
                if (!$subscription->start_date) {
                    $updateData['start_date'] = $now->toDateString();
                    $updateData['end_date'] = $now->copy()->addMonth()->toDateString();
                }
                $subscription->update($updateData);
            }
        });

        return redirect()->back()->with('success', "Tenant {$reg->business_name} berhasil diverifikasi dan diaktifkan!");
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

        $reg->update([
            'status' => 'ditolak',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return redirect()->back()->with('success', "Pendaftaran tenant {$reg->business_name} telah ditolak.");
    }
}

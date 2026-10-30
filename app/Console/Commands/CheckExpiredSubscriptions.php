<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa masa aktif subscription tenant, menandai expired, menangani suspensi tenant, dan mengirim notifikasi masa aktif.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pengecekan masa aktif subscription tenant...');

        $now = Carbon::now();

        // Cari subscription yang sudah lewat masa berlaku tetapi belum ditandai kadaluarsa
        $expiredSubscriptions = Subscription::where('end_date', '<', $now)
            ->where('status', '!=', 'kadaluarsa')
            ->with('client')
            ->lazyById(100);

        $processedCount = 0;
        $suspendedClientsCount = 0;
        $failedCount = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                DB::transaction(function () use ($subscription, $now, &$processedCount, &$suspendedClientsCount) {
                    $lockedSub = Subscription::where('id', $subscription->id)->lockForUpdate()->first();

                    if (! $lockedSub || $lockedSub->status === 'kadaluarsa' || $lockedSub->end_date->gte($now)) {
                        return;
                    }

                    // Tandai subscription menjadi kadaluarsa
                    $lockedSub->update(['status' => 'kadaluarsa']);
                    $processedCount++;

                    $client = $lockedSub->client;
                    if (! $client) {
                        return;
                    }

                    // Kunci client untuk pengecekan status
                    $lockedClient = Client::where('id', $client->id)->lockForUpdate()->first();

                    // Periksa apakah client memiliki subscription lain yang masih aktif
                    $hasOtherActiveSub = Subscription::where('client_id', $lockedClient->id)
                        ->where('id', '!=', $lockedSub->id)
                        ->where('status', 'aktif')
                        ->where('end_date', '>=', $now)
                        ->exists();

                    if (! $hasOtherActiveSub) {
                        // Tidak ada subscription aktif lain -> ubah status client menjadi nonaktif/suspended
                        if ($lockedClient->status !== 'suspended' && $lockedClient->status !== 'nonaktif') {
                            $lockedClient->update(['status' => 'suspended']);
                            $suspendedClientsCount++;
                        }

                        // Kirim notifikasi peringatan masa aktif (idempoten: cek agar tidak dikirim ganda dalam 24 jam)
                        $this->sendExpirationNotifications($lockedClient, $lockedSub);
                    }
                });
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Gagal memproses subscription kadaluarsa #{$subscription->id}: " . $e->getMessage(), [
                    'subscription_id' => $subscription->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Error subscription #{$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("Pemeriksaan subscription selesai. {$processedCount} subscription ditandai kadaluarsa. {$suspendedClientsCount} tenant disuspensi. Gagal: {$failedCount}.");

        return Command::SUCCESS;
    }

    /**
     * Kirim notifikasi in-app kepada Admin tenant dan Owner Platform secara idempoten.
     */
    private function sendExpirationNotifications(Client $client, Subscription $subscription): void
    {
        // 1. Notifikasi ke Admin Tenant
        $adminUser = $client->adminUser ?? $client->primaryAdmin ?? $client->users()->where('role', 'admin_rental')->first();

        if ($adminUser) {
            $alreadyNotifiedAdmin = Notification::where('client_id', $client->id)
                ->where('user_id', $adminUser->id)
                ->where('type', 'system')
                ->where('title', 'like', '%Subscription Berakhir%')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if (! $alreadyNotifiedAdmin) {
                Notification::create([
                    'client_id' => $client->id,
                    'user_id'   => $adminUser->id,
                    'title'     => 'Masa Aktif Subscription Berakhir',
                    'message'   => "Masa aktif paket subscription toko Anda telah berakhir pada {$subscription->end_date->format('d M Y')}. Layanan toko Anda disuspensi sementara hingga perpanjangan diverifikasi.",
                    'type'      => 'system',
                    'is_read'   => false,
                ]);
            }
        }

        // 2. Notifikasi ke Seluruh Owner Platform
        $owners = User::where('role', 'owner')->get();
        foreach ($owners as $owner) {
            $alreadyNotifiedOwner = Notification::where('user_id', $owner->id)
                ->where('type', 'subscription')
                ->where('message', 'like', "%{$client->business_name}%")
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if (! $alreadyNotifiedOwner) {
                Notification::create([
                    'client_id' => $client->id,
                    'user_id'   => $owner->id,
                    'title'     => 'Subscription Tenant Berakhir: ' . $client->business_name,
                    'message'   => "Masa aktif subscription tenant {$client->business_name} telah kadaluarsa dan akun tenant telah disuspensi otomatis.",
                    'type'      => 'subscription',
                    'is_read'   => false,
                ]);
            }
        }
    }
}

<?php

namespace App\Listeners;

use App\Events\DamageCaseCreated;
use App\Events\DamageCaseResolved;
use App\Events\FineIssued;
use App\Events\IdentityApproved;
use App\Events\IdentityProofUploaded;
use App\Events\IdentityRejected;
use App\Events\NewOrderPlaced;
use App\Events\OrderConfirmed;
use App\Events\OrderShipped;
use App\Events\PaymentProofUploaded;
use App\Events\PaymentRejected;
use App\Events\PaymentVerified;
use App\Events\ReturnAccepted;
use App\Events\ReturnRequested;
use App\Events\SubscriptionRenewalUploaded;
use App\Events\TenantQuotaAlert;
use App\Events\TenantRegistrationSubmitted;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendInAppNotification
{
    /**
     * Handle incoming notification events.
     */
    public function handle(object $event): void
    {
        try {
            match (true) {
                // Customer Triggers
                $event instanceof OrderConfirmed           => $this->handleOrderConfirmed($event),
                $event instanceof IdentityApproved         => $this->handleIdentityApproved($event),
                $event instanceof IdentityRejected         => $this->handleIdentityRejected($event),
                $event instanceof PaymentVerified          => $this->handlePaymentVerified($event),
                $event instanceof PaymentRejected          => $this->handlePaymentRejected($event),
                $event instanceof OrderShipped             => $this->handleOrderShipped($event),
                $event instanceof ReturnAccepted           => $this->handleReturnAccepted($event),
                $event instanceof FineIssued               => $this->handleFineIssued($event),
                $event instanceof DamageCaseCreated        => $this->handleDamageCaseCreated($event),
                $event instanceof DamageCaseResolved       => $this->handleDamageCaseResolved($event),

                // Admin Rental Triggers
                $event instanceof NewOrderPlaced           => $this->handleNewOrderPlaced($event),
                $event instanceof IdentityProofUploaded    => $this->handleIdentityProofUploaded($event),
                $event instanceof PaymentProofUploaded     => $this->handlePaymentProofUploaded($event),
                $event instanceof ReturnRequested          => $this->handleReturnRequested($event),

                // Owner Platform Triggers
                $event instanceof TenantRegistrationSubmitted => $this->handleTenantRegistrationSubmitted($event),
                $event instanceof SubscriptionRenewalUploaded => $this->handleSubscriptionRenewalUploaded($event),
                $event instanceof TenantQuotaAlert         => $this->handleTenantQuotaAlert($event),

                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi in-app: ' . $e->getMessage(), [
                'event' => get_class($event),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /* -------------------------------------------------------------------------- */
    /* Customer Handlers                                                          */
    /* -------------------------------------------------------------------------- */

    protected function handleOrderConfirmed(OrderConfirmed $event): void
    {
        $order = $event->order;
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Pesanan Dikonfirmasi #' . $order->order_number,
            message: "Pesanan #{$order->order_number} Anda telah dikonfirmasi oleh toko dan siap diproses ke tahap selanjutnya.",
            type: 'order'
        );
    }

    protected function handleIdentityApproved(IdentityApproved $event): void
    {
        $order = $event->order;
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Identitas Disetujui #' . $order->order_number,
            message: "Verifikasi jaminan identitas Anda untuk pesanan #{$order->order_number} telah disetujui.",
            type: 'identity'
        );
    }

    protected function handleIdentityRejected(IdentityRejected $event): void
    {
        $order = $event->order;
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Identitas Ditolak #' . $order->order_number,
            message: "Jaminan identitas untuk pesanan #{$order->order_number} belum memenuhi syarat. Silakan periksa atau unggah ulang identitas Anda.",
            type: 'identity'
        );
    }

    protected function handlePaymentVerified(PaymentVerified $event): void
    {
        $order = $event->order;
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Pembayaran Diverifikasi #' . $order->order_number,
            message: "Pembayaran untuk pesanan #{$order->order_number} telah berhasil diverifikasi oleh admin toko.",
            type: 'payment'
        );
    }

    protected function handlePaymentRejected(PaymentRejected $event): void
    {
        $order = $event->order;
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Pembayaran Ditolak #' . $order->order_number,
            message: "Bukti transfer untuk pesanan #{$order->order_number} tidak valid atau ditolak oleh toko.",
            type: 'payment'
        );
    }

    protected function handleOrderShipped(OrderShipped $event): void
    {
        $order = $event->order;
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Barang Dikirim #' . $order->order_number,
            message: "Barang pesanan #{$order->order_number} sedang dalam pengiriman atau siap diambil sesuai metode yang dipilih.",
            type: 'shipment'
        );
    }

    protected function handleReturnAccepted(ReturnAccepted $event): void
    {
        $order = $event->order;
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Pengembalian Diterima #' . $order->order_number,
            message: "Pengembalian barang untuk pesanan #{$order->order_number} telah diterima dan kondisi fisik selesai diperiksa.",
            type: 'return'
        );
    }

    protected function handleFineIssued(FineIssued $event): void
    {
        $order = $event->order;
        $amountFormatted = number_format($event->amount, 0, ',', '.');
        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Tagihan Denda Terbit #' . $order->order_number,
            message: "Terdapat denda keterlambatan sebesar Rp {$amountFormatted} untuk pesanan #{$order->order_number}. Silakan selesaikan pembayaran denda.",
            type: 'damage'
        );
    }

    protected function handleDamageCaseCreated(DamageCaseCreated $event): void
    {
        $case = $event->damageCase;
        $order = $case->order;
        if (! $order) {
            return;
        }

        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Kasus Kerusakan Diterbitkan #' . $order->order_number,
            message: "Laporan kompensasi kerusakan telah diterbitkan untuk pesanan #{$order->order_number}. Silakan tanggapi laporan ini.",
            type: 'damage'
        );
    }

    protected function handleDamageCaseResolved(DamageCaseResolved $event): void
    {
        $case = $event->damageCase;
        $order = $case->order;
        if (! $order) {
            return;
        }

        $this->createNotificationIfNotDuplicate(
            userId: $order->user_id,
            clientId: $order->client_id,
            title: 'Kasus Kerusakan Diselesaikan #' . $order->order_number,
            message: "Klaim kompensasi kerusakan untuk pesanan #{$order->order_number} telah diselesaikan.",
            type: 'damage'
        );
    }

    /* -------------------------------------------------------------------------- */
    /* Admin Rental Handlers                                                      */
    /* -------------------------------------------------------------------------- */

    protected function handleNewOrderPlaced(NewOrderPlaced $event): void
    {
        $order = $event->order;
        $admin = $this->resolveAdminUser($order->client_id);
        if ($admin) {
            $this->createNotificationIfNotDuplicate(
                userId: $admin->id,
                clientId: $order->client_id,
                title: 'Pesanan Baru Masuk #' . $order->order_number,
                message: "Pesanan baru #{$order->order_number} telah dibuat oleh pelanggan dan menunggu tindak lanjut operasional.",
                type: 'order'
            );
        }
    }

    protected function handleIdentityProofUploaded(IdentityProofUploaded $event): void
    {
        $order = $event->order;
        $admin = $this->resolveAdminUser($order->client_id);
        if ($admin) {
            $this->createNotificationIfNotDuplicate(
                userId: $admin->id,
                clientId: $order->client_id,
                title: 'Bukti Identitas Baru Diunggah #' . $order->order_number,
                message: "Pelanggan telah mengunggah/memperbarui berkas identitas untuk pesanan #{$order->order_number}. Silakan verifikasi.",
                type: 'identity'
            );
        }
    }

    protected function handlePaymentProofUploaded(PaymentProofUploaded $event): void
    {
        $order = $event->order;
        $admin = $this->resolveAdminUser($order->client_id);
        if ($admin) {
            $this->createNotificationIfNotDuplicate(
                userId: $admin->id,
                clientId: $order->client_id,
                title: 'Bukti Transfer Baru Diunggah #' . $order->order_number,
                message: "Pelanggan telah mengunggah bukti pembayaran untuk pesanan #{$order->order_number}. Silakan cek dan verifikasi pembayaran.",
                type: 'payment'
            );
        }
    }

    protected function handleReturnRequested(ReturnRequested $event): void
    {
        $order = $event->order;
        $admin = $this->resolveAdminUser($order->client_id);
        if ($admin) {
            $this->createNotificationIfNotDuplicate(
                userId: $admin->id,
                clientId: $order->client_id,
                title: 'Pengajuan Pengembalian Diajukan #' . $order->order_number,
                message: "Pelanggan telah mengajukan pengembalian untuk pesanan #{$order->order_number}. Siapkan penerimaan dan inspeksi fisik.",
                type: 'return'
            );
        }
    }

    /* -------------------------------------------------------------------------- */
    /* Owner Platform Handlers                                                    */
    /* -------------------------------------------------------------------------- */

    protected function handleTenantRegistrationSubmitted(TenantRegistrationSubmitted $event): void
    {
        $registration = $event->registration;
        $tenantName = $registration->business_name ?? $registration->nama_usaha ?? 'Tenant';
        $owners = User::where('role', 'owner')->get();

        foreach ($owners as $owner) {
            $this->createNotificationIfNotDuplicate(
                userId: $owner->id,
                clientId: null,
                title: 'Pendaftaran Tenant Baru: ' . $tenantName,
                message: "Tenant {$tenantName} mendaftar di RentalBase dan menunggu persetujuan verifikasi Anda.",
                type: 'registration'
            );
        }
    }

    protected function handleSubscriptionRenewalUploaded(SubscriptionRenewalUploaded $event): void
    {
        $subscription = $event->subscription;
        $clientName = $subscription->client?->business_name ?? 'Tenant';
        $owners = User::where('role', 'owner')->get();

        foreach ($owners as $owner) {
            $this->createNotificationIfNotDuplicate(
                userId: $owner->id,
                clientId: $subscription->client_id,
                title: 'Bukti Pembayaran Perpanjangan: ' . $clientName,
                message: "Tenant {$clientName} telah mengunggah bukti transfer pembayaran perpanjangan paket {$subscription->plan_name}.",
                type: 'subscription'
            );
        }
    }

    protected function handleTenantQuotaAlert(TenantQuotaAlert $event): void
    {
        $client = $event->client;
        $owners = User::where('role', 'owner')->get();

        foreach ($owners as $owner) {
            $this->createNotificationIfNotDuplicate(
                userId: $owner->id,
                clientId: $client->id,
                title: 'Peringatan Kuota Tenant Hampir Penuh: ' . $client->business_name,
                message: "Tenant {$client->business_name} telah menggunakan {$event->currentUsage} dari {$event->maxLimit} kuota produk ({$event->percentage}%).",
                type: 'quota'
            );
        }
    }

    /* -------------------------------------------------------------------------- */
    /* Helper & Idempotency Methods                                               */
    /* -------------------------------------------------------------------------- */

    private function resolveAdminUser(?int $clientId): ?User
    {
        if (! $clientId) {
            return null;
        }

        return User::where('client_id', $clientId)
            ->where('role', 'admin_rental')
            ->first() ?? User::where('client_id', $clientId)->first();
    }

    private function createNotificationIfNotDuplicate(int $userId, ?int $clientId, string $title, string $message, string $type): ?Notification
    {
        // Mencegah duplikasi notifikasi identik yang terpicu dalam selang waktu 5 menit
        $exists = Notification::where('user_id', $userId)
            ->where('title', $title)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->exists();

        if ($exists) {
            return null;
        }

        return Notification::create([
            'client_id' => $clientId,
            'user_id'   => $userId,
            'title'     => $title,
            'message'   => $message,
            'type'      => $type,
            'is_read'   => false,
        ]);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\ClientRegistration;
use App\Models\ConditionCheck;
use App\Models\DamageReport;
use App\Models\IdentityGuarantee;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Subscription;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncStorageToSupabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync-supabase {--force : Unggah ulang file meskipun sudah ada di S3} {--all : Unggah seluruh isi folder storage/app/public termasuk file test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasikan seluruh file gambar produk, logo, dan bukti ke Supabase S3 bucket';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDisk = 's3';

        if (empty(config("filesystems.disks.{$targetDisk}.key")) || empty(config("filesystems.disks.{$targetDisk}.bucket"))) {
            $this->error('Kredensial S3 / Supabase belum lengkap di .env (AWS_ACCESS_KEY_ID & AWS_BUCKET wajib ada).');
            return Command::FAILURE;
        }

        $s3 = Storage::disk($targetDisk);
        $force = (bool) $this->option('force');
        $bucketName = config("filesystems.disks.{$targetDisk}.bucket", 'rentalbase-storage');

        $this->info("Mengumpulkan daftar file yang digunakan database dan aset seeder...");

        $targetPaths = [];

        // 1. Kumpulkan seluruh aset dari Database
        foreach (Product::all() as $p) {
            if ($p->main_image) $targetPaths[] = $p->main_image;
            $extras = is_string($p->images) ? (json_decode($p->images, true) ?? []) : ((array) $p->images);
            foreach ($extras as $img) if ($img) $targetPaths[] = $img;
        }
        foreach (Client::whereNotNull('logo')->pluck('logo') as $path) $targetPaths[] = $path;
        foreach (TenantProfile::whereNotNull('logo_path')->pluck('logo_path') as $path) $targetPaths[] = $path;
        foreach (PaymentMethod::whereNotNull('qris_image_path')->pluck('qris_image_path') as $path) $targetPaths[] = $path;
        foreach (ClientPaymentMethod::whereNotNull('qris_image')->pluck('qris_image') as $path) $targetPaths[] = $path;
        foreach (Payment::whereNotNull('payment_proof')->pluck('payment_proof') as $path) $targetPaths[] = $path;
        foreach (Subscription::whereNotNull('payment_proof')->pluck('payment_proof') as $path) $targetPaths[] = $path;
        foreach (ClientRegistration::whereNotNull('payment_proof')->pluck('payment_proof') as $path) $targetPaths[] = $path;
        foreach (IdentityGuarantee::all() as $ig) {
            if ($ig->identity_document_image) $targetPaths[] = $ig->identity_document_image;
            if ($ig->face_image) $targetPaths[] = $ig->face_image;
        }
        foreach (ReturnModel::whereNotNull('late_fee_proof')->pluck('late_fee_proof') as $path) $targetPaths[] = $path;
        foreach (User::whereNotNull('avatar')->pluck('avatar') as $path) $targetPaths[] = $path;
        foreach (ChatMessage::whereNotNull('attachment_path')->pluck('attachment_path') as $path) $targetPaths[] = $path;
        foreach (ConditionCheck::all() as $cc) {
            foreach ((array) $cc->images_list as $img) if ($img) $targetPaths[] = $img;
        }
        foreach (DamageReport::all() as $dr) {
            foreach ((array) $dr->images_list as $img) if ($img) $targetPaths[] = $img;
        }

        // 2. Kumpulkan file dari direktori statis/seeder utama
        $coreDirs = ['products', 'tenant-logos', 'client-qris', 'clients', 'damages', 'refunds'];
        foreach ($coreDirs as $dir) {
            $dirPath = storage_path("app/public/{$dir}");
            if (File::exists($dirPath)) {
                foreach (File::allFiles($dirPath) as $file) {
                    $targetPaths[] = str_replace('\\', '/', $file->getRelativePathname());
                    $targetPaths[] = "{$dir}/" . str_replace('\\', '/', $file->getRelativePathname());
                }
            }
        }

        // Jika opsi --all diberikan, scan seluruh folder storage
        if ($this->option('all')) {
            $baseStoragePath = storage_path('app/public');
            if (File::exists($baseStoragePath)) {
                foreach (File::allFiles($baseStoragePath) as $file) {
                    $targetPaths[] = str_replace('\\', '/', $file->getRelativePathname());
                }
            }
        }

        // Normalisasi dan hilangkan duplikasi
        $normalizedPaths = array_values(array_unique(array_filter(array_map(function ($p) {
            if (empty($p) || str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
                return null;
            }
            $clean = ltrim(trim($p), '/');
            if (str_starts_with($clean, 'storage/')) {
                $clean = substr($clean, 8);
            }
            return $clean;
        }, $targetPaths))));

        $this->info("Ditemukan " . count($normalizedPaths) . " path file unik untuk diverifikasi ke bucket [{$bucketName}].");

        $uploaded = 0;
        $skipped = 0;
        $failed = 0;
        $missingLocal = 0;

        foreach ($normalizedPaths as $relativePath) {
            $localFullPath = storage_path('app/public/' . $relativePath);

            if (! File::exists($localFullPath)) {
                // Cek apakah sudah ada di S3
                if (! $s3->exists($relativePath)) {
                    $missingLocal++;
                }
                continue;
            }

            try {
                if (! $force && $s3->exists($relativePath)) {
                    $skipped++;
                    continue;
                }

                $stream = fopen($localFullPath, 'r');
                $success = $s3->put($relativePath, $stream, [
                    'visibility' => 'public',
                ]);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                if ($success) {
                    $uploaded++;
                    $this->line("<info>[UPLOADED]</info> {$relativePath}");
                } else {
                    $failed++;
                    $this->warn("[GAGAL] {$relativePath}");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error("[ERROR] {$relativePath}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Sinkronisasi selesai!");
        $this->table(
            ['Status', 'Jumlah File'],
            [
                ['Berhasil Diunggah ke S3', $uploaded],
                ['Sudah Ada di S3 (Dilewati)', $skipped],
                ['Gagal / Error', $failed],
                ['Tidak Ditemukan di Harddisk Lokal', $missingLocal],
            ]
        );

        return Command::SUCCESS;
    }
}

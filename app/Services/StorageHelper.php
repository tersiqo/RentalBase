<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Dapatkan disk penyimpanan aktif (default s3 / supabase, fallback public).
     */
    public static function disk(): string
    {
        $default = config('filesystems.default', 'public');
        if ($default !== 'local' && $default !== 'private') {
            return $default;
        }

        if (! empty(config('filesystems.disks.s3.key')) && ! empty(config('filesystems.disks.s3.bucket'))) {
            return 's3';
        }

        return 'public';
    }

    /**
     * Dapatkan URL file dengan smart fallback:
     * 1. Jika URL eksternal (http/https), kembalikan langsung.
     * 2. Jika disk aktif adalah s3/supabase, prioritaskan URL publik Supabase S3.
     * 3. Jika file fisik ada di public_path('storage/' . $cleanPath), kembalikan asset('storage/' . $cleanPath).
     * 4. Fallback ke Storage::disk($targetDisk)->url($cleanPath).
     */
    public static function url(?string $path, ?string $disk = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        $trimmed = trim($path);
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        $cleanPath = ltrim($trimmed, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        $targetDisk = $disk ?: self::disk();

        // Format khusus disk Supabase S3 agar selalu menghasilkan URL public valid (anti 403 Forbidden)
        if ($targetDisk === 's3' || $targetDisk === 'supabase') {
            $customUrl = config("filesystems.disks.{$targetDisk}.url");
            if (! empty($customUrl)) {
                return rtrim($customUrl, '/') . '/' . $cleanPath;
            }

            $endpoint = config("filesystems.disks.{$targetDisk}.endpoint");
            $bucket = config("filesystems.disks.{$targetDisk}.bucket", 'rentalbase-storage');
            if ($endpoint) {
                $host = parse_url($endpoint, PHP_URL_HOST);
                if ($host && str_contains($host, '.supabase.co')) {
                    $projectRef = explode('.', $host)[0];
                    return "https://{$projectRef}.supabase.co/storage/v1/object/public/{$bucket}/{$cleanPath}";
                }
            }

            return Storage::disk($targetDisk)->url($cleanPath);
        }

        // Cek file lokal di public/storage untuk environment lokal / disk public
        if (file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        return Storage::disk($targetDisk)->url($cleanPath);
    }

    /**
     * Hapus file dari disk aktif dan fallback disk public/s3.
     */
    public static function delete(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $trimmed = trim($path);
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return;
        }

        $cleanPath = ltrim($trimmed, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        $disks = array_unique([self::disk(), 'public', 's3', 'supabase']);
        foreach ($disks as $diskName) {
            if (config("filesystems.disks.{$diskName}")) {
                try {
                    if (Storage::disk($diskName)->exists($cleanPath)) {
                        Storage::disk($diskName)->delete($cleanPath);
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika disk gagal dihubungi saat cleanup
                }
            }
        }
    }
}

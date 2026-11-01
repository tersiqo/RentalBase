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
        return config('filesystems.default', 'public');
    }

    /**
     * Dapatkan URL file dengan smart fallback:
     * 1. Jika URL eksternal (http/https), kembalikan langsung.
     * 2. Jika file fisik ada di public_path('storage/' . $path), kembalikan asset('storage/' . $path).
     * 3. Jika tidak, gunakan Storage::disk(self::disk())->url($path) (mengarah ke Supabase S3).
     */
    public static function url(?string $path, ?string $disk = null): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Cek file lokal lama di public/storage
        if (file_exists(public_path('storage/' . $path))) {
            return asset('storage/' . $path);
        }

        $targetDisk = $disk ?: self::disk();

        return Storage::disk($targetDisk)->url($path);
    }

    /**
     * Hapus file dari disk aktif dan fallback disk public.
     */
    public static function delete(?string $path): void
    {
        if (empty($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $disks = array_unique([self::disk(), 'public', 's3']);
        foreach ($disks as $diskName) {
            try {
                if (Storage::disk($diskName)->exists($path)) {
                    Storage::disk($diskName)->delete($path);
                }
            } catch (\Throwable $e) {
                // Abaikan jika disk gagal dihubungi saat cleanup
            }
        }
    }
}

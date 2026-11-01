<?php

if (! function_exists('storage_url')) {
    /**
     * Dapatkan URL file dengan smart fallback (Supabase S3 & file lokal lama).
     */
    function storage_url(?string $path): ?string
    {
        return \App\Services\StorageHelper::url($path);
    }
}

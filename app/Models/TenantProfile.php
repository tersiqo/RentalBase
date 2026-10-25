<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProfile extends Model
{
    use HasFactory;

    protected $table = 'tenant_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'name',
        'description',
        'address',
        'city',
        'whatsapp',
        'subdomain',
        'logo_path',
        'theme_color',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'text_color',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
        ];
    }

    /**
     * Relasi ke Client (Tenant) pemilik profil.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Alias relasi ke Tenant (Client).
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Aksesor teks kontras berdasarkan kecerahan theme_color menggunakan kalkulasi YIQ.
     * Mengembalikan '#ffffff' (putih) jika theme_color gelap,
     * dan '#000000' (hitam) jika theme_color terang.
     *
     * Rumus YIQ: ((r * 299) + (g * 587) + (b * 114)) / 1000
     * Threshold: 128 (skala 0 - 255)
     */
    public function getTextColorAttribute(): string
    {
        $hex = ltrim($this->theme_color ?? '#F97316', '#');

        // Dukung format hex shorthand 3 karakter (#fff -> #ffffff)
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // Validasi format hex 6 karakter
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return '#ffffff';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $yiq >= 128 ? '#000000' : '#ffffff';
    }
}

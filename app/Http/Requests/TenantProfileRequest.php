<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->client_id !== null || $user->tenant !== null);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $client = $user?->client ?? $user?->tenant;
        $clientId = $client?->id ?? $user?->client_id;

        $profileId = $this->route('profile')?->id;
        if (! $profileId && $client) {
            try {
                $profileId = $client->relationLoaded('tenantProfile')
                    ? $client->tenantProfile?->id
                    : $client->tenantProfile()?->value('id');
            } catch (\Throwable $e) {
                $profileId = null;
            }
        }

        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address'     => ['nullable', 'string', 'max:1000'],
            'city'        => ['nullable', 'string', 'max:100'],
            'whatsapp'    => ['nullable', 'string', 'max:30'],
            'subdomain'   => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9\-]+$/i',
                Rule::unique('tenant_profiles', 'subdomain')->ignore($profileId),
                Rule::unique('clients', 'subdomain')->ignore($clientId),
            ],
            // Validasi file logo maksimal 2MB dengan mimes: jpg, jpeg, png, webp
            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'theme_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/i'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'       => 'Nama toko wajib diisi.',
            'logo.image'          => 'File logo harus berupa gambar valid.',
            'logo.mimes'          => 'Format logo harus berupa file bertipe: JPG, PNG, atau WebP.',
            'logo.max'            => 'Ukuran file logo maksimal adalah 2MB (2048 KB).',
            'subdomain.unique'    => 'Subdomain tersebut sudah terdaftar oleh toko lain.',
            'subdomain.regex'     => 'Subdomain hanya boleh berisi huruf, angka, dan tanda hubung (-).',
            'theme_color.regex'   => 'Format warna tema harus berupa kode HEX 6 digit (contoh: #F97316).',
        ];
    }

    /**
     * Mendapatkan nama paket langganan tenant saat ini.
     */
    public function tenantPlan(): string
    {
        $user = $this->user();
        $tenant = $user?->tenant ?? $user?->client;

        return strtolower((string) ($tenant?->plan ?? $tenant?->activeSubscription?->plan_name ?? 'starter'));
    }

    /**
     * Cek apakah paket langganan tenant adalah "Starter".
     */
    public function isStarterPlan(): bool
    {
        return str_contains($this->tenantPlan(), 'starter');
    }

    /**
     * Custom validation logic:
     * Jika paket tenant adalah "Starter", tolak request jika theme_color ikut dikirim
     * untuk mencegah bypass via inspect element / manipulasi request.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('theme_color') && $this->isStarterPlan()) {
                $validator->errors()->add(
                    'theme_color',
                    'Fitur kustomisasi warna tema (theme_color) tidak tersedia pada paket Starter. Silakan upgrade ke paket Business atau Professional.'
                );
            }
        });
    }

    /**
     * Sanitasi data hasil validasi:
     * Hapus field theme_color jika paket Starter untuk proteksi berlapis.
     */
    public function validated($key = null, $default = null)
    {
        $validated = $this->validator ? parent::validated($key, $default) : $this->all();

        if (is_array($validated) && $this->isStarterPlan()) {
            unset($validated['theme_color']);
        }

        return $validated;
    }
}

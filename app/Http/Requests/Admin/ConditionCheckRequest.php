<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConditionCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'          => ['required', 'integer', 'exists:orders,id'],
            'equipment_unit_id' => ['required', 'integer', 'exists:equipment_units,id'],
            'check_type'        => ['required', 'string', 'in:sebelum_serah,setelah_kembali'],
            'condition'         => ['required', 'string', 'in:baik,rusak_ringan,rusak_berat'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images'           => ['nullable', 'array', 'max:5'],
            'images.*'         => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required'          => 'Pesanan harus dipilih.',
            'order_id.exists'            => 'Pesanan tidak valid.',
            'equipment_unit_id.required' => 'Pilih unit fisik terlebih dahulu.',
            'equipment_unit_id.exists'   => 'Unit fisik yang dipilih tidak valid.',
            'check_type.required'        => 'Pilih fase pemeriksaan.',
            'check_type.in'              => 'Fase pemeriksaan tidak valid.',
            'condition.required'         => 'Pilih kondisi barang.',
            'condition.in'               => 'Kondisi yang dipilih tidak valid.',
            'image.image'                => 'File harus berupa gambar.',
            'image.mimes'                => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max'                  => 'Ukuran foto maksimal 5MB.',
        ];
    }
}

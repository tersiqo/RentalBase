<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShipmentProcessRequest extends FormRequest
{
    // Fungsi ini mengecek apakah user boleh melakukan aksi ini
    public function authorize(): bool
    {
        // Pastikan hanya admin/user yang sudah login yang bisa
        return Auth::check();
    }

    // Fungsi ini berisi aturan-aturan validasi
    public function rules(): array
    {
        return [
            // Metode pengiriman wajib diisi, dan pilihannya hanya dua
            'shipping_method' => ['required', 'in:ambil_toko,kurir'],
            
            // Nama kurir wajib (required) JIKA (if) metode pengiriman = kurir
            'courier_name'    => ['required_if:shipping_method,kurir', 'nullable', 'string', 'max:255'],
            
            // Nomor resi wajib JIKA metode pengiriman = kurir
            'tracking_number' => ['required_if:shipping_method,kurir', 'nullable', 'string', 'max:255'],
        ];
    }

    // Mengubah pesan error agar berbahasa Indonesia (opsional, agar ramah pengguna)
    public function messages(): array
    {
        return [
            'shipping_method.required'   => 'Metode pengiriman harus dipilih.',
            'courier_name.required_if'   => 'Nama kurir wajib diisi jika memilih Kurir.',
            'tracking_number.required_if'=> 'Nomor resi pelacakan wajib diisi jika memilih Kurir.',
        ];
    }
}

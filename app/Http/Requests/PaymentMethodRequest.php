<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
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
        $type = $this->input('type');

        // Mendapatkan paymentMethod dari route jika sedang update
        $paymentMethod = $this->route('paymentMethod') ?? $this->route('payment_method');
        $hasExistingQris = $paymentMethod && ! empty($paymentMethod->qris_image_path);

        $rules = [
            'type'         => ['required', 'string', 'in:bank_transfer,qris'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'is_active'    => ['nullable', 'boolean'],
        ];

        // Aturan dinamis jika tipe: bank_transfer
        if ($type === 'bank_transfer') {
            $rules['bank_name']      = ['required', 'string', 'max:100'];
            $rules['account_number'] = ['required', 'string', 'max:50'];
            $rules['account_name']   = ['required', 'string', 'max:150'];

            $rules['merchant_name']      = ['nullable', 'string', 'max:150'];
            $rules['qris_merchant_name'] = ['nullable', 'string', 'max:150'];
            $rules['qris_image']         = ['nullable'];
        }
        // Aturan dinamis jika tipe: qris
        elseif ($type === 'qris') {
            // Fleksibel menerima input merchant_name atau qris_merchant_name
            if ($this->has('qris_merchant_name')) {
                $rules['qris_merchant_name'] = ['required', 'string', 'max:150'];
                $rules['merchant_name']      = ['nullable', 'string', 'max:150'];
            } else {
                $rules['merchant_name']      = ['required', 'string', 'max:150'];
                $rules['qris_merchant_name'] = ['nullable', 'string', 'max:150'];
            }

            // Pada mode update jika gambar QRIS sudah ada sebelumnya, upload gambar baru bersifat opsional
            $imageRule = $hasExistingQris ? 'nullable' : 'required';
            $rules['qris_image'] = [$imageRule, 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

            $rules['bank_name']      = ['nullable', 'string', 'max:100'];
            $rules['account_number'] = ['nullable', 'string', 'max:50'];
            $rules['account_name']   = ['nullable', 'string', 'max:150'];
        }

        return $rules;
    }

    /**
     * Custom error messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required'               => 'Pilih jenis metode pembayaran (Transfer Bank atau QRIS).',
            'type.in'                     => 'Jenis metode pembayaran tidak valid.',
            'bank_name.required'          => 'Nama bank wajib diisi untuk metode transfer bank.',
            'account_number.required'     => 'Nomor rekening wajib diisi untuk metode transfer bank.',
            'account_name.required'       => 'Nama pemilik rekening wajib diisi untuk metode transfer bank.',
            'merchant_name.required'      => 'Nama merchant QRIS (NMID / Nama Usaha) wajib diisi.',
            'qris_merchant_name.required' => 'Nama merchant QRIS (NMID / Nama Usaha) wajib diisi.',
            'qris_image.required'         => 'File gambar barcode QRIS wajib diunggah.',
            'qris_image.image'            => 'File QRIS harus berupa gambar yang valid.',
            'qris_image.mimes'            => 'Format gambar barcode QRIS harus bertipe JPG, PNG, atau WebP.',
            'qris_image.max'              => 'Ukuran file gambar QRIS maksimal adalah 2MB (2048 KB).',
        ];
    }
}

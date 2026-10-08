<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReturnProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorize dihandle di Controller untuk cek kepemilikan client
    }

    public function rules(): array
    {
        return [
            'late_fee_amount' => ['required', 'numeric', 'min:0'],
            'action_type'     => ['required', 'in:normal,denda']
        ];
    }
}
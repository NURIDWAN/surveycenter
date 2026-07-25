<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:' . config('responden.min_withdrawal')],
            'provider_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Jumlah penarikan wajib diisi.',
            'amount.integer' => 'Jumlah penarikan harus berupa angka bulat.',
            'amount.min' => 'Jumlah penarikan minimal Rp ' . number_format(config('responden.min_withdrawal'), 0, ',', '.') . '.',
            'provider_name.required' => 'Nama penyedia layanan wajib diisi.',
            'provider_name.string' => 'Nama penyedia layanan harus berupa teks.',
            'provider_name.max' => 'Nama penyedia layanan maksimal 100 karakter.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_number.string' => 'Nomor rekening harus berupa teks.',
            'account_number.max' => 'Nomor rekening maksimal 50 karakter.',
            'account_holder_name.required' => 'Nama pemilik rekening wajib diisi.',
            'account_holder_name.string' => 'Nama pemilik rekening harus berupa teks.',
            'account_holder_name.max' => 'Nama pemilik rekening maksimal 255 karakter.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProofRequest extends FormRequest
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
            'proof_file' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proof_file.required' => 'File bukti pengisian wajib diunggah.',
            'proof_file.image' => 'File harus berupa gambar.',
            'proof_file.mimes' => 'File harus berformat JPG, JPEG, atau PNG.',
            'proof_file.max' => 'Ukuran file maksimal 2MB.',
            'catatan.string' => 'Catatan harus berupa teks.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}

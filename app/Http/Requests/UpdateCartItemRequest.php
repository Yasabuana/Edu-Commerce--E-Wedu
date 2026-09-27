<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi "Ubah jumlah" pada baris keranjang (FASE 5).
 */
class UpdateCartItemRequest extends FormRequest
{
    /**
     * Kepemilikan baris keranjang diperiksa di controller (404 bila bukan milik kita).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.integer' => 'Jumlah harus berupa angka.',
            'qty.min' => 'Jumlah minimal 1. Untuk membatalkan, hapus produk dari keranjang.',
            'qty.max' => 'Maksimal 99 item per produk dalam satu keranjang.',
        ];
    }
}

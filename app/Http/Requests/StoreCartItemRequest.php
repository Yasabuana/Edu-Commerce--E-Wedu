<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi "Tambah ke keranjang" (FASE 5).
 */
class StoreCartItemRequest extends FormRequest
{
    /**
     * Katalog publik boleh dibuka siapa saja, termasuk guest.
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
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')->whereNull('deleted_at')],
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk yang ingin ditambahkan tidak dikenali.',
            'product_id.exists' => 'Produk tidak ditemukan atau sudah tidak dijual.',
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.min' => 'Jumlah minimal 1.',
            'qty.max' => 'Maksimal 99 item per produk dalam satu keranjang.',
        ];
    }
}

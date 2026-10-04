<?php

namespace App\Http\Requests;

use App\Models\DeliveryPoint;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi checkout (FASE 6 — Rancangan §2.4).
 *
 * Lima opsi pengiriman: 4 titik gratis (`shipping_method=pickup_point`,
 * `delivery_point_id` wajib terisi) + 1 alamat kustom
 * (`shipping_method=custom_delivery`, alamat & koordinat wajib terisi).
 */
class StoreCheckoutRequest extends FormRequest
{
    /**
     * Checkout hanya untuk user login (route ber-middleware `auth`).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $isPickup = 'required_if:shipping_method,'.Order::SHIPPING_PICKUP_POINT;
        $isCustom = 'required_if:shipping_method,'.Order::SHIPPING_CUSTOM_DELIVERY;

        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],

            'shipping_method' => ['required', Rule::in([
                Order::SHIPPING_PICKUP_POINT,
                Order::SHIPPING_CUSTOM_DELIVERY,
            ])],

            'delivery_point_id' => [
                'nullable',
                'integer',
                $isPickup,
                Rule::exists(DeliveryPoint::class, 'id')
                    ->where('type', DeliveryPoint::TYPE_FREE_POINT)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'shipping_address' => ['nullable', 'string', 'max:500', $isCustom],
            'shipping_lat' => ['nullable', 'numeric', 'between:-90,90', $isCustom],
            'shipping_lng' => ['nullable', 'numeric', 'between:-180,180', $isCustom],

            'payment_method' => ['required', Rule::in([
                Order::PAYMENT_METHOD_COD,
                Order::PAYMENT_METHOD_QRIS,
            ])],

            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama penerima wajib diisi.',
            'customer_phone.required' => 'Nomor telepon wajib diisi.',

            'shipping_method.required' => 'Pilih salah satu opsi pengiriman.',
            'shipping_method.in' => 'Opsi pengiriman tidak dikenali.',

            'delivery_point_id.required_if' => 'Pilih salah satu titik pengambilan gratis.',
            'delivery_point_id.exists' => 'Titik pengambilan tidak tersedia.',

            'shipping_address.required_if' => 'Alamat kustom wajib diisi.',
            'shipping_lat.required_if' => 'Koordinat alamat wajib diisi (klik peta atau masukkan manual).',
            'shipping_lng.required_if' => 'Koordinat alamat wajib diisi (klik peta atau masukkan manual).',

            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak dikenali.',
        ];
    }
}
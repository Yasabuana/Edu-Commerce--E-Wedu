<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi hitung ongkir alamat kustom (FASE 6) — endpoint
 * `POST /ongkir/hitung`, dipanggil via fetch + Alpine.
 */
class CalculateShippingRequest extends FormRequest
{
    /**
     * Endpoint kalkulasi boleh dipanggil siapa saja (termasuk guest checkout).
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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Koordinat latitude wajib diisi.',
            'latitude.numeric' => 'Latitude tidak valid.',
            'latitude.between' => 'Latitude harus di antara -90 dan 90.',
            'longitude.required' => 'Koordinat longitude wajib diisi.',
            'longitude.numeric' => 'Longitude tidak valid.',
            'longitude.between' => 'Longitude harus di antara -180 dan 180.',
        ];
    }
}
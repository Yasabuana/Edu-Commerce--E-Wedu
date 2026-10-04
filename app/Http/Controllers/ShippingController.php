<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateShippingRequest;
use App\Models\DeliveryPoint;
use App\Services\ShippingCalculator;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint logistik mandiri (Rancangan §2.1 & §3.3 — FASE 6).
 *
 * Dipakai halaman checkout (fetch + Alpine) untuk menampilkan 4 titik gratis
 * dan menghitung ongkir alamat kustom.
 */
class ShippingController extends Controller
{
    public function __construct(private readonly ShippingCalculator $calculator)
    {
    }

    /**
     * Daftar 4 titik gratis ongkir yang aktif (untuk peta/daftar di checkout).
     */
    public function points(): JsonResponse
    {
        $points = DeliveryPoint::query()
            ->active()
            ->freePoints()
            ->ordered()
            ->get()
            ->map(fn (DeliveryPoint $point): array => [
                'id' => $point->getKey(),
                'code' => $point->code,
                'name' => $point->name,
                'address' => $point->address,
                'latitude' => (float) $point->latitude,
                'longitude' => (float) $point->longitude,
                'operation_hours' => $point->operation_hours,
                'notes' => $point->notes,
                'is_free_shipping' => (bool) $point->is_free_shipping,
            ]);

        return response()->json(['data' => $points]);
    }

    /**
     * Hitung ongkir alamat kustom -> {distance_km, fee, is_free, out_of_range}.
     */
    public function calculate(CalculateShippingRequest $request): JsonResponse
    {
        $quote = $this->calculator->calculate(
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
        );

        return response()->json($quote);
    }
}
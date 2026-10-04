<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Kalkulasi ongkir alamat kustom (Rancangan §3.3).
 *
 * Tarif dibaca dari tabel `settings` (bukan config/env) agar admin bisa
 * mengubahnya tanpa deploy ulang:
 *   fee = base_fee + (cost_per_km x jarak)
 * lalu diterapkan radius gratis & batas jarak maksimal.
 */
class ShippingCalculator
{
    public function __construct(private readonly DistanceService $distance)
    {
    }

    /**
     * Hitung ongkir untuk satu koordinat alamat pembeli (relatif origin kampus).
     *
     * @return array{
     *     distance_km: float,
     *     fee: int,
     *     is_free: bool,
     *     out_of_range: bool,
     *     free_radius_km: float,
     *     max_distance_km: float
     * }
     */
    public function calculate(float $lat, float $lng): array
    {
        $distanceKm = $this->distance->distanceFromOrigin($lat, $lng);
        $freeRadius = Setting::getFloat('shipping_free_radius_km', 3.0);
        $maxDistance = Setting::getFloat('shipping_max_distance_km', 25.0);

        $outOfRange = $this->distance->exceedsMaxDistance($distanceKm, $maxDistance);
        $isFree = ! $outOfRange && $this->distance->isWithinRadius($distanceKm, $freeRadius);

        return [
            'distance_km' => $distanceKm,
            'fee' => $isFree || $outOfRange ? 0 : $this->feeFor($distanceKm),
            'is_free' => $isFree,
            'out_of_range' => $outOfRange,
            'free_radius_km' => $freeRadius,
            'max_distance_km' => $maxDistance,
        ];
    }

    /**
     * Tarif dasar + tarif per kilometer (integer rupiah).
     */
    public function feeFor(float $distanceKm): int
    {
        $baseFee = Setting::getInt('shipping_base_fee', 5000);
        $perKm = Setting::getInt('shipping_cost_per_km', 2500);

        return $baseFee + (int) round($distanceKm * $perKm);
    }
}
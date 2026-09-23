<?php

namespace App\Services;

use App\Models\DeliveryPoint;
use App\Models\Setting;

/**
 * Kalkulasi jarak dengan formula Haversine (Rancangan §3.3).
 * Dipakai `ShippingCalculator` untuk ongkir alamat kustom.
 */
class DistanceService
{
    /**
     * Radius rata-rata bumi dalam kilometer.
     */
    public const EARTH_RADIUS_KM = 6371.0;

    /**
     * Jarak Haversine antara dua koordinat (km), dibulatkan 2 desimal.
     */
    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2, int $precision = 2): float
    {
        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $deltaLat = $latTo - $latFrom;
        $deltaLng = $lngTo - $lngFrom;

        $a = sin($deltaLat / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS_KM * $c, $precision);
    }

    /**
     * Jarak antara dua titik pengiriman.
     */
    public function distanceBetweenPoints(DeliveryPoint $from, DeliveryPoint $to, int $precision = 2): float
    {
        [$latFrom, $lngFrom] = $from->coordinates();
        [$latTo, $lngTo] = $to->coordinates();

        return $this->distanceKm($latFrom, $lngFrom, $latTo, $lngTo, $precision);
    }

    /**
     * Jarak alamat pembeli dari origin Kampus Tuguran (nilai dari tabel `settings`).
     */
    public function distanceFromOrigin(float $lat, float $lng, int $precision = 2): float
    {
        $originLat = Setting::getFloat('campus_origin_lat');
        $originLng = Setting::getFloat('campus_origin_lng');

        return $this->distanceKm($originLat, $originLng, $lat, $lng, $precision);
    }

    /**
     * Jarak masih di dalam radius gratis?
     */
    public function isWithinRadius(float $distanceKm, float $radiusKm): bool
    {
        return $distanceKm <= $radiusKm;
    }

    /**
     * Jarak melampaui batas jangkauan pengiriman?
     */
    public function exceedsMaxDistance(float $distanceKm, float $maxDistanceKm): bool
    {
        return $distanceKm > $maxDistanceKm;
    }
}

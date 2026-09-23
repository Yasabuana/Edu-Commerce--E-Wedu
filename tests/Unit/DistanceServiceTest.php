<?php

namespace Tests\Unit;

use App\Models\DeliveryPoint;
use App\Services\DistanceService;
use PHPUnit\Framework\TestCase;

/**
 * Unit test Haversine (FASE 3, Rancangan §4): origin Kampus Tuguran
 * dibandingkan dengan titik referensi berjarak pasti.
 */
class DistanceServiceTest extends TestCase
{
    private DistanceService $service;

    /**
     * Koordinat origin Kampus Tuguran Untidar (sama dengan isi `settings`).
     */
    private const ORIGIN_LAT = -7.4618;

    private const ORIGIN_LNG = 110.2148;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DistanceService;
    }

    public function test_titik_yang_sama_menghasilkan_jarak_nol(): void
    {
        $this->assertSame(0.0, $this->service->distanceKm(self::ORIGIN_LAT, self::ORIGIN_LNG, self::ORIGIN_LAT, self::ORIGIN_LNG));
    }

    public function test_satu_derajat_lintang_di_khatulistiwa_sekitar_111_km(): void
    {
        $distance = $this->service->distanceKm(0.0, 0.0, 1.0, 0.0);

        $this->assertEqualsWithDelta(111.19, $distance, 0.1);
    }

    public function test_seperempat_keliling_bumi_sesuai_radius_bumi(): void
    {
        // 90 derajat busur = (π/2) × 6371 km ≈ 10007.5 km
        $distance = $this->service->distanceKm(0.0, 0.0, 0.0, 90.0);

        $this->assertEqualsWithDelta(10007.5, $distance, 1.0);
    }

    public function test_jarak_kampus_tuguran_ke_balai_kota_magelang(): void
    {
        // Origin Kampus Tuguran -> Balai Kota Magelang (-7.4708, 110.2177)
        $distance = $this->service->distanceKm(self::ORIGIN_LAT, self::ORIGIN_LNG, -7.4708, 110.2177);

        $this->assertEqualsWithDelta(1.05, $distance, 0.05);
    }

    public function test_jarak_kampus_tuguran_ke_sentra_umkm_magelang(): void
    {
        // Origin -> Sentra UMKM Magelang (-7.4885, 110.2290)
        $distance = $this->service->distanceKm(self::ORIGIN_LAT, self::ORIGIN_LNG, -7.4885, 110.2290);

        $this->assertEqualsWithDelta(3.36, $distance, 0.05);
    }

    public function test_jarak_bersifat_simetris_dan_dibulatkan_dua_desimal(): void
    {
        $maju = $this->service->distanceKm(self::ORIGIN_LAT, self::ORIGIN_LNG, -7.4793, 110.2204);
        $mundur = $this->service->distanceKm(-7.4793, 110.2204, self::ORIGIN_LAT, self::ORIGIN_LNG);

        $this->assertSame($maju, $mundur);
        $this->assertSame(round($maju, 2), $maju);
    }

    public function test_jarak_antara_dua_titik_pengiriman(): void
    {
        $origin = new DeliveryPoint(['latitude' => self::ORIGIN_LAT, 'longitude' => self::ORIGIN_LNG]);
        $umkmCenter = new DeliveryPoint(['latitude' => -7.4793, 'longitude' => 110.2204]);

        $this->assertEqualsWithDelta(
            2.04,
            $this->service->distanceBetweenPoints($origin, $umkmCenter),
            0.05,
        );
    }

    public function test_radius_gratis_dan_batas_jangkauan(): void
    {
        $this->assertTrue($this->service->isWithinRadius(2.04, 3.0));
        $this->assertFalse($this->service->isWithinRadius(3.36, 3.0));

        $this->assertTrue($this->service->exceedsMaxDistance(26.10, 25.0));
        $this->assertFalse($this->service->exceedsMaxDistance(3.36, 25.0));
    }
}

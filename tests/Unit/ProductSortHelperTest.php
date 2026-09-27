<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

/**
 * Helper pengurutan katalog `Product` (FASE 5) — murni, tanpa database.
 *
 * Pengurutan sesungguhnya (scope `sorted()`) diuji di
 * `tests/Feature/CatalogPageTest.php` karena butuh query Eloquent.
 */
class ProductSortHelperTest extends TestCase
{
    public function test_opsi_urut_katalog_berisi_lima_pilihan_berurut(): void
    {
        $opsi = Product::sortOptions();

        $this->assertSame(
            [Product::SORT_TERBARU, Product::SORT_TERMURAH, Product::SORT_TERMAHAL, Product::SORT_TERLARIS, Product::SORT_RATING],
            array_keys($opsi),
        );

        $this->assertSame('Terbaru', $opsi[Product::SORT_TERBARU]);
        $this->assertSame('Harga termurah', $opsi[Product::SORT_TERMURAH]);
        $this->assertSame('Harga tertinggi', $opsi[Product::SORT_TERMAHAL]);
        $this->assertSame('Terlaris', $opsi[Product::SORT_TERLARIS]);
        $this->assertSame('Rating tertinggi', $opsi[Product::SORT_RATING]);
    }

    public function test_normalize_sort_mempertahankan_nilai_yang_valid(): void
    {
        foreach (array_keys(Product::sortOptions()) as $nilai) {
            $this->assertSame($nilai, Product::normalizeSort($nilai));
        }
    }

    public function test_normalize_sort_jatuh_ke_terbaru_untuk_nilai_tidak_dikenal(): void
    {
        $this->assertSame(Product::SORT_TERBARU, Product::normalizeSort(null));
        $this->assertSame(Product::SORT_TERBARU, Product::normalizeSort(''));
        $this->assertSame(Product::SORT_TERBARU, Product::normalizeSort('   '));
        $this->assertSame(Product::SORT_TERBARU, Product::normalizeSort('murah'));
        $this->assertSame(Product::SORT_TERBARU, Product::normalizeSort('TERMURAH'));
        $this->assertSame(Product::SORT_TERBARU, Product::normalizeSort('termurah; drop table products'));
    }

    public function test_normalize_sort_memangkas_spasi_di_sekitar_nilai(): void
    {
        $this->assertSame(Product::SORT_TERLARIS, Product::normalizeSort('  terlaris  '));
        $this->assertSame(Product::SORT_RATING, Product::normalizeSort("\trating\n"));
    }
}

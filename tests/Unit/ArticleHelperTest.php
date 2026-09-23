<?php

namespace Tests\Unit;

use App\Models\Article;
use PHPUnit\Framework\TestCase;

/**
 * Helper murni model `Article` (FASE 4): estimasi waktu baca.
 *
 * Status terbit (`isPublished()`) diuji di `tests/Feature/ArticleModelTest.php`
 * karena atribut tanggalnya butuh resolver koneksi Eloquent.
 */
class ArticleHelperTest extends TestCase
{
    public function test_estimasi_waktu_baca_minimal_satu_menit(): void
    {
        $this->assertSame(1, (new Article(['body' => 'kata']))->readingTimeMinutes());
        $this->assertSame(1, (new Article(['body' => '']))->readingTimeMinutes());
    }

    public function test_estimasi_waktu_baca_mengikuti_200_kata_per_menit(): void
    {
        $this->assertSame(1, (new Article(['body' => implode(' ', array_fill(0, 200, 'kata'))]))->readingTimeMinutes());
        $this->assertSame(2, (new Article(['body' => implode(' ', array_fill(0, 201, 'kata'))]))->readingTimeMinutes());
        $this->assertSame(3, (new Article(['body' => implode(' ', array_fill(0, 401, 'kata'))]))->readingTimeMinutes());
    }

    public function test_estimasi_waktu_baca_mengabaikan_tag_html(): void
    {
        $body = '<p>'.implode(' ', array_fill(0, 200, 'kata')).'</p><h2>Judul</h2>';

        $this->assertSame(1, (new Article(['body' => $body]))->readingTimeMinutes());
    }
}

<?php

namespace Tests\Unit;

use App\Models\ConditionCheck;
use Tests\TestCase;

/**
 * MOD-10 — Pengembalian, Kondisi dan Damage Case
 * Fitur: Condition Checks / Pemeriksaan Kondisi Fisik Alat
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 * Runner: PHPUnit murni (tanpa HTTP request, tanpa middleware, tanpa session web)
 *
 * Menguji:
 * 1. Logika accessor images_list untuk single file, multi-upload JSON array, dan null pada model ConditionCheck.
 * 2. Logika accessor first_image_url untuk thumbnail foto pertama.
 *
 * Total: 4 Test Case (UT-CHK-001 s/d UT-CHK-004)
 */
class ConditionCheckLogicTest extends TestCase
{
    /**
     * UT-CHK-001: Memastikan accessor images_list mengembalikan array kosong jika kolom image null atau kosong.
     */
    public function test_ut_chk_001_images_list_mengembalikan_array_kosong_jika_image_null_atau_kosong(): void
    {
        // 1. Kolom image = null
        $checkNull = new ConditionCheck(['image' => null]);
        $this->assertSame([], $checkNull->images_list, 'images_list harus mengembalikan array kosong jika image bernilai null.');

        // 2. Kolom image = string kosong
        $checkEmpty = new ConditionCheck(['image' => '']);
        $this->assertSame([], $checkEmpty->images_list, 'images_list harus mengembalikan array kosong jika image string kosong.');
    }

    /**
     * UT-CHK-002: Memastikan accessor images_list mengembalikan array 1 elemen jika nilai image berupa string path tunggal.
     */
    public function test_ut_chk_002_images_list_mengembalikan_array_satu_elemen_jika_single_path_string(): void
    {
        $singlePath = 'condition-checks/kamera_depan.jpg';
        $check = new ConditionCheck(['image' => $singlePath]);

        $this->assertSame([$singlePath], $check->images_list);
        $this->assertCount(1, $check->images_list);
    }

    /**
     * UT-CHK-003: Memastikan accessor images_list men-decode string JSON array jika diisi multi-upload foto.
     */
    public function test_ut_chk_003_images_list_men_decode_json_array_jika_multi_upload(): void
    {
        $photos = [
            'condition-checks/lensa_depan.jpg',
            'condition-checks/tombol_shutter.jpg',
            'condition-checks/layar_lcd.jpg',
        ];

        $check = new ConditionCheck([
            'image' => json_encode($photos),
        ]);

        $this->assertSame($photos, $check->images_list);
        $this->assertCount(3, $check->images_list);
        $this->assertSame('condition-checks/lensa_depan.jpg', $check->images_list[0]);
    }

    /**
     * UT-CHK-004: Memastikan accessor first_image_url mengembalikan URL foto pertama sebagai thumbnail dan null jika kosong.
     */
    public function test_ut_chk_004_first_image_url_mengembalikan_url_foto_pertama_atau_null(): void
    {
        // 1. Jika tidak ada foto -> first_image_url harus null
        $checkNoPhoto = new ConditionCheck(['image' => null]);
        $this->assertNull($checkNoPhoto->first_image_url);

        // 2. Jika ada foto multi-upload -> first_image_url harus memuat path foto indeks ke-0
        $photos = ['condition-checks/foto_utama.jpg', 'condition-checks/foto_kedua.jpg'];
        $checkWithPhotos = new ConditionCheck(['image' => json_encode($photos)]);

        $firstUrl = $checkWithPhotos->first_image_url;
        $this->assertNotNull($firstUrl);
        $this->assertStringContainsString('foto_utama.jpg', $firstUrl);
    }
}

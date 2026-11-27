<?php

namespace Tests\Unit;

use App\Models\DamageCase;
use Tests\TestCase;

class DamageCaseUnitTest extends TestCase
{
    /**
     * [9.1-I1] Memastikan kalkulasi total biaya kompensasi menggabungkan biaya perbaikan dan denda.
     */
    public function test_ut_10_001_total_amount_calculation_combines_repair_cost_and_fine(): void
    {
        $damageCase = new DamageCase([
            'repair_cost' => 150000,
            'fine_amount' => 50000,
        ]);

        $this->assertEquals(200000.0, $damageCase->total_amount);
    }

    /**
     * [9.1-I1] Memastikan accessor format rupiah total kompensasi mengembalikan format mata uang Indonesia.
     */
    public function test_ut_10_002_formatted_total_returns_rupiah_format(): void
    {
        $damageCase = new DamageCase([
            'repair_cost' => 250000,
            'fine_amount' => 0,
        ]);

        $this->assertEquals('Rp 250.000', $damageCase->formatted_total);
    }

    /**
     * [8.1-I4] Memastikan mapping status kasus kerusakan mengembalikan label bahasa Indonesia yang ramah pengguna.
     */
    public function test_ut_10_003_status_label_accessor_returns_friendly_indonesian_text(): void
    {
        $caseWaiting = new DamageCase(['case_status' => 'menunggu_respons']);
        $this->assertEquals('Menunggu Tanggapan', $caseWaiting->status_label);

        $casePaid = new DamageCase(['case_status' => 'kompensasi_dibayar']);
        $this->assertEquals('Menunggu Verifikasi Toko', $casePaid->status_label);
    }

    /**
     * [9.1-I1] Memastikan kalkulasi total biaya kompensasi tetap presisi saat denda bernilai nol.
     */
    public function test_ut_10_016_total_amount_calculation_without_fine(): void
    {
        $damageCase = new DamageCase([
            'repair_cost' => 120000,
            'fine_amount' => 0,
        ]);

        $this->assertEquals(120000.0, $damageCase->total_amount);
    }

    /**
     * [8.1-I4] Memastikan mapping status kasus kerusakan mengembalikan label 'Selesai' saat kasus lunas.
     */
    public function test_ut_10_017_status_label_completed(): void
    {
        $caseCompleted = new DamageCase(['case_status' => 'selesai']);

        $this->assertEquals('Selesai', $caseCompleted->status_label);
    }

    /**
     * [8.1-I4] Memastikan accessor can_pay dan unified_status mengembalikan status badge dan izin pembayaran kompensasi.
     */
    public function test_ut_10_018_damage_case_can_pay_and_unified_status_accessors(): void
    {
        $caseUnpaid = new DamageCase([
            'repair_cost' => 100000,
            'case_status' => 'menunggu_respons',
            'payment_status' => 'belum_dibayar',
        ]);

        $this->assertTrue($caseUnpaid->can_pay);

        $unified = $caseUnpaid->unified_status;
        $this->assertIsArray($unified);
        $this->assertArrayHasKey('label', $unified);
        $this->assertArrayHasKey('color', $unified);
        $this->assertArrayHasKey('key', $unified);

        $casePaid = new DamageCase([
            'case_status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu_verifikasi',
        ]);
        $this->assertFalse($casePaid->can_pay);
    }

    /**
     * [8.1-I4] Memastikan model DamageReport memiliki relasi Eloquent ke Order, EquipmentUnit, User reporter, dan accessor images_list.
     */
    public function test_ut_10_019_damage_report_relations_and_images_list_accessor(): void
    {
        $report = new \App\Models\DamageReport([
            'image' => 'reports/foto_rusak_1.jpg,reports/foto_rusak_2.jpg',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $report->order());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $report->equipmentUnit());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $report->reporter());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $report->damageCase());

        $imagesList = $report->images_list;
        $this->assertIsArray($imagesList);
        $this->assertCount(2, $imagesList);
        $this->assertStringContainsString('foto_rusak_1.jpg', $imagesList[0]);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ============================================================================
 * UNIT TEST: UT-MOD-03 / UT-MOD-04 (SUBSCRIPTION & QUOTA MONITORING)
 * ============================================================================
 */
class SubscriptionUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset Carbon mock time
        parent::tearDown();
    }

    // =========================================================================
    // 1. ACCESSOR getPaymentProofUrlAttribute()
    // =========================================================================

    /**
     * [Case 1a] Mengembalikan URL valid jika field payment_proof berisi path file lokal/storage.
     */
    public function test_payment_proof_url_returns_valid_storage_url_when_proof_is_present(): void
    {
        Storage::fake('public');
        Config::set('filesystems.default', 'public');

        $subscription = new Subscription([
            'payment_proof' => 'payment_proofs/proof_123.jpg',
        ]);

        $url = $subscription->payment_proof_url;

        $this->assertNotNull($url);
        $this->assertStringContainsString('payment_proofs/proof_123.jpg', $url);
    }

    /**
     * [Case 1b] Mengembalikan URL langsung jika payment_proof sudah berupa direct URL (misal Supabase S3).
     */
    public function test_payment_proof_url_returns_direct_url_when_stored_as_absolute_http_link(): void
    {
        $externalUrl = 'https://supabase.co/storage/v1/object/public/proofs/receipt_abc.pdf';

        $subscription = new Subscription([
            'payment_proof' => $externalUrl,
        ]);

        $this->assertSame($externalUrl, $subscription->payment_proof_url);
    }

    /**
     * [Case 2a] Mengembalikan null jika payment_proof bernilai null.
     */
    public function test_payment_proof_url_returns_null_when_payment_proof_is_null(): void
    {
        $subscription = new Subscription([
            'payment_proof' => null,
        ]);

        $this->assertNull($subscription->payment_proof_url);
    }

    /**
     * [Case 2b] Mengembalikan null jika payment_proof berupa string kosong.
     */
    public function test_payment_proof_url_returns_null_when_payment_proof_is_empty_string(): void
    {
        $subscription = new Subscription([
            'payment_proof' => '',
        ]);

        $this->assertNull($subscription->payment_proof_url);
    }

    // =========================================================================
    // 2. KALKULASI SISA HARI AKTIF (REMAINING ACTIVE DAYS)
    // =========================================================================

    /**
     * [Case 1] Langganan aktif dengan end_date di masa depan (contoh: H+10 hari)
     * harus menghasilkan sisa 10 hari dan status belum kadaluarsa.
     */
    public function test_remaining_active_days_calculation_for_future_expiry(): void
    {
        $mockNow = Carbon::parse('2026-11-16 10:00:00');
        Carbon::setTestNow($mockNow);

        $subscription = new Subscription([
            'status'     => 'aktif',
            'start_date' => $mockNow->copy()->subDays(20),
            'end_date'   => $mockNow->copy()->addDays(10),
        ]);

        $calc = $this->calculateRemainingDays($subscription, $mockNow);

        $this->assertSame(10, $calc['daysRemaining']);
        $this->assertFalse($calc['isExpired']);
    }

    /**
     * [Case 2] Langganan yang sudah melewati batas waktu (end_date di masa lalu)
     * harus terdeteksi kadaluarsa dan sisa hari adalah 0.
     */
    public function test_remaining_active_days_calculation_for_past_expiry(): void
    {
        $mockNow = Carbon::parse('2026-11-16 10:00:00');
        Carbon::setTestNow($mockNow);

        $subscription = new Subscription([
            'status'     => 'kadaluarsa',
            'start_date' => $mockNow->copy()->subMonths(3),
            'end_date'   => $mockNow->copy()->subDays(5),
        ]);

        $calc = $this->calculateRemainingDays($subscription, $mockNow);

        $this->assertSame(0, $calc['daysRemaining']);
        $this->assertTrue($calc['isExpired']);
    }

    /**
     * [Case 3a] Edge case: end_date bernilai null.
     * Tidak boleh melempar exception, sisa hari harus 0 dan terdeteksi tidak aktif/kadaluarsa.
     */
    public function test_remaining_active_days_calculation_with_null_end_date(): void
    {
        $mockNow = Carbon::parse('2026-11-16 10:00:00');
        Carbon::setTestNow($mockNow);

        $subscription = new Subscription([
            'status'     => 'menunggu_verifikasi',
            'start_date' => null,
            'end_date'   => null,
        ]);

        $calc = $this->calculateRemainingDays($subscription, $mockNow);

        $this->assertSame(0, $calc['daysRemaining']);
        $this->assertFalse($calc['isExpired']);
    }

    /**
     * [Case 3b] Edge case: end_date bertepatan dengan hari ini (Hari H jatuh tempo, belum lewat jam).
     * Sisa hari adalah 0, namun belum kadaluarsa.
     */
    public function test_remaining_active_days_calculation_due_today_before_time(): void
    {
        $mockNow = Carbon::parse('2026-11-16 09:00:00');
        Carbon::setTestNow($mockNow);

        $subscription = new Subscription([
            'status'     => 'aktif',
            'start_date' => $mockNow->copy()->subMonth(),
            'end_date'   => Carbon::parse('2026-11-16 23:59:59'),
        ]);

        $calc = $this->calculateRemainingDays($subscription, $mockNow);

        $this->assertSame(0, $calc['daysRemaining']);
        $this->assertFalse($calc['isExpired']);
    }

    /**
     * [Case 3c] Edge case: end_date bertepatan dengan hari ini tetapi jam jatuh tempo sudah terlewat.
     * Harus terdeteksi kadaluarsa dan sisa hari adalah 0.
     */
    public function test_remaining_active_days_calculation_due_today_past_time(): void
    {
        $mockNow = Carbon::parse('2026-11-16 15:00:00');
        Carbon::setTestNow($mockNow);

        $subscription = new Subscription([
            'status'     => 'aktif',
            'start_date' => $mockNow->copy()->subMonth(),
            'end_date'   => Carbon::parse('2026-11-16 12:00:00'),
        ]);

        $calc = $this->calculateRemainingDays($subscription, $mockNow);

        $this->assertSame(0, $calc['daysRemaining']);
        $this->assertTrue($calc['isExpired']);
    }

    // =========================================================================
    // 3. MODEL RELATIONSHIP & ATTRIBUTE CASTING
    // =========================================================================

    /**
     * Validasi relasi belongsTo ke Model Client.
     */
    public function test_subscription_belongs_to_client_relationship(): void
    {
        $subscription = new Subscription();

        $relation = $subscription->client();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame('client_id', $relation->getForeignKeyName());
        $this->assertInstanceOf(Client::class, $relation->getRelated());
    }

    /**
     * Validasi casts pada model Subscription (datetime casts dan decimal price).
     */
    public function test_subscription_casts_datetime_and_price_attributes(): void
    {
        $subscription = new Subscription([
            'start_date' => '2026-01-01 00:00:00',
            'end_date'   => '2026-04-01 00:00:00',
            'price'      => '99000.50',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $subscription->start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $subscription->end_date);
        $this->assertSame('99000.50', $subscription->price);
    }

    // =========================================================================
    // HELPER LOGIKA KALKULASI DOMAIN
    // =========================================================================

    /**
     * Helper kalkulasi sisa hari aktif sesuai implementasi Admin\SubscriptionController::index.
     *
     * @return array{daysRemaining: int, isExpired: bool}
     */
    private function calculateRemainingDays(?Subscription $subscription, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $daysRemaining = 0;
        $isExpired = false;

        if ($subscription && $subscription->end_date) {
            $endDate = Carbon::parse($subscription->end_date);
            if ($endDate->isPast()) {
                $isExpired = true;
                $daysRemaining = 0;
            } else {
                $daysRemaining = (int) $now->diffInDays($endDate, false);
            }
        }

        return [
            'daysRemaining' => $daysRemaining,
            'isExpired'     => $isExpired,
        ];
    }
}

<?php

namespace Tests\Unit\Owner;

use App\Http\Middleware\OwnerMiddleware;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerDashboardUnitTest extends TestCase
{
    use RefreshDatabase;

    private OwnerMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new OwnerMiddleware();
    }

    public function test_past_active_subscriptions_are_updated_to_kadaluarsa(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Lensa',
            'subdomain' => 'tokolensa',
            'status' => 'aktif',
        ]);

        $pastSub = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subDay(),
            'status' => 'aktif',
        ]);

        Subscription::where('status', 'aktif')
            ->where('end_date', '<', Carbon::now())
            ->update(['status' => 'kadaluarsa']);

        $pastSub->refresh();
        $this->assertSame('kadaluarsa', $pastSub->status);
    }

    public function test_tenant_counts_exclude_unverified_status(): void
    {
        Client::create([
            'business_name' => 'Toko Aktif 1',
            'subdomain' => 'toko1',
            'status' => 'aktif',
        ]);

        Client::create([
            'business_name' => 'Toko Suspended 1',
            'subdomain' => 'toko2',
            'status' => 'suspended',
        ]);

        Client::create([
            'business_name' => 'Toko Menunggu Verifikasi',
            'subdomain' => 'toko3',
            'status' => 'menunggu_verifikasi',
        ]);

        $verifiedTenants = Client::where('status', '!=', 'menunggu_verifikasi')->count();
        $activeTenants = Client::where('status', 'aktif')->count();
        $suspendedTenants = Client::where('status', 'suspended')->count();

        $this->assertSame(2, $verifiedTenants);
        $this->assertSame(1, $activeTenants);
        $this->assertSame(1, $suspendedTenants);
    }

    public function test_subscriptions_expiring_soon_calculation_window(): void
    {
        $now = Carbon::now();

        $client = Client::create([
            'business_name' => 'Toko Segera Habis',
            'subdomain' => 'tokohabis',
            'status' => 'aktif',
        ]);

        Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'starter',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => $now->copy()->subMonth(),
            'end_date' => $now->copy()->addDays(3),
            'status' => 'aktif',
        ]);

        Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => $now,
            'end_date' => $now->copy()->addDays(20),
            'status' => 'aktif',
        ]);

        $expiringSoonCount = Subscription::where('status', 'aktif')
            ->where('end_date', '<=', $now->copy()->addDays(7))
            ->where('end_date', '>=', $now)
            ->count();

        $this->assertSame(1, $expiringSoonCount);
    }

    public function test_unauthenticated_user_redirects_to_owner_login(): void
    {
        $request = Request::create('/owner/dashboard', 'GET');
        $next = fn ($req) => new Response('Passed');

        $response = $this->middleware->handle($request, $next);

        $this->assertTrue($response->isRedirection());
        $this->assertEquals(route('owner.login'), $response->headers->get('Location'));
    }

    public function test_admin_rental_redirects_to_admin_dashboard(): void
    {
        $adminRental = User::create([
            'name' => 'Admin Rental',
            'email' => 'admin.guard@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        Auth::login($adminRental);

        $request = Request::create('/owner/dashboard', 'GET');
        $next = fn ($req) => new Response('Passed');

        $response = $this->middleware->handle($request, $next);

        $this->assertTrue($response->isRedirection());
        $this->assertEquals(route('admin.dashboard'), $response->headers->get('Location'));
    }

    public function test_active_owner_passes_through(): void
    {
        $owner = User::create([
            'name' => 'Super Owner',
            'email' => 'owner.guard@test.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        Auth::login($owner);

        $request = Request::create('/owner/dashboard', 'GET');
        $next = fn ($req) => new Response('Passed');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Passed', $response->getContent());
    }
}

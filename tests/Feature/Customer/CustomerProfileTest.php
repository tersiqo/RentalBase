<?php

namespace Tests\Feature\Customer;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'client_id' => $this->client->id,
            'name' => 'Tersiqo Customer',
            'email' => 'iqocustomer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'aktif',
            'phone' => '081234567890',
            'address' => 'Jl. Veteran No. 12, Malang',
        ]);
    }

    public function test_it_01_004_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('customer.profile', ['subdomain' => $this->client->subdomain]));

        $response->assertRedirect('/');
    }

    public function test_authenticated_customer_can_view_profile_page(): void
    {
        // Buat pesanan untuk menguji widget riwayat dan stats
        Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-PROF-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 250000,
            'shipping_address' => 'Jl. Veteran No. 12, Malang',
            'status' => 'sedang_disewa',
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.profile', ['subdomain' => $this->client->subdomain]));

        $response->assertStatus(200);
        $response->assertViewIs('customer.profile');
        $response->assertSee('Profil Saya');
        $response->assertSee('Tersiqo Customer');
        $response->assertSee('iqocustomer@example.com');
        $response->assertSee('081234567890');
        $response->assertSee('ORD-PROF-001');
    }

    public function test_it_01_001_customer_can_update_profile_info(): void
    {
        $response = $this->actingAs($this->customer)
            ->put(route('customer.profile.update', ['subdomain' => $this->client->subdomain]), [
                'name' => 'Tersiqo Alfarezel Updated',
                'phone' => '089876543210',
                'address' => 'Jl. Ijen No. 99, Malang',
            ]);

        $response->assertRedirect(route('customer.profile', ['subdomain' => $this->client->subdomain]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'name' => 'Tersiqo Alfarezel Updated',
            'phone' => '089876543210',
            'address' => 'Jl. Ijen No. 99, Malang',
            // Email tidak boleh berubah
            'email' => 'iqocustomer@example.com',
        ]);
    }

    public function test_it_01_018_customer_cannot_update_profile_with_empty_name(): void
    {
        $response = $this->actingAs($this->customer)
            ->put(route('customer.profile.update', ['subdomain' => $this->client->subdomain]), [
                'name' => '',
                'phone' => '081234567890',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_it_01_002_customer_can_upload_avatar(): void
    {
        Storage::fake('public');
        config(['filesystems.default' => 'public']);

        $file = UploadedFile::fake()->image('my-avatar.jpg', 300, 300);

        $response = $this->actingAs($this->customer)
            ->put(route('customer.profile.update', ['subdomain' => $this->client->subdomain]), [
                'name' => 'Tersiqo Customer',
                'avatar' => $file,
            ]);

        $response->assertRedirect(route('customer.profile', ['subdomain' => $this->client->subdomain]));
        $response->assertSessionHas('success');

        $updatedUser = $this->customer->fresh();
        $this->assertNotNull($updatedUser->avatar);
        Storage::disk('public')->assertExists($updatedUser->avatar);
    }

    public function test_it_01_003_customer_can_update_password(): void
    {
        $response = $this->actingAs($this->customer)
            ->put(route('customer.profile.password', ['subdomain' => $this->client->subdomain]), [
                'current_password' => 'password123',
                'password' => 'newsecretpassword123',
                'password_confirmation' => 'newsecretpassword123',
            ]);

        $response->assertRedirect(route('customer.profile', ['subdomain' => $this->client->subdomain]));
        $response->assertSessionHas('password_success');

        $this->assertTrue(Hash::check('newsecretpassword123', $this->customer->fresh()->password));
    }

    public function test_it_01_019_customer_cannot_update_password_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->customer)
            ->put(route('customer.profile.password', ['subdomain' => $this->client->subdomain]), [
                'current_password' => 'wrongpass',
                'password' => 'newsecretpassword123',
                'password_confirmation' => 'newsecretpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertFalse(Hash::check('newsecretpassword123', $this->customer->fresh()->password));
    }

    public function test_customer_cannot_update_password_with_mismatched_confirmation(): void
    {
        $response = $this->actingAs($this->customer)
            ->put(route('customer.profile.password', ['subdomain' => $this->client->subdomain]), [
                'current_password' => 'password123',
                'password' => 'newsecretpassword123',
                'password_confirmation' => 'differentpass123',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_global_profile_redirects_to_scoped_profile(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('customer.profile.global'));

        $response->assertRedirect(route('customer.profile', ['subdomain' => $this->client->subdomain]));
    }
}

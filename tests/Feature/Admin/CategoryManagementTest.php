<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $admin;

    private Client $otherClient;

    private User $otherAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'BabyRent Malang',
            'subdomain' => 'babyrent',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Budi Admin',
            'email' => 'budi@babyrental.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->client->update(['primary_admin_id' => $this->admin->id]);

        $this->makeSubscription($this->client, 'Pro Enterprise');

        $this->otherClient = Client::create([
            'business_name' => 'Toko Lain',
            'subdomain' => 'tokolain',
            'status' => 'aktif',
        ]);

        $this->otherAdmin = User::create([
            'client_id' => $this->otherClient->id,
            'name' => 'Siti Admin',
            'email' => 'siti@tokolain.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $this->makeSubscription($this->otherClient, 'Pro Enterprise');
    }

    private function makeSubscription(Client $client, string $plan): Subscription
    {
        return Subscription::create([
            'client_id' => $client->id,
            'plan_name' => $plan,
            'max_products' => 50,
            'max_users' => 5,
            'price' => 300000,
            'billing_cycle' => 'bulanan',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'aktif',
        ]);
    }

    private function makeCategory(Client $client, string $name, ?string $description = null): Category
    {
        return Category::create([
            'client_id' => $client->id,
            'name' => $name,
            'slug' => str()->slug($name),
            'description' => $description,
        ]);
    }

    private function makeProduct(Client $client, Category $category): Product
    {
        return Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Produk '.$category->name,
            'slug' => str()->slug('produk-'.$category->name),
            'rental_price_per_day' => 35000,
            'deposit_fee' => 0,
            'status' => 'aktif',
        ]);
    }

    public function test_guest_is_redirected_from_categories_index(): void
    {
        $this->get(route('admin.categories.index'))->assertRedirect('/');
    }

    public function test_customer_role_cannot_access_categories(): void
    {
        $customer = User::create([
            'client_id' => $this->client->id,
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.categories.index'))
            ->assertRedirect('/');
    }

    public function test_index_only_lists_categories_of_authenticated_client(): void
    {
        $mine = $this->makeCategory($this->client, 'Stroller');
        $this->makeCategory($this->otherClient, 'Car Seat');

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

        $response->assertOk()
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories', function ($categories) use ($mine) {
                return $categories->count() === 1 && $categories->first()->is($mine);
            });
    }

    public function test_store_creates_category_for_own_client_with_slug(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Box Bayi',
            'description' => 'Tempat tidur bayi portabel',
        ]);

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'client_id' => $this->client->id,
            'name' => 'Box Bayi',
            'slug' => 'box-bayi',
        ]);
    }

    public function test_store_rejects_duplicate_name_case_insensitively(): void
    {
        $this->makeCategory($this->client, 'Stroller');

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'STROLLER',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, Category::where('client_id', $this->client->id)->count());
    }

    public function test_store_allows_same_name_for_different_client(): void
    {
        $this->makeCategory($this->otherClient, 'Stroller');

        $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Stroller',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'client_id' => $this->client->id,
            'name' => 'Stroller',
        ]);
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => '',
        ])->assertSessionHasErrors('name');
    }

    public function test_store_rejects_when_plan_category_limit_reached(): void
    {
        $this->client->subscriptions()->delete();
        $this->makeSubscription($this->client, 'Starter');

        foreach (range(1, 5) as $i) {
            $this->makeCategory($this->client, "Kategori {$i}");
        }

        $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Kategori Keenam',
        ])->assertSessionHasErrors('name');

        $this->assertSame(5, Category::where('client_id', $this->client->id)->count());
    }

    public function test_update_changes_name_and_regenerates_slug(): void
    {
        $category = $this->makeCategory($this->client, 'Stroller');

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Stroller Premium',
            'description' => 'Stroller kelas atas',
        ]);

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $category->refresh();
        $this->assertSame('Stroller Premium', $category->name);
        $this->assertSame('stroller-premium', $category->slug);
        $this->assertSame('Stroller kelas atas', $category->description);
    }

    public function test_update_keeps_slug_when_name_unchanged(): void
    {
        $category = $this->makeCategory($this->client, 'Stroller');
        $category->update(['slug' => 'stroller-lama']);

        $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Stroller',
        ])->assertSessionHasNoErrors();

        $this->assertSame('stroller-lama', $category->refresh()->slug);
    }

    public function test_update_rejects_duplicate_name(): void
    {
        $this->makeCategory($this->client, 'Stroller');
        $target = $this->makeCategory($this->client, 'Car Seat');

        $this->actingAs($this->admin)->put(route('admin.categories.update', $target), [
            'name' => 'Stroller',
        ])->assertSessionHasErrors('name');

        $this->assertSame('Car Seat', $target->refresh()->name);
    }

    public function test_update_forbidden_for_other_clients_category(): void
    {
        $foreign = $this->makeCategory($this->otherClient, 'Stroller');

        $this->actingAs($this->admin)->put(route('admin.categories.update', $foreign), [
            'name' => 'Bajakan',
        ])->assertForbidden();

        $this->assertSame('Stroller', $foreign->refresh()->name);
    }

    public function test_destroy_blocked_when_category_has_products(): void
    {
        $category = $this->makeCategory($this->client, 'Stroller');
        $this->makeProduct($this->client, $category);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_destroy_deletes_empty_category(): void
    {
        $category = $this->makeCategory($this->client, 'Stroller');

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_destroy_forbidden_for_other_clients_category(): void
    {
        $foreign = $this->makeCategory($this->otherClient, 'Stroller');

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $foreign))
            ->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $foreign->id]);
    }
}

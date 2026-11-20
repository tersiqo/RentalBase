<?php

namespace Tests\Feature\Chat;

use App\Events\ChatMessageSent;
use App\Events\ChatMessagesRead;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatBackendLogicTest extends TestCase
{
    use RefreshDatabase;

    protected Client $clientA;
    protected Client $clientB;
    protected User $adminA;
    protected User $adminB;
    protected User $customerA;
    protected User $customerB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientA = Client::firstOrCreate(
            ['subdomain' => 'store-a'],
            ['business_name' => 'Toko Rental A', 'status' => 'aktif']
        );

        $this->clientB = Client::firstOrCreate(
            ['subdomain' => 'store-b'],
            ['business_name' => 'Toko Rental B', 'status' => 'aktif']
        );

        $this->adminA = User::firstOrCreate(
            ['email' => 'admin.a@test.com'],
            ['name' => 'Admin A', 'role' => 'admin_rental', 'client_id' => $this->clientA->id, 'status' => 'aktif', 'password' => bcrypt('password')]
        );

        $this->adminB = User::firstOrCreate(
            ['email' => 'admin.b@test.com'],
            ['name' => 'Admin B', 'role' => 'admin_rental', 'client_id' => $this->clientB->id, 'status' => 'aktif', 'password' => bcrypt('password')]
        );

        $this->customerA = User::firstOrCreate(
            ['email' => 'cust.a@test.com'],
            ['name' => 'Customer A', 'role' => 'customer', 'status' => 'aktif', 'password' => bcrypt('password')]
        );

        $this->customerB = User::firstOrCreate(
            ['email' => 'cust.b@test.com'],
            ['name' => 'Customer B', 'role' => 'customer', 'status' => 'aktif', 'password' => bcrypt('password')]
        );
    }

    public function test_customer_can_get_or_create_active_conversation_with_store(): void
    {
        $response = $this->actingAs($this->customerA)
            ->getJson(route('customer.chat.active', ['client_id' => $this->clientA->id]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'conversation' => ['id', 'client_id', 'store_name'],
            'messages',
        ]);

        $this->assertDatabaseHas('chat_conversations', [
            'client_id'   => $this->clientA->id,
            'customer_id' => $this->customerA->id,
        ]);
    }

    public function test_customer_can_send_message_and_dispatches_realtime_event(): void
    {
        Event::fake([ChatMessageSent::class]);

        $response = $this->actingAs($this->customerA)
            ->postJson(route('customer.chat.send'), [
                'client_id' => $this->clientA->id,
                'message'   => 'Halo Toko A, apakah stroller ini ready?',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'sender_id'   => $this->customerA->id,
            'sender_role' => 'customer',
            'message'     => 'Halo Toko A, apakah stroller ini ready?',
        ]);

        Event::assertDispatched(ChatMessageSent::class, function ($event) {
            return $event->message->message === 'Halo Toko A, apakah stroller ini ready?'
                && $event->message->sender_role === 'customer';
        });
    }

    public function test_admin_can_reply_to_customer_and_dispatches_event(): void
    {
        Event::fake([ChatMessageSent::class]);

        $conv = ChatConversation::create([
            'client_id'   => $this->clientA->id,
            'customer_id' => $this->customerA->id,
        ]);

        $response = $this->actingAs($this->adminA)
            ->postJson(route('admin.chat.send', $conv->id), [
                'message' => 'Halo kak, stoknya ready dan siap dikirim!',
            ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender_role'     => 'admin',
            'message'         => 'Halo kak, stoknya ready dan siap dikirim!',
        ]);

        Event::assertDispatched(ChatMessageSent::class, function ($event) use ($conv) {
            return $event->message->conversation_id === $conv->id
                && $event->message->sender_role === 'admin';
        });
    }

    public function test_multi_tenant_isolation_admin_cannot_access_other_store_conversation(): void
    {
        $convStoreA = ChatConversation::create([
            'client_id'   => $this->clientA->id,
            'customer_id' => $this->customerA->id,
        ]);

        // Admin B mencoba mengintip atau membalas pesan di Toko A
        $response = $this->actingAs($this->adminB)
            ->getJson(route('admin.chat.messages', $convStoreA->id));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_other_customer_conversation(): void
    {
        $convCustomerA = ChatConversation::create([
            'client_id'   => $this->clientA->id,
            'customer_id' => $this->customerA->id,
        ]);

        // Customer B mencoba mengakses percakapan milik Customer A
        $response = $this->actingAs($this->customerB)
            ->getJson(route('customer.chat.messages', $convCustomerA->id));

        $response->assertStatus(403);
    }

    public function test_marking_chat_as_read_dispatches_chat_messages_read_event(): void
    {
        Event::fake([ChatMessagesRead::class]);

        $conv = ChatConversation::create([
            'client_id'          => $this->clientA->id,
            'customer_id'        => $this->customerA->id,
            'unread_by_admin'    => 2,
            'unread_by_customer' => 0,
        ]);

        $response = $this->actingAs($this->adminA)
            ->postJson(route('admin.chat.read', $conv->id));

        $response->assertStatus(200);

        Event::assertDispatched(ChatMessagesRead::class, function ($event) use ($conv) {
            return $event->conversationId === $conv->id
                && $event->readerRole === 'admin';
        });
    }

    public function test_admin_can_access_chat_inbox_page(): void
    {
        $conv = ChatConversation::create([
            'client_id'   => $this->clientA->id,
            'customer_id' => $this->customerA->id,
            'last_message_preview' => 'Halo min',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->adminA)
            ->get(route('admin.chat.index'));

        $response->assertStatus(200);
        $response->assertSee('Chat Penyewa');
        $response->assertSee('Customer A');
    }

    public function test_customer_can_send_product_context_message_and_admin_can_fetch_messages(): void
    {
        Event::fake([ChatMessageSent::class]);

        $category = \App\Models\Category::create([
            'client_id' => $this->clientA->id,
            'name'      => 'Sterilizer',
            'slug'      => 'sterilizer',
        ]);

        $product = Product::create([
            'client_id'            => $this->clientA->id,
            'category_id'          => $category->id,
            'name'                 => 'Haenim UV Sterilizer 4G Plus',
            'slug'                 => 'haenim-uv-sterilizer-4g-plus',
            'rental_price_per_day' => 80000,
            'status'               => 'aktif',
        ]);

        // 1. Customer mengirim pesan dengan konteks produk
        $sendResp = $this->actingAs($this->customerA)
            ->postJson(route('customer.chat.send'), [
                'client_id'    => $this->clientA->id,
                'message'      => 'Apakah Haenim UV Sterilizer 4G Plus ready disewa?',
                'context_type' => 'product',
                'context_id'   => $product->id,
            ]);

        $sendResp->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message.context_data.type', 'product')
            ->assertJsonPath('message.context_data.name', 'Haenim UV Sterilizer 4G Plus')
            ->assertJsonPath('message.context_data.price', 'Rp 80.000/hari');

        $convId = $sendResp->json('message.conversation_id');

        // 2. Customer memuat ulang percakapan aktif (harus 200 OK dan ada pesan konteksnya)
        $custResp = $this->actingAs($this->customerA)
            ->getJson(route('customer.chat.active', ['client_id' => $this->clientA->id]));

        $custResp->assertStatus(200)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.context_data.name', 'Haenim UV Sterilizer 4G Plus');

        // 3. Admin membuka percakapan (harus 200 OK dan ada pesan konteksnya)
        $adminResp = $this->actingAs($this->adminA)
            ->getJson(route('admin.chat.messages', $convId));

        $adminResp->assertStatus(200)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.context_data.name', 'Haenim UV Sterilizer 4G Plus');
    }

    public function test_customer_cannot_send_empty_message_without_attachment(): void
    {
        $response = $this->actingAs($this->customerA)
            ->postJson(route('customer.chat.send'), [
                'client_id' => $this->clientA->id,
                'message'   => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_admin_cannot_send_empty_message_without_attachment(): void
    {
        $conv = ChatConversation::create([
            'client_id'   => $this->clientA->id,
            'customer_id' => $this->customerA->id,
        ]);

        $response = $this->actingAs($this->adminA)
            ->postJson(route('admin.chat.send', $conv->id), [
                'message' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_unauthenticated_user_cannot_access_chat(): void
    {
        $response = $this->getJson(route('customer.chat.active'));
        $response->assertStatus(401);

        $responsePost = $this->postJson(route('customer.chat.send'), ['message' => 'Halo']);
        $responsePost->assertStatus(401);
    }

    public function test_order_context_messaging_and_detail_linking(): void
    {
        $order = Order::create([
            'client_id'         => $this->clientA->id,
            'user_id'           => $this->customerA->id,
            'order_number'      => 'ORD-TEST-999',
            'status'            => 'aktif',
            'rental_start_date' => now(),
            'rental_end_date'   => now()->addDays(3),
            'duration_days'     => 3,
            'shipping_address'  => 'Jl. Test No 123',
            'total_amount'      => 300000,
        ]);

        $sendResp = $this->actingAs($this->customerA)
            ->postJson(route('customer.chat.send'), [
                'client_id'    => $this->clientA->id,
                'message'      => 'Tanya status pengiriman pesanan ini ya min',
                'context_type' => 'order',
                'context_id'   => $order->id,
            ]);

        $sendResp->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message.context_data.type', 'order')
            ->assertJsonPath('message.context_data.order_number', 'ORD-TEST-999');

        $this->assertStringContainsString('/my-orders/' . $order->id, $sendResp->json('message.context_data.url'));
    }

    public function test_it_04_036_admin_can_access_chat_inbox_page(): void
    {
        $this->test_admin_can_access_chat_inbox_page();
    }

    public function test_it_04_037_admin_can_reply_to_customer_and_dispatches_event(): void
    {
        $this->test_admin_can_reply_to_customer_and_dispatches_event();
    }

    public function test_it_04_038_admin_can_send_message_with_image_attachment(): void
    {
        Storage::fake('public');

        $conv = ChatConversation::create([
            'client_id'   => $this->clientA->id,
            'customer_id' => $this->customerA->id,
        ]);

        $file = UploadedFile::fake()->image('unit-condition.png', 600, 600);

        $response = $this->actingAs($this->adminA)
            ->postJson(route('admin.chat.send', $conv->id), [
                'message'    => 'Berikut foto kondisi fisik unit sebelum dikirim.',
                'attachment' => $file,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertNotNull($response->json('message.attachment_url'));

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conv->id,
            'sender_role'     => 'admin',
            'message'         => 'Berikut foto kondisi fisik unit sebelum dikirim.',
        ]);

        $savedMsg = ChatMessage::where('conversation_id', $conv->id)->where('sender_role', 'admin')->first();
        Storage::disk('public')->assertExists($savedMsg->attachment_path);
    }

    public function test_it_04_039_marking_chat_as_read_dispatches_chat_messages_read_event(): void
    {
        $this->test_marking_chat_as_read_dispatches_chat_messages_read_event();
    }

    public function test_it_04_040_order_context_messaging_and_detail_linking(): void
    {
        $this->test_order_context_messaging_and_detail_linking();
    }

    public function test_it_04_041_multi_tenant_isolation_admin_cannot_access_other_store_conversation(): void
    {
        $this->test_multi_tenant_isolation_admin_cannot_access_other_store_conversation();
    }
}


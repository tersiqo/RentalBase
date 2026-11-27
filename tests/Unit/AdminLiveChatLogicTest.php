<?php

namespace Tests\Unit;

use App\Events\ChatMessageSent;
use App\Events\ChatMessagesRead;
use App\Models\Category;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminLiveChatLogicTest extends TestCase
{
    use RefreshDatabase;

    protected Client $clientA;
    protected Client $clientB;
    protected User $adminA;
    protected User $adminB;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->clientA = Client::create([
            'business_name' => 'Toko Rental Kamera A',
            'subdomain' => 'kamera-a',
            'status' => 'aktif',
        ]);

        $this->clientB = Client::create([
            'business_name' => 'Toko Rental Outdoor B',
            'subdomain' => 'outdoor-b',
            'status' => 'aktif',
        ]);

        $this->adminA = User::create([
            'name' => 'Admin Toko A',
            'email' => 'admin.a@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'client_id' => $this->clientA->id,
            'status' => 'aktif',
        ]);

        $this->adminB = User::create([
            'name' => 'Admin Toko B',
            'email' => 'admin.b@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'client_id' => $this->clientB->id,
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Yanuar Customer',
            'email' => 'yanuar.cust@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);
    }

    /**
     * UT-04-034: ChatConversation Model attributes, casts, and relationships
     */
    public function test_chat_conversation_model_schema_and_relations(): void
    {
        $conv = ChatConversation::create([
            'client_id' => $this->clientA->id,
            'customer_id' => $this->customer->id,
            'last_message_at' => now(),
            'last_message_preview' => 'Halo apakah barang ready?',
            'unread_by_admin' => 1,
            'unread_by_customer' => 0,
        ]);

        $this->assertInstanceOf(Client::class, $conv->client);
        $this->assertEquals($this->clientA->id, $conv->client->id);

        $this->assertInstanceOf(User::class, $conv->customer);
        $this->assertEquals($this->customer->id, $conv->customer->id);

        $this->assertInstanceOf(Carbon::class, $conv->last_message_at);
        $this->assertIsInt($conv->unread_by_admin);
        $this->assertIsInt($conv->unread_by_customer);

        // Relasi messages & latestMessage
        ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->customer->id,
            'sender_role' => 'customer',
            'message' => 'Pesan pertama',
        ]);

        $latestMsg = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->adminA->id,
            'sender_role' => 'admin',
            'message' => 'Pesan kedua terbaru',
        ]);

        $this->assertCount(2, $conv->messages);
        $this->assertEquals($latestMsg->id, $conv->latestMessage->id);
    }

    /**
     * UT-04-035: ChatMessage Model context and attachment accessors
     */
    public function test_chat_message_model_context_and_attachment_accessors(): void
    {
        $conv = ChatConversation::create([
            'client_id' => $this->clientA->id,
            'customer_id' => $this->customer->id,
        ]);

        // Attachment URL accessor
        $msgWithoutAttachment = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->adminA->id,
            'sender_role' => 'admin',
            'message' => 'Tanpa lampiran',
            'attachment_path' => null,
        ]);
        $this->assertNull($msgWithoutAttachment->attachment_url);

        $msgWithAttachment = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->adminA->id,
            'sender_role' => 'admin',
            'message' => 'Dengan lampiran',
            'attachment_path' => 'chat_attachments/bukti-cek.jpg',
        ]);
        $this->assertNotNull($msgWithAttachment->attachment_url);
        $this->assertStringContainsString('chat_attachments/bukti-cek.jpg', $msgWithAttachment->attachment_url);

        // Product Context accessor
        $category = Category::create([
            'client_id' => $this->clientA->id,
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);
        $product = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $category->id,
            'name' => 'Sony A7 IV',
            'slug' => 'sony-a7-iv',
            'rental_price_per_day' => 300000,
            'status' => 'aktif',
        ]);

        $msgProductContext = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->customer->id,
            'sender_role' => 'customer',
            'message' => 'Apakah Sony A7 IV tersedia?',
            'context_type' => 'product',
            'context_id' => $product->id,
        ]);

        $contextData = $msgProductContext->context_data;
        $this->assertIsArray($contextData);
        $this->assertEquals('product', $contextData['type']);
        $this->assertEquals('Sony A7 IV', $contextData['name']);
        $this->assertStringContainsString('300.000', $contextData['price']);

        // Order Context accessor
        $order = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CHAT-001',
            'status' => 'sedang_disewa',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 600000,
            'shipping_address' => 'Jl. Ijen No. 12',
            'shipping_method' => 'kurir',
        ]);

        $msgOrderContext = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->customer->id,
            'sender_role' => 'customer',
            'message' => 'Tanya pesanan ini min',
            'context_type' => 'order',
            'context_id' => $order->id,
        ]);

        $orderContextData = $msgOrderContext->context_data;
        $this->assertIsArray($orderContextData);
        $this->assertEquals('order', $orderContextData['type']);
        $this->assertEquals('ORD-CHAT-001', $orderContextData['order_number']);
    }

    /**
     * UT-04-036: recordNewMessage updates preview and unread counters accordingly
     */
    public function test_conversation_record_new_message_and_unread_counters(): void
    {
        $conv = ChatConversation::create([
            'client_id' => $this->clientA->id,
            'customer_id' => $this->customer->id,
            'unread_by_admin' => 0,
            'unread_by_customer' => 0,
        ]);

        // Customer sends message -> unread_by_admin should increment
        $custMsg = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->customer->id,
            'sender_role' => 'customer',
            'message' => 'Halo admin, apakah bisa sewa 3 hari?',
        ]);

        $conv->recordNewMessage($custMsg);
        $conv->refresh();

        $this->assertEquals(1, $conv->unread_by_admin);
        $this->assertEquals(0, $conv->unread_by_customer);
        $this->assertEquals('Halo admin, apakah bisa sewa 3 hari?', $conv->last_message_preview);

        // Admin replies -> unread_by_customer should increment
        $adminMsg = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->adminA->id,
            'sender_role' => 'admin',
            'message' => 'Bisa kak, silakan checkout melalui web.',
        ]);

        $conv->recordNewMessage($adminMsg);
        $conv->refresh();

        $this->assertEquals(1, $conv->unread_by_admin); // remains 1 until marked as read
        $this->assertEquals(1, $conv->unread_by_customer);
        $this->assertEquals('Bisa kak, silakan checkout melalui web.', $conv->last_message_preview);
    }

    /**
     * UT-04-037: markAsReadFor admin and customer resets counters and updates is_read flag
     */
    public function test_conversation_mark_as_read_synchronization(): void
    {
        $conv = ChatConversation::create([
            'client_id' => $this->clientA->id,
            'customer_id' => $this->customer->id,
            'unread_by_admin' => 2,
            'unread_by_customer' => 1,
        ]);

        $custMsg = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->customer->id,
            'sender_role' => 'customer',
            'message' => 'Pesan dari customer',
            'is_read' => false,
        ]);

        $adminMsg = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->adminA->id,
            'sender_role' => 'admin',
            'message' => 'Pesan dari admin',
            'is_read' => false,
        ]);

        // Admin marks as read
        $conv->markAsReadFor('admin');
        $conv->refresh();
        $custMsg->refresh();
        $adminMsg->refresh();

        $this->assertEquals(0, $conv->unread_by_admin);
        $this->assertTrue($custMsg->is_read);
        $this->assertNotNull($custMsg->read_at);
        $this->assertFalse($adminMsg->is_read); // admin's own message still not read by customer

        // Customer marks as read
        $conv->markAsReadFor('customer');
        $conv->refresh();
        $adminMsg->refresh();

        $this->assertEquals(0, $conv->unread_by_customer);
        $this->assertTrue($adminMsg->is_read);
        $this->assertNotNull($adminMsg->read_at);
    }

    /**
     * UT-04-038: ChatMessageSent event channels and payload structure
     */
    public function test_chat_message_sent_broadcasting_channels_and_payload(): void
    {
        $conv = ChatConversation::create([
            'client_id' => $this->clientA->id,
            'customer_id' => $this->customer->id,
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $this->adminA->id,
            'sender_role' => 'admin',
            'message' => 'Pesan balasan admin untuk siaran realtime',
            'is_read' => false,
        ]);

        $event = new ChatMessageSent($message);

        // Check broadcast channels
        $channels = $event->broadcastOn();
        $this->assertNotEmpty($channels);
        $this->assertEquals('private-chat.conversation.' . $conv->id, $channels[0]->name);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $channels[1]->name);

        // Check broadcast name and payload
        $this->assertEquals('chat.message', $event->broadcastAs());
        $payload = $event->broadcastWith();

        $this->assertEquals($message->id, $payload['id']);
        $this->assertEquals($conv->id, $payload['conversation_id']);
        $this->assertEquals('admin', $payload['sender_role']);
        $this->assertEquals('Pesan balasan admin untuk siaran realtime', $payload['message']);
        $this->assertFalse($payload['is_read']);
        $this->assertArrayHasKey('time_formatted', $payload);
    }

    /**
     * UT-04-039: ChatMessagesRead event channels and payload structure (Double Check Sync)
     */
    public function test_chat_messages_read_broadcasting_and_double_check_payload(): void
    {
        $conv = ChatConversation::create([
            'client_id' => $this->clientA->id,
            'customer_id' => $this->customer->id,
        ]);

        $event = new ChatMessagesRead($conv->id, 'admin');

        $channels = $event->broadcastOn();
        $this->assertNotEmpty($channels);
        $this->assertEquals('private-chat.conversation.' . $conv->id, $channels[0]->name);

        $this->assertEquals('chat.read', $event->broadcastAs());
        $payload = $event->broadcastWith();

        $this->assertEquals($conv->id, $payload['conversation_id']);
        $this->assertEquals('admin', $payload['reader_role']);
        $this->assertArrayHasKey('read_at', $payload);
    }

    /**
     * UT-04-040: AdminChatController cross-tenant isolation and empty message validation
     */
    public function test_admin_chat_controller_cross_tenant_isolation_and_validation(): void
    {
        $convA = ChatConversation::create([
            'client_id' => $this->clientA->id,
            'customer_id' => $this->customer->id,
        ]);

        // Cross-tenant access attempt by Admin B
        $responseForbidden = $this->actingAs($this->adminB)
            ->getJson(route('admin.chat.messages', $convA->id));

        $responseForbidden->assertStatus(403);

        // Empty message validation by Admin A
        $responseValidation = $this->actingAs($this->adminA)
            ->postJson(route('admin.chat.send', $convA->id), [
                'message' => '',
                'attachment' => null,
            ]);

        $responseValidation->assertStatus(422);
        $responseValidation->assertJsonValidationErrors(['message']);
    }
}

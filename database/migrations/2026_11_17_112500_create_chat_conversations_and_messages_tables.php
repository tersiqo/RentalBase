<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('last_message_at')->nullable()->index();
            $table->string('last_message_preview', 255)->nullable();
            $table->unsignedInteger('unread_by_admin')->default(0);
            $table->unsignedInteger('unread_by_customer')->default(0);
            $table->timestamps();

            // 1 Toko Rental dan 1 Customer hanya memiliki 1 thread percakapan tunggal
            $table->unique(['client_id', 'customer_id'], 'chat_conv_client_customer_unique');
            $table->index(['client_id', 'last_message_at'], 'chat_conv_client_last_msg_idx');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('sender_role', 20)->default('customer'); // 'customer' | 'admin'
            $table->text('message');
            $table->string('attachment_path', 255)->nullable();
            $table->string('context_type', 30)->nullable(); // 'product' | 'order' | null
            $table->unsignedBigInteger('context_id')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at'], 'chat_msg_conv_created_idx');
            $table->index(['conversation_id', 'is_read'], 'chat_msg_conv_is_read_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};

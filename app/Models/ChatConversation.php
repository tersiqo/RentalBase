<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatConversation extends Model
{
    use HasFactory;

    protected $table = 'chat_conversations';

    protected $fillable = [
        'client_id',
        'customer_id',
        'last_message_at',
        'last_message_preview',
        'unread_by_admin',
        'unread_by_customer',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'unread_by_admin' => 'integer',
            'unread_by_customer' => 'integer',
        ];
    }

    /**
     * Relasi ke Toko Rental (Client).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relasi ke Customer/Penyewa (User).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Seluruh daftar pesan dalam obrolan ini.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /**
     * Pesan terbaru dalam obrolan ini.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Tandai pesan telah dibaca oleh role pengguna yang sedang membuka chat.
     */
    public function markAsReadFor(string $role): void
    {
        if ($role === 'admin' || $role === 'admin_rental') {
            if ($this->unread_by_admin > 0) {
                $this->update(['unread_by_admin' => 0]);
            }
            // Update status is_read pesan customer
            $this->messages()
                ->where('sender_role', 'customer')
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        } elseif ($role === 'customer') {
            if ($this->unread_by_customer > 0) {
                $this->update(['unread_by_customer' => 0]);
            }
            // Update status is_read pesan admin
            $this->messages()
                ->where('sender_role', 'admin')
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
    }

    /**
     * Tambahkan jumlah unread counter untuk penerima pesan.
     */
    public function recordNewMessage(ChatMessage $message): void
    {
        $preview = mb_strimwidth($message->message, 0, 100, '...');

        $updates = [
            'last_message_at' => $message->created_at ?? now(),
            'last_message_preview' => $preview,
        ];

        if ($message->sender_role === 'customer') {
            $updates['unread_by_admin'] = $this->unread_by_admin + 1;
        } else {
            $updates['unread_by_customer'] = $this->unread_by_customer + 1;
        }

        $this->update($updates);
    }
}

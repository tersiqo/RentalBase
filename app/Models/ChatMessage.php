<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_role',
        'message',
        'attachment_path',
        'context_type',
        'context_id',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'context_id' => 'integer',
        ];
    }

    /**
     * Obrolan tempat pesan ini bernaung.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Pengguna pengirim pesan.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relasi opsional ke Produk jika konteks adalah pertanyaan seputar produk.
     */
    public function productContext(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'context_id');
    }

    /**
     * Relasi opsional ke Pesanan jika konteks adalah pertanyaan seputar pesanan.
     */
    public function orderContext(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'context_id');
    }

    /**
     * URL Lampiran foto jika ada.
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return storage_url($this->attachment_path);
    }

    /**
     * Format data konteks yang disematkan dalam pesan (Kartu Produk / Order).
     */
    public function getContextDataAttribute(): ?array
    {
        if ($this->context_type === 'product' && $this->context_id) {
            $product = $this->productContext;
            if ($product) {
                $subdomain = $product->client?->subdomain ?? $this->conversation?->client?->subdomain ?? 'babyrental';

                return [
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => 'Rp ' . number_format((float) ($product->rental_price_per_day ?? 0), 0, ',', '.') . '/hari',
                    'image' => $product->cover_image_url,
                    'url' => route('customer.product.show', [
                        'subdomain' => $subdomain,
                        'product' => $product->id,
                    ]),
                ];
            }
        } elseif ($this->context_type === 'order' && $this->context_id) {
            $order = $this->orderContext;
            if ($order) {
                return [
                    'type' => 'order',
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? ('ORD-' . $order->id),
                    'status_label' => $order->status_label ?? ucfirst($order->status),
                    'status_color' => $order->status_color ?? 'bg-gray-100 text-gray-800',
                    'total' => $order->formatted_total ?? ('Rp ' . number_format($order->total_amount, 0, ',', '.')),
                    'date_range' => $order->date_range ?? '-',
                    'url' => route('customer.orders.show', $order->id),
                ];
            }
        }

        return null;
    }
}

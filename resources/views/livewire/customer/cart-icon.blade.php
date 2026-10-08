<a href="{{ route('customer.cart', ['subdomain' => $client->subdomain]) }}" wire:navigate class="relative text-gray-600 hover:text-gray-900 transition-colors" title="Keranjang Sewa">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
    @if($count > 0)
        <span class="absolute -top-1 -right-2 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-primary-600 border-2 border-white rounded-full transition-transform duration-300 transform scale-100">{{ $count }}</span>
    @endif
</a>

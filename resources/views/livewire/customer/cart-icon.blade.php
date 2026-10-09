<a href="{{ route('customer.cart', ['subdomain' => $client->subdomain]) }}" wire:navigate class="relative w-[42px] h-[42px] rounded-xl border border-gray-200 flex items-center justify-center bg-white text-gray-700 hover:border-primary-500 hover:text-primary-500 transition-colors" title="Keranjang Sewa">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.5 12h11L21 7H6"/></svg>
    @if($count > 0)
        <span class="absolute -top-1.5 -right-1.5 bg-primary-500 text-white text-[11px] font-bold rounded-full min-w-[19px] h-[19px] flex items-center justify-center px-1 shadow-sm">{{ $count }}</span>
    @endif
</a>

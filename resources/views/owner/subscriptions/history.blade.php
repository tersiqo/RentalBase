@extends('layouts.owner')

@section('title', 'Riwayat Subscription — RentalBase Platform')
@section('page-title', 'Riwayat Subscription')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- Breadcrumb & Title --}}
    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.subscriptions.index') }}" class="text-xs text-orange-600 hover:underline flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Subscription
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-medium">Riwayat Perpanjangan</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight sm:text-3xl mt-1">
                Riwayat &amp; Log Perpanjangan: {{ $subscription->client?->business_name ?? 'Tenant' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Catatan aktivitas perpanjangan, nomor referensi pembayaran, dan perubahan masa aktif yang terverifikasi.
            </p>
        </div>
    </div>

    {{-- Card Detail Subscription Saat Ini --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Subdomain</span>
            <p class="text-sm font-bold text-slate-900 mt-0.5 font-mono">{{ $subscription->client?->subdomain }}</p>
        </div>
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Paket Langganan</span>
            <p class="text-sm font-bold text-orange-600 mt-0.5">{{ ucfirst($subscription->plan_name) }}</p>
        </div>
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Masa Aktif Berlaku</span>
            <p class="text-sm font-bold text-slate-900 mt-0.5">
                {{ $subscription->start_date ? $subscription->start_date->format('d M Y') : '-' }} s/d {{ $subscription->end_date ? $subscription->end_date->format('d M Y') : '-' }}
            </p>
        </div>
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</span>
            <p class="text-sm font-bold text-emerald-600 mt-0.5 uppercase">{{ $subscription->status }}</p>
        </div>
    </div>

    {{-- Riwayat Aktivitas Log --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-extrabold text-slate-900">Catatan Aktivitas Perpanjangan</h3>
            <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-600 rounded-full">
                {{ count($logs) }} Aktivitas Tercatat
            </span>
        </div>

        <div class="p-6 divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="py-4 first:pt-0 last:pb-0 space-y-1.5">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="font-bold text-slate-700 uppercase tracking-wider">{{ str_replace('_', ' ', $log->action) }}</span>
                        <span>{{ $log->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                    <p class="text-sm font-medium text-slate-800">{{ $log->description }}</p>
                    <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                        <span>Diproses oleh: <strong class="text-slate-600">{{ $log->user?->name ?? 'Owner Platform' }}</strong></span>
                        <span>{{ $log->ip_address ? 'IP: ' . $log->ip_address : '' }}</span>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-400">
                    <p class="text-base font-semibold text-slate-600">Belum ada catatan aktivitas perpanjangan manual.</p>
                    <p class="text-xs text-slate-400 mt-1">Setiap kali Owner melakukan perpanjangan, catatan log akan otomatis tampil di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

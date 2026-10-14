@extends('layouts.owner')

@section('title', 'Client & Tenant — RentalBase Platform')
@section('page-title', 'Client & Tenant')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    // Modal Detail
    showDetailModal: false,
    activeTenant: null,

    // Modal Edit
    showEditModal: false,
    editTenant: {
        id: null,
        business_name: '',
        subdomain: '',
        description: '',
        theme_color: '#F97316',
        admin_name: '',
        admin_email: '',
        admin_phone: '',
        admin_address: '',
        logo_url: null
    },
    editUrl: '',

    // Modal Ubah Status Akun
    showStatusModal: false,
    statusTenant: {
        id: null,
        business_name: '',
        status: 'aktif'
    },
    statusUrl: '',

    // Modal Kelola Subscription
    showSubModal: false,
    subTenant: {
        id: null,
        business_name: '',
        plan_name: 'starter',
        status: 'aktif',
        billing_cycle: 'monthly',
        end_date: ''
    },
    subUrl: '',

    openDetail(tenant) {
        this.activeTenant = tenant;
        this.showDetailModal = true;
    },

    openEdit(tenant) {
        this.editTenant = {
            id: tenant.id,
            business_name: tenant.business_name || '',
            subdomain: tenant.subdomain || '',
            description: tenant.description || '',
            theme_color: tenant.theme_color || '#F97316',
            admin_name: tenant.admin?.name || '',
            admin_email: tenant.admin?.email || '',
            admin_phone: tenant.admin?.phone || '',
            admin_address: tenant.admin?.address || '',
            logo_url: tenant.logo_url || null
        };
        this.editUrl = '{{ url('/owner/tenants') }}/' + tenant.id;
        this.showEditModal = true;
    },

    openStatus(tenant) {
        this.statusTenant = {
            id: tenant.id,
            business_name: tenant.business_name,
            status: tenant.status || 'aktif'
        };
        this.statusUrl = '{{ url('/owner/tenants') }}/' + tenant.id + '/status';
        this.showStatusModal = true;
    },

    openSubscription(tenant) {
        this.subTenant = {
            id: tenant.id,
            business_name: tenant.business_name,
            account_status: tenant.status || 'aktif',
            plan_name: tenant.subscription?.plan_name || 'starter',
            status: tenant.subscription?.status || 'aktif',
            is_nonaktif: tenant.subscription?.status === 'nonaktif',
            billing_cycle: tenant.subscription?.billing_cycle || 'monthly',
            end_date: tenant.subscription?.end_date_raw || ''
        };
        this.subUrl = '{{ url('/owner/tenants') }}/' + tenant.id + '/subscription';
        this.showSubModal = true;
    },

    computeSubStatus() {
        if (this.subTenant.is_nonaktif || this.subTenant.account_status === 'nonaktif') {
            return {
                key: 'nonaktif',
                label: 'Nonaktif',
                title: 'Subscription dihentikan',
                desc: 'Status ini digunakan ketika subscription dinonaktifkan secara manual oleh Owner untuk alasan administratif atau kebijakan platform.',
                badgeClass: 'bg-slate-100 text-slate-700 border-slate-300',
                dotClass: 'bg-slate-500'
            };
        }

        if (!this.subTenant.end_date) {
            return {
                key: 'kadaluarsa',
                label: 'Kadaluwarsa',
                title: 'Masa berlangganan berakhir',
                desc: 'Tanggal akhir masa berlangganan belum ditentukan atau sudah terlewati.',
                badgeClass: 'bg-rose-50 text-rose-700 border-rose-200',
                dotClass: 'bg-rose-500'
            };
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const end = new Date(this.subTenant.end_date);
        end.setHours(0, 0, 0, 0);

        if (end < today) {
            return {
                key: 'kadaluarsa',
                label: 'Kadaluwarsa',
                title: 'Masa berlangganan berakhir',
                desc: 'Status ini diberikan ketika tanggal saat ini telah melewati tanggal berakhir (End Date) dan belum diperpanjang.',
                badgeClass: 'bg-rose-50 text-rose-700 border-rose-200',
                dotClass: 'bg-rose-500'
            };
        }

        // Pemicu Status Akun Tenant:
        // Menunggu Verifikasi jika status akunnya juga menunggu verifikasi (pendaftaran belum diverifikasi)
        if (this.subTenant.account_status === 'menunggu_verifikasi') {
            return {
                key: 'menunggu_verifikasi',
                label: 'Menunggu Verifikasi',
                title: 'Masih menunggu pemeriksaan',
                desc: 'Status ini diberikan ketika status akun tenant masih menunggu verifikasi (pendaftaran tenant belum diverifikasi oleh Owner).',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-200',
                dotClass: 'bg-amber-500'
            };
        }

        // Aktif jika status akunnya sudah aktif (pendaftaran sudah diverifikasi) dan tanggal masih dalam masa berlaku
        return {
            key: 'aktif',
            label: 'Aktif',
            title: 'Subscription dapat digunakan',
            desc: 'Status ini diberikan ketika status akun tenant sudah aktif (pendaftaran tenant sudah diverifikasi) dan tanggal saat ini masih berada dalam masa berlangganan.',
            badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            dotClass: 'bg-emerald-500'
        };
    },

    addMonths(months) {
        let baseDate = this.subTenant.end_date ? new Date(this.subTenant.end_date) : new Date();
        if (isNaN(baseDate.getTime()) || baseDate < new Date()) {
            baseDate = new Date();
        }
        baseDate.setMonth(baseDate.getMonth() + months);
        this.subTenant.end_date = baseDate.toISOString().split('T')[0];
    }
}">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm space-y-1">
            <div class="font-bold flex items-center gap-2 text-rose-700">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Periksa kembali isian formulir:
            </div>
            <ul class="list-disc list-inside text-xs text-rose-600 pl-1 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200/60">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.dashboard') }}" class="text-xs text-orange-600 hover:underline flex items-center gap-1 font-semibold">
                    &larr; Dashboard
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500 font-medium">Manajemen Client & Tenant</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight sm:text-3xl mt-1">
                Kelola Client & Tenant Rental
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Pantau detail usaha toko, identitas pengelola, status akun operasional, dan status keanggotaan subscription.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ $counts['aktif'] }} Tenant Aktif
            </span>
            @if($counts['suspended'] > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    {{ $counts['suspended'] }} Suspended
                </span>
            @endif
        </div>
    </div>

    {{-- Ringkasan Kartu Metrik --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Aktif --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Tenant Aktif</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($counts['aktif']) }}</h3>
                <p class="text-[11px] text-emerald-600 mt-0.5">Toko beroperasi normal</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Suspended --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Suspended</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($counts['suspended']) }}</h3>
                <p class="text-[11px] text-rose-600 mt-0.5">Operasional ditangguhkan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
        </div>

        {{-- Langganan Segera Berakhir --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Masa Aktif Kritis</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($counts['expiring_soon']) }}</h3>
                <p class="text-[11px] text-amber-600 mt-0.5">Jatuh tempo &le; 7 hari</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Filter Status Tabs, Paket & Search Bar --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        {{-- Status Tabs --}}
        <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200/60 self-start sm:self-auto overflow-x-auto max-w-full">
            <a href="{{ route('owner.tenants.index', ['status' => 'all', 'plan' => request('plan'), 'search' => request('search')]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Semua ({{ $counts['total'] }})
            </a>
            <a href="{{ route('owner.tenants.index', ['status' => 'aktif', 'plan' => request('plan'), 'search' => request('search')]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'aktif' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Aktif ({{ $counts['aktif'] }})
            </a>
            <a href="{{ route('owner.tenants.index', ['status' => 'suspended', 'plan' => request('plan'), 'search' => request('search')]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'suspended' ? 'bg-white text-rose-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Suspended ({{ $counts['suspended'] }})
            </a>
            @if($counts['nonaktif'] > 0)
                <a href="{{ route('owner.tenants.index', ['status' => 'nonaktif', 'plan' => request('plan'), 'search' => request('search')]) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $status === 'nonaktif' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Nonaktif ({{ $counts['nonaktif'] }})
                </a>
            @endif
        </div>

        {{-- Filter Plan & Search Input --}}
        <form method="GET" action="{{ route('owner.tenants.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <input type="hidden" name="status" value="{{ $status }}">

            {{-- Filter Paket --}}
            <select name="plan" onchange="this.form.submit()"
                    class="bg-white border border-slate-200 text-xs font-semibold rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                <option value="">Semua Paket</option>
                <option value="starter" {{ request('plan') === 'starter' ? 'selected' : '' }}>Starter</option>
                <option value="business" {{ request('plan') === 'business' ? 'selected' : '' }}>Business</option>
                <option value="professional" {{ request('plan') === 'professional' ? 'selected' : '' }}>Professional</option>
            </select>

            {{-- Input Search --}}
            <div class="relative w-full sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari toko, subdomain, PIC..."
                       class="w-full bg-white border border-slate-200 text-xs rounded-xl pl-9 pr-8 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                    <a href="{{ route('owner.tenants.index', ['status' => $status, 'plan' => request('plan')]) }}"
                       class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>

            <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-bold transition-colors shrink-0">
                Cari
            </button>
        </form>
    </div>

    {{-- Main Tenant List Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Tenant Terverifikasi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh client rental yang telah diverifikasi dengan status operasional dan langganan.</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Menampilkan {{ $tenants->count() }} dari {{ $tenants->total() }} tenant
            </div>
        </div>

        @if($tenants->isEmpty())
            <div class="py-16 px-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Tidak Ada Data Tenant Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    @if(request('search') || request('plan') || $status !== 'all')
                        Tidak ada tenant yang cocok dengan filter atau kata kunci pencarian yang dipilih.
                    @else
                        Belum ada tenant yang terdaftar di dalam sistem RentalBase.
                    @endif
                </p>
                @if(request('search') || request('plan') || $status !== 'all')
                    <div class="mt-4">
                        <a href="{{ route('owner.tenants.index') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 transition-colors">
                            Reset Filter
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th scope="col" class="py-3.5 px-6">Tenant & Usaha</th>
                            <th scope="col" class="py-3.5 px-6">Identitas PIC / Kontak</th>
                            <th scope="col" class="py-3.5 px-6">Statistik Toko</th>
                            <th scope="col" class="py-3.5 px-6">Paket & Masa Aktif</th>
                            <th scope="col" class="py-3.5 px-6">Status Akun</th>
                            <th scope="col" class="py-3.5 px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($tenants as $tenant)
                            @php
                                $admin = $tenant->adminUser;
                                $sub = $tenant->latestSubscription;
                                $activeSub = $tenant->activeSubscription;

                                // Subdomain live URL
                                $liveUrl = route('customer.home', $tenant->subdomain);

                                // Inisial nama usaha
                                $words = array_values(array_filter(explode(' ', trim($tenant->business_name))));
                                if (count($words) >= 2) {
                                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                } else {
                                    $initials = strtoupper(substr($tenant->business_name ?: 'RB', 0, 2));
                                }

                                // Perhitungan sisa hari subscription
                                $daysRemaining = null;
                                $isExpiring = false;
                                $isExpired = false;
                                if ($sub && $sub->end_date) {
                                    $daysRemaining = (int) now()->diffInDays($sub->end_date, false);
                                    if ($daysRemaining < 0) {
                                        $isExpired = true;
                                    } elseif ($daysRemaining <= 7) {
                                        $isExpiring = true;
                                    }
                                }

                                // Logo asset URL
                                $logoUrl = $tenant->logo ? asset('storage/' . $tenant->logo) : null;

                                // payload data tenant untuk Alpine Modal
                                $tenantPayload = [
                                    'id'            => $tenant->id,
                                    'business_name' => $tenant->business_name,
                                    'subdomain'     => $tenant->subdomain,
                                    'live_url'      => $liveUrl,
                                    'description'   => $tenant->description,
                                    'theme_color'   => $tenant->theme_color ?: '#F97316',
                                    'status'        => $tenant->status,
                                    'created_at'    => $tenant->created_at ? $tenant->created_at->translatedFormat('d F Y') : '-',
                                    'logo_url'      => $logoUrl,
                                    'initials'      => $initials,
                                    'products_count'=> $tenant->products_count,
                                    'units_count'   => $tenant->equipment_units_count,
                                    'admin'         => [
                                        'name'    => $admin?->name ?: '-',
                                        'email'   => $admin?->email ?: '-',
                                        'phone'   => $admin?->phone ?: '-',
                                        'address' => $admin?->address ?: '-',
                                        'status'  => $admin?->status ?: 'aktif',
                                    ],
                                    'subscription'  => [
                                        'plan_name'     => $sub?->plan_name ?: 'starter',
                                        'status'        => $sub?->status ?: 'aktif',
                                        'billing_cycle' => $sub?->billing_cycle ?: 'monthly',
                                        'price'         => $sub?->price ? number_format($sub->price, 0, ',', '.') : number_format(config("packages.{$sub?->plan_name}.price", 0), 0, ',', '.'),
                                        'max_products'  => $sub?->max_products !== null ? $sub->max_products : config("packages.{$sub?->plan_name}.limits.products"),
                                        'max_users'     => $sub?->max_users ?: config("packages.{$sub?->plan_name}.limits.admins", 1),
                                        'start_date'    => $sub?->start_date ? $sub->start_date->translatedFormat('d M Y') : '-',
                                        'end_date'      => $sub?->end_date ? $sub->end_date->translatedFormat('d M Y') : '-',
                                        'end_date_raw'  => $sub?->end_date ? $sub->end_date->format('Y-m-d') : '',
                                        'days_left'     => $daysRemaining,
                                        'is_expired'    => $isExpired,
                                        'is_expiring'   => $isExpiring,
                                    ]
                                ];
                            @endphp

                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Kolom 1: Tenant & Usaha --}}
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-center gap-3">
                                        @if($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $tenant->business_name }}"
                                                 class="w-10 h-10 rounded-xl object-cover border border-slate-200/80 shadow-xs shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-extrabold text-xs shadow-xs shrink-0"
                                                 style="background-color: {{ $tenant->theme_color ? $tenant->theme_color . '15' : '#FFF7ED' }}; color: {{ $tenant->theme_color ?: '#EA580C' }}; border: 1px solid {{ $tenant->theme_color ? $tenant->theme_color . '30' : '#FED7AA' }};">
                                                {{ $initials }}
                                            </div>
                                        @endif

                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-slate-900 leading-snug">{{ $tenant->business_name }}</span>
                                                @if($tenant->theme_color)
                                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 border border-white shadow-xs"
                                                          style="background-color: {{ $tenant->theme_color }};"
                                                          title="Warna Brand: {{ $tenant->theme_color }}"></span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <a href="{{ $liveUrl }}" target="_blank"
                                                   class="font-mono text-xs text-orange-600 hover:text-orange-700 hover:underline flex items-center gap-1">
                                                    <span>{{ $tenant->subdomain }}.rentalbase.id</span>
                                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom 2: Identitas PIC / Kontak --}}
                                <td class="py-4 px-6 align-middle">
                                    <span class="block font-semibold text-slate-900 text-xs leading-snug">
                                        {{ $admin?->name ?: '-' }}
                                    </span>
                                    <span class="block text-xs text-slate-500 mt-0.5">
                                        {{ $admin?->email ?: '-' }}
                                    </span>
                                    @if($admin?->phone)
                                        @php
                                            $waClean = preg_replace('/[^0-9]/', '', $admin->phone);
                                            if (str_starts_with($waClean, '0')) {
                                                $waClean = '62' . substr($waClean, 1);
                                            }
                                        @endphp
                                        <a href="https://wa.me/{{ $waClean }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 hover:underline mt-1">
                                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.101.005.241-.038.375.285.144.346.491 1.198.534 1.285.044.086.073.188.015.303-.058.116-.087.188-.173.289l-.26.303c-.087.086-.177.181-.076.355.101.173.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                            </svg>
                                            <span>{{ $admin->phone }}</span>
                                        </a>
                                    @endif
                                </td>

                                {{-- Kolom 3: Statistik Toko --}}
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/60">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            {{ $tenant->products_count }} Produk
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/60">
                                            {{ $tenant->equipment_units_count }} Unit
                                        </span>
                                    </div>
                                </td>

                                {{-- Kolom 4: Paket & Masa Berlaku --}}
                                <td class="py-4 px-6 align-middle">
                                    @if($sub)
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize
                                                {{ $sub->plan_name === 'professional' ? 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20' : '' }}
                                                {{ $sub->plan_name === 'business' ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20' : '' }}
                                                {{ $sub->plan_name === 'starter' ? 'bg-slate-100 text-slate-700 ring-1 ring-slate-600/20' : '' }}">
                                                {{ $sub->plan_name }}
                                            </span>
                                            <span class="text-[11px] font-semibold text-slate-500">
                                                Rp {{ number_format($sub->price, 0, ',', '.') }}/bln
                                            </span>
                                        </div>

                                        <div class="mt-1 flex items-center gap-1.5 text-xs">
                                            @if($isExpired)
                                                <span class="inline-flex items-center gap-1 font-bold text-rose-600">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Kadaluarsa ({{ $sub->end_date ? $sub->end_date->translatedFormat('d M Y') : '-' }})
                                                </span>
                                            @elseif($isExpiring)
                                                <span class="inline-flex items-center gap-1 font-semibold text-amber-600">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                                    Sisa {{ $daysRemaining }} hari ({{ $sub->end_date ? $sub->end_date->translatedFormat('d M') : '-' }})
                                                </span>
                                            @else
                                                <span class="text-slate-500">
                                                    Hingga: <strong class="text-slate-700">{{ $sub->end_date ? $sub->end_date->translatedFormat('d M Y') : '-' }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ada paket</span>
                                    @endif
                                </td>

                                {{-- Kolom 5: Status Akun --}}
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    @if($tenant->status === 'aktif')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @elseif($tenant->status === 'suspended')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Suspended
                                        </span>
                                    @elseif($tenant->status === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu Verifikasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            {{ ucfirst($tenant->status ?: 'Nonaktif') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom 6: Aksi Dropdown / Tombol --}}
                                <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Tombol Detail --}}
                                        <button type="button"
                                                @click="openDetail({{ json_encode($tenantPayload) }})"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:text-orange-600 hover:bg-orange-50 border border-slate-200 transition-colors"
                                                title="Lihat Detail Lengkap">
                                            Detail
                                        </button>
                                        {{-- Tombol Kelola Subscription --}}
                                        <button type="button"
                                                @click="openSubscription({{ json_encode($tenantPayload) }})"
                                                class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 border border-blue-200 transition-colors"
                                                title="Kelola Paket & Subscription">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </button>

                                        {{-- Tombol Ubah Status Akun --}}
                                        <button type="button"
                                                @click="openStatus({{ json_encode($tenantPayload) }})"
                                                class="p-1.5 rounded-lg {{ $tenant->status === 'aktif' ? 'text-rose-600 hover:bg-rose-50 border-rose-200' : 'text-emerald-600 hover:bg-emerald-50 border-emerald-200' }} border transition-colors"
                                                title="{{ $tenant->status === 'aktif' ? 'Tangguhkan / Suspend Akun' : 'Aktifkan Akun' }}">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tenants->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $tenants->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 1: DETAIL LENGKAP TENANT & SUBSCRIPTION                             --}}
    {{-- ========================================================================= --}}
    <div x-show="showDetailModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5"
         role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showDetailModal = false"></div>

        <div x-show="showDetailModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden z-10">

            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-gradient-to-r from-orange-50/60 to-white">
                <div class="flex items-center gap-3.5">
                    <template x-if="activeTenant?.logo_url">
                        <img :src="activeTenant?.logo_url" class="w-12 h-12 rounded-2xl object-cover border border-slate-200/80 shadow-xs">
                    </template>
                    <template x-if="!activeTenant?.logo_url">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-200/70 text-orange-600 font-black text-sm flex items-center justify-center shadow-xs"
                             x-text="activeTenant?.initials"></div>
                    </template>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-extrabold text-slate-900" x-text="activeTenant?.business_name"></h3>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase"
                                  :class="{
                                      'bg-emerald-50 text-emerald-700 border border-emerald-200': activeTenant?.status === 'aktif',
                                      'bg-rose-50 text-rose-700 border border-rose-200': activeTenant?.status === 'suspended',
                                      'bg-amber-50 text-amber-700 border border-amber-200': activeTenant?.status === 'menunggu_verifikasi',
                                      'bg-slate-100 text-slate-600': activeTenant?.status === 'nonaktif'
                                  }"
                                  x-text="activeTenant?.status"></span>
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">
                            <span x-text="activeTenant?.subdomain"></span>.rentalbase.id
                        </p>
                    </div>
                </div>

                <button type="button" @click="showDetailModal = false"
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body (Scrollable) --}}
            <div class="p-6 overflow-y-auto space-y-6 text-sm">
                {{-- Section 1: Profil Usaha --}}
                <div class="bg-slate-50/70 rounded-2xl p-4 border border-slate-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Informasi Profil Toko
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Nama Bisnis:</span>
                            <span class="font-bold text-slate-800" x-text="activeTenant?.business_name"></span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Subdomain Toko:</span>
                            <a :href="activeTenant?.live_url" target="_blank"
                               class="font-mono text-orange-600 hover:underline flex items-center gap-1 font-semibold text-xs">
                                <span x-text="activeTenant?.subdomain + '.rentalbase.id'"></span>
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-xs text-slate-400 block font-medium">Deskripsi Usaha:</span>
                            <p class="text-slate-700 text-xs mt-1 leading-relaxed bg-white p-3 rounded-xl border border-slate-100"
                               x-text="activeTenant?.description || 'Belum ada deskripsi usaha.'"></p>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Warna Tema Toko:</span>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="w-5 h-5 rounded-lg border border-slate-200 shadow-2xs"
                                      :style="'background-color: ' + activeTenant?.theme_color"></span>
                                <span class="font-mono text-xs font-semibold text-slate-700" x-text="activeTenant?.theme_color"></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Tanggal Pendaftaran:</span>
                            <span class="font-semibold text-slate-700 text-xs" x-text="activeTenant?.created_at"></span>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Identitas PIC / Pengelola Toko --}}
                <div class="bg-slate-50/70 rounded-2xl p-4 border border-slate-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Identitas Pemilik & Pengelola (PIC)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Nama PIC / Owner:</span>
                            <span class="font-bold text-slate-800" x-text="activeTenant?.admin?.name"></span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Alamat Email PIC:</span>
                            <span class="font-semibold text-slate-800 text-xs" x-text="activeTenant?.admin?.email"></span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Nomor WhatsApp / HP:</span>
                            <span class="font-semibold text-slate-800 text-xs" x-text="activeTenant?.admin?.phone"></span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Status Akun Admin:</span>
                            <span class="text-xs font-bold capitalize"
                                  :class="activeTenant?.admin?.status === 'aktif' ? 'text-emerald-600' : 'text-rose-600'"
                                  x-text="activeTenant?.admin?.status"></span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-xs text-slate-400 block font-medium">Alamat Lengkap Toko:</span>
                            <p class="text-slate-700 text-xs mt-1" x-text="activeTenant?.admin?.address || '-'"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Status Paket Langganan / Subscription --}}
                <div class="bg-gradient-to-br from-orange-50/40 via-white to-slate-50/60 rounded-2xl p-5 border border-orange-100 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-orange-600 flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Detail Keanggotaan & Paket Subscription
                        </h4>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-extrabold uppercase bg-white border border-orange-200 text-orange-600 shadow-2xs"
                              x-text="activeTenant?.subscription?.plan_name"></span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center mb-4">
                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Biaya Langganan</span>
                            <span class="text-xs font-extrabold text-slate-900 mt-0.5 block" x-text="'Rp ' + activeTenant?.subscription?.price"></span>
                            <span class="text-[10px] text-slate-400" x-text="activeTenant?.subscription?.billing_cycle"></span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Batas Produk</span>
                            <span class="text-xs font-extrabold text-slate-900 mt-0.5 block"
                                  x-text="activeTenant?.subscription?.max_products !== null && activeTenant?.subscription?.max_products !== undefined ? activeTenant?.subscription?.max_products + ' Produk' : 'Unlimited Produk'"></span>
                            <span class="text-[10px] text-slate-400" x-text="'Terpakai: ' + (activeTenant?.products_count || 0)"></span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Batas Admin</span>
                            <span class="text-xs font-extrabold text-slate-900 mt-0.5 block"
                                  x-text="(activeTenant?.subscription?.max_users || 1) + ' Admin'"></span>
                            <span class="text-[10px] text-slate-400">Pengelola</span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Masa Berlaku</span>
                            <span class="text-xs font-extrabold block mt-0.5"
                                  :class="activeTenant?.subscription?.is_expired ? 'text-rose-600' : 'text-emerald-600'"
                                  x-text="activeTenant?.subscription?.is_expired ? 'Kadaluarsa' : activeTenant?.subscription?.end_date"></span>
                            <span class="text-[10px] text-slate-400"
                                  x-text="activeTenant?.subscription?.days_left !== null ? (activeTenant?.subscription?.is_expired ? 'Lewat ' + Math.abs(activeTenant?.subscription?.days_left) + ' hari' : 'Sisa ' + activeTenant?.subscription?.days_left + ' hari') : '-'"></span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-600 flex items-center justify-between bg-white/80 p-3 rounded-xl border border-slate-100">
                        <span>Periode: <strong x-text="activeTenant?.subscription?.start_date"></strong> s/d <strong x-text="activeTenant?.subscription?.end_date"></strong></span>
                        <span class="font-semibold capitalize px-2 py-0.5 rounded-lg text-[11px]"
                              :class="{
                                  'bg-emerald-50 text-emerald-700': activeTenant?.subscription?.status === 'aktif',
                                  'bg-amber-50 text-amber-700': activeTenant?.subscription?.status === 'menunggu_verifikasi',
                                  'bg-rose-50 text-rose-700': activeTenant?.subscription?.status === 'kadaluarsa'
                              }"
                              x-text="'Status: ' + activeTenant?.subscription?.status"></span>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <button type="button" @click="showDetailModal = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Tutup
                </button>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="showDetailModal = false; openSubscription(activeTenant)"
                            class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold border border-blue-200 transition-colors">
                        Kelola Subscription
                    </button>
                    <button type="button"
                            @click="showDetailModal = false; openEdit(activeTenant)"
                            class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-xs transition-colors">
                        Edit Data Usaha
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2: EDIT INFORMASI TENANT & IDENTITAS PIC                            --}}
    {{-- ========================================================================= --}}
    <div x-show="showEditModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5"
         role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showEditModal = false"></div>

        <div x-show="showEditModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden z-10">

            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Perbarui Informasi Tenant</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Edit detail usaha toko rental dan identitas kontak pengelola PIC.</p>
                </div>
                <button type="button" @click="showEditModal = false"
                        class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form Edit --}}
            <form method="POST" :action="editUrl" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')

                <div class="p-6 overflow-y-auto space-y-5 text-xs">
                    {{-- Section Usaha --}}
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm mb-3 pb-1 border-b border-slate-100 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            Data Usaha & Subdomain
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">Nama Usaha / Toko <span class="text-rose-500">*</span></label>
                                <input type="text" name="business_name" x-model="editTenant.business_name" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">Subdomain Rental <span class="text-rose-500">*</span></label>
                                <div class="flex items-center">
                                    <input type="text" name="subdomain" x-model="editTenant.subdomain" required
                                           class="w-full bg-slate-50 border border-slate-200 rounded-l-xl px-3.5 py-2.5 text-xs font-mono font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                                    <span class="bg-slate-100 border border-l-0 border-slate-200 text-slate-500 px-3 py-2.5 text-xs font-mono rounded-r-xl select-none">
                                        .rentalbase.id
                                    </span>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="font-bold text-slate-700 block mb-1.5">Deskripsi Singkat Usaha</label>
                                <textarea name="description" x-model="editTenant.description" rows="2"
                                          placeholder="Toko rental penyewaan perlengkapan..."
                                          class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"></textarea>
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">Warna Tema Brand (Hex)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="theme_color_picker" x-model="editTenant.theme_color"
                                           class="w-10 h-10 p-0.5 border border-slate-200 rounded-xl cursor-pointer">
                                    <input type="text" name="theme_color" x-model="editTenant.theme_color"
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                                </div>
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">Ganti File Logo (Opsional)</label>
                                <input type="file" name="logo" accept="image/*"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs text-slate-600 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                            </div>
                        </div>
                    </div>

                    {{-- Section PIC --}}
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm mb-3 pb-1 border-b border-slate-100 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Identitas Pengelola Toko (PIC)
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">Nama Lengkap PIC <span class="text-rose-500">*</span></label>
                                <input type="text" name="admin_name" x-model="editTenant.admin_name" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">Alamat Email Login <span class="text-rose-500">*</span></label>
                                <input type="email" name="admin_email" x-model="editTenant.admin_email" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">No. WhatsApp / Telepon <span class="text-rose-500">*</span></label>
                                <input type="text" name="admin_phone" x-model="editTenant.admin_phone" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1.5">Alamat Fisik Toko</label>
                                <input type="text" name="admin_address" x-model="editTenant.admin_address"
                                       placeholder="Jl. Raya No. 123..."
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-xs transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 3: KELOLA STATUS AKUN TENANT                                        --}}
    {{-- ========================================================================= --}}
    <div x-show="showStatusModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5"
         role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showStatusModal = false"></div>

        <div x-show="showStatusModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden z-10">

            <form method="POST" :action="statusUrl">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 border border-orange-200/60 flex items-center justify-center shadow-xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-slate-900">Kelola Status Akun Tenant</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Atur izin operasional toko untuk <strong class="text-slate-900" x-text="statusTenant.business_name"></strong>. Perubahan status akan langsung berpengaruh pada hak akses login pengelola dan katalog publik.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2">
                        {{-- Opsi 1: Aktif --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="statusTenant.status === 'aktif' ? 'bg-emerald-50/50 border-emerald-300 ring-1 ring-emerald-400' : 'bg-white border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="aktif" x-model="statusTenant.status" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">Aktif (Operasional Normal)</span>
                                <span class="text-[11px] text-slate-500">Toko dapat diakses publik, pelanggan dapat menyewa, dan admin toko dapat login normal.</span>
                            </div>
                        </label>

                        {{-- Opsi 2: Suspended --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="statusTenant.status === 'suspended' ? 'bg-rose-50/50 border-rose-300 ring-1 ring-rose-400' : 'bg-white border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="suspended" x-model="statusTenant.status" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div>
                                <span class="text-xs font-bold text-rose-900 block">Tangguhkan (Suspended)</span>
                                <span class="text-[11px] text-slate-500">Blokir login admin toko dan tangguhkan transaksi rental sementara karena pelanggaran atau kendala teknis.</span>
                            </div>
                        </label>

                        {{-- Opsi 3: Nonaktif --}}
                        <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="statusTenant.status === 'nonaktif' ? 'bg-slate-100 border-slate-300 ring-1 ring-slate-400' : 'bg-white border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="nonaktif" x-model="statusTenant.status" class="mt-0.5 text-slate-600 focus:ring-slate-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Nonaktifkan Tenant</span>
                                <span class="text-[11px] text-slate-500">Nonaktifkan toko dari platform secara menyeluruh.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showStatusModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-xs transition-colors">
                        Terapkan Status
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 4: KELOLA PAKET & SUBSCRIPTION TENANT                               --}}
    {{-- ========================================================================= --}}
    <div x-show="showSubModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5"
         role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="showSubModal = false"></div>

        <div x-show="showSubModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden z-10">

            <form method="POST" :action="subUrl">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Kelola Paket & Subscription</h3>
                            <p class="text-xs text-slate-500 mt-0.5" x-text="subTenant.business_name"></p>
                        </div>
                        <button type="button" @click="showSubModal = false"
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4 text-xs">
                        {{-- Pilihan Paket --}}
                        <div>
                            <label class="font-bold text-slate-700 block mb-1.5">Pilih Paket Langganan</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="border rounded-xl p-3 text-center cursor-pointer transition-all"
                                       :class="subTenant.plan_name === 'starter' ? 'bg-orange-50 border-orange-500 ring-1 ring-orange-500 text-orange-700 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    <input type="radio" name="plan_name" value="starter" x-model="subTenant.plan_name" class="sr-only">
                                    <span class="block font-bold">Starter</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Gratis (10 Produk, 1 Admin)</span>
                                </label>

                                <label class="border rounded-xl p-3 text-center cursor-pointer transition-all"
                                       :class="subTenant.plan_name === 'business' ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500 text-blue-700 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    <input type="radio" name="plan_name" value="business" x-model="subTenant.plan_name" class="sr-only">
                                    <span class="block font-bold">Business</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Rp 99k (50 Produk, 3 Admin)</span>
                                </label>

                                <label class="border rounded-xl p-3 text-center cursor-pointer transition-all"
                                       :class="subTenant.plan_name === 'professional' ? 'bg-purple-50 border-purple-500 ring-1 ring-purple-500 text-purple-700 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    <input type="radio" name="plan_name" value="professional" x-model="subTenant.plan_name" class="sr-only">
                                    <span class="block font-bold">Professional</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Rp 249k (Unlimited, 10 Admin)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Tanggal Berakhir & Quick Buttons --}}
                        <div>
                            <label class="font-bold text-slate-700 block mb-1.5">Tanggal Berakhir Masa Aktif (End Date)</label>
                            <input type="date" name="end_date" x-model="subTenant.end_date" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">

                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-[11px] text-slate-400">Tambah Masa:</span>
                                <button type="button" @click="addMonths(1)"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-700 text-[11px] font-bold text-slate-700 transition-colors">
                                    +1 Bulan
                                </button>
                                <button type="button" @click="addMonths(3)"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-700 text-[11px] font-bold text-slate-700 transition-colors">
                                    +3 Bulan
                                </button>
                                <button type="button" @click="addMonths(12)"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-orange-100 hover:text-orange-700 text-[11px] font-bold text-slate-700 transition-colors">
                                    +1 Tahun
                                </button>
                            </div>
                        </div>

                        {{-- Siklus Penagihan --}}
                        <div>
                            <label class="font-bold text-slate-700 block mb-1.5">Siklus Penagihan</label>
                            <select name="billing_cycle" x-model="subTenant.billing_cycle"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                                <option value="monthly">Bulanan (Monthly)</option>
                                <option value="yearly">Tahunan (Yearly)</option>
                            </select>
                        </div>

                        {{-- Status Subscription --}}
                        <input type="hidden" name="status" :value="computeSubStatus().key">
                        <input type="hidden" name="is_disabled" :value="subTenant.is_nonaktif ? '1' : '0'">

                        <div class="rounded-2xl border p-4 transition-all"
                             :class="{
                                 'bg-emerald-50/60 border-emerald-200': computeSubStatus().key === 'aktif',
                                 'bg-rose-50/60 border-rose-200': computeSubStatus().key === 'kadaluarsa',
                                 'bg-amber-50/60 border-amber-200': computeSubStatus().key === 'menunggu_verifikasi',
                                 'bg-slate-100/70 border-slate-300': computeSubStatus().key === 'nonaktif'
                             }">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs"
                                         :class="{
                                             'bg-emerald-100 text-emerald-700': computeSubStatus().key === 'aktif',
                                             'bg-rose-100 text-rose-700': computeSubStatus().key === 'kadaluarsa',
                                             'bg-amber-100 text-amber-700': computeSubStatus().key === 'menunggu_verifikasi',
                                             'bg-slate-200 text-slate-700': computeSubStatus().key === 'nonaktif'
                                         }">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Status Subscription</span>
                                        <span class="text-xs font-extrabold text-slate-900" x-text="computeSubStatus().title"></span>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border shadow-2xs shrink-0"
                                      :class="computeSubStatus().badgeClass">
                                    <span class="w-2 h-2 rounded-full" :class="computeSubStatus().dotClass"></span>
                                    <span x-text="computeSubStatus().label"></span>
                                </span>
                            </div>

                            <p class="text-[11px] text-slate-600 mt-2.5 leading-relaxed bg-white/70 p-2.5 rounded-xl border border-slate-200/50"
                               x-text="computeSubStatus().desc"></p>

                            {{-- Pemicu Nonaktif Manual oleh Owner --}}
                            <div class="mt-3 pt-3 border-t border-slate-200/70 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Nonaktifkan Subscription</span>
                                    <span class="text-[10px] text-slate-500 block">Gunakan jika ingin menghentikan langganan tenant.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" x-model="subTenant.is_nonaktif" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showSubModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-xs transition-colors">
                        Simpan Subscription
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

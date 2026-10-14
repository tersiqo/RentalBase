<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Owner') — RentalBase Platform</title>
    <meta name="description" content="Panel Owner Pusat RentalBase — Pantau dan kelola semua tenant rental di platform.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        :root {
            --owner-sidebar-width: 260px;
            --owner-sidebar-collapsed: 72px;
            --owner-topbar-height: 64px;
            --owner-primary: #F97316;
            --owner-primary-hover: #EA580C;
            --owner-sidebar-bg: #FFF7ED;
            --owner-sidebar-hover: #FFEDD5;
            --owner-sidebar-active: #FFEDD5;
        }

        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #FFF7ED; }
        ::-webkit-scrollbar-thumb { background: #FED7AA; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #FDBA74; }

        #owner-sidebar {
            transition: width 0.25s cubic-bezier(0.4,0,0.2,1), transform 0.25s cubic-bezier(0.4,0,0.2,1);
            will-change: width, transform;
            border-right: 1px solid #FFEDD5;
        }

        #owner-main {
            transition: margin-left 0.25s cubic-bezier(0.4,0,0.2,1);
        }

        .owner-nav-active {
            background: #FFEDD5;
            color: #C2410C;
            font-weight: 700;
            border-left: 3px solid #F97316;
        }

        .owner-nav-active .owner-nav-icon {
            color: #EA580C;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #F1F5F9;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-gray-800 antialiased"
      x-data="ownerLayout()"
      x-init="init()">

{{-- Mobile Overlay --}}
<div x-show="mobileOpen"
     x-cloak
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileOpen = false"
     class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"
     aria-hidden="true">
</div>

{{-- Sidebar --}}
<aside id="owner-sidebar"
       :class="sidebarClasses()"
       class="fixed top-0 left-0 h-full z-40 flex flex-col overflow-hidden shadow-xs"
       style="background: var(--owner-sidebar-bg);">

    {{-- Logo / Brand --}}
    <div class="flex items-center gap-3 px-5 border-b border-orange-200/70" style="height: 64px; min-height: 64px;">
        <div class="shrink-0 w-9 h-9 rounded-xl flex items-center justify-center shadow-md"
             style="background: linear-gradient(135deg, #F97316 0%, #EA580C 100%); box-shadow: 0 4px 12px rgba(249,115,22,0.25);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div x-show="!collapsed || mobileOpen"
             x-transition:enter="transition-opacity duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="overflow-hidden whitespace-nowrap">
            <p class="text-sm font-bold text-slate-900 leading-tight">RentalBase</p>
            <p class="text-[11px] font-semibold" style="color: #EA580C;">Platform Owner Pusat</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-1">

        <p x-show="!collapsed || mobileOpen"
           class="px-3 mb-2.5 text-[10px] font-bold uppercase tracking-wider text-orange-900/50">
           MENU UTAMA
        </p>

        {{-- Dashboard --}}
        <a href="{{ route('owner.dashboard') }}"
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('owner.dashboard') ? 'owner-nav-active' : 'text-slate-700 hover:bg-orange-100/70 hover:text-slate-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('owner.dashboard') ? 'text-orange-600' : 'text-slate-500 group-hover:text-orange-600' }} transition-colors">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate font-semibold">Dashboard</span>
        </a>

        {{-- Pendaftaran Baru --}}
        <a href="{{ route('owner.registrations.index') }}"
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('owner.registrations.*') ? 'owner-nav-active' : 'text-slate-700 hover:bg-orange-100/70 hover:text-slate-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('owner.registrations.*') ? 'text-orange-600' : 'text-slate-500 group-hover:text-orange-600' }} transition-colors">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate font-medium">Pendaftaran Baru</span>
            @if(isset($pendaftaranMenunggu) && $pendaftaranMenunggu > 0)
                <span x-show="!collapsed || mobileOpen"
                      class="ml-auto shrink-0 px-2 py-0.5 text-[10px] font-bold rounded-full text-white shadow-xs"
                      style="background: #F97316;">
                    {{ $pendaftaranMenunggu }}
                </span>
            @endif
        </a>

        {{-- Manajemen Tenant --}}
        <a href="{{ route('owner.tenants.index') }}"
           class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('owner.tenants.*') ? 'owner-nav-active' : 'text-slate-700 hover:bg-orange-100/70 hover:text-slate-900' }}">
            <span class="shrink-0 w-5 h-5 {{ request()->routeIs('owner.tenants.*') ? 'text-orange-600' : 'text-slate-500 group-hover:text-orange-600' }} transition-colors">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate font-medium">Manajemen Tenant</span>
        </a>

        {{-- Manajemen Admin --}}
        <div class="group flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 cursor-not-allowed select-none">
            <span class="shrink-0 w-5 h-5 text-slate-400">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Manajemen Admin</span>
            <span x-show="!collapsed || mobileOpen" class="ml-auto text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-600">Segera</span>
        </div>

        <hr class="my-3 border-orange-200/60" x-show="!collapsed || mobileOpen">

        <p x-show="!collapsed || mobileOpen"
           class="px-3 mb-2.5 text-[10px] font-bold uppercase tracking-wider text-orange-900/50">
           MANAJEMEN & LOG
        </p>

        {{-- Subscriptions --}}
        <div class="group flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 cursor-not-allowed select-none">
            <span class="shrink-0 w-5 h-5 text-slate-400">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Subscriptions</span>
            <span x-show="!collapsed || mobileOpen" class="ml-auto text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-600">Segera</span>
        </div>

        {{-- Activity Log --}}
        <div class="group flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 cursor-not-allowed select-none">
            <span class="shrink-0 w-5 h-5 text-slate-400">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Activity Log</span>
            <span x-show="!collapsed || mobileOpen" class="ml-auto text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-600">Segera</span>
        </div>

        {{-- Notifikasi --}}
        <div class="group flex items-center gap-3 px-3.5 py-2 rounded-xl text-sm font-medium text-slate-400 cursor-not-allowed select-none">
            <span class="shrink-0 w-5 h-5 text-slate-400">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                </svg>
            </span>
            <span x-show="!collapsed || mobileOpen" class="truncate">Notifikasi</span>
            <span x-show="!collapsed || mobileOpen" class="ml-auto text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-600">Segera</span>
        </div>

    </nav>

    {{-- User Profile Bottom --}}
    <div class="border-t border-orange-200/70 p-3 bg-orange-100/40">
        <div class="flex items-center gap-3 px-2 py-2 rounded-xl bg-white/80 border border-orange-200/70 shadow-xs">
            <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                 style="background: linear-gradient(135deg, #F97316, #EA580C);">
                {{ strtoupper(substr(Auth::user()->name ?? 'O', 0, 1)) }}
            </div>
            <div x-show="!collapsed || mobileOpen" class="flex-1 min-w-0">
                <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'Owner' }}</p>
                <p class="text-[10px] font-semibold truncate text-orange-600">Owner Pusat</p>
            </div>
            <form x-show="!collapsed || mobileOpen"
                  method="POST"
                  action="{{ route('owner.logout') }}"
                  class="shrink-0">
                @csrf
                <button type="submit"
                        title="Keluar"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Topbar --}}
<header id="owner-topbar"
        :style="topbarStyle()"
        class="fixed top-0 right-0 z-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center gap-4 px-6 lg:px-8 shadow-sm shadow-stone-100/40 transition-all duration-250"
        style="height: var(--owner-topbar-height);">

    {{-- Hamburger --}}
    <button @click="toggleSidebar()"
            id="owner-btn-hamburger"
            class="shrink-0 w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 hover:bg-stone-100 hover:text-gray-800 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs font-medium">
        <span class="font-bold text-gray-900">RentalBase</span>
        <span class="text-gray-300">/</span>
        <span class="text-gray-500">@yield('page-title', 'Dashboard Owner')</span>
    </div>

    <div class="flex-1"></div>

    {{-- Date --}}
    <div class="hidden lg:flex items-center gap-1.5 text-xs text-gray-500 font-medium">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, j F Y') }}</span>
    </div>

    {{-- Owner Badge --}}
    <div class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border"
         style="background: #FFF7ED; border-color: rgba(249,115,22,0.3);">
        <span class="w-2 h-2 rounded-full" style="background: #F97316;"></span>
        <span class="text-xs font-bold" style="color: #C2410C;">Owner Pusat</span>
    </div>

    {{-- Bell --}}
    <button class="relative w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-stone-100 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
        </svg>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full ring-2 ring-white" style="background: #F97316;"></span>
    </button>

    {{-- Avatar --}}
    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-sm"
         style="background: linear-gradient(135deg, #0F172A, #1E293B);">
        {{ strtoupper(substr(Auth::user()->name ?? 'O', 0, 1)) }}
    </div>
</header>

{{-- Main Content --}}
<div id="owner-main"
     :style="mainContentStyle()"
     class="transition-all duration-250">
    <main class="min-h-screen px-6 py-6 lg:px-9 lg:py-8">
        @yield('content')
    </main>
</div>

<script>
    function ownerLayout() {
        return {
            collapsed: false,
            mobileOpen: false,
            isMobile: false,

            init() {
                this.checkMobile();
                window.addEventListener('resize', () => this.checkMobile());
            },

            checkMobile() {
                this.isMobile = window.innerWidth < 1024;
                if (this.isMobile) {
                    this.collapsed = false;
                    this.mobileOpen = false;
                }
            },

            toggleSidebar() {
                if (this.isMobile) {
                    this.mobileOpen = !this.mobileOpen;
                } else {
                    this.collapsed = !this.collapsed;
                }
            },

            sidebarClasses() {
                if (this.isMobile) {
                    return this.mobileOpen ? 'translate-x-0' : '-translate-x-full';
                }
                return this.collapsed ? 'w-[72px]' : 'w-[260px]';
            },

            topbarStyle() {
                const left = this.isMobile ? '0' : (this.collapsed ? '72px' : '260px');
                return `left: ${left}; height: var(--owner-topbar-height);`;
            },

            mainContentStyle() {
                const ml = this.isMobile ? '0' : (this.collapsed ? '72px' : '260px');
                return `margin-left: ${ml}; padding-top: var(--owner-topbar-height);`;
            },
        };
    }
</script>

</body>
</html>

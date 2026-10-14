@extends('auth.layout')

@section('title', 'Masuk ke Akun — RentalBase')

@section('content')
    <h1>Masuk ke Akun Anda</h1>
    <p class="sub">Akses Dashboard Admin Rental atau Panel Owner Pusat RentalBase.</p>

    <a href="{{ route('auth.google') }}" class="btn btn-google">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M23.5 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.45a5.52 5.52 0 01-2.39 3.62v3h3.87c2.27-2.09 3.57-5.17 3.57-8.81z"/>
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.91l-3.87-3c-1.07.72-2.45 1.15-4.06 1.15-3.12 0-5.77-2.11-6.71-4.95H1.29v3.1A12 12 0 0012 24z"/>
            <path fill="#FBBC05" d="M5.29 14.29a7.2 7.2 0 010-4.58v-3.1H1.29a12 12 0 000 10.78l4-3.1z"/>
            <path fill="#EA4335" d="M12 4.76c1.76 0 3.34.61 4.58 1.8l3.43-3.43C17.95 1.19 15.24 0 12 0A12 12 0 001.29 6.61l4 3.1C6.23 6.87 8.88 4.76 12 4.76z"/>
        </svg>
        Masuk dengan Google
    </a>

    <div class="divider">atau masuk dengan email</div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success" role="alert" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
            {{ session('status') }}
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info" role="alert" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;">
            {{ session('info') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert" role="alert" style="background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ submitting: false }" @submit="submitting = true" novalidate>
        @csrf
        @if(request()->filled('redirect'))
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
        @endif

        {{-- Input Email / Username --}}
        <div class="field">
            <label for="email">Alamat Email atau Username <i>*</i></label>
            <div class="input-wrapper">
                <div class="icon-left">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </div>
                <input id="email" name="email" type="text" class="input @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="admin@rental.com atau nama_user" autocomplete="username" required autofocus>
            </div>
            @error('email')
                <div style="color: #ef4444; font-size: 12px; margin-top: 6px; font-weight: 500;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Input Password --}}
        <div class="field">
            <label for="password">Password <i>*</i></label>
            <div class="pw">
                <div class="icon-left" style="position: absolute; left: 12px; color: var(--slate-400); display: flex; align-items: center; justify-content: center; pointer-events: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                </div>
                <input id="password" name="password" type="password" class="input @error('password') is-invalid @enderror"
                       placeholder="Masukkan password" autocomplete="current-password" required>
                <button type="button" data-toggle-pw="password" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('password')
                <div style="color: #ef4444; font-size: 12px; margin-top: 6px; font-weight: 500;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Remember & Forgot Password --}}
        <div class="row">
            <label class="remember"><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Ingat saya</label>
            <a href="{{ route('password.request') }}" class="link">Lupa password?</a>
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn btn-primary" :disabled="submitting" style="display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 8px;">
            <span x-show="!submitting" class="inline-flex items-center justify-center gap-2" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <span>Masuk</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px; flex-shrink: 0; display: inline-block; vertical-align: middle;"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </span>
            <span x-show="submitting" x-cloak class="inline-flex items-center justify-center gap-2" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <svg class="animate-spin" style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor"></path>
                </svg>
                <span>Memproses...</span>
            </span>
        </button>
    </form>

    <p class="switch">Ingin mendaftarkan toko rental baru? <a href="{{ route('tenant.register') }}" class="link">Daftar Tenant</a></p>
@endsection
@extends('auth.layout')

@section('title', 'Reset Password')

@section('content')
    <h1>Reset Password Baru</h1>
    <p class="sub">Silakan buat password baru untuk akun Anda.</p>

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ request()->email }}">

        <div class="field">
            <label for="password">Password Baru <i>*</i></label>
            <div class="pw">
                <div class="icon-left" style="position: absolute; left: 12px; color: var(--slate-400); display: flex; align-items: center; justify-content: center; pointer-events: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                </div>
                <input id="password" name="password" type="password" class="input @error('password') is-invalid @enderror"
                       placeholder="Minimal 8 karakter" required autofocus>
                <button type="button" data-toggle-pw="password" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <div class="field">
            <label for="password_confirmation">Konfirmasi Password Baru <i>*</i></label>
            <div class="pw">
                <div class="icon-left" style="position: absolute; left: 12px; color: var(--slate-400); display: flex; align-items: center; justify-content: center; pointer-events: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                </div>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input @error('password_confirmation') is-invalid @enderror"
                       placeholder="Ulangi password baru" required>
                <button type="button" data-toggle-pw="password_confirmation" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 24px;">
            Simpan Password Baru
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
    </form>
@endsection

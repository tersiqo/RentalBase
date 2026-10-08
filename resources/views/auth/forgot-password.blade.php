@extends('auth.layout')

@section('title', 'Lupa Password')

@section('content')
    <h1>Lupa Password?</h1>
    <p class="sub">Masukkan email Anda untuk menerima link reset password.</p>

    @if (session('success'))
        <div class="alert" style="background-color: var(--orange-50); color: var(--orange-600); border-color: var(--orange-200);" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="field">
            <label for="email">Alamat email <i>*</i></label>
            <div class="input-wrapper">
                <div class="icon-left">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </div>
                <input id="email" name="email" type="email" class="input @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 24px;">
            Kirim Link Reset
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
    </form>

    <p class="switch">Ingat password Anda? <a href="{{ route('login') }}" class="link">Kembali ke Login</a></p>
@endsection

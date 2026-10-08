@extends('auth.layout')

@section('title', 'Setup Toko')
@section('container-class', 'auth-container-wide')

@section('nav-link')
    <a href="{{ route('admin.logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
@section('before-content')
    <!-- Step Indicator -->
    <div style="display: flex; justify-content: center; margin-bottom: 32px; width: 100%;">
        <div style="display: flex; align-items: center; width: 100%; max-width: 400px;">
            <!-- Step 1 (Active) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--orange-500); color: white; box-shadow: 0 0 0 4px #fffaf5;">
                    1
                </div>
                <span style="font-size: 12px; font-weight: 700; color: var(--slate-900); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Info Toko</span>
            </div>
            
            <!-- Line 1 -->
            <div style="flex: 1; height: 3px; background-color: var(--slate-200); position: relative; margin: 0 8px;"></div>
            
            <!-- Step 2 (Inactive) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--slate-200); color: var(--slate-500); box-shadow: 0 0 0 4px #fffaf5;">
                    2
                </div>
                <span style="font-size: 12px; font-weight: 600; color: var(--slate-500); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Tema Tampilan</span>
            </div>
            
            <!-- Line 2 -->
            <div style="flex: 1; height: 3px; background-color: var(--slate-200); position: relative; margin: 0 8px;"></div>
            
            <!-- Step 3 (Inactive) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--slate-200); color: var(--slate-500); box-shadow: 0 0 0 4px #fffaf5;">
                    3
                </div>
                <span style="font-size: 12px; font-weight: 600; color: var(--slate-500); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Verifikasi</span>
            </div>
        </div>
    </div>
@endsection

@section('content')

    <div class="auth-header" style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 8px; color: var(--slate-900);">Setup Toko Anda</h1>
        <p style="font-size: 14px; color: var(--slate-500);">Lengkapi informasi toko untuk menyelesaikan pendaftaran.</p>
    </div>

    @if ($errors->any())
        <div class="alert" role="alert">
            <ul style="padding-left: 16px; margin: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.setup') }}" enctype="multipart/form-data" novalidate>
        @csrf
        
        <h2 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-top: 8px; margin-bottom: 16px; border-bottom: 1px solid var(--slate-200); padding-bottom: 8px;">1. Info Usaha</h2>
        <div class="grid-2">
            <div class="field">
                <label for="business_name">Nama Usaha/Toko <i>*</i></label>
                <div class="input-wrapper">
                    <div class="icon-left">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <input id="business_name" name="business_name" type="text" class="input @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" placeholder="Contoh: Kamera Sewa Jakarta" required autofocus>
                </div>
            </div>

            <div class="field">
                <label for="subdomain">Subdomain Toko <i>*</i></label>
                <div style="display: flex; position: relative;">
                    <input id="subdomain" name="subdomain" type="text" class="input @error('subdomain') is-invalid @enderror" value="{{ old('subdomain') }}" placeholder="namatoko" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;" required>
                    <div style="display: flex; align-items: center; justify-content: center; padding: 0 16px; background-color: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 0 var(--radius-lg) var(--radius-lg) 0; color: var(--slate-500); font-size: 14px; font-weight: 600;">
                        .rentalbase.com
                    </div>
                </div>
            </div>
        </div>

        <div class="field">
            <label for="description">Deskripsi Usaha <i>*</i></label>
            <textarea id="description" name="description" class="input @error('description') is-invalid @enderror" rows="3" placeholder="Jelaskan secara singkat barang yang disewakan" required style="resize: vertical; padding-left: 16px;">{{ old('description') }}</textarea>
        </div>

        <div class="field" style="margin-top: 4px;">
            <label for="logo">Logo Bisnis</label>
            <input type="file" id="logo" name="logo" accept="image/*" class="input @error('logo') is-invalid @enderror" style="padding: 10px 16px;">
        </div>

        <h2 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-top: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--slate-200); padding-bottom: 8px;">2. Info PIC (Pemilik)</h2>
        <div class="grid-2">
            <div class="field">
                <label for="owner_name">Nama Lengkap PIC <i>*</i></label>
                <div class="input-wrapper">
                    <div class="icon-left">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <input id="owner_name" name="owner_name" type="text" class="input @error('owner_name') is-invalid @enderror" value="{{ old('owner_name', auth()->user()->name) }}" placeholder="Nama Lengkap" required>
                </div>
            </div>

            <div class="field">
                <label for="phone">No. WhatsApp Aktif <i>*</i></label>
                <div class="input-wrapper">
                    <div class="icon-left">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    </div>
                    <input id="phone" name="phone" type="tel" class="input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="081234567890" required>
                </div>
            </div>
        </div>

        <div class="field">
            <label for="address">Alamat Lengkap <i>*</i></label>
            <textarea id="address" name="address" class="input @error('address') is-invalid @enderror" rows="2" placeholder="Alamat rumah/kantor" required style="resize: vertical; padding-left: 16px;">{{ old('address') }}</textarea>
        </div>

        <style>
            .plan-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
                margin-top: 16px;
                margin-bottom: 8px;
            }
            .plan-card {
                border: 2px solid var(--slate-200);
                border-radius: var(--radius-lg);
                padding: 20px;
                text-align: center;
                cursor: pointer;
                transition: all 0.2s;
                position: relative;
            }
            .plan-card:hover {
                border-color: var(--slate-400);
            }
            .plan-input:checked + .plan-card {
                border-color: var(--orange-500);
                background-color: var(--orange-50);
                box-shadow: 0 0 0 1px var(--orange-500);
            }
            .plan-title {
                font-size: 16px;
                font-weight: 800;
                color: var(--slate-900);
                margin-bottom: 4px;
            }
            .plan-price {
                font-size: 14px;
                color: var(--slate-500);
                font-weight: 600;
            }
            .plan-input:checked + .plan-card .plan-price {
                color: var(--orange-600);
            }
            .badge {
                position: absolute;
                top: -10px;
                left: 50%;
                transform: translateX(-50%);
                background-color: var(--orange-500);
                color: white;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 2px 8px;
                border-radius: 12px;
            }
            @media (max-width: 640px) {
                .plan-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <h2 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-top: 16px; margin-bottom: 8px; border-bottom: 1px solid var(--slate-200); padding-bottom: 8px;">3. Konfirmasi Paket</h2>
        <div class="field" style="margin-top: 0;">
            <div class="plan-grid">
                <label>
                    <input type="radio" name="plan_name" value="starter" class="plan-input" style="display: none;" {{ (isset($plan) && $plan === 'starter') || old('plan_name') === 'starter' ? 'checked' : '' }}>
                    <div class="plan-card">
                        <div class="plan-title">Starter</div>
                        <div class="plan-price">Gratis</div>
                    </div>
                </label>
                
                <label>
                    <input type="radio" name="plan_name" value="business" class="plan-input" style="display: none;" {{ (isset($plan) && $plan === 'business') || old('plan_name') === 'business' ? 'checked' : '' }}>
                    <div class="plan-card">
                        <div class="badge">Rekomendasi</div>
                        <div class="plan-title" style="margin-top: 8px;">Business</div>
                        <div class="plan-price">Rp 99.000 / bln</div>
                    </div>
                </label>

                <label>
                    <input type="radio" name="plan_name" value="professional" class="plan-input" style="display: none;" {{ (isset($plan) && $plan === 'professional') || old('plan_name') === 'professional' ? 'checked' : '' }}>
                    <div class="plan-card">
                        <div class="plan-title">Professional</div>
                        <div class="plan-price">Rp 249.000 / bln</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="field" style="margin-top: 16px;">
            <label class="remember" style="display: flex; align-items: flex-start; gap: 12px;">
                <input type="checkbox" name="terms" value="1" required style="margin-top: 2px;"> 
                <span style="font-size: 13px; color: var(--slate-700); line-height: 1.5; font-weight: 500;">
                    Saya setuju dengan <a href="#" class="link" target="_blank">Syarat dan Ketentuan</a> serta <a href="#" class="link" target="_blank">Kebijakan Privasi</a> yang berlaku.
                </span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
            Berikutnya
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
    </form>
@endsection

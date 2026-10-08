@extends('auth.layout')

@section('title', 'Pilih Tema Tampilan')

@section('container-class', 'auth-container-wide')

@section('before-content')
    <!-- Step Indicator -->
    <div style="display: flex; justify-content: center; margin-bottom: 32px; width: 100%;">
        <div style="display: flex; align-items: center; width: 100%; max-width: 400px;">
            <!-- Step 1 (Completed) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--orange-500); color: white; box-shadow: 0 0 0 4px #fffaf5;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span style="font-size: 12px; font-weight: 600; color: var(--slate-500); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Info Toko</span>
            </div>
            
            <!-- Line 1 -->
            <div style="flex: 1; height: 3px; background-color: var(--orange-500); position: relative; margin: 0 8px;"></div>
            
            <!-- Step 2 (Active) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--orange-500); color: white; box-shadow: 0 0 0 4px #fffaf5;">
                    2
                </div>
                <span style="font-size: 12px; font-weight: 700; color: var(--slate-900); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Tema Tampilan</span>
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

    <div class="auth-header">
        <h1 class="auth-title" style="font-size: 24px; font-weight: 800; text-align: center; margin-bottom: 8px;">Personalisasi toko Anda</h1>
        <p class="auth-subtitle" style="font-size: 14px; text-align: center; color: var(--slate-500); margin-bottom: 32px; line-height: 1.5;">Pilih warna tema sesuai brand usaha rental Anda.<br>Warna ini dipakai di halaman Customer dan Panel Admin.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error" style="background: var(--red-50); color: var(--red-600); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.theme.setup') }}" novalidate>
        @csrf
        
        <div x-data="themeSetup()" class="theme-setup-container" style="display: grid; grid-template-columns: 1fr; gap: 40px; align-items: start;">
            
            <!-- Kiri: Pengaturan Tema & Tombol -->
            <div style="display: flex; flex-direction: column;">
                
                <h2 style="font-size: 14px; font-weight: 700; color: var(--slate-900); margin-bottom: 16px;">Pilihan warna</h2>
                
                <input type="hidden" name="theme_color" x-model="theme">

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px; max-width: 200px;">
                    <!-- Preset Colors -->
                    <template x-for="color in presets" :key="color.hex">
                        <div @click="theme = color.hex" 
                             :class="{'active-color': theme === color.hex}"
                             class="color-circle" 
                             :style="`background-color: ${color.hex}; --ring-color: ${color.hex};`" 
                             :title="color.name">
                             
                             <svg x-show="theme === color.hex" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px; color: white;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </template>
                </div>
                
                <div style="margin-bottom: 32px;">
                    <h2 style="font-size: 14px; font-weight: 700; color: var(--slate-900); margin-bottom: 12px;">Warna kustom</h2>
                    
                    <div class="relative border rounded-lg py-3 px-4 flex items-center justify-between bg-white cursor-pointer transition-colors max-w-[280px]" :style="`border-color: ${theme};`">
                        <input type="color" x-model="theme" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                        
                        <div class="flex items-center gap-4 pointer-events-none">
                            <div class="w-8 h-8 rounded-lg shadow-sm" :style="`background-color: ${theme};`"></div>
                            <span class="font-mono font-bold text-[15px] text-slate-900 uppercase tracking-wide" x-text="theme"></span>
                        </div>
                        
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 pointer-events-none"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    </div>
                    <p style="font-size: 13px; color: var(--slate-500); margin-top: 12px;">Masukkan kode hex atau pilih langsung.</p>
                </div>

            </div>

            <!-- Kanan: Preview UI -->
            <div>
                <h2 style="font-size: 14px; font-weight: 700; color: var(--slate-900); margin-bottom: 16px;">Pratinjau langsung</h2>
                
                <!-- Mockup App -->
                <div class="mockup-app" :style="`--dynamic-theme: ${theme}; --dynamic-theme-light: ${shadeHex(theme, 90)};`">
                    
                    <!-- Header -->
                    <div class="mockup-header">
                        <div class="mockup-logo">
                            <div class="mockup-logo-icon"></div>
                            <div class="mockup-logo-text">TokoKu Studio</div>
                        </div>
                        <div class="mockup-nav">
                            <div class="mockup-nav-item">Beranda</div>
                            <div class="mockup-nav-item active">Katalog</div>
                        </div>
                        <div class="mockup-header-right">
                            <div class="mockup-btn-outline">Masuk / Daftar</div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mockup-content">
                        <!-- Hero Section -->
                        <div style="margin-bottom: 16px;">
                            <h2 style="font-size: 14px; font-weight: 800; color: var(--slate-900); margin-bottom: 4px;">Katalog peralatan TokoKu Studio</h2>
                            <p style="font-size: 10px; color: var(--slate-500); margin-bottom: 12px;">Sewa kamera dan perlengkapan fotografi profesional.</p>
                            <div style="display: flex; gap: 8px;">
                                <div style="flex: 1; border: 1px solid var(--slate-200); background-color: var(--slate-50); border-radius: 6px; padding: 6px 12px; font-size: 10px; color: var(--slate-400); display: flex; align-items: center;">Cari kamera, lensa, atau aksesoris</div>
                                <div class="mockup-btn" style="padding: 6px 16px; border-radius: 6px;">Cari</div>
                            </div>
                        </div>

                        <!-- Grid -->
                        <div class="mockup-grid">
                            <!-- Card 1 -->
                            <div class="mockup-card">
                                <div class="mockup-card-img">
                                    <div class="mockup-badge-available">Tersedia 2</div>
                                </div>
                                <div class="mockup-card-body">
                                    <div class="mockup-card-title">Kamera Mirrorless A7</div>
                                    <div class="mockup-card-price">Rp 350.000 <span style="font-size: 8px; font-weight: 400; color: var(--slate-500);">/ hari</span></div>
                                    <div class="mockup-btn" style="margin-top: 10px; width: 100%; text-align: center;">Sewa alat</div>
                                </div>
                            </div>
                            <!-- Card 2 -->
                            <div class="mockup-card">
                                <div class="mockup-card-img">
                                    <div class="mockup-badge-available">Tersedia 2</div>
                                </div>
                                <div class="mockup-card-body">
                                    <div class="mockup-card-title">Kamera Mirrorless R5</div>
                                    <div class="mockup-card-price">Rp 450.000 <span style="font-size: 8px; font-weight: 400; color: var(--slate-500);">/ hari</span></div>
                                    <div class="mockup-btn" style="margin-top: 10px; width: 100%; text-align: center;">Sewa alat</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 16px; margin-top: 24px;">
            <button type="button" class="btn btn-primary" style="flex: 1; margin-top: 0;" onclick="history.back()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px; width: 18px; height: 18px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Kembali
            </button>
            <button type="submit" class="btn btn-primary" style="flex: 1; margin-top: 0;">
                Terapkan dan Lanjutkan
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px; width: 18px; height: 18px;"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
        </div>
    </form>

    <style>
        .color-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid transparent;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin: 0 auto;
        }
        .color-circle:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .active-color {
            transform: scale(1.1);
            box-shadow: 0 0 0 3px white, 0 0 0 5px var(--ring-color);
        }

        /* Mockup Styles */
        .mockup-app {
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            background-color: white;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            font-family: sans-serif;
            transition: all 0.3s;
        }
        
        .mockup-header {
            border-bottom: 1px solid var(--slate-100);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mockup-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .mockup-logo-icon {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            background-color: var(--dynamic-theme);
        }
        
        .mockup-logo-text {
            font-weight: 800;
            font-size: 12px;
            color: var(--slate-900);
        }
        
        .mockup-nav {
            display: flex;
            gap: 16px;
        }
        
        .mockup-nav-item {
            font-size: 10px;
            font-weight: 600;
            color: var(--slate-500);
        }
        .mockup-nav-item.active {
            color: var(--dynamic-theme);
        }

        .mockup-header-right {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .mockup-btn-outline {
            border: 1px solid var(--dynamic-theme);
            color: var(--dynamic-theme);
            border-radius: 99px;
            padding: 4px 12px;
            font-size: 9px;
            font-weight: 700;
        }
        
        .mockup-content {
            padding: 16px;
            background-color: var(--slate-50);
        }

        .mockup-btn {
            background-color: var(--dynamic-theme);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 700;
            border: none;
        }
        
        .mockup-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .mockup-card {
            background-color: white;
            border: 1px solid var(--slate-200);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .mockup-card-img {
            background-color: var(--slate-100);
            height: 80px;
            position: relative;
            padding: 8px;
            border-bottom: 1px solid var(--slate-100);
        }

        .mockup-card-body {
            padding: 10px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .mockup-badge-available {
            background-color: white;
            color: var(--dynamic-theme);
            font-size: 8px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 99px;
            width: fit-content;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .mockup-card-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 2px;
        }
        
        .mockup-card-price {
            font-size: 12px;
            font-weight: 800;
            color: var(--slate-900);
        }

        @media (min-width: 768px) {
            .theme-setup-container {
                grid-template-columns: 280px 1fr !important;
            }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeSetup', () => ({
                theme: '#FF9F43', // Default soft orange
                presets: [
                    { name: 'Soft Orange', hex: '#FF9F43' },
                    { name: 'Soft Blue', hex: '#7367F0' },
                    { name: 'Soft Red', hex: '#FF7976' },
                    { name: 'Soft Green', hex: '#5DDAB4' },
                    { name: 'Soft Purple', hex: '#9E86FF' },
                    { name: 'Soft Teal', hex: '#28C76F' }
                ],
                shadeHex(color, percent) {
                    if(!color || color.length !== 7) return color;
                    
                    let R = parseInt(color.substring(1,3),16);
                    let G = parseInt(color.substring(3,5),16);
                    let B = parseInt(color.substring(5,7),16);

                    R = parseInt(R * (100 + percent) / 100);
                    G = parseInt(G * (100 + percent) / 100);
                    B = parseInt(B * (100 + percent) / 100);

                    R = (R<255)?R:255;  
                    G = (G<255)?G:255;  
                    B = (B<255)?B:255;  

                    R = Math.round(R);
                    G = Math.round(G);
                    B = Math.round(B);

                    let RR = ((R.toString(16).length==1)?"0"+R.toString(16):R.toString(16));
                    let GG = ((G.toString(16).length==1)?"0"+G.toString(16):G.toString(16));
                    let BB = ((B.toString(16).length==1)?"0"+B.toString(16):B.toString(16));

                    return "#"+RR+GG+BB;
                },
                getContrastYIQ(hexcolor){
                    if(!hexcolor) return 'white';
                    hexcolor = hexcolor.replace("#", "");
                    var r = parseInt(hexcolor.substr(0,2),16);
                    var g = parseInt(hexcolor.substr(2,2),16);
                    var b = parseInt(hexcolor.substr(4,2),16);
                    var yiq = ((r*299)+(g*587)+(b*114))/1000;
                    return (yiq >= 128) ? 'var(--slate-900)' : 'white';
                }
            }))
        })
    </script>
@endsection

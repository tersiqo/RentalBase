@extends('auth.layout')

@section('title', 'Menunggu Verifikasi')
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
            
            <!-- Step 2 (Completed) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--orange-500); color: white; box-shadow: 0 0 0 4px #fffaf5;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span style="font-size: 12px; font-weight: 600; color: var(--slate-500); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Tema Tampilan</span>
            </div>
            
            <!-- Line 2 -->
            <div style="flex: 1; height: 3px; background-color: var(--orange-500); position: relative; margin: 0 8px;"></div>
            
            <!-- Step 3 (Active) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--orange-500); color: white; box-shadow: 0 0 0 4px #fffaf5;">
                    3
                </div>
                <span style="font-size: 12px; font-weight: 700; color: var(--slate-900); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Verifikasi</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div style="text-align: center; padding: 24px 0;">
        <div style="width: 80px; height: 80px; background-color: var(--orange-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--orange-500)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        
        <h1 style="font-size: 24px; font-weight: 800; color: var(--slate-900); margin-bottom: 16px;">Menunggu Verifikasi</h1>
        
        <p style="font-size: 15px; color: var(--slate-600); line-height: 1.6; margin-bottom: 24px;">
            Terima kasih! Pendaftaran toko Anda berhasil diajukan dan saat ini sedang ditinjau oleh tim kami. 
        </p>
        
        <div style="background-color: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 12px; padding: 16px; margin-bottom: 32px; text-align: left;">
            <p style="font-size: 13px; color: var(--slate-700); margin-bottom: 8px;"><strong>Langkah selanjutnya:</strong></p>
            <ul style="font-size: 13px; color: var(--slate-600); padding-left: 16px; margin: 0; line-height: 1.5;">
                <li>Tim kami akan meninjau kelengkapan data usaha Anda.</li>
                <li>Proses verifikasi biasanya memakan waktu maksimal 1x24 jam.</li>
                <li>Pemberitahuan persetujuan akan dikirimkan melalui email yang Anda daftarkan.</li>
            </ul>
        </div>
        
        <div style="margin-bottom: 24px;">
            <a href="{{ route('landing.home') }}" class="btn btn-primary" style="text-decoration: none; display: inline-flex; width: auto; padding: 12px 32px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px; width: 18px; height: 18px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Kembali ke Beranda
            </a>
        </div>
        
        <p style="font-size: 13px; color: var(--slate-500);">
            Ada pertanyaan? Silakan hubungi <a href="#" class="link">Support kami</a>.
        </p>
    </div>
@endsection

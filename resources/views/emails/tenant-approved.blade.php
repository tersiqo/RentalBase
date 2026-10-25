<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Toko Disetujui — RentalBase</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155; line-height: 1.6;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    
                    {{-- Header Banner --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%); padding: 32px 30px; text-align: center;">
                            <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 9999px; margin-bottom: 12px;">
                                <span style="color: #ffffff; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">RentalBase Platform</span>
                            </div>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Selamat! Pendaftaran Toko Disetujui</h1>
                        </td>
                    </tr>

                    {{-- Main Body --}}
                    <tr>
                        <td style="padding: 32px 30px;">
                            <p style="font-size: 15px; margin: 0 0 16px; color: #1e293b;">
                                Halo <strong>{{ $adminName ?: ($user?->name ?: 'Mitra RentalBase') }}</strong>,
                            </p>
                            
                            <p style="font-size: 14px; margin: 0 0 20px; color: #475569; line-height: 1.6;">
                                Kabar baik! Pengajuan pendaftaran toko sewa Anda untuk <strong>{{ $businessName }}</strong> telah diverifikasi dan <strong>disetujui</strong> oleh Owner platform RentalBase. Akun Admin Rental serta toko Anda saat ini telah aktif dan siap dioperasikan.
                            </p>

                            {{-- Status Box --}}
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td width="28" valign="top">
                                            <span style="display: inline-block; width: 22px; height: 22px; background-color: #16a34a; color: #ffffff; border-radius: 50%; text-align: center; line-height: 22px; font-size: 13px; font-weight: bold;">✓</span>
                                        </td>
                                        <td>
                                            <span style="font-size: 13px; font-weight: 700; color: #166534; display: block;">Status Akun: Aktif & Terverifikasi</span>
                                            <span style="font-size: 12px; color: #15803d;">Langganan dan operasional toko sewa Anda sudah dapat digunakan langsung.</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Summary Data Pendaftaran --}}
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
                                <h3 style="margin: 0 0 12px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Ringkasan Data Usaha:</h3>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b; width: 40%;">Nama Usaha/Toko:</td>
                                        <td style="padding: 4px 0; color: #0f172a; font-weight: 600;">{{ $businessName }}</td>
                                    </tr>
                                    @if(!empty($subdomain))
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b;">Subdomain Toko:</td>
                                        <td style="padding: 4px 0; color: #ea580c; font-weight: 600; font-family: monospace;">{{ $subdomain }}.rentalbase.id</td>
                                    </tr>
                                    @endif
                                    @if(!empty($planName))
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b;">Paket Langganan:</td>
                                        <td style="padding: 4px 0; color: #0f172a; font-weight: 600; text-transform: capitalize;">{{ $planName }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b;">Email Terdaftar:</td>
                                        <td style="padding: 4px 0; color: #0f172a; font-weight: 600;">{{ $registration?->admin_email ?? ($user?->email ?: '-') }}</td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Onboarding Next Steps --}}
                            <div style="margin-bottom: 28px;">
                                <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 12px;">Langkah Selanjutnya untuk Memulai:</h3>
                                <ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.7;">
                                    <li style="margin-bottom: 6px;">
                                        <strong>Masuk ke Dashboard Admin:</strong> Silakan login menggunakan email atau username serta kata sandi yang telah Anda daftarkan. <em>(Demi keamanan, jangan pernah membagikan kata sandi Anda kepada siapa pun).</em>
                                    </li>
                                    <li style="margin-bottom: 6px;">
                                        <strong>Lengkapi Profil Rental:</strong> Periksa nama toko, logo, deskripsi, dan ketentuan sewa pada pengaturan profil toko Anda.
                                    </li>
                                    <li style="margin-bottom: 6px;">
                                        <strong>Kelola Katalog Produk & Unit:</strong> Tambahkan inventaris peralatan rental Anda agar siap disewa oleh pelanggan.
                                    </li>
                                    <li style="margin-bottom: 6px;">
                                        <strong>Mulai Terima Pesanan:</strong> Pantau alur reservasi, pembayaran sewa, dan pengembalian unit melalui dashboard admin.
                                    </li>
                                </ol>
                            </div>

                            {{-- CTA Buttons --}}
                            <div style="text-align: center; margin: 32px 0 24px;">
                                <a href="{{ $loginUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; background-color: #ea580c; color: #ffffff; text-decoration: none; padding: 13px 28px; border-radius: 10px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.3);">
                                    Login ke Dashboard Admin &rarr;
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #64748b; margin: 0 0 16px; line-height: 1.5; text-align: center;">
                                Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut di browser Anda:<br>
                                <a href="{{ $loginUrl }}" style="color: #ea580c; word-break: break-all;">{{ $loginUrl }}</a>
                            </p>

                            @if(!empty($storeUrl))
                            <div style="border-top: 1px dashed #e2e8f0; padding-top: 16px; margin-top: 20px; text-align: center;">
                                <span style="font-size: 12px; color: #64748b; display: block; margin-bottom: 4px;">Alamat etalase toko online publik Anda:</span>
                                <a href="{{ $storeUrl }}" target="_blank" rel="noopener noreferrer" style="font-size: 13px; font-weight: 600; color: #ea580c; text-decoration: underline; word-break: break-all;">
                                    {{ $storeUrl }}
                                </a>
                            </div>
                            @endif

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0 0 4px;">
                                Email ini dikirim secara otomatis oleh sistem RentalBase sebagai konfirmasi persetujuan pendaftaran toko.
                            </p>
                            <p style="font-size: 11px; color: #cbd5e1; margin: 0;">
                                &copy; {{ date('Y') }} RentalBase: Equipment Rental System. Seluruh hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

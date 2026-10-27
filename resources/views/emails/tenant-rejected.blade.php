<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Pendaftaran Toko — RentalBase</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155; line-height: 1.6;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    
                    {{-- Header Banner --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); padding: 32px 30px; text-align: center;">
                            <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 9999px; margin-bottom: 12px;">
                                <span style="color: #ffffff; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">RentalBase Platform</span>
                            </div>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Pemberitahuan Pendaftaran Toko</h1>
                        </td>
                    </tr>

                    {{-- Main Body --}}
                    <tr>
                        <td style="padding: 32px 30px;">
                            <p style="font-size: 15px; margin: 0 0 16px; color: #1e293b;">
                                Halo <strong>{{ $adminName ?: ($user?->name ?: 'Mitra RentalBase') }}</strong>,
                            </p>
                            
                            <p style="font-size: 14px; margin: 0 0 20px; color: #475569; line-height: 1.6;">
                                Terima kasih atas minat Anda untuk bergabung sebagai mitra rental di platform RentalBase. Setelah melalui proses pemeriksaan berkas dan verifikasi data oleh tim Owner, kami menginformasikan bahwa pendaftaran toko untuk <strong>{{ $businessName }}</strong> saat ini <strong>belum dapat disetujui</strong>.
                            </p>

                            {{-- Rejection Reason Box --}}
                            <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td width="28" valign="top">
                                            <span style="display: inline-block; width: 22px; height: 22px; background-color: #e11d48; color: #ffffff; border-radius: 50%; text-align: center; line-height: 22px; font-size: 13px; font-weight: bold;">✕</span>
                                        </td>
                                        <td>
                                            <span style="font-size: 13px; font-weight: 700; color: #9f1239; display: block; margin-bottom: 4px;">Alasan Penolakan:</span>
                                            <div style="font-size: 13px; color: #be123c; background-color: #ffffff; padding: 12px 14px; border-radius: 8px; border: 1px solid #fecdd3; font-style: italic; line-height: 1.5;">
                                                "{{ $rejectionReason }}"
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Summary Data Pendaftaran --}}
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
                                <h3 style="margin: 0 0 12px; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Ringkasan Pengajuan Pendaftaran:</h3>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b; width: 40%;">Nama Usaha/Toko:</td>
                                        <td style="padding: 4px 0; color: #0f172a; font-weight: 600;">{{ $businessName }}</td>
                                    </tr>
                                    @if(!empty($subdomain))
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b;">Subdomain Toko:</td>
                                        <td style="padding: 4px 0; color: #e11d48; font-weight: 600; font-family: monospace;">{{ $subdomain }}.rentalbase.id</td>
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

                            {{-- Resubmission Instructions --}}
                            <div style="margin-bottom: 28px;">
                                <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 12px;">Anda Dapat Memperbaiki & Mengajukan Ulang:</h3>
                                <p style="font-size: 13px; color: #475569; margin: 0 0 10px; line-height: 1.6;">
                                    Akun Anda <strong>tidak dihapus</strong> dari sistem. Anda dapat memperbarui data usaha atau mengunggah ulang bukti pembayaran yang sesuai dengan mengikuti langkah berikut:
                                </p>
                                <ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.7;">
                                    <li style="margin-bottom: 6px;">
                                        <strong>Masuk ke Akun Anda:</strong> Klik tombol di bawah dan login menggunakan email/Google dan kata sandi yang telah Anda daftarkan.
                                    </li>
                                    <li style="margin-bottom: 6px;">
                                        <strong>Periksa Form Perbaikan:</strong> Sistem akan otomatis mengarahkan Anda ke formulir pendaftaran dengan data sebelumnya yang masih tersimpan.
                                    </li>
                                    <li style="margin-bottom: 6px;">
                                        <strong>Perbaiki Sesuai Catatan:</strong> Sesuaikan data usaha atau unggah ulang bukti transfer yang jelas sesuai alasan penolakan di atas.
                                    </li>
                                    <li style="margin-bottom: 6px;">
                                        <strong>Kirim Ulang:</strong> Pengajuan Anda akan otomatis masuk kembali ke antrean verifikasi Owner untuk disetujui.
                                    </li>
                                </ol>
                            </div>

                            {{-- CTA Button --}}
                            <div style="text-align: center; margin: 32px 0 24px;">
                                <a href="{{ $loginUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; background-color: #ea580c; color: #ffffff; text-decoration: none; padding: 13px 28px; border-radius: 10px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.3);">
                                    Masuk & Perbaiki Data Pendaftaran &rarr;
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #64748b; margin: 0 0 16px; line-height: 1.5; text-align: center;">
                                Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut di browser Anda:<br>
                                <a href="{{ $loginUrl }}" style="color: #ea580c; word-break: break-all;">{{ $loginUrl }}</a>
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0 0 4px;">
                                Email ini dikirim secara otomatis oleh sistem RentalBase terkait status pendaftaran toko Anda.
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

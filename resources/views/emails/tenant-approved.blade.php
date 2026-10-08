<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-w-xl mx-auto p-4">
        <h2 style="color: #f97316;">Pendaftaran Toko Disetujui!</h2>
        
        <p>Halo {{ $user->name }},</p>
        
        <p>Pendaftaran toko Anda telah disetujui oleh Owner.</p>
        
        <p>Akun Anda sekarang sudah aktif dan Anda dapat masuk ke Dashboard Admin RentalBase.</p>
        
        <p>Silakan login menggunakan username atau email dan password yang telah Anda daftarkan.</p>
        
        <div style="margin-top: 30px; margin-bottom: 30px;">
            <a href="{{ route('login') }}" style="background-color: #f97316; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Login ke Dashboard</a>
        </div>
        
        <p>Jika tombol di atas tidak berfungsi, Anda dapat menyalin tautan berikut ke browser Anda:</p>
        <p>{{ route('login') }}</p>
        
        <br>
        <p>Terima kasih,<br>Tim RentalBase</p>
    </div>
</body>
</html>

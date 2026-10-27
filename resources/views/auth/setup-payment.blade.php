@extends('auth.layout')

@section('title', 'Pembayaran Subscription')
@section('container-class', 'auth-container-wide')

@section('before-content')
    <!-- Step Indicator (4 Steps) -->
    <div style="display: flex; justify-content: center; margin-bottom: 32px; width: 100%;">
        <div style="display: flex; align-items: center; width: 100%; max-width: 520px;">
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
                <span style="font-size: 12px; font-weight: 700; color: var(--slate-900); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Pembayaran</span>
            </div>

            <!-- Line 3 -->
            <div style="flex: 1; height: 3px; background-color: var(--slate-200); position: relative; margin: 0 8px;"></div>
            
            <!-- Step 4 (Inactive) -->
            <div style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; background-color: var(--slate-200); color: var(--slate-500); box-shadow: 0 0 0 4px #fffaf5;">
                    4
                </div>
                <span style="font-size: 12px; font-weight: 600; color: var(--slate-500); margin-top: 8px; position: absolute; top: 32px; white-space: nowrap;">Verifikasi</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="auth-header" style="text-align: center; margin-bottom: 28px;">
        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 8px; color: var(--slate-900);">Konfirmasi Pembayaran</h1>
        <p style="font-size: 14px; color: var(--slate-500); line-height: 1.5;">
            Selesaikan pembayaran subscription untuk mengirim pengajuan pendaftaran toko Anda ke Owner.
        </p>
    </div>

    @if (session('rejection_reason'))
        <div class="alert" style="background-color: var(--red-50); border: 1px solid var(--red-500); color: var(--red-700); padding: 14px 18px; border-radius: 12px; margin-bottom: 24px; font-size: 14px; line-height: 1.5;" role="alert">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px; color: var(--red-500); flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div>
                    <strong style="display: block; font-weight: 700; color: var(--red-800); margin-bottom: 2px;">Pendaftaran Ditolak</strong>
                    <span>Pendaftaran Anda sebelumnya ditolak dengan alasan: <strong>{{ session('rejection_reason') }}</strong>. Silakan perbaiki data di bawah ini dan ajukan ulang.</span>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert" style="background-color: var(--red-50); border: 1px solid var(--red-500); color: var(--red-500); padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; font-weight: 500;" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Ringkasan Paket & Detail Pembayaran -->
    <div style="background-color: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--slate-200); padding-bottom: 14px; margin-bottom: 14px;">
            <div>
                <span style="font-size: 12px; font-weight: 600; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.5px;">Paket Terpilih</span>
                <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin: 2px 0 0;">{{ $planDetails['name'] }}</h3>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px; font-weight: 600; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.5px;">Total Tagihan</span>
                <div style="font-size: 20px; font-weight: 800; color: var(--orange-500);">
                    {{ $price > 0 ? 'Rp ' . number_format($price, 0, ',', '.') : 'Gratis (Rp 0)' }}
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--slate-600);">
            <span>Masa Berlaku: <strong>1 Bulan</strong></span>
            <span>Toko: <strong>{{ $client->business_name }} ({{ $client->subdomain }}.rentalbase.id)</strong></span>
        </div>
    </div>

    @if($price > 0)
        <!-- Instruksi Metode Pembayaran -->
        <div style="margin-bottom: 24px;">
            <h2 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px; color: var(--orange-500);"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                Instruksi Transfer & QRIS
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                <!-- Rekening Bank Transfer -->
                <div style="background-color: white; border: 1px solid var(--slate-200); border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--orange-600); background-color: var(--orange-50); padding: 4px 10px; border-radius: 99px;">Transfer Bank</span>
                        <span style="font-weight: 800; font-size: 15px; color: var(--slate-800);">{{ $paymentInfo['bank_name'] }}</span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 12px; color: var(--slate-500); display: block; margin-bottom: 2px;">Nomor Rekening:</span>
                        <div style="display: flex; align-items: center; justify-content: space-between; background-color: var(--slate-50); border: 1px dashed var(--slate-300); padding: 8px 12px; border-radius: 8px;">
                            <span id="account-number" style="font-family: monospace; font-size: 16px; font-weight: 700; color: var(--slate-900); letter-spacing: 1px;">{{ $paymentInfo['account_number'] }}</span>
                            <button type="button" id="copy-btn" onclick="copyAccountNumber()" style="background: none; border: none; color: var(--orange-500); font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 4px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                <span id="copy-text">Salin</span>
                            </button>
                        </div>
                    </div>

                    <div style="font-size: 13px; color: var(--slate-600);">
                        <span>Atas Nama: <strong>{{ $paymentInfo['account_holder'] }}</strong></span>
                    </div>
                </div>

                <!-- QRIS Pembayaran -->
                <div style="background-color: white; border: 1px solid var(--slate-200); border-radius: 12px; padding: 18px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--orange-600); background-color: var(--orange-50); padding: 4px 10px; border-radius: 99px;">QRIS Standar</span>
                        <span style="font-size: 12px; color: var(--slate-400);">Scan & Bayar</span>
                    </div>

                    <div style="display: inline-block; background-color: white; padding: 8px; border: 1px solid var(--slate-200); border-radius: 8px; margin-bottom: 8px;">
                        @if(!empty($paymentInfo['qris_image']) && file_exists(public_path($paymentInfo['qris_image'])))
                            <img src="{{ asset($paymentInfo['qris_image']) }}" alt="QRIS RentalBase" style="width: 130px; height: 130px; object-fit: contain;">
                        @else
                            <!-- Placeholder QRIS SVG jika file belum diunggah di server -->
                            <div style="width: 130px; height: 130px; background-color: var(--slate-100); border-radius: 6px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 8px; color: var(--slate-500);">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 36px; height: 36px; margin-bottom: 6px; color: var(--slate-700);"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                <span style="font-size: 10px; font-weight: 700; color: var(--slate-700);">QRIS RentalBase</span>
                                <span style="font-size: 8px; color: var(--slate-400);">Gopay/OVO/BCA/Dana/dll</span>
                            </div>
                        @endif
                    </div>
                    <p style="font-size: 12px; color: var(--slate-500); margin: 0;">Pindai kode QRIS di atas melalui mobile banking atau e-wallet Anda.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Upload Bukti Pembayaran & Pengiriman Pengajuan -->
    <form method="POST" action="{{ route('tenant.register') }}" enctype="multipart/form-data" novalidate id="payment-form">
        @csrf
        <input type="hidden" name="action_step" value="payment">

        <div style="background-color: white; border: 1px solid var(--slate-200); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
            <h2 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px; color: var(--orange-500);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Unggah Bukti Pembayaran
                <span style="color: var(--orange-500); font-size: 14px;">*</span>
            </h2>
            <p style="font-size: 13px; color: var(--slate-500); margin-bottom: 16px;">
                @if($price > 0)
                    Unggah struk transfer ATM, m-banking, atau tangkapan layar pembayaran QRIS (Format: JPG, JPEG, PNG. Maks: 2MB).
                @else
                    Paket Starter bebas biaya berlangganan. Silakan unggah tangkapan layar persetujuan atau konfirmasi registrasi (Maks: 2MB).
                @endif
            </p>

            <div class="field" style="margin-bottom: 0;">
                @if(!empty($existingProof))
                    <div style="margin-bottom: 12px; padding: 10px 14px; background-color: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 8px; font-size: 12px; color: var(--slate-600); display: flex; align-items: center; justify-content: space-between;">
                        <span>Bukti pembayaran sebelumnya telah tersimpan. Pilih file baru jika ingin menggantinya.</span>
                        <a href="{{ asset('storage/' . $existingProof) }}" target="_blank" style="color: var(--orange-600); font-weight: 600; text-decoration: none;">Lihat Bukti &rarr;</a>
                    </div>
                @endif
                <input type="file" id="payment_proof" name="payment_proof" accept="image/png,image/jpeg,image/jpg" class="input @error('payment_proof') is-invalid @enderror" style="padding: 10px;" {{ empty($existingProof) ? 'required' : '' }}>
                <div id="file-preview-container" style="display: none; margin-top: 12px; align-items: center; gap: 12px; background-color: var(--slate-50); border: 1px solid var(--slate-200); padding: 8px 12px; border-radius: 8px;">
                    <img id="file-preview-img" src="" alt="Preview Bukti" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid var(--slate-200);">
                    <div style="flex: 1; min-width: 0;">
                        <span id="file-preview-name" style="font-size: 13px; font-weight: 700; color: var(--slate-800); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                        <span id="file-preview-size" style="font-size: 11px; color: var(--slate-500);"></span>
                    </div>
                    <button type="button" onclick="clearPaymentFile()" style="background: none; border: none; color: var(--red-500); font-size: 12px; font-weight: 600; cursor: pointer; padding: 4px;">
                        Ganti File
                    </button>
                </div>

                <span id="payment-proof-error" style="color: var(--red-500); font-size: 12px; margin-top: 6px; display: none; font-weight: 500;"></span>
                @error('payment_proof')
                    <span style="color: var(--red-500); font-size: 12px; margin-top: 6px; display: block; font-weight: 500;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: flex; gap: 16px;">
            <a href="{{ route('tenant.register', ['step' => 'theme']) }}" class="btn btn-primary" style="flex: 1; margin-top: 0; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; background-color: white; color: var(--slate-700); border: 1px solid var(--slate-300);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px; width: 18px; height: 18px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Kembali ke Tema
            </a>
            
            <button type="submit" id="submit-btn" class="btn btn-primary" style="flex: 1; margin-top: 0; display: inline-flex; align-items: center; justify-content: center;">
                <span id="submit-btn-text">Kirim Pengajuan</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 6px; width: 18px; height: 18px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </div>
    </form>

    <script>
        function copyAccountNumber() {
            const accNum = document.getElementById('account-number').innerText;
            navigator.clipboard.writeText(accNum).then(() => {
                const copyText = document.getElementById('copy-text');
                copyText.innerText = 'Tersalin!';
                setTimeout(() => {
                    copyText.innerText = 'Salin';
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin nomor rekening:', err);
            });
        }

        function clearPaymentFile() {
            const input = document.getElementById('payment_proof');
            const previewContainer = document.getElementById('file-preview-container');
            const previewImg = document.getElementById('file-preview-img');
            const errorElem = document.getElementById('payment-proof-error');

            input.value = '';
            previewContainer.style.display = 'none';
            previewImg.src = '';
            if (errorElem) {
                errorElem.style.display = 'none';
                errorElem.textContent = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('payment_proof');
            const previewContainer = document.getElementById('file-preview-container');
            const previewImg = document.getElementById('file-preview-img');
            const previewName = document.getElementById('file-preview-name');
            const previewSize = document.getElementById('file-preview-size');
            const errorElem = document.getElementById('payment-proof-error');
            const form = document.getElementById('payment-form');
            const submitBtn = document.getElementById('submit-btn');
            const submitBtnText = document.getElementById('submit-btn-text');

            const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
            const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg'];

            if (input) {
                input.addEventListener('change', function () {
                    const file = this.files[0];
                    if (file) {
                        if (!ALLOWED_TYPES.includes(file.type)) {
                            errorElem.textContent = 'Format file tidak didukung. Harap unggah gambar JPG atau PNG.';
                            errorElem.style.display = 'block';
                            this.value = '';
                            previewContainer.style.display = 'none';
                            return;
                        }

                        if (file.size > MAX_FILE_SIZE) {
                            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                            errorElem.textContent = `Ukuran file (${sizeMB}MB) melebihi batas maksimal 2MB. Silakan pilih file yang lebih kecil.`;
                            errorElem.style.display = 'block';
                            this.value = '';
                            previewContainer.style.display = 'none';
                            return;
                        }

                        errorElem.style.display = 'none';
                        errorElem.textContent = '';

                        // Show preview
                        previewName.textContent = file.name;
                        previewSize.textContent = `${(file.size / 1024).toFixed(1)} KB`;

                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewImg.src = e.target.result;
                            previewContainer.style.display = 'flex';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.style.display = 'none';
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', function (e) {
                    const file = input ? input.files[0] : null;

                    if (!file) {
                        e.preventDefault();
                        if (errorElem) {
                            errorElem.textContent = 'Bukti pembayaran wajib diunggah sebelum mengirim pengajuan.';
                            errorElem.style.display = 'block';
                        }
                        if (input) input.focus();
                        return;
                    }

                    if (file.size > MAX_FILE_SIZE) {
                        e.preventDefault();
                        if (errorElem) {
                            errorElem.textContent = 'Ukuran file bukti pembayaran tidak boleh lebih dari 2MB.';
                            errorElem.style.display = 'block';
                        }
                        if (input) input.focus();
                        return;
                    }

                    // Prevent double submit
                    submitBtn.disabled = true;
                    submitBtnText.textContent = 'Mengirim pengajuan...';
                });
            }
        });
    </script>
@endsection

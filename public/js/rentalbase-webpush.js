/**
 * RentalBase Web Push Client Handler (Alpine.js component)
 * Mengelola izin notifikasi OS, registrasi Service Worker, dan sinkronisasi subscription.
 */
function rbWebPush() {
    return {
        isSupported: ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window),
        permission: typeof Notification !== 'undefined' ? Notification.permission : 'default',
        isSubscribed: false,
        isLoading: false,
        feedbackMessage: '',

        async init() {
            if (!this.isSupported) return;

            window.addEventListener('rb-webpush-state', (e) => {
                if (e.detail && typeof e.detail.isSubscribed === 'boolean') {
                    this.isSubscribed = e.detail.isSubscribed;
                }
            });

            await this.registerServiceWorker();
            await this.checkSubscription();
        },

        async registerServiceWorker() {
            try {
                return await navigator.serviceWorker.register('/sw.js', { scope: '/' });
            } catch (err) {
                console.warn('[RentalBase WebPush] Register SW error:', err);
            }
        },

        async checkSubscription() {
            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                this.isSubscribed = Boolean(sub);
                if (typeof Notification !== 'undefined') {
                    this.permission = Notification.permission;
                }

                // Sinkronkan subscription aktif ke server sekali per halaman agar database selalu up-to-date
                if (sub && !window.__rbWebPushSynced) {
                    window.__rbWebPushSynced = true;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const subJson = sub.toJSON();
                    if (subJson && subJson.keys) {
                        fetch('/webpush/subscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                endpoint: sub.endpoint,
                                keys: subJson.keys,
                                content_encoding: 'aes128gcm'
                            })
                        }).catch(() => {});
                    }
                }
            } catch (err) {}
        },

        broadcastState(subscribed) {
            this.isSubscribed = subscribed;
            window.dispatchEvent(new CustomEvent('rb-webpush-state', {
                detail: { isSubscribed: subscribed }
            }));
        },

        async toggleSubscription() {
            if (this.isSubscribed) {
                await this.unsubscribe();
            } else {
                await this.subscribe();
            }
        },

        async subscribe() {
            if (!this.isSupported) return;
            this.isLoading = true;
            this.feedbackMessage = '';

            try {
                // 1. Minta izin notifikasi ke pengguna
                const perm = await Notification.requestPermission();
                this.permission = perm;
                if (perm !== 'granted') {
                    this.feedbackMessage = 'Izin notifikasi ditolak di pengaturan browser.';
                    setTimeout(() => { this.feedbackMessage = ''; }, 4000);
                    return;
                }

                // 2. Ambil VAPID Public Key dari backend
                const resKey = await fetch('/webpush/vapid-public-key', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!resKey.ok) throw new Error('Gagal mengambil kunci VAPID server');
                const { publicKey } = await resKey.json();
                const appServerKey = this.urlBase64ToUint8Array(publicKey);

                // 3. Daftarkan subscription ke PushManager (handle rotasi kunci jika ada sub lama)
                const reg = await navigator.serviceWorker.ready;
                let sub = null;
                try {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: appServerKey
                    });
                } catch (subErr) {
                    const existing = await reg.pushManager.getSubscription();
                    if (existing) {
                        try {
                            await existing.unsubscribe();
                        } catch (unsubErr) {}
                        sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: appServerKey
                        });
                    } else {
                        throw subErr;
                    }
                }

                // 4. Simpan ke database Laravel
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const subJson = sub.toJSON();

                const resSub = await fetch('/webpush/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        endpoint: sub.endpoint,
                        keys: subJson.keys,
                        content_encoding: 'aes128gcm'
                    })
                });

                if (resSub.ok) {
                    window.__rbWebPushSynced = true;
                    this.broadcastState(true);
                    this.feedbackMessage = 'Notifikasi perangkat berhasil diaktifkan!';
                    setTimeout(() => { this.feedbackMessage = ''; }, 4000);
                } else {
                    throw new Error('Gagal menyimpan subscription ke server');
                }
            } catch (err) {
                console.error('[RentalBase WebPush] Subscribe error:', err);
                const errMsg = String(err?.message || '');
                let isBrave = false;
                try {
                    if (navigator.brave && typeof navigator.brave.isBrave === 'function') {
                        isBrave = await navigator.brave.isBrave();
                    }
                } catch (e) {}

                if (errMsg.toLowerCase().includes('push service error')) {
                    if (isBrave) {
                        this.feedbackMessage = 'Brave memblokir push messaging bawaan. Buka brave://settings/privacy, aktifkan "Use Google services for push messaging", lalu restart browser.';
                    } else {
                        this.feedbackMessage = 'Push service gagal dihubungi oleh browser. Pastikan koneksi internet stabil & push service tidak diblokir pengaturan browser.';
                    }
                    setTimeout(() => { this.feedbackMessage = ''; }, 12000);
                } else {
                    this.feedbackMessage = 'Gagal: ' + errMsg;
                    setTimeout(() => { this.feedbackMessage = ''; }, 5000);
                }
            } finally {
                this.isLoading = false;
            }
        },

        async unsubscribe() {
            this.isLoading = true;
            this.feedbackMessage = '';
            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                if (sub) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    await fetch('/webpush/unsubscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ endpoint: sub.endpoint })
                    });
                    await sub.unsubscribe();
                }
                this.broadcastState(false);
                this.feedbackMessage = 'Notifikasi perangkat telah dinonaktifkan.';
                setTimeout(() => { this.feedbackMessage = ''; }, 3000);
            } catch (err) {
                console.error('[RentalBase WebPush] Unsubscribe error:', err);
            } finally {
                this.isLoading = false;
            }
        },

        async sendTestNotification() {
            this.isLoading = true;
            this.feedbackMessage = '';
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/webpush/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                this.feedbackMessage = data.message || 'Notifikasi uji coba dikirim!';
                setTimeout(() => { this.feedbackMessage = ''; }, 4000);
            } catch (err) {
                this.feedbackMessage = 'Gagal mengirim uji coba.';
                setTimeout(() => { this.feedbackMessage = ''; }, 4000);
            } finally {
                this.isLoading = false;
            }
        },

        urlBase64ToUint8Array(base64String) {
            if (!base64String || typeof base64String !== 'string') {
                throw new Error('VAPID public key kosong atau tidak valid.');
            }
            const cleanStr = base64String.trim().replace(/=/g, '');
            const padding = '='.repeat((4 - (cleanStr.length % 4)) % 4);
            const base64 = (cleanStr + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    };
}

window.rbWebPush = rbWebPush;

/**
 * RentalBase Service Worker for Background Web Push Notifications
 * Standar W3C Push API & RFC 8292
 */

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let payload = {};
    try {
        if (event.data) {
            payload = event.data.json();
        }
    } catch (e) {
        payload = {
            title: 'RentalBase Notification',
            body: event.data ? event.data.text() : 'Pemberitahuan baru diterima.',
            url: '/'
        };
    }

    const title = payload.title || 'RentalBase';
    const targetUrl = payload.url || (payload.data && payload.data.url) || '/';

    const notificationOptions = {
        body: payload.body || 'Pemberitahuan baru dari RentalBase.',
        icon: payload.icon || '/images/rentalbase-icon.svg',
        badge: payload.badge || '/images/rentalbase-icon.svg',
        tag: payload.tag || ('rb-' + Date.now()),
        renotify: true,
        vibrate: [200, 100, 200],
        data: {
            url: targetUrl,
            time: Date.now()
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, notificationOptions)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/';

    const getPortalRole = (u) => {
        try {
            const p = new URL(u, self.location.origin).pathname;
            if (p.startsWith('/admin')) return 'admin';
            if (p.startsWith('/owner')) return 'owner';
            return 'customer';
        } catch (e) {
            return 'customer';
        }
    };

    const targetRole = getPortalRole(targetUrl);

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // 1. Jika ada tab yang sudah persis membuka URL tersebut, langsung fokuskan
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // 2. Jika ada tab dengan portal role yang sama (misal sama-sama /admin), arahkan & fokuskan
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (getPortalRole(client.url) === targetRole && 'focus' in client && 'navigate' in client) {
                    return client.navigate(targetUrl).then(() => client.focus());
                }
            }
            // 3. Jika tab portal tersebut sedang ditutup, buka tab/jendela browser baru
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

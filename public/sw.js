const CACHE_NAME = 'sanfran-healthsys-v6';
const APP_BASE_PATH = new URL(self.registration.scope).pathname.replace(/\/$/, '');
const appPath = (path) => `${APP_BASE_PATH}/${path}`;
const PRECACHE_URLS = [
    'offline.html',
    'manifest.webmanifest',
    'images/logo.png',
    'icons/icon-32-v2.png',
    'icons/icon-192-v2.png',
    'icons/icon-512-v2.png',
    'icons/icon-192-maskable-v2.png',
    'icons/icon-512-maskable-v2.png',
    'icons/apple-touch-icon-v2.png',
].map((path) => new URL(path, self.registration.scope).toString());

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (
        url.pathname.includes('@vite')
        || url.pathname.includes('hot')
        || url.pathname.startsWith(appPath('login'))
        || event.request.mode === 'navigate'
        || event.request.destination === 'document'
    ) {
        return;
    }

    const isPublicAsset = url.pathname === appPath('offline.html')
        || url.pathname === appPath('manifest.webmanifest')
        || url.pathname.startsWith(appPath('icons/'))
        || url.pathname.startsWith(appPath('images/'))
        || url.pathname.startsWith(appPath('build/'));

    if (! isPublicAsset) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                }

                return response;
            })
            .catch(async () => {
                const cached = await caches.match(event.request);

                return cached || caches.match(appPath('offline.html'));
            }),
    );
});

self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    const payload = event.data.json();
    const unreadCount = Number(payload.unreadCount) || 0;

    event.waitUntil(Promise.all([
        self.registration.showNotification(payload.title || 'New health announcement', {
            body: payload.body || 'A new announcement is available.',
            icon: appPath('icons/icon-192-v2.png'),
            badge: appPath('icons/icon-32-v2.png'),
            tag: `sanfran-announcement-${payload.announcementId || Date.now()}`,
            data: {
                url: payload.url || '/',
            },
        }),
        self.navigator.setAppBadge?.(unreadCount).catch(() => {}),
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            clients.forEach((client) => client.postMessage({
                type: 'announcement-push',
                unreadCount,
            }));
        }),
    ]));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = new URL(event.notification.data?.url || '/', self.location.origin);
    const announcementHash = targetUrl.hash.match(/^#announcement-(\d+)$/);

    if (announcementHash && ! targetUrl.searchParams.has('announcement')) {
        targetUrl.searchParams.set('announcement', announcementHash[1]);
        targetUrl.hash = '';
    }

    targetUrl.searchParams.set('push_read', '1');

    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
        const existingClient = clients.find((client) => new URL(client.url).origin === self.location.origin);

        if (existingClient) {
            return existingClient.navigate(targetUrl.toString()).then((client) => (client || existingClient).focus());
        }

        return self.clients.openWindow(targetUrl.toString());
    }));
});

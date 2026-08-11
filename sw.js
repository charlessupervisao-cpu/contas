/* POLLICONTAS PWA — Service Worker */
const CACHE_VERSION = 'pollicontas-app-v10';

self.addEventListener('install', (event) => {
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_VERSION);
      const base = self.registration.scope;
      const assets = [
        base + 'login.php',
        base + 'assets/img/pollicontas-logo.png',
        base + 'assets/img/pollicontas-logo-128.png',
        base + 'assets/img/icons/icon-192.png',
        base + 'assets/img/icons/icon-512.png',
        base + 'manifest.php',
      ];
      await Promise.all(
        assets.map(async (url) => {
          try {
            const res = await fetch(url, { cache: 'reload' });
            if (res && res.ok) await cache.put(url, res);
          } catch (e) {
            /* ignora asset ausente no install */
          }
        })
      );
      self.skipWaiting();
    })()
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const keys = await caches.keys();
      await Promise.all(keys.filter((k) => k !== CACHE_VERSION).map((k) => caches.delete(k)));
      await self.clients.claim();
    })()
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (url.pathname.includes('/api/')) return;

  const isPage =
    req.mode === 'navigate' ||
    url.pathname.endsWith('.php') ||
    url.pathname.endsWith('/');

  // CSS/JS sempre rede primeiro — evita formulário preso em JS antigo
  const isCodeAsset =
    url.pathname.includes('/assets/css/') ||
    url.pathname.includes('/assets/js/') ||
    url.pathname.endsWith('/sw.js');

  if (isPage || isCodeAsset) {
    event.respondWith(networkFirst(req));
    return;
  }

  if (url.pathname.includes('/assets/')) {
    event.respondWith(cacheFirst(req));
  }
});

async function networkFirst(req) {
  const cache = await caches.open(CACHE_VERSION);
  try {
    const fresh = await fetch(req, { cache: 'no-cache' });
    if (fresh && fresh.ok && req.method === 'GET') {
      try {
        cache.put(req, fresh.clone());
      } catch (e) {
        /* ignore opaque */
      }
    }
    return fresh;
  } catch (err) {
    const cached = await cache.match(req);
    if (cached) return cached;
    const login = await cache.match(new URL('login.php', self.registration.scope).href);
    if (login) return login;
    return new Response(
      '<!DOCTYPE html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title><body style="font-family:system-ui;padding:2rem;background:#0a254a;color:#fff"><h1>POLLICONTAS</h1><p>Você está offline. Conecte-se à internet para continuar.</p></body></html>',
      { headers: { 'Content-Type': 'text/html; charset=utf-8' }, status: 503 }
    );
  }
}

async function cacheFirst(req) {
  const cache = await caches.open(CACHE_VERSION);
  const cached = await cache.match(req);
  if (cached) return cached;
  try {
    const fresh = await fetch(req);
    if (fresh && fresh.ok) cache.put(req, fresh.clone());
    return fresh;
  } catch (err) {
    return cached || Response.error();
  }
}

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

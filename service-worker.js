const CACHE_NAME = 'despacho-cache-v3';
const urlsToCache = [
  '/manifest.json',
  '/src/LOGO ESQUINA WEB ICONO.png',
  '/src/LOGO ESQUINA WEB.png',
  '/src/LogoDespacho.png',
  'https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
  // Otros recursos estáticos...
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache abierta');
        return cache.addAll(urlsToCache);
      })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (!cacheWhitelist.includes(cacheName)) {
            console.log('Eliminando caché antigua:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);

  // Ignorar solicitudes a orígenes externos
  if (url.origin !== location.origin) {
    return;
  }

  // Estrategia para recursos estáticos
  if (request.destination === 'style' ||
      request.destination === 'script' ||
      request.destination === 'image' ||
      /\.(?:js|css|png|jpg|jpeg|svg|gif)$/.test(url.pathname)) {

    // Estrategia Cache First para recursos estáticos
    event.respondWith(
      caches.match(request)
        .then(cachedResponse => {
          if (cachedResponse) {
            return cachedResponse;
          }
          return fetch(request).then(networkResponse => {
            // Verifica que la respuesta sea válida y del mismo origen
            if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }
            return caches.open(CACHE_NAME).then(cache => {
              cache.put(request, networkResponse.clone());
              return networkResponse;
            });
          });
        })
    );

  } else {
    // Estrategia Network First para recursos dinámicos
    event.respondWith(
      fetch(request)
        .then(networkResponse => {
          // Verifica que la respuesta sea válida y del mismo origen
          if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
            return networkResponse;
          }
          return caches.open(CACHE_NAME).then(cache => {
            cache.put(request, networkResponse.clone());
            return networkResponse;
          });
        })
        .catch(() => {
          return caches.match(request);
        })
    );
  }
});
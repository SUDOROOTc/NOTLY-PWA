const cacheName = 'notly-v1';
const assets = [
  '../index.php',
  '../css/style.css',
  '../js/indexedDB.js',
  '../js/insert_product.js',
  '../js/insert_produit_phase1.js',
];

// Installation du SW
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(cacheName).then(cache => {
      return cache.addAll(assets);
    })
  );
});

// Activation du SW
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== cacheName).map(key => caches.delete(key))
      );
    })
  );
});

// Intercepter les requêtes
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(resp => {
      return resp || fetch(event.request);
    })
  );
});

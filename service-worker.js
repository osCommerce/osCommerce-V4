// true in production
var doCache = true;

// name cash
var CACHE_NAME = 'my-pwa-cache-v2';

// delete cash
self.addEventListener('activate', event => {
   const cacheWhitelist = [CACHE_NAME];
   event.waitUntil(
       caches.keys()
           .then(keyList =>
               Promise.all(keyList.map(key => {
                   if (!cacheWhitelist.includes(key)) {
                       console.log('Deleting cache: ' + key)
                       return caches.delete(key);
                   }
               }))
           )
   );
});

self.addEventListener('install', function(event) {
   if (doCache) {
       event.waitUntil(
           caches.open(CACHE_NAME)
               .then(function(cache) {
                   fetch('/manifest.json')
                       .then(response => {
                           response.json()
                       })
                       .then(assets => {
                           const urlsToCache = [
                               '',
                           ]
                           cache.addAll(urlsToCache)
                           console.log('cached');
                       })
               })
       );
   }
});

self.addEventListener('fetch', function(event) {
   if (doCache) {
       event.respondWith(
           caches.match(event.request).then(function(response) {
               return response || fetch(event.request);
           })
       );
   }
});

self.addEventListener('push', function(event) {
    var body;
    if (event.data){
        body = event.data.json();
    }
    if (!body) return;
    var options = {
        body: body.message,
        icon: body.icon,
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
    };
    if (body.link){
        options.actions = [
            {action: 'show', title: body.show.title, icon:body.show.icon},
            {action: 'close', title: body.close.title, icon:body.close.icon},
        ];
        options.data.link = body.link;
    }
    if (body.requireInteraction){
        options.requireInteraction = true;
    }

    event.waitUntil(
        self.registration.showNotification(body.title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    if (!event.action) return;
    if (event.action == 'show'){
        var client = clients.openWindow(event.notification.data.link);
        event.waitUntil(client);
    } else {
        event.notification.close();
    }
});
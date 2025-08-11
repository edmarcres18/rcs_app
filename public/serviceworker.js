// Version 3 - Updated with push notification support
const CACHE_NAME = 'mhr-rcs-cache-v3';
const urlsToCache = [
  '/',
  '/offline',
  '/storage/app_logo/logo.png',
];

// Install the service worker with improved error handling
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Cache opened successfully');

        // Cache the offline page first as it's critical
        return cache.add('/offline')
          .then(() => {
            console.log('Service Worker: Offline page cached');

            // Try to cache each remaining URL individually
            const remainingUrls = urlsToCache.filter(url => url !== '/offline');
            const cachePromises = remainingUrls.map(url => {
              return fetch(url, { cache: 'no-store' })
                .then(response => {
                  if (response.status === 200) {
                    cache.put(url, response.clone());
                    console.log(`Service Worker: Cached ${url}`);
                    return Promise.resolve();
                  } else {
                    console.log(`Service Worker: Skipping ${url} - status ${response.status}`);
                    return Promise.resolve();
                  }
                })
                .catch(error => {
                  console.log(`Service Worker: Failed to cache ${url}`, error.message);
                  return Promise.resolve(); // Continue despite errors
                });
            });

            return Promise.all(cachePromises)
              .catch(error => {
                console.log('Service Worker: Some caching operations failed, but continuing', error.message);
                return Promise.resolve(); // Continue installation despite errors
              });
          })
          .catch(error => {
            console.log('Service Worker: Failed to cache offline page, but continuing', error.message);
            return Promise.resolve(); // Continue installation despite errors
          });
      })
      .catch(error => {
        console.log('Service Worker: Cache opening failed, but continuing', error.message);
        return Promise.resolve(); // Continue installation despite errors
      })
  );

  // Force activation of the new service worker
  self.skipWaiting();
});

// Activate the service worker and clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating');

  // Take control of all clients immediately
  self.clients.claim();

  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            console.log('Service Worker: Deleting old cache:', cache);
            return caches.delete(cache);
          }
          return Promise.resolve();
        })
      );
    })
    .then(() => {
      console.log('Service Worker: Activated and controlling clients');
    })
    .catch(error => {
      console.log('Service Worker: Activation error, but continuing', error.message);
      return Promise.resolve(); // Continue activation despite errors
    })
  );
});

// Handle push events (new notifications)
self.addEventListener('push', event => {
  console.log('Service Worker: Push event received');

  let notificationData = {};

  try {
    if (event.data) {
      notificationData = event.data.json();
    }
  } catch (error) {
    console.error('Service Worker: Error parsing push data', error);
    notificationData = {
      title: 'New Notification',
      body: 'You have a new notification.',
      icon: '/storage/app_logo/logo.png',
      data: {
        url: '/'
      }
    };
  }

  const title = notificationData.title || 'New Notification';
  const options = {
    body: notificationData.body || 'You have a new notification',
    icon: notificationData.icon || '/storage/app_logo/logo.png',
    badge: '/storage/app_logo/logo.png',
    data: notificationData.data || { url: '/' },
    vibrate: [100, 50, 100],
    requireInteraction: true
  };

  event.waitUntil(
    self.registration.showNotification(title, options)
      .catch(error => {
        console.error('Service Worker: Error showing notification', error);
      })
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Notification click received');

  event.notification.close();

  const urlToOpen = event.notification.data && event.notification.data.url
    ? event.notification.data.url
    : '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then(windowClients => {
        // Check if there is already a window/tab open with the target URL
        for (let i = 0; i < windowClients.length; i++) {
          const client = windowClients[i];
          // If so, focus it
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }

        // If not, open a new window/tab
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
      .catch(error => {
        console.error('Service Worker: Error handling notification click', error);
      })
  );
});

// Fetch resources with improved error handling
self.addEventListener('fetch', event => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Handle API requests differently - don't cache them
  if (event.request.url.includes('/api/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }

        return fetch(event.request)
          .then(response => {
            // Return the response but don't cache it
            return response;
          })
          .catch(() => {
            // If the request is for a page, return the offline page
            if (event.request.headers.get('accept').includes('text/html')) {
              return caches.match('/offline');
            }

            // Otherwise just resolve with an error response
            return new Response('Network error happened', {
              status: 408,
              headers: { 'Content-Type': 'text/plain' }
            });
          });
      })
      .catch(error => {
        console.log('Service Worker: Fetch handler error', error.message);
        return caches.match('/offline');
      })
  );
});

// Pärlikee service worker — installability only, no offline caching.
// Bump SW_VERSION when you want clients to drop the old worker.
const SW_VERSION = "v1";

self.addEventListener("install", () => {
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(self.clients.claim());
});

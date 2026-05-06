// Pärlikee service worker — installability + web push.
// Bump SW_VERSION when you want clients to drop the old worker.
const SW_VERSION = "v2";

self.addEventListener("install", () => {
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener("push", (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: "Pärlikee", body: event.data ? event.data.text() : "" };
    }

    const title = data.title || "Pärlikee";
    const options = {
        body: data.body || "",
        icon: data.icon || "/icons/icon-192.png",
        badge: data.badge || "/icons/icon-192.png",
        data: data.data || {},
        tag: data.data?.message_id ? `msg-${data.data.message_id}` : undefined,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", (event) => {
    event.notification.close();
    const target = event.notification.data?.url || "/dashboard";

    event.waitUntil((async () => {
        const all = await self.clients.matchAll({ type: "window", includeUncontrolled: true });
        for (const client of all) {
            if (client.url.includes(target) && "focus" in client) {
                return client.focus();
            }
        }
        if (self.clients.openWindow) {
            return self.clients.openWindow(target);
        }
    })());
});

<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const vapidPublicKey = computed(() => usePage().props.vapidPublicKey ?? null);

const supported = ref(false);
const permission = ref('default');
const subscribed = ref(false);
const busy = ref(false);
const error = ref('');
const isStandalone = ref(true);
const isIosSafari = ref(false);

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    const out = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
    return out;
}

async function refreshState() {
    if (!supported.value) return;
    permission.value = Notification.permission;
    const reg = await navigator.serviceWorker.ready;
    const sub = await reg.pushManager.getSubscription();
    subscribed.value = !!sub;
}

onMounted(async () => {
    supported.value =
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window;

    const ua = navigator.userAgent || '';
    isIosSafari.value = /iPhone|iPad|iPod/.test(ua);
    isStandalone.value =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    if (supported.value) {
        try {
            await refreshState();
        } catch (e) {
            error.value = e.message || 'Could not read subscription state.';
        }
    }
});

async function enable() {
    error.value = '';
    if (!supported.value || !vapidPublicKey.value) return;

    busy.value = true;
    try {
        const reg = await navigator.serviceWorker.ready;
        const result = await Notification.requestPermission();
        permission.value = result;
        if (result !== 'granted') {
            error.value = 'Permission was not granted.';
            return;
        }

        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey.value),
        });

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            ?? document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1];

        const res = await fetch(route('push.store'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
                ),
            },
            body: JSON.stringify(sub.toJSON()),
        });

        if (!res.ok) throw new Error('Server rejected the subscription.');

        subscribed.value = true;
    } catch (e) {
        error.value = e.message || 'Failed to enable notifications.';
    } finally {
        busy.value = false;
    }
}

async function disable() {
    error.value = '';
    busy.value = true;
    try {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        if (sub) {
            await fetch(route('push.destroy'), {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(
                        document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
                    ),
                },
                body: JSON.stringify({ endpoint: sub.endpoint }),
            });
            await sub.unsubscribe();
        }
        subscribed.value = false;
    } catch (e) {
        error.value = e.message || 'Failed to disable notifications.';
    } finally {
        busy.value = false;
    }
}

const iosNeedsInstall = computed(() => isIosSafari.value && !isStandalone.value);
</script>

<template>
    <div class="space-y-3">
        <p class="text-sm text-[var(--slate-soft)]">
            Get a push notification when teammates send a chat message in one of your projects.
        </p>

        <div v-if="!supported" class="rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">
            Your browser doesn't support web push notifications.
        </div>

        <div v-else-if="iosNeedsInstall" class="rounded-md bg-blue-50 px-3 py-2 text-xs text-blue-800">
            On iPhone, add Pärlikee to your home screen first (Share → Add to Home Screen),
            then open it from there to enable notifications.
        </div>

        <div v-else-if="permission === 'denied'" class="rounded-md bg-rose-50 px-3 py-2 text-xs text-rose-700">
            Notifications are blocked at the browser level. Enable them in your browser settings, then return here.
        </div>

        <div v-else class="flex flex-wrap items-center gap-2">
            <button
                v-if="!subscribed"
                type="button"
                class="app-button-primary"
                :disabled="busy"
                @click="enable"
            >
                {{ busy ? 'Working…' : 'Enable notifications' }}
            </button>
            <button
                v-else
                type="button"
                class="app-button-secondary"
                :disabled="busy"
                @click="disable"
            >
                {{ busy ? 'Working…' : 'Disable notifications' }}
            </button>
            <span v-if="subscribed" class="text-xs text-emerald-700">Notifications are on for this device.</span>
        </div>

        <p v-if="error" class="text-xs text-rose-600">{{ error }}</p>
    </div>
</template>

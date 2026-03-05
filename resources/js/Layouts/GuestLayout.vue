<script setup>
import { Link } from '@inertiajs/vue3';

const AUTH_BACKGROUND_IMAGE = '/images/auth/bg.webp';
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
        <div class="absolute inset-0 bg-slate-950"></div>
        <div
            class="absolute inset-0 bg-cover bg-center"
            :style="{ backgroundImage: `url('${AUTH_BACKGROUND_IMAGE}')` }"
        ></div>
        <div class="absolute inset-0 bg-slate-950/55"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_22%_20%,rgba(251,191,36,0.15),transparent_35%),linear-gradient(180deg,rgba(15,23,42,0.28),rgba(15,23,42,0.56))]"></div>

        <div class="absolute left-4 top-4 z-10 sm:left-6 sm:top-6">
            <Link
                href="/"
                class="inline-flex items-center gap-2 rounded-full border border-white/35 bg-white/15 px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-white backdrop-blur-sm transition hover:bg-white/24"
            >
                ← Back to website
            </Link>
        </div>

        <div class="relative w-full sm:max-w-md">
            <div
                class="w-full overflow-hidden border border-slate-200 bg-white px-6 py-5 shadow-[0_26px_58px_rgba(15,23,42,0.35)] sm:rounded-xl"
            >
                <slot />
            </div>
        </div>
    </div>
</template>

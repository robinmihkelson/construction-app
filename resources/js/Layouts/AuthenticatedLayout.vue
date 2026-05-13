<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import FlashMessages from '@/Components/FlashMessages.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useT } from '@/i18n/useT';

const { t } = useT();

const showingMobileMenu = ref(false);
const showUserMenu = ref(false);
const userMenuRef = ref(null);

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);
const locale = computed(() => page.props.locale ?? 'et');

const locales = [
    { code: 'et', label: 'Eesti', flag: '/images/flags/et.webp' },
    { code: 'en', label: 'English', flag: '/images/flags/en.webp' },
    { code: 'fi', label: 'Suomi', flag: '/images/flags/fi.webp' },
];

function setLocale(code) {
    if (code === locale.value) {
        showUserMenu.value = false;
        return;
    }
    showUserMenu.value = false;
    window.location.assign(
        route('locale.set', { locale: code, redirect: page.url ?? '/' })
    );
}

const authUserName = computed(() => {
    const name = authUser.value?.name?.trim();
    if (name) return name;
    const email = authUser.value?.email?.trim();
    if (email) return email.split('@')[0];
    return 'User';
});

const userInitials = computed(() => {
    const name = authUserName.value;
    const parts = name.split(' ').filter(Boolean);
    if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    return name.slice(0, 2).toUpperCase();
});

function closeMobile() { showingMobileMenu.value = false; }

function handleClickOutside(e) {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        showUserMenu.value = false;
    }
}
onMounted(() => document.addEventListener('click', handleClickOutside, true))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside, true))

onMounted(() => {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
});
</script>

<template>
    <Head>
        <link rel="manifest" href="/manifest.webmanifest" />
        <meta name="theme-color" content="#2563eb" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="Pärlikee" />
        <link rel="apple-touch-icon" href="/icons/icon-192.png" />
    </Head>

    <div class="min-h-screen bg-[var(--site-bg)]">

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showingMobileMenu"
                class="fixed inset-0 z-20 bg-black/40 backdrop-blur-[2px] lg:hidden"
                @click="closeMobile"
            />
        </Transition>

        <aside
            class="fixed inset-y-0 left-0 z-30 flex w-60 flex-col border-r border-[var(--line)] bg-[var(--panel-bg)] transition-transform duration-200 ease-out lg:translate-x-0"
            :class="showingMobileMenu ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
        >
            <div class="flex h-14 shrink-0 items-center border-b border-[var(--line)] px-4">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center rounded-lg px-2 py-1.5 transition hover:bg-[var(--panel-strong)]"
                    @click="closeMobile"
                >
                    <div>
                        <div class="text-base font-extrabold tracking-wider text-[var(--ink)]">PÄRLIKEE</div>
                        <div class="text-[0.58rem] font-semibold uppercase tracking-[0.14em] text-[var(--slate-soft)]">{{ t('nav.workspace_subtitle') }}</div>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-3">

                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                    :class="route().current('dashboard')
                        ? 'bg-[var(--accent-soft)] font-semibold text-[var(--accent)]'
                        : 'font-medium text-[var(--slate-soft)] hover:bg-[var(--panel-muted)] hover:text-[var(--ink)]'"
                    @click="closeMobile"
                >
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    {{ t('nav.activity') }}
                    <span v-if="route().current('dashboard')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                </Link>

                <Link
                    :href="route('projects.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                    :class="route().current('projects.*')
                        ? 'bg-[var(--accent-soft)] font-semibold text-[var(--accent)]'
                        : 'font-medium text-[var(--slate-soft)] hover:bg-[var(--panel-muted)] hover:text-[var(--ink)]'"
                    @click="closeMobile"
                >
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                    </svg>
                    {{ t('nav.projects') }}
                    <span v-if="route().current('projects.*')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                </Link>

                <Link
                    :href="route('my-tasks.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                    :class="route().current('my-tasks.*')
                        ? 'bg-[var(--accent-soft)] font-semibold text-[var(--accent)]'
                        : 'font-medium text-[var(--slate-soft)] hover:bg-[var(--panel-muted)] hover:text-[var(--ink)]'"
                    @click="closeMobile"
                >
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    {{ t('nav.my_tasks') }}
                    <span v-if="route().current('my-tasks.*')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                </Link>

                <Link
                    :href="route('calendar.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                    :class="route().current('calendar.*')
                        ? 'bg-[var(--accent-soft)] font-semibold text-[var(--accent)]'
                        : 'font-medium text-[var(--slate-soft)] hover:bg-[var(--panel-muted)] hover:text-[var(--ink)]'"
                    @click="closeMobile"
                >
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ t('nav.calendar') }}
                    <span v-if="route().current('calendar.*')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                </Link>

                <Link
                    :href="route('chat.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                    :class="route().current('chat*')
                        ? 'bg-[var(--accent-soft)] font-semibold text-[var(--accent)]'
                        : 'font-medium text-[var(--slate-soft)] hover:bg-[var(--panel-muted)] hover:text-[var(--ink)]'"
                    @click="closeMobile"
                >
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    {{ t('nav.chat') }}
                    <span v-if="route().current('chat*')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                </Link>

                <Link
                    :href="route('inquiries.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                    :class="route().current('inquiries.*')
                        ? 'bg-[var(--accent-soft)] font-semibold text-[var(--accent)]'
                        : 'font-medium text-[var(--slate-soft)] hover:bg-[var(--panel-muted)] hover:text-[var(--ink)]'"
                    @click="closeMobile"
                >
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    {{ t('nav.inquiries') }}
                    <span v-if="route().current('inquiries.*')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                </Link>

            </nav>
        </aside>

        <div class="flex min-h-screen flex-col lg:pl-60">

            <header class="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-3 border-b border-[var(--line)] bg-[var(--panel-bg)] px-4">

                <button
                    @click="showingMobileMenu = !showingMobileMenu"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)] lg:hidden"
                    aria-label="Toggle navigation"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>

                <Link :href="route('dashboard')" class="flex items-center lg:hidden">
                    <span class="text-base font-extrabold tracking-wide text-[var(--ink)]">PÄRLIKEE</span>
                </Link>

                <GlobalSearch />

                <div class="ml-auto flex items-center gap-1">

                    <button
                        class="relative flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                        aria-label="Notifications"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    <ThemeToggle />

                    <Link
                        href="/"
                        :aria-label="t('nav.back_to_website')"
                        :title="t('nav.back_to_website')"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)] md:hidden"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </Link>

                    <Link
                        href="/"
                        class="hidden items-center gap-1.5 rounded-lg border border-[var(--line)] bg-[var(--panel-bg)] px-3 py-1.5 text-xs font-medium text-[var(--slate)] transition hover:bg-[var(--panel-muted)] md:inline-flex"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        {{ t('nav.website') }}
                    </Link>

                    <div class="relative ml-1" ref="userMenuRef">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="rounded-full transition hover:ring-2 hover:ring-[var(--accent)]/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                            :aria-expanded="showUserMenu"
                            :aria-label="t('nav.notifications')"
                        >
                            <UserAvatar
                                :url="authUser?.avatar_url ?? null"
                                :name="authUserName"
                                :email="authUser?.email"
                                :seed="authUser?.id"
                                :size="32"
                                accent
                            />
                        </button>

                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="opacity-0 scale-95 -translate-y-1"
                            enter-to-class="opacity-100 scale-100 translate-y-0"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="opacity-100 scale-100 translate-y-0"
                            leave-to-class="opacity-0 scale-95 -translate-y-1"
                        >
                            <div
                                v-if="showUserMenu"
                                class="absolute right-0 top-full z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-xl border border-[var(--line)] bg-[var(--panel-bg)] shadow-[var(--shadow-md)]"
                            >

                                <div class="border-b border-[var(--line)] px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <UserAvatar
                                            :url="authUser?.avatar_url ?? null"
                                            :name="authUserName"
                                            :email="authUser?.email"
                                            :seed="authUser?.id"
                                            :size="32"
                                            accent
                                        />
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-[var(--ink)]">{{ authUserName }}</div>
                                            <div class="truncate text-xs text-[var(--slate-soft)]">{{ authUser?.email ?? '' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-b border-[var(--line)] px-3 py-2.5">
                                    <div class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-[var(--slate-soft)]">{{ t('common.language') }}</div>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            v-for="item in locales"
                                            :key="item.code"
                                            type="button"
                                            :aria-label="item.label"
                                            :aria-pressed="locale === item.code"
                                            :title="item.label"
                                            class="inline-flex h-8 w-10 items-center justify-center overflow-hidden rounded-full border bg-white p-0.5 transition"
                                            :class="locale === item.code
                                                ? 'border-[var(--accent)] ring-2 ring-[var(--accent)]/30'
                                                : 'border-[var(--line)] hover:border-[var(--line-strong)]'"
                                            @click="setLocale(item.code)"
                                        >
                                            <img :src="item.flag" :alt="item.label" class="h-full w-full rounded-full object-cover" />
                                        </button>
                                    </div>
                                </div>

                                <div class="p-1.5 space-y-0.5">
                                    <Link
                                        :href="route('profile.edit')"
                                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-[var(--slate)] transition hover:bg-[var(--panel-strong)] hover:text-[var(--ink)]"
                                        :class="route().current('profile.edit') ? 'bg-[var(--accent-soft)] text-[var(--accent)]' : ''"
                                        @click="showUserMenu = false"
                                    >
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ t('nav.profile_settings') }}
                                    </Link>

                                    <div class="border-t border-[var(--line)] pt-1.5 mt-1.5">
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                                            @click="showUserMenu = false"
                                        >
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            {{ t('nav.log_out') }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>

                </div>
            </header>

            <div v-if="$slots.header" class="shrink-0 border-b border-[var(--line)] bg-[var(--panel-bg)] px-4 py-4 sm:px-6">
                <slot name="header" />
            </div>

            <main class="flex-1">
                <div class="px-4 py-6 sm:px-6">
                    <FlashMessages />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

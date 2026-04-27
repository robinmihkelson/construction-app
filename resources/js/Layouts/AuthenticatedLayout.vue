<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import FlashMessages from '@/Components/FlashMessages.vue'
import { Link, usePage } from '@inertiajs/vue3';

const showingMobileMenu = ref(false);
const showUserMenu = ref(false);
const userMenuRef = ref(null);

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);

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
</script>

<template>
    <div class="min-h-screen bg-[var(--site-bg)]">

        <!-- Mobile sidebar backdrop -->
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

        <!-- ── Sidebar ──────────────────────────────────────── -->
        <aside
            class="fixed inset-y-0 left-0 z-30 flex w-60 flex-col border-r border-[var(--line)] bg-white transition-transform duration-200 ease-out lg:translate-x-0"
            :class="showingMobileMenu ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex h-14 shrink-0 items-center border-b border-[var(--line)] px-4">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 transition hover:bg-[var(--panel-strong)]"
                    @click="closeMobile"
                >
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[var(--accent)] text-[0.65rem] font-black text-white shadow-sm">
                        P
                    </div>
                    <div>
                        <div class="text-[0.8rem] font-extrabold tracking-wider text-[var(--ink)]">PÄRLIKEE</div>
                        <div class="text-[0.58rem] font-semibold uppercase tracking-[0.14em] text-[var(--slate-soft)]">Workspace</div>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
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
                    Activity
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
                    Projects
                    <span v-if="route().current('projects.*')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
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
                    Chat
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
                    Inquiries
                    <span v-if="route().current('inquiries.*')" class="ml-auto h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                </Link>

            </nav>
            <!-- No footer — profile/logout live in the top header avatar dropdown -->
        </aside>

        <!-- ── Main area ────────────────────────────────────── -->
        <div class="flex min-h-screen flex-col lg:pl-60">

            <!-- Top header bar -->
            <header class="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-3 border-b border-[var(--line)] bg-white px-4">

                <!-- Hamburger (mobile) -->
                <button
                    @click="showingMobileMenu = !showingMobileMenu"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)] lg:hidden"
                    aria-label="Toggle navigation"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>

                <!-- Mobile brand -->
                <Link :href="route('dashboard')" class="flex items-center gap-2 lg:hidden">
                    <div class="flex h-6 w-6 items-center justify-center rounded-md bg-[var(--accent)] text-[0.6rem] font-black text-white">P</div>
                    <span class="text-sm font-extrabold tracking-wide text-[var(--ink)]">PÄRLIKEE</span>
                </Link>

                <!-- Global search -->
                <div class="relative hidden flex-1 sm:block" style="max-width: 22rem;">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                    <input
                        type="search"
                        placeholder="Search…"
                        class="w-full rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] py-2 pl-9 pr-3 text-sm text-[var(--ink)] placeholder:text-[var(--slate-soft)] transition focus:border-[var(--accent)] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/12"
                    />
                </div>

                <!-- Right side -->
                <div class="ml-auto flex items-center gap-1">

                    <!-- Notifications bell -->
                    <button
                        class="relative flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                        aria-label="Notifications"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    <!-- Back to website -->
                    <Link
                        href="/"
                        class="hidden items-center gap-1.5 rounded-lg border border-[var(--line)] bg-white px-3 py-1.5 text-xs font-medium text-[var(--slate)] transition hover:bg-[var(--panel-muted)] md:inline-flex"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Website
                    </Link>

                    <!-- ── User avatar + dropdown ──────────────── -->
                    <div class="relative ml-1" ref="userMenuRef">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--accent-soft)] text-xs font-bold text-[var(--accent)] transition hover:ring-2 hover:ring-[var(--accent)]/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                            :aria-expanded="showUserMenu"
                            aria-label="User menu"
                        >
                            {{ userInitials }}
                        </button>

                        <!-- Dropdown panel -->
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
                                class="absolute right-0 top-full z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-xl border border-[var(--line)] bg-white shadow-[var(--shadow-md)]"
                            >
                                <!-- User info -->
                                <div class="border-b border-[var(--line)] px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--accent-soft)] text-xs font-bold text-[var(--accent)]">
                                            {{ userInitials }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-[var(--ink)]">{{ authUserName }}</div>
                                            <div class="truncate text-xs text-[var(--slate-soft)]">{{ authUser?.email ?? '' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Menu items -->
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
                                        Profile settings
                                    </Link>

                                    <div class="border-t border-[var(--line)] pt-1.5 mt-1.5">
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700"
                                            @click="showUserMenu = false"
                                        >
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Log out
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>

                </div>
            </header>

            <!-- Optional page sub-header -->
            <div v-if="$slots.header" class="shrink-0 border-b border-[var(--line)] bg-white px-4 py-4 sm:px-6">
                <slot name="header" />
            </div>

            <!-- Page content -->
            <main class="flex-1">
                <div class="px-4 py-6 sm:px-6">
                    <FlashMessages />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

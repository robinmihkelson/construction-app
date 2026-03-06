<script setup>
import { computed, ref } from 'vue';
import FlashMessages from '@/Components/FlashMessages.vue'
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const authUser = computed(() => page.props.auth?.user ?? null);
const authUserName = computed(() => {
    const name = authUser.value?.name?.trim();

    if (name) return name;

    const email = authUser.value?.email?.trim();
    if (email) return email.split('@')[0];

    return 'User';
});
</script>

<template>
    <div class="min-h-screen">
        <div class="mx-auto max-w-[1600px] px-4 py-4 sm:px-6 sm:py-6">
            <div class="flex flex-col gap-6 lg:flex-row">
                <aside class="hidden lg:block lg:w-72 lg:shrink-0">
                    <div
                        class="sticky top-6 flex h-[calc(100vh-3rem)] flex-col rounded-3xl border border-[color:var(--line)] bg-white/90 p-4 shadow-[0_18px_40px_rgba(15,23,42,0.06)] backdrop-blur"
                    >
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-3 rounded-2xl px-2 py-2"
                        >
                            <div>
                                <div class="text-2xl font-bold text-[color:var(--ink)]">
                                    PÄRLIKEE
                                </div>
                            </div>
                        </Link>

                        <nav class="mt-6 space-y-1">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Activity
                            </NavLink>
                            <NavLink :href="route('projects.index')" :active="route().current('projects.*')">
                                Projects
                            </NavLink>
                            <NavLink :href="route('chat.index')" :active="route().current('chat*')">
                                Chat
                            </NavLink>
                            <NavLink :href="route('inquiries.index')" :active="route().current('inquiries.*')">
                                Inquiries
                            </NavLink>
                        </nav>

                        <div class="mt-auto space-y-3">
                            <div class="rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-strong)] p-4">
                                <div class="text-xs font-medium text-[color:var(--slate-soft)]">
                                    Signed in as
                                </div>
                                <div class="mt-1 text-sm font-semibold text-[color:var(--ink)]">
                                    {{ authUserName }}
                                </div>
                                <div class="text-xs text-[color:var(--slate-soft)]">
                                    {{ authUser?.email ?? '' }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <Link
                                    :href="route('profile.edit')"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-sm font-medium text-[color:var(--slate)] transition hover:bg-[color:var(--panel-muted)]"
                                >
                                    Profile
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="inline-flex items-center justify-center rounded-2xl bg-[color:var(--ink)] px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800"
                                >
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="min-w-0 flex-1">
                    <div
                        class="rounded-3xl border border-[color:var(--line)] bg-white/90 p-4 shadow-[0_18px_40px_rgba(15,23,42,0.05)] lg:hidden"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <Link :href="route('dashboard')" class="flex min-w-0 items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[color:var(--accent-soft)]">
                                    <ApplicationLogo class="h-6 w-6 text-[color:var(--accent)]" />
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-[color:var(--ink)]">
                                        Construction App
                                    </div>
                                    <div class="truncate text-xs text-[color:var(--slate-soft)]">
                                        {{ authUserName }}
                                    </div>
                                </div>
                            </Link>

                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-strong)] px-3 py-3 text-[color:var(--slate)] transition hover:bg-white focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div v-if="showingNavigationDropdown" class="mt-4 space-y-2 border-t border-[color:var(--line)] pt-4">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Activity
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('projects.index')" :active="route().current('projects.*')">
                                Projects
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('chat.index')" :active="route().current('chat*')">
                                Chat
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('inquiries.index')" :active="route().current('inquiries.*')">
                                Inquiries
                            </ResponsiveNavLink>

                            <div class="grid grid-cols-2 gap-2 pt-2">
                                <Link
                                    :href="route('profile.edit')"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-sm font-medium text-[color:var(--slate)]"
                                >
                                    Profile
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="inline-flex items-center justify-center rounded-2xl bg-[color:var(--ink)] px-4 py-3 text-sm font-medium text-white"
                                >
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mb-6 mt-6 ml-auto flex w-fit items-center rounded-3xl border border-[color:var(--line)] bg-white/85 px-3 py-3 shadow-[0_18px_40px_rgba(15,23,42,0.04)] lg:mt-0"
                    >
                        <Link
                            href="/"
                            class="inline-flex items-center rounded-2xl border border-[color:var(--line)] bg-white px-4 py-2.5 text-sm font-medium text-[color:var(--slate)] transition hover:bg-[color:var(--panel-muted)]"
                        >
                            Visit Website
                        </Link>
                    </div>

                    <header
                        v-if="$slots.header"
                        class="mb-6 rounded-3xl border border-[color:var(--line)] bg-white/90 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.04)]"
                    >
                        <slot name="header" />
                    </header>

                    <FlashMessages />

                    <main class="pb-10">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>

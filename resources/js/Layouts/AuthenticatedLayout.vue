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
        <div class="mx-auto max-w-[1480px] px-4 py-4 sm:px-6">
            <div class="flex flex-col gap-5 lg:flex-row">
                <aside class="hidden lg:block lg:w-64 lg:shrink-0">
                    <div
                        class="sticky top-4 flex h-[calc(100vh-2rem)] flex-col rounded-xl border border-[color:var(--line)] bg-white p-4 shadow-sm"
                    >
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-3 rounded-lg px-2 py-2"
                        >
                            <div>
                                <div class="text-xl font-bold text-[color:var(--ink)]">
                                    PÄRLIKEE
                                </div>
                                <div class="text-xs font-medium text-[color:var(--slate-soft)]">
                                    Workspace
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
                            <div class="rounded-lg border border-[color:var(--line)] bg-[color:var(--panel-strong)] p-3">
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
                                    class="app-button-secondary"
                                >
                                    Profile
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="inline-flex items-center justify-center rounded-lg bg-[color:var(--ink)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                                >
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="min-w-0 flex-1">
                    <div
                        class="rounded-xl border border-[color:var(--line)] bg-white p-4 shadow-sm lg:hidden"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <Link :href="route('dashboard')" class="flex min-w-0 flex-1 items-center">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-[color:var(--ink)]">
                                        PÄRLIKEE
                                    </div>
                                    <div class="truncate text-xs text-[color:var(--slate-soft)]">
                                        {{ authUserName }}
                                    </div>
                                </div>
                            </Link>

                            <div class="flex shrink-0 items-center gap-2">
                                <Link
                                    href="/"
                                    class="inline-flex h-[50px] items-center whitespace-nowrap rounded-lg border border-[color:var(--line)] bg-white px-3 text-xs font-semibold text-[color:var(--slate)] transition hover:bg-[color:var(--panel-muted)]"
                                >
                                    Back to website
                                </Link>

                                <button
                                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                                    class="inline-flex items-center justify-center rounded-lg border border-[color:var(--line)] bg-[color:var(--panel-strong)] px-3 py-3 text-[color:var(--slate)] transition hover:bg-white focus:outline-none"
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
                                    class="app-button-secondary"
                                >
                                    Profile
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="inline-flex items-center justify-center rounded-lg bg-[color:var(--ink)] px-4 py-2.5 text-sm font-semibold text-white"
                                >
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 lg:mt-0">
                        <div class="mb-5 hidden justify-end lg:flex">
                            <Link
                                href="/"
                                class="app-button-secondary"
                            >
                                Back to website
                            </Link>
                        </div>

                        <header
                            v-if="$slots.header"
                            class="mb-5 border-b border-[color:var(--line)] pb-5"
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
    </div>
</template>

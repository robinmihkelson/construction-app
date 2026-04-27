<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const authUser = computed(() => usePage().props.auth?.user ?? null);

const userInitials = computed(() => {
    const name = authUser.value?.name?.trim() ?? authUser.value?.email?.split('@')[0] ?? 'U';
    const parts = name.split(' ').filter(Boolean);
    if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    return name.slice(0, 2).toUpperCase();
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Page title -->
            <div>
                <div class="app-label">Account</div>
                <h1 class="mt-1 text-2xl font-bold text-[var(--ink)]">Profile settings</h1>
                <p class="mt-1 text-sm text-[var(--slate-soft)]">Manage your name, email, password, and account.</p>
            </div>

            <!-- Avatar / identity hero -->
            <div class="app-panel flex items-center gap-5 p-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--accent-soft)] text-xl font-bold text-[var(--accent)]">
                    {{ userInitials }}
                </div>
                <div class="min-w-0">
                    <div class="text-base font-bold text-[var(--ink)]">{{ authUser?.name ?? authUser?.email }}</div>
                    <div class="mt-0.5 text-sm text-[var(--slate-soft)]">{{ authUser?.email }}</div>
                </div>
                <Link :href="route('dashboard')" class="app-button-secondary ml-auto shrink-0 gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Dashboard
                </Link>
            </div>

            <!-- Profile information -->
            <div class="app-panel overflow-hidden">
                <div class="border-b border-[var(--line)] px-6 py-4">
                    <h2 class="text-sm font-bold text-[var(--ink)]">Profile information</h2>
                    <p class="mt-0.5 text-xs text-[var(--slate-soft)]">Update your display name and email address.</p>
                </div>
                <div class="px-6 py-5">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                </div>
            </div>

            <!-- Password -->
            <div class="app-panel overflow-hidden">
                <div class="border-b border-[var(--line)] px-6 py-4">
                    <h2 class="text-sm font-bold text-[var(--ink)]">Password</h2>
                    <p class="mt-0.5 text-xs text-[var(--slate-soft)]">Use a long, random password to keep your account secure.</p>
                </div>
                <div class="px-6 py-5">
                    <UpdatePasswordForm />
                </div>
            </div>

            <!-- Danger zone -->
            <div class="overflow-hidden rounded-xl border border-rose-200 bg-white">
                <div class="border-b border-rose-100 bg-rose-50/60 px-6 py-4">
                    <h2 class="text-sm font-bold text-rose-800">Danger zone</h2>
                    <p class="mt-0.5 text-xs text-rose-600">Irreversible actions — please proceed carefully.</p>
                </div>
                <div class="px-6 py-5">
                    <DeleteUserForm />
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

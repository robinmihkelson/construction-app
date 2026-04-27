<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue'

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = computed(() => usePage().props.auth?.user ?? null)

const form = useForm({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
});
</script>

<template>
    <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-5 max-w-lg">

        <div>
            <label for="name" class="app-label mb-1.5 block">Name</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                class="app-input"
                required
                autofocus
                autocomplete="name"
            />
            <InputError class="mt-1.5" :message="form.errors.name" />
        </div>

        <div>
            <label for="email" class="app-label mb-1.5 block">Email</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                class="app-input"
                required
                autocomplete="username"
            />
            <InputError class="mt-1.5" :message="form.errors.email" />
        </div>

        <div v-if="mustVerifyEmail && user?.email_verified_at === null" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm">
            <p class="text-amber-800">
                Your email address is unverified.
                <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    class="ml-1 font-semibold underline underline-offset-2 hover:text-amber-900"
                >
                    Resend verification email
                </Link>
            </p>
            <div v-show="status === 'verification-link-sent'" class="mt-2 font-medium text-emerald-700">
                A new verification link has been sent to your email.
            </div>
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" :disabled="form.processing" class="app-button-primary disabled:opacity-60">
                Save changes
            </button>
            <Transition
                enter-active-class="transition ease-in-out duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out duration-200"
                leave-to-class="opacity-0"
            >
                <span v-if="form.recentlySuccessful" class="flex items-center gap-1.5 text-sm font-medium text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Saved
                </span>
            </Transition>
        </div>
    </form>
</template>

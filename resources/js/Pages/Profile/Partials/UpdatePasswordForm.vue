<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <form @submit.prevent="updatePassword" class="space-y-5 max-w-lg">

        <div>
            <label for="current_password" class="app-label mb-1.5 block">Current password</label>
            <input
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                type="password"
                class="app-input"
                autocomplete="current-password"
            />
            <InputError :message="form.errors.current_password" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="app-label mb-1.5 block">New password</label>
            <input
                id="password"
                ref="passwordInput"
                v-model="form.password"
                type="password"
                class="app-input"
                autocomplete="new-password"
            />
            <InputError :message="form.errors.password" class="mt-1.5" />
        </div>

        <div>
            <label for="password_confirmation" class="app-label mb-1.5 block">Confirm new password</label>
            <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="app-input"
                autocomplete="new-password"
            />
            <InputError :message="form.errors.password_confirmation" class="mt-1.5" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" :disabled="form.processing" class="app-button-primary disabled:opacity-60">
                Update password
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

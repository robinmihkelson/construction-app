<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { useT } from '@/i18n/useT';

const { t } = useT();

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({ password: '' });

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div>
        <p class="mb-4 text-sm text-[var(--slate-soft)]">
            {{ t('delete_user.desc') }}
        </p>

        <button @click="confirmUserDeletion" class="app-button-danger">
            {{ t('delete_user.button') }}
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <div class="mb-5 flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-950/50">
                        <svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[var(--ink)]">{{ t('delete_user.confirm_title') }}</h2>
                        <p class="mt-1 text-sm text-[var(--slate-soft)]">
                            {{ t('delete_user.confirm_desc') }}
                        </p>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="delete-password" class="app-label mb-1.5 block">{{ t('profile.password') }}</label>
                    <input
                        id="delete-password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="app-input"
                        :placeholder="t('delete_user.password_placeholder')"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-1.5" />
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeModal" class="app-button-secondary">
                        {{ t('common.cancel') }}
                    </button>
                    <button
                        type="button"
                        @click="deleteUser"
                        :disabled="form.processing"
                        class="app-button-danger disabled:opacity-60"
                    >
                        {{ t('delete_user.confirm_button') }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

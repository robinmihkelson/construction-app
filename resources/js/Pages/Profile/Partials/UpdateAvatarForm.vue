<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';
import { useT } from '@/i18n/useT';

const { t } = useT();

const authUser = computed(() => usePage().props.auth?.user ?? null);

const form = useForm({ avatar: null });
const fileInput = ref(null);
const previewUrl = ref(null);
const error = ref('');

function pickFile() {
    fileInput.value?.click();
}

function onFileChange(event) {
    const file = event.target.files?.[0] ?? null;
    error.value = '';

    if (!file) {
        form.avatar = null;
        previewUrl.value = null;
        return;
    }

    if (!file.type.startsWith('image/')) {
        error.value = t('avatar.err_choose');
        event.target.value = '';
        return;
    }

    if (file.size > 4 * 1024 * 1024) {
        error.value = t('avatar.err_size');
        event.target.value = '';
        return;
    }

    form.avatar = file;
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = URL.createObjectURL(file);
}

function reset() {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
    form.reset();
    if (fileInput.value) fileInput.value.value = '';
    error.value = '';
}

function submit() {
    if (!form.avatar) return;
    form.post(route('profile.avatar.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => reset(),
        onError: (errors) => { error.value = errors.avatar || t('avatar.err_upload'); },
    });
}

function remove() {
    if (!authUser.value?.avatar_url) return;
    if (!window.confirm(t('avatar.confirm_remove'))) return;
    router.delete(route('profile.avatar.destroy'), { preserveScroll: true });
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-5">
            <UserAvatar
                :url="previewUrl ?? authUser?.avatar_url ?? null"
                :name="authUser?.name"
                :email="authUser?.email"
                :seed="authUser?.id"
                :size="80"
                accent
            />

            <div class="flex flex-wrap gap-2">
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="onFileChange"
                />
                <button
                    v-if="!form.avatar"
                    type="button"
                    @click="pickFile"
                    class="app-button-secondary gap-1.5"
                >
                    {{ authUser?.avatar_url ? t('avatar.change') : t('avatar.upload') }}
                </button>

                <template v-else>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="app-button-primary gap-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ form.processing ? t('avatar.uploading') : t('avatar.save') }}
                    </button>
                    <button
                        type="button"
                        @click="reset"
                        :disabled="form.processing"
                        class="app-button-secondary gap-1.5"
                    >
                        {{ t('common.cancel') }}
                    </button>
                </template>

                <button
                    v-if="authUser?.avatar_url && !form.avatar"
                    type="button"
                    @click="remove"
                    class="app-button-danger gap-1.5"
                >
                    {{ t('common.remove') }}
                </button>
            </div>
        </div>

        <p class="text-xs text-[var(--slate-soft)]">{{ t('avatar.help') }}</p>

        <p v-if="error" class="text-xs font-medium text-rose-600 dark:text-rose-300">{{ error }}</p>
    </div>
</template>

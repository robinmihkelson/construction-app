<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { useT } from '@/i18n/useT'

const { t } = useT()

defineProps({ projects: Array })

const form = useForm({ name: '' })

function statusBadge(status) {
    if (status === 'active')    return 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/30'
    if (status === 'completed') return 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/30'
    if (status === 'on_hold')   return 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/30'
    return 'bg-[var(--panel-muted)] text-[var(--slate)] border border-[var(--line)]'
}
</script>

<template>
    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="app-label">{{ t('projects.label') }}</div>
                <h1 class="mt-1 text-2xl font-bold text-[var(--ink)]">{{ t('projects.title') }}</h1>
                <p class="mt-1 text-sm text-[var(--slate-soft)]">{{ t('projects.subtitle') }}</p>
            </div>
            <div class="shrink-0 rounded-xl border border-[var(--line)] bg-[var(--panel-bg)] px-6 py-3 text-center shadow-sm">
                <div class="app-label">{{ t('common.total') }}</div>
                <div class="mt-0.5 text-3xl font-bold text-[var(--ink)]">{{ projects?.length ?? 0 }}</div>
            </div>
        </div>

        <div class="app-panel p-5">
            <h2 class="mb-3 text-sm font-bold text-[var(--ink)]">{{ t('projects.start_new') }}</h2>
            <form @submit.prevent="form.post(route('projects.store'))" class="flex gap-3">
                <input
                    v-model="form.name"
                    :placeholder="t('projects.name_placeholder')"
                    class="app-input flex-1"
                />
                <button class="app-button-primary shrink-0 gap-1.5 px-5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('projects.add') }}
                </button>
            </form>
            <div v-if="form.errors.name" class="mt-2 text-xs text-rose-600">{{ form.errors.name }}</div>
        </div>

        <div v-if="!projects || projects.length === 0" class="app-panel flex flex-col items-center justify-center gap-4 py-16 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--panel-muted)]">
                <svg class="h-7 w-7 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
            </div>
            <div>
                <div class="font-semibold text-[var(--ink)]">{{ t('projects.empty_title') }}</div>
                <div class="mt-1 text-sm text-[var(--slate-soft)]">{{ t('projects.empty_desc') }}</div>
            </div>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="route('projects.show', project.id)"
                class="group app-panel flex flex-col p-5 transition hover:border-[var(--line-strong)] hover:shadow-md"
            >
                <!-- Header row -->
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-[0.64rem] font-bold uppercase tracking-wider text-[var(--accent)]">
                            {{ t('projects.label_single') }} #{{ project.id }}
                        </div>
                        <div class="mt-1 break-words text-[0.95rem] font-bold text-[var(--ink)] [overflow-wrap:anywhere]">
                            {{ project.name }}
                        </div>
                    </div>
                    <span
                        class="shrink-0 rounded-full px-2.5 py-1 text-[0.64rem] font-bold capitalize"
                        :class="statusBadge(project.status)"
                    >
                        {{ project.status }}
                    </span>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-[var(--line)] pt-3.5">
                    <span class="text-xs text-[var(--slate-soft)]">{{ t('projects.tasks_progress') }}</span>
                    <span class="flex items-center gap-1 text-xs font-semibold text-[var(--accent)]">
                        {{ t('common.open') }}
                        <svg class="h-3.5 w-3.5 transition-transform duration-150 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </Link>
        </div>

    </div>
</template>

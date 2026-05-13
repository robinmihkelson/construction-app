<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { useT } from '@/i18n/useT'

const { t } = useT()

defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
    recentCounts: {
        type: Object,
        default: () => ({ projects: 0, chat: 0, inquiries: 0 }),
    },
})

function moduleLabel(module) {
    if (module === 'projects') return t('dashboard.projects')
    if (module === 'chat') return t('nav.chat')
    if (module === 'inquiries') return t('dashboard.inquiries')
    return t('nav.activity')
}

function moduleBorderClass(module) {
    if (module === 'projects') return 'border-l-blue-400'
    if (module === 'chat') return 'border-l-emerald-400'
    if (module === 'inquiries') return 'border-l-amber-400'
    return 'border-l-slate-300'
}

function moduleLabelClass(module) {
    if (module === 'projects') return 'text-blue-600'
    if (module === 'chat') return 'text-emerald-600'
    if (module === 'inquiries') return 'text-amber-600'
    return 'text-slate-500'
}

function relativeTime(isoString) {
    if (!isoString) return 'just now'
    const diff = Math.max(0, Date.now() - new Date(isoString).getTime())
    const m = 60000, h = 3600000, d = 86400000
    if (diff < h) return `${Math.max(1, Math.floor(diff / m))}m ago`
    if (diff < d) return `${Math.floor(diff / h)}h ago`
    if (diff < 7 * d) return `${Math.floor(diff / d)}d ago`
    return new Date(isoString).toLocaleDateString()
}
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Page title -->
            <div>
                <div class="app-label">{{ t('dashboard.label') }}</div>
                <h1 class="mt-1 text-2xl font-bold text-[var(--ink)]">{{ t('dashboard.title') }}</h1>
                <p class="mt-1 text-sm text-[var(--slate-soft)]">{{ t('dashboard.subtitle') }}</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(240px,0.55fr)]">

                <!-- ── Activity feed ───────────────────────────── -->
                <div class="app-panel overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-[var(--line)] px-5 py-3.5">
                        <h2 class="text-sm font-bold text-[var(--ink)]">{{ t('dashboard.recent_activity') }}</h2>
                        <span class="rounded-full bg-[var(--panel-muted)] px-2.5 py-0.5 text-xs font-semibold text-[var(--slate-soft)]">
                            {{ activities.length }}
                        </span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="activities.length === 0" class="flex flex-col items-center justify-center gap-4 px-5 py-16 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--panel-muted)]">
                            <svg class="h-6 w-6 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-[var(--ink)]">{{ t('dashboard.no_activity_title') }}</div>
                            <div class="mt-1 text-sm text-[var(--slate-soft)]">{{ t('dashboard.no_activity_desc') }}</div>
                        </div>
                    </div>

                    <!-- Feed items -->
                    <div v-else class="divide-y divide-[var(--line)]">
                        <Link
                            v-for="activity in activities"
                            :key="activity.id"
                            :href="activity.href"
                            class="flex items-start gap-4 border-l-2 px-5 py-3.5 transition hover:bg-[var(--panel-strong)]"
                            :class="moduleBorderClass(activity.module)"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline gap-2">
                                    <span class="text-[0.64rem] font-bold uppercase tracking-wider" :class="moduleLabelClass(activity.module)">
                                        {{ moduleLabel(activity.module) }}
                                    </span>
                                    <span class="text-sm font-semibold text-[var(--ink)]">{{ activity.title }}</span>
                                </div>
                                <div class="mt-1 truncate text-sm text-[var(--slate-soft)]">{{ activity.description }}</div>
                            </div>
                            <div class="shrink-0 text-xs font-medium tabular-nums text-[var(--slate-soft)]">
                                {{ relativeTime(activity.time) }}
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- ── Right column ────────────────────────────── -->
                <div class="space-y-5">

                    <!-- Snapshot card -->
                    <div class="app-panel p-5">
                        <div class="app-label">{{ t('dashboard.last_24h') }}</div>
                        <h2 class="mt-1 text-base font-bold text-[var(--ink)]">{{ t('dashboard.snapshot') }}</h2>
                        <p class="mt-1.5 text-sm text-[var(--slate-soft)]">{{ t('dashboard.snapshot_desc') }}</p>

                        <div class="mt-4 space-y-2">
                            <!-- Projects -->
                            <div class="flex items-center gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 dark:border-blue-500/30 dark:bg-blue-500/10">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-blue-700 dark:text-blue-300">{{ t('dashboard.projects') }}</div>
                                </div>
                                <div class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ recentCounts.projects ?? 0 }}</div>
                            </div>

                            <!-- Chat -->
                            <div class="flex items-center gap-3 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-500/20">
                                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-emerald-700 dark:text-emerald-300">{{ t('dashboard.chat_messages') }}</div>
                                </div>
                                <div class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ recentCounts.chat ?? 0 }}</div>
                            </div>

                            <!-- Inquiries -->
                            <div class="flex items-center gap-3 rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/20">
                                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-medium text-amber-700 dark:text-amber-300">{{ t('dashboard.inquiries') }}</div>
                                </div>
                                <div class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ recentCounts.inquiries ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="app-panel overflow-hidden">
                        <div class="border-b border-[var(--line)] px-5 py-3">
                            <div class="app-label">{{ t('dashboard.quick_access') }}</div>
                        </div>
                        <div class="divide-y divide-[var(--line)]">
                            <Link :href="route('projects.index')" class="group flex items-center justify-between px-5 py-3.5 text-sm transition hover:bg-[var(--panel-strong)]">
                                <span class="font-medium text-[var(--ink)]">{{ t('dashboard.all_projects') }}</span>
                                <svg class="h-4 w-4 text-[var(--slate-soft)] transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                            <Link :href="route('chat.index')" class="group flex items-center justify-between px-5 py-3.5 text-sm transition hover:bg-[var(--panel-strong)]">
                                <span class="font-medium text-[var(--ink)]">{{ t('dashboard.chat_channels') }}</span>
                                <svg class="h-4 w-4 text-[var(--slate-soft)] transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                            <Link :href="route('inquiries.index')" class="group flex items-center justify-between px-5 py-3.5 text-sm transition hover:bg-[var(--panel-strong)]">
                                <span class="font-medium text-[var(--ink)]">{{ t('dashboard.client_inquiries') }}</span>
                                <svg class="h-4 w-4 text-[var(--slate-soft)] transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

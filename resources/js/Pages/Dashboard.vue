<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
    recentCounts: {
        type: Object,
        default: () => ({
            projects: 0,
            chat: 0,
            inquiries: 0,
        }),
    },
})

function moduleLabel(module) {
    if (module === 'projects') return 'Projects'
    if (module === 'chat') return 'Chat'
    if (module === 'inquiries') return 'Inquiries'

    return 'Activity'
}

function moduleBadgeClass(module) {
    if (module === 'projects') return 'bg-blue-50 text-blue-700'
    if (module === 'chat') return 'bg-emerald-50 text-emerald-700'
    if (module === 'inquiries') return 'bg-amber-50 text-amber-700'

    return 'bg-slate-100 text-slate-700'
}

function relativeTime(isoString) {
    if (!isoString) return 'just now'

    const then = new Date(isoString).getTime()
    const now = Date.now()
    const diff = Math.max(0, now - then)

    const minute = 60 * 1000
    const hour = 60 * minute
    const day = 24 * hour

    if (diff < hour) return `${Math.max(1, Math.floor(diff / minute))}m ago`
    if (diff < day) return `${Math.floor(diff / hour)}h ago`
    if (diff < 7 * day) return `${Math.floor(diff / day)}d ago`

    return new Date(isoString).toLocaleDateString()
}
</script>

<template>
    <Head title="Activity" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="app-label">Overview</div>
                    <h2 class="mt-1 text-2xl font-semibold leading-tight text-[color:var(--ink)]">
                        Activity
                    </h2>
                </div>
            </div>
        </template>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.65fr)]">
            <section class="app-panel overflow-hidden">
                <div class="flex items-end justify-between gap-3 border-b border-[color:var(--line)] px-4 py-3">
                    <div class="app-section-title">Recent activity</div>
                    <div class="text-xs font-semibold text-[color:var(--slate-soft)]">
                        {{ activities.length }} item{{ activities.length === 1 ? '' : 's' }}
                    </div>
                </div>

                <div v-if="activities.length === 0" class="px-4 py-10 text-center text-sm text-[color:var(--slate-soft)]">
                    No recent activity yet.
                </div>

                <div v-else class="divide-y divide-[color:var(--line)]">
                    <Link
                        v-for="activity in activities"
                        :key="activity.id"
                        :href="activity.href"
                        class="flex items-start justify-between gap-4 px-4 py-3 transition hover:bg-[color:var(--panel-strong)]"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="moduleBadgeClass(activity.module)"
                                >
                                    {{ moduleLabel(activity.module) }}
                                </span>
                                <span class="text-sm font-semibold text-[color:var(--ink)]">
                                    {{ activity.title }}
                                </span>
                            </div>

                            <div class="mt-2 truncate text-sm text-[color:var(--slate-soft)]">
                                {{ activity.description }}
                            </div>
                        </div>

                        <div class="shrink-0 text-xs font-medium text-[color:var(--slate-soft)]">
                            {{ relativeTime(activity.time) }}
                        </div>
                    </Link>
                </div>
            </section>

            <section class="app-panel p-4 lg:self-start">
                <div class="app-label">Last 24 hours</div>
                <div class="mt-1 text-xl font-semibold text-[color:var(--ink)]">Activity snapshot</div>
                <p class="mt-2 text-sm text-[color:var(--slate-soft)]">
                    Quick count of recent updates across the main modules.
                </p>

                <div class="mt-4 grid gap-3">
                    <div class="app-panel-muted px-3 py-3">
                        <div class="text-xs font-medium text-[color:var(--slate-soft)]">Projects</div>
                        <div class="mt-1 text-2xl font-semibold text-[color:var(--ink)]">{{ recentCounts.projects ?? 0 }}</div>
                    </div>
                    <div class="app-panel-muted px-3 py-3">
                        <div class="text-xs font-medium text-[color:var(--slate-soft)]">Chat messages</div>
                        <div class="mt-1 text-2xl font-semibold text-[color:var(--ink)]">{{ recentCounts.chat ?? 0 }}</div>
                    </div>
                    <div class="app-panel-muted px-3 py-3">
                        <div class="text-xs font-medium text-[color:var(--slate-soft)]">Inquiries</div>
                        <div class="mt-1 text-2xl font-semibold text-[color:var(--ink)]">{{ recentCounts.inquiries ?? 0 }}</div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

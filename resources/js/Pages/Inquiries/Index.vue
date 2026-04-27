<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    inquiries: Object,
    filters: Object,
    counts: Object,
})

const currentStatus = props.filters?.status ?? 'new'

const tabs = [
    { key: 'new',       label: 'New',       color: 'bg-blue-50 text-blue-700' },
    { key: 'contacted', label: 'Contacted', color: 'bg-amber-50 text-amber-700' },
    { key: 'converted', label: 'Converted', color: 'bg-emerald-50 text-emerald-700' },
]

function statusBadge(status) {
    if (status === 'new')       return 'bg-blue-50 text-blue-700 border border-blue-200'
    if (status === 'contacted') return 'bg-amber-50 text-amber-700 border border-amber-200'
    if (status === 'converted') return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    return 'bg-[var(--panel-muted)] text-[var(--slate)] border border-[var(--line)]'
}

const totalCount = () => (props.counts?.new ?? 0) + (props.counts?.contacted ?? 0) + (props.counts?.converted ?? 0)
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Page header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="app-label">Pipeline</div>
                    <h1 class="mt-1 text-2xl font-bold text-[var(--ink)]">Client inquiries</h1>
                    <p class="mt-1 text-sm text-[var(--slate-soft)]">Review new leads, mark them contacted, and convert them into projects.</p>
                </div>
                <div class="shrink-0 rounded-xl border border-[var(--line)] bg-white px-6 py-3 text-center shadow-sm">
                    <div class="app-label">Total</div>
                    <div class="mt-0.5 text-3xl font-bold text-[var(--ink)]">{{ totalCount() }}</div>
                </div>
            </div>

            <!-- Status tabs -->
            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :href="route('inquiries.index', { status: tab.key })"
                    class="flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition"
                    :class="tab.key === currentStatus
                        ? 'border-[var(--accent)] bg-[var(--accent)] text-white shadow-sm'
                        : 'border-[var(--line)] bg-white text-[var(--slate-soft)] hover:border-[var(--line-strong)] hover:bg-[var(--panel-strong)] hover:text-[var(--ink)]'"
                >
                    {{ tab.label }}
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-bold tabular-nums"
                        :class="tab.key === currentStatus
                            ? 'bg-white/25 text-white'
                            : tab.color"
                    >
                        {{ counts?.[tab.key] ?? 0 }}
                    </span>
                </Link>
            </div>

            <!-- Inquiry list -->
            <div class="app-panel overflow-hidden">

                <!-- Empty state -->
                <div v-if="!inquiries?.data?.length" class="flex flex-col items-center gap-4 px-6 py-16 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--panel-muted)]">
                        <svg class="h-6 w-6 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-[var(--ink)]">No inquiries</div>
                        <div class="mt-1 text-sm text-[var(--slate-soft)]">Nothing in the "{{ currentStatus }}" queue right now.</div>
                    </div>
                </div>

                <div v-else class="divide-y divide-[var(--line)]">
                    <div
                        v-for="i in inquiries.data"
                        :key="i.id"
                        class="flex flex-col gap-4 px-5 py-4 transition hover:bg-[var(--panel-strong)] sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-[var(--ink)]">{{ i.name }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-[0.64rem] font-bold uppercase tracking-wide" :class="statusBadge(i.status)">
                                    {{ i.status }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-[var(--slate-soft)]">
                                {{ i.email }}<span v-if="i.phone"> · {{ i.phone }}</span>
                            </div>
                            <p class="mt-2 text-sm text-[var(--ink)] line-clamp-2">{{ i.message_preview }}</p>
                            <div class="mt-2 text-[0.65rem] font-medium text-[var(--slate-soft)]">{{ i.created_at }}</div>
                        </div>

                        <Link
                            :href="route('inquiries.show', i.id)"
                            class="app-button-secondary shrink-0 gap-1.5 self-start"
                        >
                            Open
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="inquiries?.prev_page_url || inquiries?.next_page_url" class="flex gap-2 border-t border-[var(--line)] px-5 py-3.5">
                    <Link
                        v-if="inquiries?.prev_page_url"
                        :href="inquiries.prev_page_url"
                        class="app-button-secondary gap-1.5"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </Link>
                    <Link
                        v-if="inquiries?.next_page_url"
                        :href="inquiries.next_page_url"
                        class="app-button-secondary gap-1.5"
                    >
                        Next
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

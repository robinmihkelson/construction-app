<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>

<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    tasks: { type: Array, default: () => [] },
})

const filter = ref('open')

const todayStr = (() => {
    const d = new Date()
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
})()

function isOverdue(task) {
    if (!task.due_date || task.status === 'done') return false
    return task.due_date.slice(0, 10) < todayStr
}

const counts = computed(() => ({
    all: props.tasks.length,
    open: props.tasks.filter((t) => t.status !== 'done').length,
    overdue: props.tasks.filter(isOverdue).length,
    done: props.tasks.filter((t) => t.status === 'done').length,
}))

const visibleTasks = computed(() => {
    if (filter.value === 'all') return props.tasks
    if (filter.value === 'open') return props.tasks.filter((t) => t.status !== 'done')
    if (filter.value === 'overdue') return props.tasks.filter(isOverdue)
    if (filter.value === 'done') return props.tasks.filter((t) => t.status === 'done')
    return props.tasks
})

function statusLabel(status) {
    if (status === 'todo') return 'To do'
    if (status === 'doing') return 'Doing'
    if (status === 'done') return 'Done'
    return status
}
function statusBadge(status) {
    if (status === 'doing') return 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30'
    if (status === 'done') return 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30'
    return 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-500/15 dark:text-slate-200 dark:ring-slate-500/30'
}
function statusDot(status) {
    if (status === 'doing') return 'bg-amber-500'
    if (status === 'done') return 'bg-emerald-500'
    return 'bg-slate-400'
}

function formatDue(date) {
    if (!date) return null
    const d = new Date(date.slice(0, 10))
    return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })
}

function setStatus(task, status) {
    if (task.status === status) return
    router.patch(
        route('tasks.update', task.id),
        { status },
        { preserveScroll: true, preserveState: true }
    )
}

const filters = [
    { key: 'open', label: 'Open' },
    { key: 'overdue', label: 'Overdue' },
    { key: 'done', label: 'Done' },
    { key: 'all', label: 'All' },
]
</script>

<template>
    <Head title="My tasks" />

    <div class="space-y-5">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="app-label">Workspace</div>
                <h1 class="mt-1 text-2xl font-bold text-[var(--ink)]">My tasks</h1>
                <p class="mt-1 text-sm text-[var(--slate-soft)]">Tasks assigned to you across all your projects.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center sm:flex sm:gap-3">
                <div class="rounded-xl border border-[var(--line)] bg-[var(--panel-bg)] px-4 py-2 shadow-sm">
                    <div class="app-label">Open</div>
                    <div class="mt-0.5 text-xl font-bold text-[var(--ink)]">{{ counts.open }}</div>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/10">
                    <div class="text-[0.64rem] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300">Overdue</div>
                    <div class="mt-0.5 text-xl font-bold text-rose-700 dark:text-rose-300">{{ counts.overdue }}</div>
                </div>
                <div class="rounded-xl border border-[var(--line)] bg-[var(--panel-bg)] px-4 py-2 shadow-sm">
                    <div class="app-label">Done</div>
                    <div class="mt-0.5 text-xl font-bold text-[var(--ink)]">{{ counts.done }}</div>
                </div>
            </div>
        </div>

        <div class="inline-flex flex-wrap rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] p-1 gap-0.5">
            <button
                v-for="f in filters"
                :key="f.key"
                type="button"
                @click="filter = f.key"
                class="rounded-md px-3 py-1.5 text-xs font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                :class="filter === f.key
                    ? 'bg-[var(--panel-bg)] text-[var(--accent)] shadow-sm'
                    : 'text-[var(--slate-soft)] hover:text-[var(--ink)]'"
            >
                {{ f.label }}
                <span class="ml-1 text-[0.65rem] text-[var(--slate-soft)]">{{ counts[f.key] }}</span>
            </button>
        </div>

        <div v-if="visibleTasks.length === 0" class="app-panel flex flex-col items-center justify-center gap-3 py-16 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--panel-muted)]">
                <svg class="h-7 w-7 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <div class="font-semibold text-[var(--ink)]">Nothing here</div>
                <div class="mt-1 text-sm text-[var(--slate-soft)]">
                    {{ filter === 'overdue' ? 'No overdue tasks — nice!' : filter === 'done' ? 'No completed tasks yet.' : 'No tasks match this filter.' }}
                </div>
            </div>
        </div>

        <div v-else class="app-panel divide-y divide-[var(--line)] overflow-hidden">
            <div
                v-for="task in visibleTasks"
                :key="task.id"
                class="flex flex-col gap-3 px-4 py-3 transition hover:bg-[var(--panel-strong)] sm:flex-row sm:items-center"
            >
                <Link
                    :href="task.project ? route('projects.show', task.project.id) + '?task=' + task.id : '#'"
                    class="flex min-w-0 flex-1 items-start gap-3"
                >
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="statusDot(task.status)" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="break-words text-sm font-semibold text-[var(--ink)] [overflow-wrap:anywhere]">
                                {{ task.title }}
                            </span>
                            <span
                                v-if="isOverdue(task)"
                                class="rounded-full bg-rose-100 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-wider text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-500/30"
                            >
                                Overdue
                            </span>
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--slate-soft)]">
                            <span v-if="task.project" class="truncate">{{ task.project.name }}</span>
                            <span v-if="task.due_date" :class="isOverdue(task) ? 'font-semibold text-rose-700 dark:text-rose-300' : ''">
                                Due {{ formatDue(task.due_date) }}
                            </span>
                            <span v-else class="italic">No due date</span>
                        </div>
                        <div v-if="task.description" class="mt-1 line-clamp-2 text-xs text-[var(--slate)]">
                            {{ task.description }}
                        </div>
                    </div>
                </Link>

                <div class="flex shrink-0 items-center gap-2">
                    <div class="inline-flex rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] p-0.5 gap-0.5">
                        <button
                            v-for="status in ['todo', 'doing', 'done']"
                            :key="status"
                            type="button"
                            @click="setStatus(task, status)"
                            class="rounded-md px-2 py-1 text-[0.65rem] font-semibold transition"
                            :class="task.status === status
                                ? 'bg-[var(--panel-bg)] text-[var(--accent)] shadow-sm'
                                : 'text-[var(--slate-soft)] hover:text-[var(--ink)]'"
                        >
                            {{ statusLabel(status) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>

<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
})

const STORAGE_KEY = 'calendar:selectedProjectId'

function loadSavedProjectId() {
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY)
        if (raw === null || raw === 'all') return null
        const n = Number(raw)
        return Number.isFinite(n) ? n : null
    } catch (e) {
        return null
    }
}

const localTasks = ref(props.tasks.map((t) => ({ ...t })))
const cursor = ref(startOfMonth(new Date()))
const draggingId = ref(null)
const dragOverKey = ref(null)
const savingIds = ref(new Set())
const selectedProjectId = ref(loadSavedProjectId())

watch(selectedProjectId, (val) => {
    try {
        window.localStorage.setItem(STORAGE_KEY, val == null ? 'all' : String(val))
    } catch (e) { /* ignore */ }
})

const visibleTasks = computed(() => {
    if (selectedProjectId.value == null) return localTasks.value
    return localTasks.value.filter((t) => t.project && t.project.id === selectedProjectId.value)
})

const selectedProjectIdModel = computed({
    get: () => (selectedProjectId.value == null ? 0 : selectedProjectId.value),
    set: (v) => { selectedProjectId.value = v === 0 ? null : v },
})

watch(
    () => props.tasks,
    (next) => {
        if (savingIds.value.size > 0) return
        localTasks.value = next.map((t) => ({ ...t }))
    },
    { deep: true }
)

const weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const monthLabel = computed(() =>
    cursor.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
)

const days = computed(() => {
    const year = cursor.value.getFullYear()
    const month = cursor.value.getMonth()
    const first = new Date(year, month, 1)
    const offset = (first.getDay() + 6) % 7
    const start = new Date(year, month, 1 - offset)

    return Array.from({ length: 42 }, (_, i) => {
        const d = new Date(start)
        d.setDate(start.getDate() + i)
        return d
    })
})

const tasksByDate = computed(() => {
    const map = new Map()
    for (const t of visibleTasks.value) {
        const key = (t.due_date || '').slice(0, 10)
        if (!key) continue
        if (!map.has(key)) map.set(key, [])
        map.get(key).push(t)
    }
    return map
})

const monthCount = computed(() => {
    const m = cursor.value.getMonth()
    const y = cursor.value.getFullYear()
    return visibleTasks.value.filter((t) => {
        const d = new Date((t.due_date || '').slice(0, 10))
        return d.getMonth() === m && d.getFullYear() === y
    }).length
})

function startOfMonth(d) {
    return new Date(d.getFullYear(), d.getMonth(), 1)
}

function ymd(date) {
    const y = date.getFullYear()
    const m = String(date.getMonth() + 1).padStart(2, '0')
    const d = String(date.getDate()).padStart(2, '0')
    return `${y}-${m}-${d}`
}

function isCurrentMonth(date) {
    return date.getMonth() === cursor.value.getMonth()
}
function isToday(date) {
    return ymd(date) === ymd(new Date())
}
function isWeekend(date) {
    const d = date.getDay()
    return d === 0 || d === 6
}

function prevMonth() {
    const d = new Date(cursor.value)
    d.setMonth(d.getMonth() - 1)
    cursor.value = d
}
function nextMonth() {
    const d = new Date(cursor.value)
    d.setMonth(d.getMonth() + 1)
    cursor.value = d
}
function goToday() {
    cursor.value = startOfMonth(new Date())
}

function statusBadge(status) {
    if (status === 'doing') return 'bg-amber-100 text-amber-800 ring-amber-200'
    if (status === 'done') return 'bg-emerald-100 text-emerald-800 ring-emerald-200'
    return 'bg-slate-100 text-slate-700 ring-slate-200'
}
function statusDot(status) {
    if (status === 'doing') return 'bg-amber-500'
    if (status === 'done') return 'bg-emerald-500'
    return 'bg-slate-400'
}

function onDragStart(task, event) {
    if (!task.can_edit) {
        event.preventDefault()
        return
    }
    draggingId.value = task.id
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', String(task.id))
}
function onDragEnd() {
    draggingId.value = null
    dragOverKey.value = null
}
function onDragOver(date, event) {
    if (draggingId.value == null) return
    event.preventDefault()
    event.dataTransfer.dropEffect = 'move'
    dragOverKey.value = ymd(date)
}
function onDragLeave(date) {
    if (dragOverKey.value === ymd(date)) dragOverKey.value = null
}
function onDrop(date, event) {
    event.preventDefault()
    const id = draggingId.value
    draggingId.value = null
    dragOverKey.value = null
    if (id == null) return

    const idx = localTasks.value.findIndex((t) => t.id === id)
    if (idx === -1) return
    const task = localTasks.value[idx]
    if (!task.can_edit) return

    const newDate = ymd(date)
    const previous = (task.due_date || '').slice(0, 10)
    if (newDate === previous) return

    localTasks.value[idx] = { ...task, due_date: newDate }
    savingIds.value = new Set([...savingIds.value, id])

    router.patch(
        route('tasks.update', id),
        { due_date: newDate },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                const i = localTasks.value.findIndex((t) => t.id === id)
                if (i !== -1) localTasks.value[i] = { ...localTasks.value[i], due_date: previous }
            },
            onFinish: () => {
                const next = new Set(savingIds.value)
                next.delete(id)
                savingIds.value = next
            },
        }
    )
}
</script>

<template>
    <Head title="Calendar" />

    <div class="space-y-4">
        <div class="app-panel flex flex-wrap items-center gap-3 px-4 py-3">
            <div>
                <h1 class="text-lg font-bold text-[var(--ink)]">{{ monthLabel }}</h1>
                <p class="text-xs text-[var(--slate-soft)]">
                    {{ monthCount }} task{{ monthCount === 1 ? '' : 's' }} due this month
                </p>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-1.5">
                <div class="relative">
                    <select
                        v-model.number="selectedProjectIdModel"
                        class="h-8 appearance-none rounded-lg border border-[var(--line)] bg-[var(--panel-bg)] py-0 pl-3 pr-8 text-xs font-medium text-[var(--slate)] transition focus:border-[var(--accent)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/12"
                        aria-label="Filter by project"
                    >
                        <option :value="0">All projects</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>

                <span class="mx-1 hidden h-5 w-px bg-[var(--line)] sm:inline-block" />

                <button
                    type="button"
                    @click="prevMonth"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--line)] bg-[var(--panel-bg)] text-[var(--slate)] transition hover:bg-[var(--panel-muted)]"
                    aria-label="Previous month"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="goToday"
                    class="rounded-lg border border-[var(--line)] bg-[var(--panel-bg)] px-3 py-1.5 text-xs font-medium text-[var(--slate)] transition hover:bg-[var(--panel-muted)]"
                >
                    Today
                </button>
                <button
                    type="button"
                    @click="nextMonth"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--line)] bg-[var(--panel-bg)] text-[var(--slate)] transition hover:bg-[var(--panel-muted)]"
                    aria-label="Next month"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="app-panel overflow-hidden">
            <div class="grid grid-cols-7 border-b border-[var(--line)] bg-[var(--panel-strong)]">
                <div
                    v-for="label in weekdayLabels"
                    :key="label"
                    class="px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--slate-soft)]"
                >
                    {{ label }}
                </div>
            </div>

            <div class="grid grid-cols-7 grid-rows-6">
                <div
                    v-for="(date, i) in days"
                    :key="i"
                    class="relative min-h-[7rem] border-b border-r border-[var(--line)] p-1.5 transition"
                    :class="[
                        isCurrentMonth(date) ? 'bg-[var(--panel-bg)]' : 'bg-[var(--panel-strong)]/60',
                        isWeekend(date) && isCurrentMonth(date) ? 'bg-[var(--panel-strong)]' : '',
                        dragOverKey === ymd(date) ? 'ring-2 ring-inset ring-[var(--accent)] bg-[var(--accent-soft)]' : '',
                        (i + 1) % 7 === 0 ? 'border-r-0' : '',
                        i >= 35 ? 'border-b-0' : '',
                    ]"
                    @dragover="onDragOver(date, $event)"
                    @dragleave="onDragLeave(date)"
                    @drop="onDrop(date, $event)"
                >
                    <div class="mb-1 flex items-center justify-between">
                        <span
                            class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full px-1.5 text-xs font-semibold"
                            :class="isToday(date) && isCurrentMonth(date)
                                ? 'bg-[var(--accent)] text-white'
                                : isCurrentMonth(date)
                                    ? 'text-[var(--ink)]'
                                    : 'text-[var(--slate-soft)]'"
                        >
                            {{ date.getDate() }}
                        </span>
                    </div>

                    <div v-if="isCurrentMonth(date)" class="space-y-1">
                        <div
                            v-for="task in tasksByDate.get(ymd(date)) || []"
                            :key="task.id"
                            :draggable="task.can_edit"
                            @dragstart="onDragStart(task, $event)"
                            @dragend="onDragEnd"
                            class="group rounded-md border border-[var(--line)] bg-[var(--panel-bg)] px-2 py-1 text-[0.7rem] shadow-[var(--shadow-xs)] transition"
                            :class="[
                                task.can_edit ? 'cursor-grab hover:border-[var(--accent)] hover:shadow-sm active:cursor-grabbing' : 'cursor-default opacity-90',
                                draggingId === task.id ? 'opacity-50' : '',
                                savingIds.has(task.id) ? 'animate-pulse' : '',
                            ]"
                        >
                            <Link
                                :href="task.project ? route('projects.show', task.project.id) + '?task=' + task.id : '#'"
                                :draggable="false"
                                class="block"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="statusDot(task.status)" />
                                    <span class="truncate font-medium text-[var(--ink)]">{{ task.title }}</span>
                                </div>
                                <div
                                    v-if="task.project"
                                    class="mt-0.5 truncate text-[0.625rem] text-[var(--slate-soft)]"
                                >
                                    {{ task.project.name }}
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 px-1 text-xs text-[var(--slate-soft)]">
            <span class="inline-flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400" /> To do
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500" /> Doing
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" /> Done
            </span>
        </div>
    </div>
</template>

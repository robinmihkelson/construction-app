<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>

<script setup>
import { useForm, router, Link, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

const page = usePage()
const currentUser = computed(() => page.props.auth?.user ?? null)
const currentUserId = computed(() => {
    const id = currentUser.value?.id
    return id == null ? null : Number(id)
})

const props = defineProps({
    project: Object,
    tasks: Array,
    users: Array,
    members: Array,
    can: Object,
})

const form = useForm({ title: '', assigned_to: '', due_date: '' })
const memberForm = useForm({ user_id: '', role: '' })
const taskCommentForm = useForm({ body: '' })
const progressImageForm = useForm({ images: [] })

const isBusy = ref(false)
const selectedTaskId = ref(props.tasks[0]?.id ?? null)
const selectedTask = computed(() => props.tasks.find((t) => t.id === selectedTaskId.value) ?? null)
const statusOptions = ['todo', 'doing', 'done']
const statusCounts = computed(() => ({
    todo:  props.tasks.filter((t) => t.status === 'todo').length,
    doing: props.tasks.filter((t) => t.status === 'doing').length,
    done:  props.tasks.filter((t) => t.status === 'done').length,
}))

let removeStart, removeFinish

onMounted(() => {
    removeStart = router.on('start', () => { isBusy.value = true })
    removeFinish = router.on('finish', () => { isBusy.value = false })
    const queryTaskId = Number(new URLSearchParams(window.location.search).get('task'))
    if (props.tasks.some((t) => t.id === queryTaskId)) selectedTaskId.value = queryTaskId
})

onBeforeUnmount(() => {
    removeStart && removeStart()
    removeFinish && removeFinish()
})

function deleteTask(taskId) {
    if (!window.confirm('Delete this task?')) return
    router.delete(route('tasks.destroy', taskId), { preserveScroll: true })
}

function isCurrentUser(userId) {
    if (currentUserId.value == null || userId == null) return false
    return Number(userId) === currentUserId.value
}

function updateMemberRole(memberId, role) {
    router.patch(route('projects.members.update', [props.project.id, memberId]), { role }, { preserveScroll: true })
}

function setStatus(taskId, status) {
    router.patch(route('tasks.update', taskId), { status }, { preserveScroll: true })
}

function statusBadgeClass(status) {
    if (status === 'doing') return 'bg-amber-100 text-amber-800 ring-amber-200'
    if (status === 'done')  return 'bg-emerald-100 text-emerald-800 ring-emerald-200'
    return 'bg-slate-100 text-slate-700 ring-slate-200'
}

function statusLabel(status) {
    if (status === 'todo')  return 'To do'
    if (status === 'doing') return 'Doing'
    if (status === 'done')  return 'Done'
    return status
}

function openTask(taskId) {
    selectedTaskId.value = taskId
    taskCommentForm.reset('body')
    progressImageForm.reset('images')
    const url = new URL(window.location.href)
    url.searchParams.set('task', taskId)
    window.history.replaceState({}, '', url)
}

function submitTaskComment() {
    if (!selectedTask.value) return
    taskCommentForm.post(route('tasks.comments.store', selectedTask.value.id), {
        preserveScroll: true,
        onSuccess: () => taskCommentForm.reset('body'),
    })
}

function handleProgressImages(event) {
    progressImageForm.images = Array.from(event.target.files ?? [])
}

function uploadProgressImages(event) {
    if (!selectedTask.value) return
    progressImageForm.post(route('tasks.progress-images.store', selectedTask.value.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            progressImageForm.reset('images')
            event.target.reset()
        },
    })
}

function deleteProgressImage(imageId) {
    if (!selectedTask.value) return
    if (!window.confirm('Delete this progress image?')) return
    router.delete(route('tasks.progress-images.destroy', [selectedTask.value.id, imageId]), { preserveScroll: true })
}
</script>

<template>
    <div class="space-y-5">

        <!-- Project header -->
        <div class="app-panel p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="app-label">Project</div>
                    <h1 class="mt-1 break-words text-2xl font-bold text-[var(--ink)] [overflow-wrap:anywhere]">
                        {{ project.name }}
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="app-panel-muted px-3 py-2 text-xs font-semibold text-[var(--slate-soft)]">
                        {{ tasks.length }} task{{ tasks.length === 1 ? '' : 's' }}
                    </span>
                    <span class="app-panel-muted px-3 py-2 text-xs font-semibold text-[var(--slate-soft)]">
                        {{ members.length }} member{{ members.length === 1 ? '' : 's' }}
                    </span>
                    <Link :href="route('projects.index')" class="app-button-secondary gap-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Projects
                    </Link>
                </div>
            </div>

            <!-- Status counters -->
            <div class="mt-4 grid gap-2.5 sm:grid-cols-3">
                <div
                    v-for="status in statusOptions"
                    :key="status"
                    class="flex items-center justify-between rounded-lg border border-[var(--line)] px-4 py-3"
                    :class="status === 'done' ? 'bg-emerald-50 border-emerald-100' : status === 'doing' ? 'bg-amber-50 border-amber-100' : 'bg-[var(--panel-strong)]'"
                >
                    <span class="text-sm font-medium text-[var(--slate)]">{{ statusLabel(status) }}</span>
                    <span class="text-lg font-bold text-[var(--ink)]">{{ statusCounts[status] }}</span>
                </div>
            </div>
        </div>

        <!-- Syncing indicator -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isBusy" class="flex items-center gap-2 rounded-lg border border-[var(--line)] bg-white px-4 py-2.5 text-sm text-[var(--slate-soft)] shadow-sm">
                <svg class="h-4 w-4 animate-spin text-[var(--accent)]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Syncing…
            </div>
        </Transition>

        <!-- Two-column layout: sidebar + main -->
        <div class="grid min-w-0 gap-5 xl:grid-cols-[300px_minmax(0,1fr)]">

            <!-- ── Left sidebar ────────────────────────────────── -->
            <aside class="min-w-0 space-y-5 xl:sticky xl:top-20 xl:self-start">

                <!-- Add task form -->
                <div v-if="can.manageTasks" class="app-panel p-4">
                    <h2 class="app-section-title mb-0.5">Add task</h2>
                    <p class="mb-4 text-xs text-[var(--slate-soft)]">Create a work item, then track it inside.</p>
                    <form
                        @submit.prevent="form.post(route('projects.tasks.store', project.id), {
                            preserveScroll: true,
                            onSuccess: () => form.reset('title', 'assigned_to', 'due_date'),
                        })"
                        class="space-y-2.5"
                    >
                        <input v-model="form.title" class="app-input" placeholder="Task title" />
                        <select v-model="form.assigned_to" class="app-input">
                            <option value="">Unassigned</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <input v-model="form.due_date" type="date" class="app-input" />
                        <button class="app-button-primary w-full">Add task</button>
                    </form>
                </div>

                <!-- Team -->
                <div class="app-panel p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="app-section-title">Team</h2>
                            <p v-if="!can.manageMembers" class="mt-0.5 text-xs text-[var(--slate-soft)]">
                                Office users can manage members.
                            </p>
                        </div>
                        <span class="rounded-full bg-[var(--panel-muted)] px-2.5 py-1 text-xs font-bold text-[var(--slate)]">
                            {{ members.length }}
                        </span>
                    </div>

                    <div v-if="members.length === 0" class="rounded-lg border border-dashed border-[var(--line)] px-3 py-4 text-center text-sm text-[var(--slate-soft)]">
                        No members yet.
                    </div>

                    <ul v-else class="space-y-2">
                        <li
                            v-for="m in members"
                            :key="m.id"
                            class="rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] p-3"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[0.6rem] font-bold text-[var(--accent)]">
                                        {{ m.name?.slice(0,2).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-xs font-semibold text-[var(--ink)]">{{ m.name }}</div>
                                        <div class="text-[0.64rem] font-semibold capitalize text-[var(--accent)]">
                                            {{ m.pivot?.role ?? 'member' }}
                                        </div>
                                    </div>
                                </div>
                                <span v-if="isCurrentUser(m.id)" class="shrink-0 text-[0.64rem] font-bold text-[var(--slate-soft)]">You</span>
                                <button
                                    v-else-if="can.manageMembers"
                                    type="button"
                                    @click.prevent.stop="
                                        window.confirm('Remove this member?') &&
                                        router.delete(route('projects.members.destroy', [project.id, m.id]), { preserveScroll: true })
                                    "
                                    class="shrink-0 text-[0.64rem] font-semibold text-rose-600 transition hover:text-rose-800"
                                >
                                    Remove
                                </button>
                            </div>
                            <select
                                v-if="can.manageMembers"
                                class="app-input mt-2 py-1.5 text-xs"
                                :value="m.pivot.role ?? 'member'"
                                :disabled="isCurrentUser(m.id)"
                                @change="updateMemberRole(m.id, $event.target.value)"
                            >
                                <option value="member">member</option>
                                <option value="client">client</option>
                                <option value="worker">worker</option>
                                <option value="office">office</option>
                            </select>
                        </li>
                    </ul>

                    <form
                        v-if="can.manageMembers"
                        @submit.prevent="memberForm.post(route('projects.members.store', project.id))"
                        class="mt-4 space-y-2.5 border-t border-[var(--line)] pt-4"
                    >
                        <select v-model="memberForm.user_id" class="app-input">
                            <option value="">Select user</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <select v-model="memberForm.role" class="app-input">
                            <option value="">member</option>
                            <option value="client">client</option>
                            <option value="worker">worker</option>
                            <option value="office">office</option>
                        </select>
                        <button class="app-button-secondary w-full">Add member</button>
                    </form>
                </div>
            </aside>

            <!-- ── Main content ──────────────────────────────────── -->
            <div class="min-w-0 space-y-5">

                <!-- Task list -->
                <div class="app-panel overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-[var(--line)] px-5 py-3.5">
                        <div>
                            <h2 class="app-section-title">Tasks</h2>
                            <p class="text-xs text-[var(--slate-soft)]">Select a task to view and update it.</p>
                        </div>
                        <span class="rounded-full bg-[var(--panel-muted)] px-2.5 py-1 text-xs font-bold text-[var(--slate)]">{{ tasks.length }}</span>
                    </div>

                    <div v-if="tasks.length === 0" class="flex flex-col items-center gap-3 px-5 py-12 text-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--panel-muted)]">
                            <svg class="h-5 w-5 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="text-sm text-[var(--slate-soft)]">No tasks yet. Add one above.</div>
                    </div>

                    <div v-else class="divide-y divide-[var(--line)]">
                        <div
                            v-for="t in tasks"
                            :key="t.id"
                            role="button"
                            tabindex="0"
                            @click="openTask(t.id)"
                            @keydown.enter.prevent="openTask(t.id)"
                            @keydown.space.prevent="openTask(t.id)"
                            class="grid min-w-0 cursor-pointer gap-3 px-5 py-3.5 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--accent)] hover:bg-[var(--panel-strong)] sm:grid-cols-[5rem_minmax(0,1fr)_auto] sm:items-center"
                            :class="selectedTask?.id === t.id ? 'bg-blue-50/60' : ''"
                        >
                            <span
                                class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                :class="statusBadgeClass(t.status)"
                            >
                                {{ statusLabel(t.status) }}
                            </span>

                            <div class="min-w-0">
                                <div class="break-words text-sm font-semibold text-[var(--ink)] [overflow-wrap:anywhere]">{{ t.title }}</div>
                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-[var(--slate-soft)]">
                                    <span>{{ t.assignee ? t.assignee.name : 'Unassigned' }}</span>
                                    <span v-if="t.due_date">Due {{ t.due_date }}</span>
                                    <span>{{ t.comments?.length ?? 0 }} comment{{ (t.comments?.length ?? 0) === 1 ? '' : 's' }}</span>
                                    <span>{{ t.progress_images?.length ?? 0 }} photo{{ (t.progress_images?.length ?? 0) === 1 ? '' : 's' }}</span>
                                </div>
                            </div>

                            <div class="text-xs font-semibold" :class="selectedTask?.id === t.id ? 'text-[var(--accent)]' : 'text-[var(--slate-soft)]'">
                                {{ selectedTask?.id === t.id ? 'Selected' : 'Open' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task detail -->
                <div class="app-panel overflow-hidden">
                    <!-- No task selected -->
                    <div v-if="!selectedTask" class="flex flex-col items-center gap-3 px-5 py-12 text-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--panel-muted)]">
                            <svg class="h-5 w-5 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
                            </svg>
                        </div>
                        <div class="text-sm text-[var(--slate-soft)]">Select a task to view details, photos, and comments.</div>
                    </div>

                    <div v-else>
                        <!-- Task detail header -->
                        <div class="border-b border-[var(--line)] px-5 py-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="statusBadgeClass(selectedTask.status)">
                                            {{ statusLabel(selectedTask.status) }}
                                        </span>
                                        <span class="text-xs text-[var(--slate-soft)]">Task details</span>
                                    </div>
                                    <h2 class="mt-2 break-words text-lg font-bold text-[var(--ink)] [overflow-wrap:anywhere]">
                                        {{ selectedTask.title }}
                                    </h2>
                                </div>
                                <button
                                    v-if="can.manageTasks"
                                    type="button"
                                    @click.prevent.stop="deleteTask(selectedTask.id)"
                                    class="app-button-danger shrink-0 w-fit"
                                >
                                    Delete task
                                </button>
                            </div>

                            <!-- Meta -->
                            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                                <span class="app-panel-muted px-3 py-1.5 text-xs text-[var(--slate-soft)]">
                                    Assignee: <strong class="font-semibold text-[var(--ink)]">{{ selectedTask.assignee?.name ?? 'Unassigned' }}</strong>
                                </span>
                                <span class="app-panel-muted px-3 py-1.5 text-xs text-[var(--slate-soft)]">
                                    Due: <strong class="font-semibold text-[var(--ink)]">{{ selectedTask.due_date ?? 'None' }}</strong>
                                </span>
                            </div>

                            <!-- Status switcher -->
                            <div v-if="can.changeTaskStatus" class="mt-4">
                                <div class="app-label mb-2">Change status</div>
                                <div class="inline-flex flex-wrap rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] p-1 gap-0.5">
                                    <button
                                        v-for="status in statusOptions"
                                        :key="status"
                                        type="button"
                                        @click.prevent.stop="setStatus(selectedTask.id, status)"
                                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                                        :class="selectedTask.status === status
                                            ? 'bg-white text-[var(--accent)] shadow-sm'
                                            : 'text-[var(--slate-soft)] hover:text-[var(--ink)]'"
                                    >
                                        {{ statusLabel(status) }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Progress photos + comments -->
                        <div class="grid gap-0 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">

                            <!-- Progress photos -->
                            <div class="border-b border-[var(--line)] px-5 py-4 lg:border-b-0 lg:border-r">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <h3 class="app-section-title">Progress photos</h3>
                                    <span class="text-xs text-[var(--slate-soft)]">{{ selectedTask.progress_images?.length ?? 0 }}</span>
                                </div>

                                <form @submit.prevent="uploadProgressImages" class="space-y-2.5">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleProgressImages"
                                        class="block w-full rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] px-3 py-2 text-xs text-[var(--ink)] file:mr-3 file:rounded-md file:border-0 file:bg-[var(--accent)] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white"
                                    />
                                    <button
                                        type="submit"
                                        :disabled="progressImageForm.processing || progressImageForm.images.length === 0"
                                        class="app-button-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{ progressImageForm.processing ? 'Uploading…' : 'Upload photos' }}
                                    </button>
                                </form>

                                <div v-if="(selectedTask.progress_images?.length ?? 0) === 0" class="mt-4 rounded-lg border border-dashed border-[var(--line)] px-3 py-6 text-center text-xs text-[var(--slate-soft)]">
                                    No photos yet.
                                </div>

                                <div v-else class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <figure
                                        v-for="image in selectedTask.progress_images"
                                        :key="image.id"
                                        class="overflow-hidden rounded-lg border border-[var(--line)] bg-white"
                                    >
                                        <a :href="image.url" target="_blank" rel="noreferrer">
                                            <img :src="image.url" :alt="image.original_name" class="h-32 w-full object-cover transition hover:opacity-90" />
                                        </a>
                                        <figcaption class="space-y-1.5 px-3 py-2.5 text-xs text-[var(--slate-soft)]">
                                            <div class="truncate font-medium text-[var(--ink)]">{{ image.original_name }}</div>
                                            <div>{{ image.user?.name ?? 'Unknown' }} · {{ new Date(image.created_at).toLocaleDateString() }}</div>
                                            <button
                                                v-if="isCurrentUser(image.user?.id) || can.manageTasks"
                                                type="button"
                                                @click="deleteProgressImage(image.id)"
                                                class="font-semibold text-rose-600 transition hover:text-rose-800"
                                            >
                                                Delete
                                            </button>
                                        </figcaption>
                                    </figure>
                                </div>
                            </div>

                            <!-- Task comments -->
                            <div class="px-5 py-4">
                                <h3 class="app-section-title mb-3">Task comments</h3>

                                <form @submit.prevent="submitTaskComment" class="space-y-2.5">
                                    <textarea
                                        v-model="taskCommentForm.body"
                                        class="app-input min-h-24 resize-y"
                                        rows="3"
                                        placeholder="Write a comment…"
                                    />
                                    <button
                                        type="submit"
                                        :disabled="taskCommentForm.processing"
                                        class="app-button-primary ml-auto disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{ taskCommentForm.processing ? 'Sending…' : 'Send comment' }}
                                    </button>
                                </form>

                                <div v-if="(selectedTask.comments?.length ?? 0) === 0" class="mt-5 rounded-lg border border-dashed border-[var(--line)] px-3 py-6 text-center text-xs text-[var(--slate-soft)]">
                                    No comments yet.
                                </div>

                                <div v-else class="mt-5 divide-y divide-[var(--line)] border-t border-[var(--line)]">
                                    <div v-for="c in selectedTask.comments" :key="c.id" class="py-4">
                                        <div class="mb-2 flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <span class="text-xs font-semibold text-[var(--ink)]">{{ c.user?.name ?? 'Unknown' }}</span>
                                                <span v-if="isCurrentUser(c.user?.id)" class="ml-1 text-[0.65rem] font-bold text-[var(--accent)]">you</span>
                                                <div class="text-[0.65rem] text-[var(--slate-soft)]">{{ new Date(c.created_at).toLocaleString() }}</div>
                                            </div>
                                            <button
                                                v-if="isCurrentUser(c.user?.id)"
                                                type="button"
                                                :disabled="isBusy"
                                                @click="window.confirm('Delete this comment?') && router.delete(route('tasks.comments.destroy', [selectedTask.id, c.id]), { preserveScroll: true })"
                                                class="shrink-0 text-xs font-semibold text-rose-600 transition hover:text-rose-800 disabled:opacity-50"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                        <div class="whitespace-pre-wrap break-words text-sm leading-6 text-[var(--ink)] [overflow-wrap:anywhere]">{{ c.body }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

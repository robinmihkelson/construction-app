<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
    layout: AuthenticatedLayout,
}
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

const form = useForm({
    title: '',
    assigned_to: '',
    due_date: '',
})

const memberForm = useForm({
    user_id: '',
    role: '',
})

const taskCommentForm = useForm({ body: '' })
const progressImageForm = useForm({ images: [] })

const isBusy = ref(false)
const selectedTaskId = ref(props.tasks[0]?.id ?? null)
const selectedTask = computed(() => props.tasks.find((task) => task.id === selectedTaskId.value) ?? null)
const statusOptions = ['todo', 'doing', 'done']
const statusCounts = computed(() => ({
    todo: props.tasks.filter((task) => task.status === 'todo').length,
    doing: props.tasks.filter((task) => task.status === 'doing').length,
    done: props.tasks.filter((task) => task.status === 'done').length,
}))

let removeStart, removeFinish

onMounted(() => {
    removeStart = router.on('start', () => { isBusy.value = true })
    removeFinish = router.on('finish', () => { isBusy.value = false })

    const queryTaskId = Number(new URLSearchParams(window.location.search).get('task'))
    if (props.tasks.some((task) => task.id === queryTaskId)) {
        selectedTaskId.value = queryTaskId
    }
})

onBeforeUnmount(() => {
    removeStart && removeStart()
    removeFinish && removeFinish()
})


function confirmDelete() {
    return window.confirm('Delete this task?')
}

function deleteTask(taskId) {
    if (!window.confirm('Delete this task?')) return

    router.delete(route('tasks.destroy', taskId), {
        preserveScroll: true,
    })
}

function confirmRemoveMember() {
    return window.confirm('Remove this member?')
}

function isCurrentUser(userId) {
    if (currentUserId.value == null || userId == null) return false

    return Number(userId) === currentUserId.value
}

function updateMemberRole(memberId, role) {
    router.patch(
        route('projects.members.update', [props.project.id, memberId]),
        { role },
        { preserveScroll: true }
    )
}

function confirmDeleteComment() {
    return window.confirm('Delete this comment?')
}

function setStatus(taskId, status) {
    router.patch(route('tasks.update', taskId), { status }, { preserveScroll: true })
}

function statusBadgeClass(status) {
    if (status === 'doing') return 'bg-amber-100 text-amber-800 ring-amber-200'
    if (status === 'done') return 'bg-emerald-100 text-emerald-800 ring-emerald-200'

    return 'bg-slate-100 text-slate-700 ring-slate-200'
}

function statusLabel(status) {
    if (status === 'todo') return 'To do'
    if (status === 'doing') return 'Doing'
    if (status === 'done') return 'Done'

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

    router.delete(route('tasks.progress-images.destroy', [selectedTask.value.id, imageId]), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="space-y-5">
        <section class="app-panel p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <div class="app-label">Project</div>
                    <h1 class="mt-1 break-words text-2xl font-semibold text-[color:var(--ink)] [overflow-wrap:anywhere]">
                        {{ project.name }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="app-panel-muted px-3 py-2 text-sm text-[color:var(--slate-soft)]">
                        {{ tasks.length }} task{{ tasks.length === 1 ? '' : 's' }}
                    </div>
                    <div class="app-panel-muted px-3 py-2 text-sm text-[color:var(--slate-soft)]">
                        {{ members.length }} member{{ members.length === 1 ? '' : 's' }}
                    </div>
                    <Link :href="route('projects.index')" class="app-button-secondary">
                        Back to projects
                    </Link>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div
                    v-for="status in statusOptions"
                    :key="status"
                    class="app-panel-muted flex items-center justify-between px-3 py-2"
                >
                    <span class="text-sm font-medium text-[color:var(--slate)]">{{ statusLabel(status) }}</span>
                    <span class="text-sm font-semibold text-[color:var(--ink)]">{{ statusCounts[status] }}</span>
                </div>
            </div>
        </section>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isBusy" class="app-panel px-4 py-3 text-sm font-semibold text-[color:var(--slate-soft)]">
                Syncing project updates...
            </div>
        </transition>

        <div class="grid min-w-0 gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <aside class="min-w-0 space-y-5 xl:sticky xl:top-4 xl:self-start">
                <section v-if="can.manageTasks" class="app-panel p-4">
                    <div class="mb-3">
                        <h2 class="app-section-title">Add task</h2>
                        <p class="mt-1 text-sm text-[color:var(--slate-soft)]">
                            Create the work item first, then track progress inside it.
                        </p>
                    </div>

                    <form
                        @submit.prevent="form.post(route('projects.tasks.store', project.id), {
                            preserveScroll: true,
                            onSuccess: () => form.reset('title', 'assigned_to', 'due_date'),
                        })"
                        class="grid gap-3"
                    >
                        <input v-model="form.title" class="app-input" placeholder="Task title" />

                        <select v-model="form.assigned_to" class="app-input">
                            <option value="">Unassigned</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>

                        <input v-model="form.due_date" type="date" class="app-input" />

                        <button class="app-button-primary">
                            Add task
                        </button>
                    </form>
                </section>

                <section class="app-panel p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="app-section-title">Team</h2>
                            <p v-if="!can.manageMembers" class="mt-1 text-sm text-[color:var(--slate-soft)]">
                                Only office users can manage members.
                            </p>
                        </div>
                        <span class="rounded-full bg-[color:var(--panel-muted)] px-2.5 py-1 text-xs font-semibold text-[color:var(--slate)]">
                            {{ members.length }}
                        </span>
                    </div>

                    <div v-if="members.length === 0" class="rounded-lg border border-dashed border-[color:var(--line)] px-3 py-4 text-sm text-[color:var(--slate-soft)]">
                        No members yet.
                    </div>

                    <ul v-else class="space-y-2">
                        <li
                            v-for="m in members"
                            :key="m.id"
                            class="rounded-lg border border-[color:var(--line)] bg-[color:var(--panel-strong)] p-3"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-[color:var(--ink)]">
                                        {{ m.name }}
                                    </div>
                                    <div class="mt-1 text-xs font-semibold capitalize text-[color:var(--accent)]">
                                        {{ m.pivot?.role ?? 'member' }}
                                    </div>
                                </div>

                                <button
                                    v-if="can.manageMembers && !isCurrentUser(m.id)"
                                    type="button"
                                    @click.prevent.stop="
                                        confirmRemoveMember() && route('projects.members.destroy', [project.id, m.id]) &&
                                        router.delete(route('projects.members.destroy', [project.id, m.id]), { preserveScroll: true })
                                    "
                                    class="text-xs font-semibold text-rose-700 hover:text-rose-900"
                                >
                                    Remove
                                </button>
                                <span v-else-if="isCurrentUser(m.id)" class="text-xs font-semibold text-[color:var(--slate-soft)]">
                                    You
                                </span>
                            </div>

                            <select
                                v-if="can.manageMembers"
                                class="app-input mt-3 py-2 text-sm"
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
                        class="mt-4 grid gap-3 border-t border-[color:var(--line)] pt-4"
                    >
                        <select v-model="memberForm.user_id" class="app-input">
                            <option value="">Select user</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>

                        <select v-model="memberForm.role" class="app-input">
                            <option value="">member</option>
                            <option value="client">client</option>
                            <option value="worker">worker</option>
                            <option value="office">office</option>
                        </select>

                        <button class="app-button-secondary">
                            Add member
                        </button>
                    </form>
                </section>
            </aside>

            <div class="min-w-0 space-y-5">
                <section class="app-panel overflow-hidden">
                    <div class="flex flex-col gap-2 border-b border-[color:var(--line)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="app-section-title">Tasks</h2>
                            <p class="text-sm text-[color:var(--slate-soft)]">
                                Select a task to update its status, photos, and comments.
                            </p>
                        </div>
                    </div>

                    <div v-if="tasks.length === 0" class="px-4 py-8 text-sm text-[color:var(--slate-soft)]">
                        No tasks yet.
                    </div>

                    <div v-else class="divide-y divide-[color:var(--line)]">
                        <div
                            v-for="t in tasks"
                            :key="t.id"
                            role="button"
                            tabindex="0"
                            @click="openTask(t.id)"
                            @keydown.enter.prevent="openTask(t.id)"
                            @keydown.space.prevent="openTask(t.id)"
                            class="grid min-w-0 cursor-pointer gap-3 px-4 py-3 text-left transition hover:bg-[color:var(--panel-strong)] sm:grid-cols-[5.5rem_minmax(0,1fr)_auto] sm:items-center"
                            :class="selectedTask?.id === t.id ? 'bg-blue-50/70' : ''"
                        >
                            <span
                                class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                :class="statusBadgeClass(t.status)"
                            >
                                {{ statusLabel(t.status) }}
                            </span>

                            <div class="min-w-0">
                                <div class="break-words text-sm font-semibold text-[color:var(--ink)] [overflow-wrap:anywhere]">
                                    {{ t.title }}
                                </div>
                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-[color:var(--slate-soft)]">
                                    <span>{{ t.assignee ? t.assignee.name : 'Unassigned' }}</span>
                                    <span v-if="t.due_date">Due {{ t.due_date }}</span>
                                    <span>{{ t.comments?.length ?? 0 }} comments</span>
                                    <span>{{ t.progress_images?.length ?? 0 }} photos</span>
                                </div>
                            </div>

                            <div class="text-xs font-semibold text-[color:var(--accent)]">
                                {{ selectedTask?.id === t.id ? 'Selected' : 'Open' }}
                            </div>
                        </div>
                    </div>
                </section>

                <section class="app-panel overflow-hidden">
                    <div v-if="!selectedTask" class="px-4 py-8 text-sm text-[color:var(--slate-soft)]">
                        Select a task to view progress and comments.
                    </div>

                    <div v-else>
                        <div class="border-b border-[color:var(--line)] px-4 py-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                            :class="statusBadgeClass(selectedTask.status)"
                                        >
                                            {{ statusLabel(selectedTask.status) }}
                                        </span>
                                        <span class="text-xs font-semibold text-[color:var(--slate-soft)]">Task details</span>
                                    </div>
                                    <h2 class="mt-2 break-words text-xl font-semibold text-[color:var(--ink)] [overflow-wrap:anywhere]">
                                        {{ selectedTask.title }}
                                    </h2>
                                </div>

                                <button
                                    v-if="can.manageTasks"
                                    type="button"
                                    @click.prevent.stop="deleteTask(selectedTask.id)"
                                    class="app-button-danger w-fit"
                                >
                                    Delete task
                                </button>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                                <span class="app-panel-muted px-3 py-2 text-[color:var(--slate-soft)]">
                                    Assignee: <strong class="font-semibold text-[color:var(--ink)]">{{ selectedTask.assignee?.name ?? 'Unassigned' }}</strong>
                                </span>
                                <span class="app-panel-muted px-3 py-2 text-[color:var(--slate-soft)]">
                                    Due: <strong class="font-semibold text-[color:var(--ink)]">{{ selectedTask.due_date ?? 'No due date' }}</strong>
                                </span>
                            </div>

                            <div v-if="can.changeTaskStatus" class="mt-4">
                                <div class="app-label mb-2">Change status</div>
                                <div class="inline-flex flex-wrap rounded-lg border border-[color:var(--line)] bg-[color:var(--panel-strong)] p-1">
                                    <button
                                        v-for="status in statusOptions"
                                        :key="status"
                                        type="button"
                                        @click.prevent.stop="setStatus(selectedTask.id, status)"
                                        class="rounded-md px-3 py-2 text-xs font-semibold transition"
                                        :class="selectedTask.status === status
                                            ? 'bg-white text-[color:var(--accent)] shadow-sm'
                                            : 'text-[color:var(--slate-soft)] hover:text-[color:var(--ink)]'"
                                    >
                                        {{ statusLabel(status) }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-0 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                            <div class="border-b border-[color:var(--line)] px-4 py-4 lg:border-b-0 lg:border-r">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <h3 class="app-section-title">Progress photos</h3>
                                    <span class="text-sm text-[color:var(--slate-soft)]">{{ selectedTask.progress_images?.length ?? 0 }}</span>
                                </div>

                                <form @submit.prevent="uploadProgressImages" class="grid gap-3">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleProgressImages"
                                        class="block w-full rounded-lg border border-[color:var(--line)] bg-[color:var(--panel-strong)] px-3 py-2 text-sm text-[color:var(--ink)] file:mr-3 file:rounded-md file:border-0 file:bg-[color:var(--slate)] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"
                                    />
                                    <button
                                        type="submit"
                                        :disabled="progressImageForm.processing || progressImageForm.images.length === 0"
                                        class="app-button-primary disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{ progressImageForm.processing ? 'Uploading...' : 'Upload photos' }}
                                    </button>
                                </form>

                                <div v-if="(selectedTask.progress_images?.length ?? 0) === 0" class="mt-4 rounded-lg border border-dashed border-[color:var(--line)] px-3 py-5 text-sm text-[color:var(--slate-soft)]">
                                    No progress photos yet.
                                </div>

                                <div v-else class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <figure
                                        v-for="image in selectedTask.progress_images"
                                        :key="image.id"
                                        class="overflow-hidden rounded-lg border border-[color:var(--line)] bg-white"
                                    >
                                        <a :href="image.url" target="_blank" rel="noreferrer">
                                            <img :src="image.url" :alt="image.original_name" class="h-36 w-full object-cover" />
                                        </a>
                                        <figcaption class="space-y-2 px-3 py-3 text-xs text-[color:var(--slate-soft)]">
                                            <div class="truncate font-medium text-[color:var(--ink)]">{{ image.original_name }}</div>
                                            <div>
                                                {{ image.user?.name ?? 'Unknown' }} / {{ new Date(image.created_at).toLocaleString() }}
                                            </div>
                                            <button
                                                v-if="isCurrentUser(image.user?.id) || can.manageTasks"
                                                type="button"
                                                @click="deleteProgressImage(image.id)"
                                                class="text-xs font-semibold text-rose-700 hover:text-rose-900"
                                            >
                                                Delete
                                            </button>
                                        </figcaption>
                                    </figure>
                                </div>
                            </div>

                            <div class="px-4 py-4">
                                <h3 class="app-section-title">Task comments</h3>

                                <form @submit.prevent="submitTaskComment" class="mt-3 grid gap-3">
                                    <textarea
                                        v-model="taskCommentForm.body"
                                        class="app-input min-h-28 resize-y"
                                        rows="4"
                                        placeholder="Write a task comment..."
                                    ></textarea>
                                    <button
                                        type="submit"
                                        :disabled="taskCommentForm.processing"
                                        class="app-button-primary justify-self-end disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{ taskCommentForm.processing ? 'Sending...' : 'Send comment' }}
                                    </button>
                                </form>

                                <div v-if="(selectedTask.comments?.length ?? 0) === 0" class="mt-5 rounded-lg border border-dashed border-[color:var(--line)] px-3 py-5 text-sm text-[color:var(--slate-soft)]">
                                    No task comments yet.
                                </div>

                                <div v-else class="mt-5 divide-y divide-[color:var(--line)] border-t border-[color:var(--line)]">
                                    <div v-for="c in selectedTask.comments" :key="c.id" class="py-4">
                                        <div class="mb-2 flex items-start justify-between gap-3 text-sm text-[color:var(--slate-soft)]">
                                            <div class="min-w-0">
                                                <span class="font-semibold text-[color:var(--ink)]">{{ c.user?.name ?? 'Unknown' }}</span>
                                                <span v-if="isCurrentUser(c.user?.id)" class="ml-1 text-xs font-semibold text-[color:var(--accent)]">
                                                    you
                                                </span>
                                                <div class="text-xs">{{ new Date(c.created_at).toLocaleString() }}</div>
                                            </div>

                                            <button
                                                v-if="isCurrentUser(c.user?.id)"
                                                type="button"
                                                :disabled="isBusy"
                                                @click="confirmDeleteComment() && router.delete(route('tasks.comments.destroy', [selectedTask.id, c.id]), { preserveScroll: true })"
                                                class="shrink-0 text-xs font-semibold text-rose-700 hover:text-rose-900 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                Delete
                                            </button>
                                        </div>

                                        <div class="whitespace-pre-wrap break-words text-sm leading-6 text-[color:var(--ink)] [overflow-wrap:anywhere]">{{ c.body }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

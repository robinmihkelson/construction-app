<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>

<script setup>
import { useForm, router, Link, usePage } from '@inertiajs/vue3'
import { computed, nextTick, ref, onMounted, onBeforeUnmount } from 'vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import { useT } from '@/i18n/useT'

const { t } = useT()

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
const selectedTaskId = ref(null)
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
    if (!window.confirm(t('projects.confirm_delete_task'))) return
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
    if (status === 'doing') return 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30'
    if (status === 'done')  return 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30'
    return 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-500/15 dark:text-slate-200 dark:ring-slate-500/30'
}

function statusLabel(status) {
    if (status === 'todo')  return t('status.todo')
    if (status === 'doing') return t('status.doing')
    if (status === 'done')  return t('status.done')
    return status
}

function openTask(taskId) {
    selectedTaskId.value = taskId
    taskCommentForm.reset('body')
    progressImageForm.reset('images')
    isEditingDescription.value = false
    descriptionError.value = ''
    const url = new URL(window.location.href)
    url.searchParams.set('task', taskId)
    window.history.replaceState({}, '', url)
}

function closeTask() {
    selectedTaskId.value = null
    taskCommentForm.reset('body')
    progressImageForm.reset('images')
    isEditingDescription.value = false
    descriptionError.value = ''
    const url = new URL(window.location.href)
    url.searchParams.delete('task')
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
    if (!window.confirm(t('projects.confirm_delete_image'))) return
    router.delete(route('tasks.progress-images.destroy', [selectedTask.value.id, imageId]), { preserveScroll: true })
}

function removeMember(memberId) {
    if (!window.confirm(t('projects.confirm_remove_member'))) return
    router.delete(route('projects.members.destroy', [props.project.id, memberId]), { preserveScroll: true })
}

function deleteComment(commentId) {
    if (!selectedTask.value) return
    if (!window.confirm(t('projects.confirm_delete_comment'))) return
    router.delete(route('tasks.comments.destroy', [selectedTask.value.id, commentId]), { preserveScroll: true })
}

const isRenaming = ref(false)
const renameValue = ref('')
const renameError = ref('')
const isSavingRename = ref(false)
const renameInputEl = ref(null)

const isEditingDescription = ref(false)
const descriptionValue = ref('')
const descriptionError = ref('')
const isSavingDescription = ref(false)
const descriptionInputEl = ref(null)

async function startEditDescription() {
    if (!selectedTask.value) return
    isEditingDescription.value = true
    descriptionValue.value = selectedTask.value.description ?? ''
    descriptionError.value = ''
    await nextTick()
    if (descriptionInputEl.value) {
        descriptionInputEl.value.focus()
    }
}

function cancelEditDescription() {
    isEditingDescription.value = false
    descriptionValue.value = ''
    descriptionError.value = ''
}

function saveDescription() {
    if (!selectedTask.value) return
    const next = descriptionValue.value
    const previous = selectedTask.value.description ?? ''
    if (next === previous) {
        cancelEditDescription()
        return
    }

    isSavingDescription.value = true
    router.patch(
        route('tasks.update', selectedTask.value.id),
        { description: next },
        {
            preserveScroll: true,
            onSuccess: () => cancelEditDescription(),
            onError: (errors) => { descriptionError.value = errors.description || t('common.could_not_save') },
            onFinish: () => { isSavingDescription.value = false },
        }
    )
}

async function startRename() {
    isRenaming.value = true
    renameValue.value = props.project?.name ?? ''
    renameError.value = ''
    await nextTick()
    if (renameInputEl.value) {
        renameInputEl.value.focus()
        renameInputEl.value.select()
    }
}

function cancelRename() {
    isRenaming.value = false
    renameValue.value = ''
    renameError.value = ''
}

function saveRename() {
    const name = renameValue.value.trim()
    if (!name) {
        renameError.value = t('projects.name_required')
        return
    }
    if (name === props.project.name) {
        cancelRename()
        return
    }

    isSavingRename.value = true
    router.patch(
        route('projects.update', props.project.id),
        { name },
        {
            preserveScroll: true,
            onSuccess: () => cancelRename(),
            onError: (errors) => { renameError.value = errors.name || t('common.could_not_save') },
            onFinish: () => { isSavingRename.value = false },
        }
    )
}
</script>

<template>
    <div class="space-y-5">

        <div class="app-panel p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="app-label">{{ t('projects.label_single') }}</div>

                    <div v-if="!isRenaming" class="mt-1 flex items-start gap-2">
                        <h1 class="break-words text-2xl font-bold text-[var(--ink)] [overflow-wrap:anywhere]">
                            {{ project.name }}
                        </h1>
                        <button
                            v-if="can?.editProject"
                            type="button"
                            @click="startRename"
                            class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-transparent text-[var(--slate-soft)] transition hover:border-[var(--line)] hover:bg-[var(--panel-strong)] hover:text-[var(--ink)] focus-visible:opacity-100"
                            :aria-label="t('projects.rename')"
                            :title="t('projects.rename')"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                    </div>

                    <div v-else class="mt-1 space-y-2">
                        <input
                            ref="renameInputEl"
                            v-model="renameValue"
                            @keydown.enter.prevent="saveRename"
                            @keydown.esc.prevent="cancelRename"
                            :disabled="isSavingRename"
                            maxlength="255"
                            class="app-input w-full text-2xl font-bold"
                        />
                        <div v-if="renameError" class="text-xs text-rose-600">{{ renameError }}</div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="saveRename"
                                :disabled="isSavingRename"
                                class="rounded-lg bg-[var(--accent)] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[var(--accent-deep)] disabled:opacity-50"
                            >
                                {{ isSavingRename ? t('common.saving') : t('common.save') }}
                            </button>
                            <button
                                type="button"
                                @click="cancelRename"
                                :disabled="isSavingRename"
                                class="rounded-lg border border-[var(--line)] bg-white px-3 py-1.5 text-xs font-medium text-[var(--slate)] transition hover:bg-[var(--panel-muted)] disabled:opacity-50"
                            >
                                {{ t('common.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="app-panel-muted px-3 py-2 text-xs font-semibold text-[var(--slate-soft)]">
                        {{ tasks.length }} {{ t('projects.tasks') }}
                    </span>
                    <span class="app-panel-muted px-3 py-2 text-xs font-semibold text-[var(--slate-soft)]">
                        {{ members.length }} {{ t('projects.team') }}
                    </span>
                    <Link :href="route('projects.index')" class="app-button-secondary gap-1.5">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ t('projects.title') }}
                    </Link>
                </div>
            </div>

            <div class="mt-4 grid gap-2.5 sm:grid-cols-3">
                <div
                    v-for="status in statusOptions"
                    :key="status"
                    class="flex items-center justify-between rounded-lg border border-[var(--line)] px-4 py-3"
                    :class="status === 'done' ? 'bg-emerald-50 border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/30' : status === 'doing' ? 'bg-amber-50 border-amber-100 dark:bg-amber-500/10 dark:border-amber-500/30' : 'bg-[var(--panel-strong)]'"
                >
                    <span class="text-sm font-medium text-[var(--slate)]">{{ statusLabel(status) }}</span>
                    <span class="text-lg font-bold text-[var(--ink)]">{{ statusCounts[status] }}</span>
                </div>
            </div>
        </div>

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
                {{ t('common.syncing') }}
            </div>
        </Transition>

        <div class="grid min-w-0 gap-5 xl:grid-cols-[300px_minmax(0,1fr)]">

            <aside class="order-2 min-w-0 space-y-5 xl:order-none xl:sticky xl:top-20 xl:self-start">

                <div v-if="can.manageTasks" class="app-panel p-4">
                    <h2 class="app-section-title mb-0.5">{{ t('projects.add_task_title') }}</h2>
                    <p class="mb-4 text-xs text-[var(--slate-soft)]">{{ t('projects.add_task_desc') }}</p>
                    <form
                        @submit.prevent="form.post(route('projects.tasks.store', project.id), {
                            preserveScroll: true,
                            onSuccess: () => form.reset('title', 'assigned_to', 'due_date'),
                        })"
                        class="space-y-2.5"
                    >
                        <input v-model="form.title" class="app-input" :placeholder="t('projects.task_title_placeholder')" />
                        <select v-model="form.assigned_to" class="app-input">
                            <option value="">{{ t('common.unassigned') }}</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <input v-model="form.due_date" type="date" class="app-input" />
                        <button class="app-button-primary w-full">{{ t('projects.add_task_title') }}</button>
                    </form>
                </div>

                <div class="app-panel p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="app-section-title">{{ t('projects.team') }}</h2>
                            <p v-if="!can.manageMembers" class="mt-0.5 text-xs text-[var(--slate-soft)]">
                                {{ t('projects.team_desc') }}
                            </p>
                        </div>
                        <span class="rounded-full bg-[var(--panel-muted)] px-2.5 py-1 text-xs font-bold text-[var(--slate)]">
                            {{ members.length }}
                        </span>
                    </div>

                    <div v-if="members.length === 0" class="rounded-lg border border-dashed border-[var(--line)] px-3 py-4 text-center text-sm text-[var(--slate-soft)]">
                        {{ t('projects.team_empty') }}
                    </div>

                    <ul v-else class="space-y-2">
                        <li
                            v-for="m in members"
                            :key="m.id"
                            class="rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] p-3"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <UserAvatar
                                        :url="m.avatar_url ?? null"
                                        :name="m.name"
                                        :seed="m.id"
                                        :size="28"
                                        :accent="isCurrentUser(m.id)"
                                    />
                                    <div class="min-w-0">
                                        <div class="truncate text-xs font-semibold text-[var(--ink)]">{{ m.name }}</div>
                                        <div class="text-[0.64rem] font-semibold capitalize text-[var(--accent)]">
                                            {{ t(`projects.role_${m.pivot?.role ?? 'member'}`) }}
                                        </div>
                                    </div>
                                </div>
                                <span v-if="isCurrentUser(m.id)" class="shrink-0 text-[0.64rem] font-bold text-[var(--slate-soft)]">{{ t('common.you') }}</span>
                                <button
                                    v-else-if="can.manageMembers"
                                    type="button"
                                    @click.prevent.stop="removeMember(m.id)"
                                    class="shrink-0 text-[0.64rem] font-semibold text-rose-600 transition hover:text-rose-800"
                                >
                                    {{ t('common.remove') }}
                                </button>
                            </div>
                            <select
                                v-if="can.manageMembers"
                                class="app-input mt-2 py-1.5 text-xs"
                                :value="m.pivot.role ?? 'member'"
                                :disabled="isCurrentUser(m.id)"
                                @change="updateMemberRole(m.id, $event.target.value)"
                            >
                                <option value="member">{{ t('projects.role_member') }}</option>
                                <option value="client">{{ t('projects.role_client') }}</option>
                                <option value="worker">{{ t('projects.role_worker') }}</option>
                                <option value="office">{{ t('projects.role_office') }}</option>
                            </select>
                        </li>
                    </ul>

                    <form
                        v-if="can.manageMembers"
                        @submit.prevent="memberForm.post(route('projects.members.store', project.id))"
                        class="mt-4 space-y-2.5 border-t border-[var(--line)] pt-4"
                    >
                        <select v-model="memberForm.user_id" class="app-input">
                            <option value="">{{ t('projects.select_user') }}</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <select v-model="memberForm.role" class="app-input">
                            <option value="">{{ t('projects.role_member') }}</option>
                            <option value="client">{{ t('projects.role_client') }}</option>
                            <option value="worker">{{ t('projects.role_worker') }}</option>
                            <option value="office">{{ t('projects.role_office') }}</option>
                        </select>
                        <button class="app-button-secondary w-full">{{ t('projects.add_member') }}</button>
                    </form>
                </div>
            </aside>

            <div class="order-1 min-w-0 space-y-5 xl:order-none">

                <div class="app-panel overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-[var(--line)] px-5 py-3.5">
                        <div>
                            <h2 class="app-section-title">{{ t('projects.tasks') }}</h2>
                            <p class="text-xs text-[var(--slate-soft)]">{{ t('projects.tasks_desc') }}</p>
                        </div>
                        <span class="rounded-full bg-[var(--panel-muted)] px-2.5 py-1 text-xs font-bold text-[var(--slate)]">{{ tasks.length }}</span>
                    </div>

                    <div v-if="tasks.length === 0" class="flex flex-col items-center gap-3 px-5 py-12 text-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--panel-muted)]">
                            <svg class="h-5 w-5 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="text-sm text-[var(--slate-soft)]">{{ t('projects.empty_tasks') }}</div>
                    </div>

                    <div v-else class="divide-y divide-[var(--line)]">
                        <div
                            v-for="task in tasks"
                            :key="task.id"
                            role="button"
                            tabindex="0"
                            @click="openTask(task.id)"
                            @keydown.enter.prevent="openTask(task.id)"
                            @keydown.space.prevent="openTask(task.id)"
                            class="grid min-w-0 cursor-pointer gap-3 px-5 py-3.5 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--accent)] hover:bg-[var(--panel-strong)] sm:grid-cols-[5rem_minmax(0,1fr)_auto] sm:items-center"
                            :class="selectedTask?.id === task.id ? 'bg-blue-50/60 dark:bg-blue-500/10' : ''"
                        >
                            <span
                                class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                :class="statusBadgeClass(task.status)"
                            >
                                {{ statusLabel(task.status) }}
                            </span>

                            <div class="min-w-0">
                                <div class="break-words text-sm font-semibold text-[var(--ink)] [overflow-wrap:anywhere]">{{ task.title }}</div>
                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-[var(--slate-soft)]">
                                    <span>{{ task.assignee ? task.assignee.name : t('common.unassigned') }}</span>
                                    <span v-if="task.due_date">{{ t('calendar.due') }} {{ task.due_date }}</span>
                                    <span>{{ task.comments?.length ?? 0 }}</span>
                                    <span>{{ task.progress_images?.length ?? 0 }}</span>
                                </div>
                            </div>

                            <div class="text-xs font-semibold" :class="selectedTask?.id === task.id ? 'text-[var(--accent)]' : 'text-[var(--slate-soft)]'">
                                {{ selectedTask?.id === task.id ? t('common.selected') : t('common.open') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="app-panel overflow-hidden">
                    <div v-if="!selectedTask" class="flex flex-col items-center gap-3 px-5 py-12 text-center">
                        <div class="text-sm text-[var(--slate-soft)]">{{ t('projects.task_details_empty') }}</div>
                    </div>

                    <div v-else>
                        <div class="border-b border-[var(--line)] px-5 py-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="statusBadgeClass(selectedTask.status)">
                                            {{ statusLabel(selectedTask.status) }}
                                        </span>
                                        <span class="text-xs text-[var(--slate-soft)]">{{ t('projects.task_details') }}</span>
                                    </div>
                                    <h2 class="mt-2 break-words text-lg font-bold text-[var(--ink)] [overflow-wrap:anywhere]">
                                        {{ selectedTask.title }}
                                    </h2>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <button
                                        v-if="can.manageTasks"
                                        type="button"
                                        @click.prevent.stop="deleteTask(selectedTask.id)"
                                        class="app-button-danger w-fit"
                                    >
                                        {{ t('projects.delete_task') }}
                                    </button>
                                    <button
                                        type="button"
                                        @click.prevent.stop="closeTask"
                                        :aria-label="t('projects.close_task')"
                                        :title="t('projects.close_task')"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                                <span class="app-panel-muted px-3 py-1.5 text-xs text-[var(--slate-soft)]">
                                    {{ t('projects.assignee_label') }} <strong class="font-semibold text-[var(--ink)]">{{ selectedTask.assignee?.name ?? t('common.unassigned') }}</strong>
                                </span>
                                <span class="app-panel-muted px-3 py-1.5 text-xs text-[var(--slate-soft)]">
                                    {{ t('projects.due_label') }} <strong class="font-semibold text-[var(--ink)]">{{ selectedTask.due_date ?? t('common.none') }}</strong>
                                </span>
                            </div>

                            <div class="mt-4">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <div class="app-label">{{ t('projects.description') }}</div>
                                    <button
                                        v-if="can.manageTasks && !isEditingDescription"
                                        type="button"
                                        @click.prevent.stop="startEditDescription"
                                        class="text-xs font-semibold text-[var(--accent)] transition hover:text-[var(--accent-deep)]"
                                    >
                                        {{ selectedTask.description ? t('common.edit') : t('projects.add_description') }}
                                    </button>
                                </div>

                                <div v-if="!isEditingDescription">
                                    <p
                                        v-if="selectedTask.description"
                                        class="whitespace-pre-wrap break-words rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] px-3 py-2.5 text-sm text-[var(--slate)] [overflow-wrap:anywhere]"
                                    >{{ selectedTask.description }}</p>
                                    <p v-else class="rounded-lg border border-dashed border-[var(--line)] px-3 py-2.5 text-xs italic text-[var(--slate-soft)]">
                                        {{ t('projects.no_description') }}
                                    </p>
                                </div>

                                <div v-else class="space-y-2">
                                    <textarea
                                        ref="descriptionInputEl"
                                        v-model="descriptionValue"
                                        :disabled="isSavingDescription"
                                        rows="4"
                                        maxlength="10000"
                                        :placeholder="t('projects.description_placeholder')"
                                        class="app-input w-full text-sm"
                                    />
                                    <div v-if="descriptionError" class="text-xs text-rose-600">{{ descriptionError }}</div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click.prevent.stop="saveDescription"
                                            :disabled="isSavingDescription"
                                            class="rounded-lg bg-[var(--accent)] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[var(--accent-deep)] disabled:opacity-50"
                                        >
                                            {{ isSavingDescription ? t('common.saving') : t('common.save') }}
                                        </button>
                                        <button
                                            type="button"
                                            @click.prevent.stop="cancelEditDescription"
                                            :disabled="isSavingDescription"
                                            class="rounded-lg border border-[var(--line)] bg-white px-3 py-1.5 text-xs font-medium text-[var(--slate)] transition hover:bg-[var(--panel-muted)] disabled:opacity-50"
                                        >
                                            {{ t('common.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="can.changeTaskStatus" class="mt-4">
                                <div class="app-label mb-2">{{ t('projects.change_status') }}</div>
                                <div class="inline-flex flex-wrap rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] p-1 gap-0.5">
                                    <button
                                        v-for="status in statusOptions"
                                        :key="status"
                                        type="button"
                                        @click.prevent.stop="setStatus(selectedTask.id, status)"
                                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                                        :class="selectedTask.status === status
                                            ? 'bg-[var(--panel-bg)] text-[var(--accent)] shadow-sm'
                                            : 'text-[var(--slate-soft)] hover:text-[var(--ink)]'"
                                    >
                                        {{ statusLabel(status) }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-0 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">

                            <div class="border-b border-[var(--line)] px-5 py-4 lg:border-b-0 lg:border-r">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <h3 class="app-section-title">{{ t('projects.progress_photos') }}</h3>
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
                                        {{ progressImageForm.processing ? t('avatar.uploading') : t('projects.upload_photos') }}
                                    </button>
                                </form>

                                <div v-if="(selectedTask.progress_images?.length ?? 0) === 0" class="mt-4 rounded-lg border border-dashed border-[var(--line)] px-3 py-6 text-center text-xs text-[var(--slate-soft)]">
                                    {{ t('projects.no_photos') }}
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
                                            <div>{{ image.user?.name ?? t('common.unknown') }} · {{ new Date(image.created_at).toLocaleDateString() }}</div>
                                            <button
                                                v-if="isCurrentUser(image.user?.id) || can.manageTasks"
                                                type="button"
                                                @click="deleteProgressImage(image.id)"
                                                class="font-semibold text-rose-600 transition hover:text-rose-800"
                                            >
                                                {{ t('common.delete') }}
                                            </button>
                                        </figcaption>
                                    </figure>
                                </div>
                            </div>

                            <div class="px-5 py-4">
                                <h3 class="app-section-title mb-3">{{ t('projects.task_comments') }}</h3>

                                <form @submit.prevent="submitTaskComment" class="space-y-2.5">
                                    <textarea
                                        v-model="taskCommentForm.body"
                                        class="app-input min-h-24 resize-y"
                                        rows="3"
                                        :placeholder="t('projects.comment_placeholder')"
                                    />
                                    <button
                                        type="submit"
                                        :disabled="taskCommentForm.processing"
                                        class="app-button-primary ml-auto disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{ taskCommentForm.processing ? t('common.sending') : t('projects.send_comment') }}
                                    </button>
                                </form>

                                <div v-if="(selectedTask.comments?.length ?? 0) === 0" class="mt-5 rounded-lg border border-dashed border-[var(--line)] px-3 py-6 text-center text-xs text-[var(--slate-soft)]">
                                    {{ t('projects.no_comments') }}
                                </div>

                                <div v-else class="mt-5 divide-y divide-[var(--line)] border-t border-[var(--line)]">
                                    <div v-for="c in selectedTask.comments" :key="c.id" class="py-4">
                                        <div class="flex items-start gap-3">
                                            <UserAvatar
                                                :url="c.user?.avatar_url ?? null"
                                                :name="c.user?.name ?? ''"
                                                :seed="c.user?.id"
                                                :size="32"
                                                :accent="isCurrentUser(c.user?.id)"
                                            />
                                            <div class="min-w-0 flex-1">
                                                <div class="mb-2 flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <span class="text-xs font-semibold text-[var(--ink)]">{{ c.user?.name ?? t('common.unknown') }}</span>
                                                        <span v-if="isCurrentUser(c.user?.id)" class="ml-1 text-[0.65rem] font-bold text-[var(--accent)]">{{ t('common.you_inline') }}</span>
                                                        <div class="text-[0.65rem] text-[var(--slate-soft)]">{{ new Date(c.created_at).toLocaleString() }}</div>
                                                    </div>
                                                    <button
                                                        v-if="isCurrentUser(c.user?.id)"
                                                        type="button"
                                                        :disabled="isBusy"
                                                        @click="deleteComment(c.id)"
                                                        class="shrink-0 text-xs font-semibold text-rose-600 transition hover:text-rose-800 disabled:opacity-50"
                                                    >
                                                        {{ t('common.delete') }}
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
        </div>
    </div>
</template>

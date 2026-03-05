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
    comments: Array,
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

const commentForm = useForm({ body: '' })

const isBusy = ref(false)

let removeStart, removeFinish

onMounted(() => {
    removeStart = router.on('start', () => { isBusy.value = true })
    removeFinish = router.on('finish', () => { isBusy.value = false })
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
    router.patch(route('tasks.update', taskId), { status })
}
</script>

<template>
    <div class="space-y-6">
        <section
            class="rounded-3xl border border-[color:var(--line)] bg-white/90 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.05)]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-sm font-medium text-[color:var(--slate-soft)]">
                        Project
                    </div>
                    <h1 class="mt-2 text-3xl font-semibold text-[color:var(--ink)]">{{ project.name }}</h1>
                    <p class="mt-2 text-sm text-[color:var(--slate-soft)]">Status: {{ project.status }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        :href="route('projects.index')"
                        class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-sm font-medium text-[color:var(--slate)] transition hover:bg-[color:var(--panel-muted)]"
                    >
                        Back to projects
                    </Link>
                    <div class="rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-muted)] px-4 py-3 text-sm text-[color:var(--slate-soft)]">
                        {{ tasks.length }} task{{ tasks.length === 1 ? '' : 's' }} · {{ members.length }} member{{ members.length === 1 ? '' : 's' }}
                    </div>
                </div>
            </div>
        </section>

        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isBusy"
                class="rounded-2xl border border-[color:var(--line)] bg-white/80 px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">
                Syncing project updates...
            </div>
        </transition>

        <div class="grid gap-6 xl:grid-cols-[1.05fr_1.45fr]">
            <div class="space-y-6">
                <section class="rounded-[2rem] border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-5 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-xl font-semibold text-[color:var(--ink)]">Project members</h2>
                        <span class="rounded-full border border-[color:var(--line)] bg-white px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)]">
                            {{ members.length }}
                        </span>
                    </div>

                    <p v-if="!can.manageMembers" class="mb-4 text-sm text-[color:var(--slate-soft)]">
                        Only office users can add or remove members.
                    </p>

                    <div class="mb-4 text-sm text-[color:var(--slate-soft)]">
                        <div v-if="members.length === 0" class="rounded-2xl border border-dashed border-[color:var(--line)] bg-white/70 px-4 py-5">
                            No members yet.
                        </div>

                        <ul v-else class="space-y-3">
                            <li
                                v-for="m in members"
                                :key="m.id"
                                class="rounded-[1.5rem] border border-[color:var(--line)] bg-white/85 p-4 shadow-sm"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-base font-semibold text-[color:var(--ink)]">
                                            {{ m.name }}
                                        </div>
                                        <div class="text-[0.72rem] font-semibold uppercase tracking-[0.2em] text-[color:var(--accent)]">
                                            {{ m.pivot?.role ?? 'member' }}
                                        </div>
                                    </div>

                                    <div v-if="can.manageMembers" class="flex items-center gap-2 shrink-0">
                                        <select
                                            class="min-w-[7rem] rounded-xl border border-[color:var(--line)] bg-[color:var(--panel-strong)] px-3 py-2 text-sm text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                                            :value="m.pivot.role ?? 'member'"
                                            :disabled="isCurrentUser(m.id)"
                                            @change="updateMemberRole(m.id, $event.target.value)"
                                        >
                                            <option value="member">member</option>
                                            <option value="client">client</option>
                                            <option value="worker">worker</option>
                                            <option value="office">office</option>
                                        </select>

                                        <button
                                            v-if="!isCurrentUser(m.id)"
                                            type="button"
                                            @click.prevent.stop="
                                                confirmRemoveMember() && route('projects.members.destroy', [project.id, m.id]) &&
                                                router.delete(route('projects.members.destroy', [project.id, m.id]), { preserveScroll: true })
                                            "
                                            class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-rose-700 transition hover:bg-rose-100"
                                        >
                                            Remove
                                        </button>

                                        <span v-else class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">
                                            You
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <form
                        v-if="can.manageMembers"
                        @submit.prevent="memberForm.post(route('projects.members.store', project.id))"
                        class="grid gap-3"
                    >
                        <select
                            v-model="memberForm.user_id"
                            class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                        >
                            <option value="">Select user</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>

                        <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                            <select
                                v-model="memberForm.role"
                                class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                            >
                                <option value="">member</option>
                                <option value="client">client</option>
                                <option value="worker">worker</option>
                                <option value="office">office</option>
                            </select>

                            <button class="rounded-2xl bg-[color:var(--accent)] px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[color:var(--accent-deep)]">
                                Add member
                            </button>
                        </div>
                    </form>
                </section>

                <section
                    v-if="can.manageTasks"
                    class="rounded-[2rem] border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-5 shadow-[0_18px_50px_rgba(31,41,51,0.08)]"
                >
                    <h2 class="mb-4 text-xl font-semibold text-[color:var(--ink)]">Add task</h2>

                    <form @submit.prevent="form.post(route('projects.tasks.store', project.id))" class="grid gap-3">
                        <input
                            v-model="form.title"
                            class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                            placeholder="Task title"
                        />

                        <div class="grid gap-3 sm:grid-cols-2">
                            <select
                                v-model="form.assigned_to"
                                class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                            >
                                <option value="">Unassigned</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">
                                    {{ u.name }}
                                </option>
                            </select>

                            <input
                                v-model="form.due_date"
                                type="date"
                                class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                            />
                        </div>

                        <div class="flex justify-end">
                            <button class="rounded-2xl bg-[color:var(--accent)] px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[color:var(--accent-deep)]">
                                Add task
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <div class="space-y-6">
                <section class="overflow-hidden rounded-[2rem] border border-[color:var(--line)] bg-white/90 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
                    <div class="border-b border-[color:var(--line)] bg-[color:var(--panel-bg)] px-5 py-4">
                        <h2 class="text-xl font-semibold text-[color:var(--ink)]">Tasks</h2>
                    </div>

                    <div v-if="tasks.length === 0" class="px-5 py-6 text-sm text-[color:var(--slate-soft)]">
                        No tasks yet.
                    </div>

                    <div v-else class="divide-y divide-[color:var(--line)]">
                        <div v-for="t in tasks" :key="t.id" class="px-5 py-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="truncate text-base font-semibold text-[color:var(--ink)]">{{ t.title }}</div>
                                    <div class="text-sm text-[color:var(--slate-soft)]">
                                        {{ t.assignee ? `Assigned to: ${t.assignee.name}` : 'Unassigned' }}
                                        <span v-if="t.due_date"> · Due: {{ t.due_date }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.18em]"
                                        :class="{
                                            'bg-stone-200 text-stone-800': t.status === 'todo',
                                            'bg-amber-200 text-amber-900': t.status === 'doing',
                                            'bg-emerald-200 text-emerald-900': t.status === 'done',
                                        }"
                                    >
                                        {{ t.status }}
                                    </span>

                                    <div v-if="can.changeTaskStatus" class="flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            v-for="status in ['todo', 'doing', 'done']"
                                            :key="status"
                                            @click.prevent.stop="setStatus(t.id, status)"
                                            class="rounded-xl border px-3 py-2 text-[0.68rem] font-semibold uppercase tracking-[0.16em] transition"
                                            :class="t.status === status
                                                ? 'border-[color:var(--slate)] bg-[color:var(--slate)] text-white'
                                                : 'border-[color:var(--line)] bg-white text-[color:var(--slate-soft)] hover:bg-[color:var(--panel-strong)]'"
                                        >
                                            {{ status }}
                                        </button>

                                        <button
                                            v-if="can.manageTasks"
                                            type="button"
                                            @click.prevent.stop="deleteTask(t.id)"
                                            class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-rose-700 transition hover:bg-rose-100"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[2rem] border border-[color:var(--line)] bg-white/90 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
                    <div class="border-b border-[color:var(--line)] bg-[color:var(--panel-bg)] px-5 py-4">
                        <h2 class="text-xl font-semibold text-[color:var(--ink)]">Comments</h2>
                    </div>

                    <div class="border-b border-[color:var(--line)] px-5 py-4">
                        <form @submit.prevent="commentForm.post(route('projects.comments.store', project.id), {
                            preserveScroll: true,
                            onSuccess: () => commentForm.reset('body'),
                        })" class="grid gap-3">
                            <textarea
                                v-model="commentForm.body"
                                class="rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-3 text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                                rows="3"
                                placeholder="Write a comment..."
                            ></textarea>
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="commentForm.processing"
                                    class="rounded-2xl bg-[color:var(--slate)] px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{ commentForm.processing ? 'Sending…' : 'Send' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-if="comments.length === 0" class="px-5 py-6 text-sm text-[color:var(--slate-soft)]">
                        No comments yet.
                    </div>

                    <div v-else class="divide-y divide-[color:var(--line)]">
                        <div v-for="c in comments" :key="c.id" class="px-5 py-4">
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm text-[color:var(--slate-soft)]">
                                <div>
                                    <span class="font-semibold text-[color:var(--ink)]">{{ c.user?.name ?? 'Unknown' }}</span>
                                    <span v-if="isCurrentUser(c.user?.id)" class="text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-[color:var(--accent)]">
                                        you
                                    </span>
                                    <span> · {{ new Date(c.created_at).toLocaleString() }}</span>
                                </div>

                                <button
                                    v-if="isCurrentUser(c.user?.id)"
                                    type="button"
                                    :disabled="isBusy"
                                    @click="confirmDeleteComment() && router.delete(route('projects.comments.destroy', [project.id, c.id]), { preserveScroll: true })"
                                    class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Delete
                                </button>
                            </div>

                            <div class="whitespace-pre-wrap text-sm text-[color:var(--ink)]">{{ c.body }}</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

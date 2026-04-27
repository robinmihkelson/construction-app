<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>

<script setup>
import { nextTick, watch } from 'vue'
import { onBeforeUnmount, onMounted, computed, ref } from 'vue'
import { Link, useForm, router, usePage } from '@inertiajs/vue3'

const currentUser = computed(() => usePage().props.auth?.user ?? null)

const props = defineProps({
    project: Object,
    messages: Array,
    has_more: Boolean,
    last_read_at: String,
})

const lastReadAtDate = computed(() => props.last_read_at ? new Date(props.last_read_at) : null)

function isNewMessage(msg) {
    if (!lastReadAtDate.value) return false
    return new Date(msg.created_at) > lastReadAtDate.value
}

const messagesLocal = ref([...props.messages])
const hasMore = ref(!!props.has_more)
const loadingOlder = ref(false)
const fileInput = ref(null)

const form = useForm({ body: '', attachments: [] })
const messagesBox = ref(null)
const shownNewDivider = ref(false)

watch(() => messagesLocal.value.length, () => { shownNewDivider.value = false })

function isNearBottom(el) {
    if (!el) return true
    return el.scrollHeight - el.scrollTop - el.clientHeight < 120
}

async function scrollToBottom() {
    await nextTick()
    const el = messagesBox.value
    if (!el) return
    el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' })
}

const canSend = computed(() => {
    const hasText = form.body?.trim().length > 0
    const hasFiles = (form.attachments?.length ?? 0) > 0
    return hasText || hasFiles
})

function send() {
    if (!canSend.value) return
    form.post(route('chat.messages.store', props.project.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: async () => {
            form.reset('body', 'attachments')
            if (fileInput.value) fileInput.value.value = ''
            await scrollToBottom()
        },
    })
}

function deleteMessage(messageId) {
    if (!window.confirm('Delete this message?')) return
    const previousMessages = [...messagesLocal.value]
    messagesLocal.value = messagesLocal.value.filter((m) => m.id !== messageId)
    router.delete(route('chat.messages.destroy', [props.project.id, messageId]), {
        preserveScroll: true,
        onError: () => { messagesLocal.value = previousMessages },
        onCancel: () => { messagesLocal.value = previousMessages },
    })
}

async function loadOlder() {
    if (loadingOlder.value || !hasMore.value || messagesLocal.value.length === 0) return
    loadingOlder.value = true
    const el = messagesBox.value
    const prevScrollHeight = el?.scrollHeight ?? 0
    const prevScrollTop = el?.scrollTop ?? 0
    const firstId = messagesLocal.value[0].id
    const res = await fetch(`/chat/${props.project.id}/messages?before_id=${firstId}&limit=30`, {
        headers: { Accept: 'application/json' },
    })
    if (!res.ok) { loadingOlder.value = false; return }
    const data = await res.json()
    const existing = new Set(messagesLocal.value.map((m) => m.id))
    const older = data.messages.filter((m) => !existing.has(m.id))
    messagesLocal.value = [...older, ...messagesLocal.value]
    hasMore.value = !!data.has_more
    await nextTick()
    if (el) el.scrollTop = prevScrollTop + (el.scrollHeight - prevScrollHeight)
    loadingOlder.value = false
}

watch(
    () => messagesLocal.value.length,
    async () => { if (isNearBottom(messagesBox.value)) await scrollToBottom() },
)

function dateKey(d) { return new Date(d).toISOString().slice(0, 10) }
function labelForDateKey(key) {
    const today = new Date().toISOString().slice(0, 10)
    const yesterday = new Date(Date.now() - 86400000).toISOString().slice(0, 10)
    if (key === today) return 'Today'
    if (key === yesterday) return 'Yesterday'
    return key
}

function isMine(msg) {
    return currentUser.value && msg.user_id === currentUser.value.id
}

let timer = null
onMounted(() => {
    timer = setInterval(() => {
        fetch(route('chat.messages.index', { project: props.project.id, before_id: null, limit: 20 }), {
            headers: { Accept: 'application/json' },
        }).then(async (res) => {
            if (!res.ok) return
            const data = await res.json()
            const newOnes = data.messages.filter((m) => !messagesLocal.value.some((e) => e.id === m.id))
            if (newOnes.length) messagesLocal.value.push(...newOnes)
        })
    }, 4000)
    scrollToBottom()
})
onBeforeUnmount(() => timer && clearInterval(timer))
</script>

<template>
    <div class="flex flex-col gap-4">

        <!-- Chat header -->
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="app-label">Chat</div>
                <h1 class="mt-0.5 truncate text-2xl font-bold text-[var(--ink)]">{{ project.name }}</h1>
                <div class="mt-0.5 text-sm text-[var(--slate-soft)]">Project conversation</div>
            </div>
            <Link :href="route('chat.index')" class="app-button-secondary shrink-0 gap-1.5">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Channels
            </Link>
        </div>

        <!-- Messages area -->
        <div
            ref="messagesBox"
            class="app-panel flex h-[62vh] min-h-64 flex-col gap-3 overflow-y-auto p-4"
        >
            <!-- Load older -->
            <div v-if="hasMore" class="flex justify-center pb-1">
                <button
                    @click="loadOlder"
                    :disabled="loadingOlder"
                    class="rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] px-4 py-2 text-xs font-semibold text-[var(--slate)] transition hover:bg-white disabled:opacity-50"
                >
                    {{ loadingOlder ? 'Loading…' : 'Load older messages' }}
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="messagesLocal.length === 0" class="flex flex-1 flex-col items-center justify-center gap-3 text-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--panel-muted)]">
                    <svg class="h-5 w-5 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="text-sm text-[var(--slate-soft)]">No messages yet. Start the conversation!</div>
            </div>

            <template v-for="(m, i) in messagesLocal" :key="m.id">

                <!-- Date divider -->
                <div
                    v-if="i === 0 || dateKey(messagesLocal[i - 1].created_at) !== dateKey(m.created_at)"
                    class="flex items-center gap-2 py-1 text-xs text-[var(--slate-soft)]"
                >
                    <div class="flex-1 border-t border-[var(--line)]" />
                    <span>{{ labelForDateKey(dateKey(m.created_at)) }}</span>
                    <div class="flex-1 border-t border-[var(--line)]" />
                </div>

                <!-- New messages divider -->
                <div
                    v-if="!shownNewDivider && isNewMessage(m)"
                    class="flex items-center gap-2 py-1 text-xs text-[var(--accent)]"
                >
                    <div class="flex-1 border-t border-[var(--accent)]/40" />
                    <span class="font-semibold">New</span>
                    <div class="flex-1 border-t border-[var(--accent)]/40" />
                </div>

                <!-- Message bubble -->
                <div
                    class="flex gap-2.5"
                    :class="isMine(m) ? 'flex-row-reverse' : 'flex-row'"
                >
                    <!-- Avatar -->
                    <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[0.6rem] font-bold"
                        :class="isMine(m) ? 'bg-[var(--accent)] text-white' : 'bg-[var(--panel-muted)] text-[var(--slate)]'"
                    >
                        {{ (m.user?.name ?? '?').charAt(0).toUpperCase() }}
                    </div>

                    <!-- Bubble -->
                    <div class="max-w-[75%]" :class="isMine(m) ? 'items-end' : 'items-start'">
                        <div
                            class="rounded-2xl px-4 py-2.5 text-sm"
                            :class="isMine(m)
                                ? 'rounded-tr-sm bg-[var(--accent)] text-white'
                                : 'rounded-tl-sm border border-[var(--line)] bg-white text-[var(--ink)]'"
                        >
                            <!-- Sender name (others only) -->
                            <div v-if="!isMine(m)" class="mb-1 text-[0.65rem] font-bold text-[var(--accent)]">
                                {{ m.user?.name ?? 'Unknown' }}
                            </div>

                            <!-- Message body -->
                            <div class="whitespace-pre-wrap break-words leading-relaxed">{{ m.body }}</div>

                            <!-- Attachments -->
                            <div v-if="(m?.attachments?.length ?? 0) > 0" class="mt-2 space-y-1.5">
                                <div v-for="a in m.attachments" :key="a.id">
                                    <img
                                        v-if="(a?.mime ?? '').startsWith('image/')"
                                        :src="route('chat.attachments.view', [project.id, a.id])"
                                        class="max-w-full rounded-xl border border-white/20"
                                        alt=""
                                    />
                                    <a
                                        v-else
                                        :href="route('chat.attachments.download', [project.id, a.id])"
                                        class="underline underline-offset-2 text-xs opacity-90"
                                    >
                                        {{ a.original_name }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Meta: time + delete -->
                        <div
                            class="mt-0.5 flex items-center gap-2 px-1 text-[0.65rem] text-[var(--slate-soft)]"
                            :class="isMine(m) ? 'flex-row-reverse' : ''"
                        >
                            <span>{{ new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                            <button
                                v-if="isMine(m)"
                                type="button"
                                @click="deleteMessage(m.id)"
                                class="font-semibold text-rose-400 transition hover:text-rose-600"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <span v-if="!shownNewDivider && isNewMessage(m)" class="hidden">{{ (shownNewDivider = true) }}</span>
            </template>
        </div>

        <!-- Compose area -->
        <div class="app-panel p-4">
            <form @submit.prevent="send" class="space-y-3">
                <textarea
                    v-model="form.body"
                    class="app-input min-h-20 resize-none"
                    rows="3"
                    placeholder="Write a message… (Enter to add a new line)"
                />

                <!-- File upload row -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <input
                            ref="fileInput"
                            type="file"
                            multiple
                            class="block w-full text-xs text-[var(--slate-soft)] file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--panel-muted)] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[var(--slate)] file:transition file:hover:bg-[var(--panel-strong)]"
                            @change="e => form.attachments = Array.from(e.target.files)"
                        />
                        <div v-if="form.attachments?.length" class="mt-1.5 text-xs text-[var(--slate-soft)]">
                            {{ form.attachments.length }} file{{ form.attachments.length === 1 ? '' : 's' }} selected
                        </div>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing || !canSend"
                        class="app-button-primary shrink-0 gap-1.5 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        {{ form.processing ? 'Sending…' : 'Send' }}
                    </button>
                </div>

                <!-- Upload progress -->
                <div v-if="form.progress" class="space-y-1">
                    <div class="text-xs text-[var(--slate-soft)]">Uploading {{ form.progress.percentage }}%</div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-[var(--panel-muted)]">
                        <div class="h-1.5 bg-[var(--accent)] transition-all" :style="{ width: form.progress.percentage + '%' }" />
                    </div>
                </div>
            </form>
        </div>

    </div>
</template>

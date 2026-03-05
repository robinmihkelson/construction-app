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

const form = useForm({
  body: '',
  attachments: [],
})

const messagesBox = ref(null)

const shownNewDivider = ref(false)
watch(() => messagesLocal.value.length, () => { shownNewDivider.value = false })

function isNearBottom(el) {
  if (!el) return true
  const threshold = 120
  return el.scrollHeight - el.scrollTop - el.clientHeight < threshold
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


function confirmDeleteMessage() {
  return window.confirm('Delete this message?')
}

function deleteMessage(messageId) {
  if (!confirmDeleteMessage()) return

  const previousMessages = [...messagesLocal.value]
  messagesLocal.value = messagesLocal.value.filter((message) => message.id !== messageId)

  router.delete(route('chat.messages.destroy', [props.project.id, messageId]), {
    preserveScroll: true,
    onError: () => {
      messagesLocal.value = previousMessages
    },
    onCancel: () => {
      messagesLocal.value = previousMessages
    },
  })
}

async function loadOlder() {
  if (loadingOlder.value || !hasMore.value || messagesLocal.value.length === 0) return

  loadingOlder.value = true

  const el = messagesBox.value
  const prevScrollHeight = el?.scrollHeight ?? 0
  const prevScrollTop = el?.scrollTop ?? 0

  const firstId = messagesLocal.value[0].id

  const url = `/chat/${props.project.id}/messages?before_id=${firstId}&limit=30`

  const res = await fetch(url, { headers: { Accept: 'application/json' } })
  if (!res.ok) {
    loadingOlder.value = false
    return
  }

  const data = await res.json()

  const existing = new Set(messagesLocal.value.map((m) => m.id))
  const older = data.messages.filter((m) => !existing.has(m.id))

  messagesLocal.value = [...older, ...messagesLocal.value]
  hasMore.value = !!data.has_more

  await nextTick()
  if (el) {
    const newScrollHeight = el.scrollHeight
    el.scrollTop = prevScrollTop + (newScrollHeight - prevScrollHeight)
  }

  loadingOlder.value = false

}

watch(
  () => messagesLocal.value.length,
  async () => {
    const el = messagesBox.value
    if (isNearBottom(el)) {
      await scrollToBottom()
    }
  }
)

function dateKey(d) {
  const dt = new Date(d)
  return dt.toISOString().slice(0, 10) // YYYY-MM-DD
}
function labelForDateKey(key) {
  const today = new Date().toISOString().slice(0, 10)
  const yesterday = new Date(Date.now() - 86400000).toISOString().slice(0, 10)
  if (key === today) return 'Today'
  if (key === yesterday) return 'Yesterday'
  return key
}


let timer = null
onMounted(() => {
  timer = setInterval(() => {
    fetch(
      route('chat.messages.index', {
        project: props.project.id,
        before_id: null,
        limit: 20,
      }),
      { headers: { Accept: 'application/json' } }
    ).then(async res => {
      if (!res.ok) return
      const data = await res.json()

      const newOnes = data.messages.filter(
        m => !messagesLocal.value.some(e => e.id === m.id)
      )

      if (newOnes.length) {
        messagesLocal.value.push(...newOnes)
      }
    })
  }, 4000)
  scrollToBottom()
})
onBeforeUnmount(() => timer && clearInterval(timer))

</script>

<template>
  <div class="mx-auto max-w-5xl space-y-6">
    <section
      class="rounded-3xl border border-[color:var(--line)] bg-white/90 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.05)]">
      <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <div class="text-sm font-medium text-[color:var(--slate-soft)]">
            Chat
          </div>
          <h1 class="mt-2 text-3xl font-semibold text-[color:var(--ink)]">{{ project.name }}</h1>
          <div class="mt-2 text-sm text-[color:var(--slate-soft)]">Project conversation</div>
        </div>
        <Link
          :href="route('chat.index')"
          class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-sm font-medium text-[color:var(--slate)] transition hover:bg-[color:var(--panel-muted)]"
        >
          Back to chat
        </Link>
      </div>
    </section>

    <div ref="messagesBox" class="h-[60vh] space-y-3 overflow-y-auto rounded-[2rem] border border-[color:var(--line)] bg-white/90 p-5 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">

      <div v-if="hasMore" class="text-center mb-2">
        <button @click="loadOlder" :disabled="loadingOlder"
          class="rounded-xl border border-[color:var(--line)] bg-[color:var(--panel-strong)] px-4 py-2 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-[color:var(--slate)] disabled:opacity-50">
          {{ loadingOlder ? 'Loading…' : 'Load older messages' }}
        </button>
      </div>

      <div v-if="messagesLocal.length === 0" class="text-sm text-[color:var(--slate-soft)]">
        No messages yet.
      </div>

      <template v-for="(m, i) in messagesLocal" :key="m.id">

        <div v-if="i === 0 || dateKey(messagesLocal[i - 1].created_at) !== dateKey(m.created_at)"
          class="my-3 flex items-center gap-2 text-xs text-[color:var(--slate-soft)]">
          <div class="flex-1 border-t border-[color:var(--line)]"></div>
          <span>{{ labelForDateKey(dateKey(m.created_at)) }}</span>
          <div class="flex-1 border-t border-[color:var(--line)]"></div>
        </div>

        <div v-if="!shownNewDivider && isNewMessage(m)" class="my-2 flex items-center gap-2 text-xs text-[color:var(--accent)]">
          <div class="flex-1 border-t border-[color:var(--accent)]/40"></div>
          <span>New messages</span>
          <div class="flex-1 border-t border-[color:var(--accent)]/40"></div>
        </div>

        <div class="rounded-[1.5rem] border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-4 text-sm">
          <div class="flex items-start justify-between gap-3">
            <div class="text-[color:var(--slate-soft)]">
              <span class="font-semibold text-[color:var(--ink)]">{{ m.user?.name ?? 'Unknown' }}</span>
              <span class="text-stone-400">
                • {{ new Date(m.created_at).toLocaleString() }}
              </span>
            </div>

            <button v-if="currentUser && m.user_id === currentUser.id" type="button"
              class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-rose-700 hover:bg-rose-100"
              @click="deleteMessage(m.id)">
              Delete
            </button>
          </div>

          <div class="mt-2 whitespace-pre-wrap text-[color:var(--ink)]">
            {{ m.body }}
          </div>

          <div v-if="(m?.attachments?.length ?? 0) > 0" class="mt-2 space-y-2">
            <div v-for="a in m.attachments" :key="a.id">
              <img v-if="(a?.mime ?? '').startsWith('image/')" :src="route('chat.attachments.view', [project.id, a.id])"
                class="w-64 max-w-full rounded-2xl border border-[color:var(--line)]" alt="" />
              <a v-else :href="route('chat.attachments.download', [project.id, a.id])" class="text-sm font-semibold text-[color:var(--accent)] underline">
                {{ a.original_name }}
              </a>
            </div>
          </div>
        </div>

        <span v-if="!shownNewDivider && isNewMessage(m)" class="hidden">
          {{ (shownNewDivider = true) }}
        </span>

      </template>
    </div>

    <form @submit.prevent="send" class="grid gap-3 rounded-[2rem] border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-5 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
      <textarea v-model="form.body" class="rounded-2xl border border-[color:var(--line)] bg-white p-3 text-[color:var(--ink)] focus:border-[color:var(--accent)] focus:outline-none focus:ring-0" rows="3" placeholder="Write a message..." />

      <input ref="fileInput" type="file" multiple class="text-sm text-[color:var(--slate-soft)] file:mr-4 file:rounded-xl file:border-0 file:bg-[color:var(--slate)] file:px-4 file:py-2 file:text-[0.72rem] file:font-semibold file:uppercase file:tracking-[0.16em] file:text-white"
        @change="e => form.attachments = Array.from(e.target.files)" />

      <div v-if="form.attachments.length" class="text-sm text-[color:var(--slate-soft)]">
        <div class="mb-1 font-semibold text-[color:var(--ink)]">Selected files:</div>
        <ul class="list-disc pl-5">
          <li v-for="f in form.attachments" :key="f.name + f.size">
            {{ f.name }} ({{ Math.round(f.size / 1024) }} KB)
          </li>
        </ul>
      </div>

      <div v-if="form.progress" class="text-sm text-[color:var(--slate-soft)]">
        Uploading: {{ form.progress.percentage }}%
        <div class="mt-1 h-2 overflow-hidden rounded-full border border-[color:var(--line)]">
          <div class="h-2 bg-[color:var(--accent)]" :style="{ width: form.progress.percentage + '%' }" />
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit" :disabled="form.processing || !canSend"
          class="rounded-2xl bg-[color:var(--accent)] px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white disabled:cursor-not-allowed disabled:opacity-50">
          {{ form.processing ? 'Sending…' : 'Send' }}
        </button>
      </div>
    </form>
  </div>
</template>

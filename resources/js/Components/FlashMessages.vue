<script setup>
import { computed, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const success = computed(() => page.props.flash?.success ?? null)
const error = computed(() => page.props.flash?.error ?? null)

const visible = ref(false)
let timer = null

function showTemporarily() {
  visible.value = true
  if (timer) clearTimeout(timer)
  timer = setTimeout(() => (visible.value = false), 3500)
}

function close() {
  visible.value = false
  if (timer) clearTimeout(timer)
}

watch([success, error], ([s, e]) => {
  if (s || e) showTemporarily()
})
</script>

<template>
  <transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div v-if="visible && (success || error)" class="mb-6">
      <div
        class="flex items-start justify-between gap-3 rounded-2xl border p-4 shadow-[0_12px_30px_rgba(15,23,42,0.08)]"
        :class="success
          ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
          : 'border-rose-200 bg-rose-50 text-rose-900'"
      >
        <div class="text-sm">
          <div class="mb-1 text-xs font-medium opacity-70">
            {{ success ? 'Saved' : 'Could not complete action' }}
          </div>
          <div v-if="success">{{ success }}</div>
          <div v-else>{{ error }}</div>
        </div>

        <button
          type="button"
          @click="close"
          class="rounded-xl border border-black/10 bg-white/80 px-3 py-1.5 text-xs font-medium hover:bg-white"
        >
          Close
        </button>
      </div>
    </div>
  </transition>
</template>

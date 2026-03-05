<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout,
}
</script>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  projects: Array,
})
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
          <h1 class="mt-2 text-3xl font-semibold text-[color:var(--ink)]">Project chat channels</h1>
          <p class="mt-2 text-sm text-[color:var(--slate-soft)]">
            Open any project thread and keep field updates, files, and decisions in one place.
          </p>
        </div>

        <div class="rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-muted)] px-4 py-3 text-sm text-[color:var(--slate-soft)]">
          {{ projects?.length ?? 0 }} active conversation{{ (projects?.length ?? 0) === 1 ? '' : 's' }}
        </div>
      </div>
    </section>

    <section class="overflow-hidden rounded-[2rem] border border-[color:var(--line)] bg-white/90 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
      <div v-if="!projects || projects.length === 0" class="px-6 py-8 text-sm text-[color:var(--slate-soft)]">
        You are not a member of any projects.
      </div>

      <div v-else class="divide-y divide-[color:var(--line)]">
        <Link
          v-for="p in projects"
          :key="p.id"
          :href="route('chat.show', p.id)"
          class="flex items-center justify-between gap-4 px-6 py-5 transition hover:bg-[color:var(--panel-bg)]"
        >
          <div>
            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-[color:var(--accent)]">
              Channel
            </div>
            <div class="mt-1 text-lg font-semibold text-[color:var(--ink)]">{{ p.name }}</div>
          </div>

          <span
            v-if="(p.unread_count ?? 0) > 0"
            class="rounded-full bg-[color:var(--accent)] px-3 py-1 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-white"
          >
            {{ p.unread_count }} new
          </span>
          <span v-else class="text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">
            Open
          </span>
        </Link>
      </div>
    </section>
  </div>
</template>

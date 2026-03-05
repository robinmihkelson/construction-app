<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'

function convertToProject() {
  router.post(route('inquiries.convert', props.inquiry.id), {}, {
    preserveScroll: true,
  })
}

const props = defineProps({
  inquiry: Object,
})

function setStatus(status) {
  router.patch(route('inquiries.status', props.inquiry.id), { status }, {
    preserveScroll: true,
  })
}

function badgeClasses(status) {
  switch (status) {
    case 'new':
      return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'contacted':
      return 'bg-yellow-50 text-yellow-700 border-yellow-200'
    case 'converted':
      return 'bg-green-50 text-green-700 border-green-200'
    default:
      return 'bg-gray-50 text-gray-700 border-gray-200'
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <h2 class="text-2xl font-semibold leading-tight text-[color:var(--ink)]">
            Inquiry #{{ inquiry.id }}
          </h2>
          <span class="rounded-full border px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.18em]" :class="badgeClasses(inquiry.status)">
            {{ inquiry.status }}
          </span>
        </div>

        <Link :href="route('inquiries.index', { status: inquiry.status })" class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)]">
          Back
        </Link>
      </div>
    </template>

    <div class="space-y-6">
      <section
        class="rounded-3xl border border-[color:var(--line)] bg-white/90 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.05)]">
        <div class="text-sm font-medium text-[color:var(--slate-soft)]">
          Inquiry
        </div>
        <p class="mt-3 max-w-2xl text-sm text-[color:var(--slate-soft)]">
          Change status, convert the lead into a project, or hand it back to the new queue without changing the existing backend workflow.
        </p>
      </section>

        <div class="flex flex-wrap gap-2 rounded-[2rem] border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-5 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
          <button v-if="inquiry.status !== 'contacted'" @click="setStatus('contacted')"
            class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)] transition hover:bg-stone-50">
            Mark contacted
          </button>

          <button v-if="inquiry.status !== 'converted'" @click="convertToProject()"
            class="rounded-2xl bg-[color:var(--accent)] px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[color:var(--accent-deep)]">
            Convert → Create project
          </button>

          <Link v-if="inquiry.project_id" :href="route('projects.show', inquiry.project_id)"
            class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)] transition hover:bg-stone-50">
            Open project
          </Link>

          <button v-if="inquiry.status !== 'new'" @click="setStatus('new')"
            class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)] transition hover:bg-stone-50">
            Set back to new
          </button>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-[color:var(--line)] bg-white/90 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
          <div class="p-6 space-y-4">
            <div>
              <div class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">Name</div>
              <div class="font-semibold text-[color:var(--ink)]">{{ inquiry.name }}</div>
            </div>

            <div>
              <div class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">Email</div>
              <div class="text-[color:var(--ink)]">{{ inquiry.email }}</div>
            </div>

            <div v-if="inquiry.phone">
              <div class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">Phone</div>
              <div class="text-[color:var(--ink)]">{{ inquiry.phone }}</div>
            </div>

            <div>
              <div class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">Message</div>
              <pre class="whitespace-pre-wrap rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-4 text-sm text-[color:var(--ink)]">{{ inquiry.message }}
          </pre>
            </div>

            <div class="text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">
              Created: {{ inquiry.created_at }}
            </div>
          </div>
        </div>
    </div>
  </AuthenticatedLayout>
</template>

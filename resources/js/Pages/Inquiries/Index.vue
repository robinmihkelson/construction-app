<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  inquiries: Object,
  filters: Object,
  counts: Object,
})

const currentStatus = props.filters?.status ?? 'new'

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

function tabClasses(status) {
  const base = 'rounded-2xl border px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] transition'
  return status === currentStatus
    ? `${base} border-[color:var(--accent)] bg-[color:var(--accent)] text-white`
    : `${base} border-[color:var(--line)] bg-white text-[color:var(--slate)] hover:bg-stone-50`
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <section
        class="rounded-3xl border border-[color:var(--line)] bg-white/90 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.05)]">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <div class="text-sm font-medium text-[color:var(--slate-soft)]">
              Inquiries
            </div>
            <h1 class="mt-2 text-3xl font-semibold text-[color:var(--ink)]">Client inquiries</h1>
            <p class="mt-2 text-sm text-[color:var(--slate-soft)]">
              Review new leads, mark them contacted, and convert them into projects when they are ready.
            </p>
          </div>
          <div class="rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-muted)] px-4 py-3 text-sm text-[color:var(--slate-soft)]">
            {{ (counts?.new ?? 0) + (counts?.contacted ?? 0) + (counts?.converted ?? 0) }} total inquiries
          </div>
        </div>
      </section>

      <div class="flex flex-wrap gap-2">
        <Link :href="route('inquiries.index', { status: 'new' })" :class="tabClasses('new')">
          New ({{ counts?.new ?? 0 }})
        </Link>
        <Link :href="route('inquiries.index', { status: 'contacted' })" :class="tabClasses('contacted')">
          Contacted ({{ counts?.contacted ?? 0 }})
        </Link>
        <Link :href="route('inquiries.index', { status: 'converted' })" :class="tabClasses('converted')">
          Converted ({{ counts?.converted ?? 0 }})
        </Link>
      </div>

      <section class="overflow-hidden rounded-[2rem] border border-[color:var(--line)] bg-white/90 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
        <div class="p-6">
          <div v-if="!inquiries?.data?.length" class="text-sm text-[color:var(--slate-soft)]">
            No inquiries in this status.
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="i in inquiries.data"
              :key="i.id"
              class="rounded-[1.6rem] border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-5"
            >
              <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <div class="text-lg font-semibold text-[color:var(--ink)]">{{ i.name }}</div>
                    <span class="rounded-full border px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.18em]" :class="badgeClasses(i.status)">
                      {{ i.status }}
                    </span>
                  </div>

                  <div class="mt-1 text-sm text-[color:var(--slate-soft)]">
                    {{ i.email }} <span v-if="i.phone">· {{ i.phone }}</span>
                  </div>

                  <div class="mt-3 text-sm text-[color:var(--ink)]">
                    {{ i.message_preview }}
                  </div>

                  <div class="mt-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate-soft)]">
                    {{ i.created_at }}
                  </div>
                </div>

                <Link
                  :href="route('inquiries.show', i.id)"
                  class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)] transition hover:bg-stone-50"
                >
                  Open
                </Link>
              </div>
            </div>
          </div>

          <div class="mt-5 flex gap-2 text-sm">
            <Link
              v-if="inquiries?.prev_page_url"
              :href="inquiries.prev_page_url"
              class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)] transition hover:bg-stone-50"
            >
              Prev
            </Link>
            <Link
              v-if="inquiries?.next_page_url"
              :href="inquiries.next_page_url"
              class="rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)] transition hover:bg-stone-50"
            >
              Next
            </Link>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>

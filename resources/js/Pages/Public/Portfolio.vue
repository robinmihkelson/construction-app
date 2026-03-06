<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const CONTENT = {
  et: {
    title: 'Tehtud tööd',
    body: 'Näidisprojektid, mis näitavad meie tööde ulatust, kvaliteeti ja selget teostust.',
    cta: 'Alusta oma projektiga',
    projects: [
      { title: 'Korteri värvimine', meta: 'Tartu • siseviimistlus', image: '/images/portfolio/renovation.webp' },
      { title: 'Maja täielik uuendus', meta: 'Vantaa • välistööd', image: '/images/portfolio/house.webp' },
      { title: 'Tennisekeskuse värvimine', meta: 'Tali Tennisekeskus, Helsingi • välistööd', image: '/images/portfolio/tennis.webp' },
      { title: 'Köögi renoveerimine', meta: 'Helsingi • siseviimistlus', image: '/images/portfolio/kitchen.webp' },
      { title: 'Korteri ehitus', meta: 'Tikkruila, Vantaa • ehitus', image: '/images/portfolio/katu10.webp' },
      { title: 'Vannitoa renoveerimine', meta: 'Helsingi • siseviimistlus', image: '/images/portfolio/bathroom.webp' },
    ],
  },
  en: {
    title: 'Portfolio',
    body: 'Sample projects that show the scope, quality, and structure of our delivery.',
    cta: 'Start your project',
    projects: [
      { title: 'Apartment painting', meta: 'Tartu • interior finish', image: '/images/portfolio/renovation.webp' },
      { title: 'Full house upgrade', meta: 'Vantaa • exterior work', image: '/images/portfolio/house.webp' },
      { title: 'Tennis center painting', meta: 'Tali Tennis Center, Helsinki • exterior work', image: '/images/portfolio/tennis.webp' },
      { title: 'Kitchen renovation', meta: 'Helsinki • interior finish', image: '/images/portfolio/kitchen.webp' },
      { title: 'Apartment construction', meta: 'Tikkurila, Vantaa • construction', image: '/images/portfolio/katu10.webp' },
      { title: 'Bathroom renovation', meta: 'Helsinki • interior finish', image: '/images/portfolio/bathroom.webp' },
    ],
  },
  fi: {
    title: 'Referenssit',
    body: 'Esimerkkikohteita, jotka näyttävät työn laajuuden, laadun ja hallitun toteutuksen.',
    cta: 'Aloita oma projekti',
    projects: [
      { title: 'Asuinrakennuksen saneeraus', meta: 'Tartto • sisäviimeistely', image: '/images/portfolio/renovation.webp' },
      { title: 'Asunnon täydellinen uudistus', meta: 'Vantaa • ulkotyöt', image: '/images/portfolio/house.webp' },
      { title: 'Tenniskeskuksen Maalaus', meta: 'Talin Tenniskeskus, Helsinki • ulkotyöt', image: '/images/portfolio/tennis.webp' },
      { title: 'Keittiöremontti', meta: 'Helsingi • muutostyö', image: '/images/portfolio/kitchen.webp' },
      { title: 'Asunnon rakentaminen', meta: 'Tikkurila, Vantaa • rakentaminen', image: '/images/portfolio/katu10.webp' },
      { title: 'Kylpyhuoneremontti', meta: 'Helsinki • sisäviimeistely', image: '/images/portfolio/bathroom.webp' },
    ],
  },
}

const page = usePage()
const locale = computed(() => page.props.locale ?? 'et')
const copy = computed(() => CONTENT[locale.value] ?? CONTENT.et)
</script>

<template>
  <PublicLayout>
    <section class="mx-auto max-w-[92rem] px-4 py-14 xl:px-6">
      <div data-reveal="curtain" class="border border-slate-800 bg-slate-950 p-8 text-white shadow-[0_22px_44px_rgba(15,23,42,0.14)]">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
          <div class="max-w-3xl">
            <div class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-300">{{ copy.title }}</div>
            <h1 class="mt-2 text-4xl font-semibold tracking-tight text-white">{{ copy.title }}</h1>
            <p class="mt-4 text-base leading-7 text-slate-300">{{ copy.body }}</p>
          </div>
          <Link
            href="/contact"
            class="inline-flex items-center bg-amber-400 px-6 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-slate-950 transition hover:bg-amber-300"
          >
            {{ copy.cta }}
          </Link>
        </div>
      </div>

      <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="(project, index) in copy.projects"
          :key="project.title"
          data-reveal="card"
          :style="{ '--reveal-delay': `${90 + index * 80}ms` }"
          class="group overflow-hidden border border-slate-200 bg-white shadow-[0_16px_32px_rgba(15,23,42,0.04)] transition hover:-translate-y-1 hover:shadow-[0_24px_44px_rgba(15,23,42,0.08)]"
        >
          <div class="relative h-56 sm:h-60 bg-[linear-gradient(145deg,#111827,#334155)]">
            <img
              v-if="project.image"
              :src="project.image"
              :alt="project.title"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            />
            <div class="absolute inset-0 bg-[linear-gradient(to_top,rgba(15,23,42,0.35),transparent_55%)] opacity-0 transition group-hover:opacity-100"></div>
            <div class="absolute left-4 top-9 bg-white/90 px-3 py-1 text-xs font-semibold text-slate-900">
              0{{ index + 1 }}
            </div>
            <div class="absolute bottom-4 left-4 bg-white/90 px-3 py-1 text-xs font-semibold text-slate-900">
              {{ project.meta }}
            </div>
          </div>
          <div class="p-6">
            <div class="h-1 w-12 bg-amber-400"></div>
            <div class="mt-5 text-lg font-semibold text-slate-950">{{ project.title }}</div>
            <div class="mt-3 text-sm leading-6 text-slate-600">{{ project.note }}</div>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

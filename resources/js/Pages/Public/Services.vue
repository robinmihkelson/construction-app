<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const CONTENT = {
  et: {
    title: 'Teenused',
    body: 'Koostame tööplaani vastavalt objekti vajadustele ning viime töö etappide kaupa korrektselt lõpuni.',
    cta: 'Küsi hinnapakkumist',
    groups: [
      {
        title: 'Renoveerimine',
        text: 'Korterid, eramud ja äripinnad alates lammutusest kuni viimistluseni.',
        items: ['Vannitoad ja köögid', 'Siseviimistlus', 'Võtmed kätte remondid'],
      },
      {
        title: 'Fassaad ja välitööd',
        text: 'Ilmastikukindlad lahendused ja korralik lõpptulemus.',
        items: ['Soojustus ja viimistlus', 'Värvimine', 'Parandused ja hooldus'],
      },
      {
        title: 'Üldehitus',
        text: 'Selge tööjuhtimine nii väiksemate kui ka keerukamate objektide puhul.',
        items: ['Ehitamine ja ümberehitus', 'Konstruktsioonitööd', 'Tööetappide koordineerimine'],
      },
    ],
    workflowTitle: 'Tööprotsess',
    workflowBody:
      'Pärast hinnapäringut viime info kohe töövoogu: kaardistus, pakkumine, ajakava, teostus ja üleandmine.',
    workflowSteps: ['Objekti ülevaatus', 'Pakkumine ja ajakava', 'Teostus', 'Üleandmine'],
  },
  en: {
    title: 'Services',
    body: 'We structure the work around the site requirements and deliver each phase cleanly from start to finish.',
    cta: 'Request a quote',
    groups: [
      {
        title: 'Renovation',
        text: 'Apartments, homes, and commercial spaces from demolition to finishing.',
        items: ['Bathrooms and kitchens', 'Interior finishing', 'Turnkey renovations'],
      },
      {
        title: 'Facade and exterior work',
        text: 'Weather-resistant solutions with a reliable final result.',
        items: ['Insulation and finishing', 'Painting', 'Repairs and maintenance'],
      },
      {
        title: 'General construction',
        text: 'Clear project control for smaller and more complex sites.',
        items: ['Construction and rebuilds', 'Structural work', 'Phase coordination'],
      },
    ],
    workflowTitle: 'Delivery process',
    workflowBody:
      'After the first inquiry, we move the information directly into a clear workflow: discovery, quote, schedule, execution, and handover.',
    workflowSteps: ['Site review', 'Quote and schedule', 'Execution', 'Handover'],
  },
  fi: {
    title: 'Palvelut',
    body: 'Rakennamme työnkulun kohteen tarpeiden mukaan ja viemme jokaisen vaiheen hallitusti maaliin.',
    cta: 'Pyydä tarjous',
    groups: [
      {
        title: 'Saneeraus',
        text: 'Asunnot, omakotitalot ja liiketilat purusta viimeistelyyn.',
        items: ['Kylpyhuoneet ja keittiöt', 'Sisäviimeistely', 'Avaimet käteen -saneeraukset'],
      },
      {
        title: 'Julkisivu ja ulkotyöt',
        text: 'Säänkestävät ratkaisut ja varma lopputulos.',
        items: ['Eristys ja viimeistely', 'Maalaus', 'Korjaukset ja huolto'],
      },
      {
        title: 'Yleisrakentaminen',
        text: 'Selkeä työnjohto pienissä ja vaativammissa kohteissa.',
        items: ['Rakentaminen ja muutostyöt', 'Runkotyöt', 'Työvaiheiden koordinointi'],
      },
    ],
    workflowTitle: 'Työprosessi',
    workflowBody:
      'Ensimmäisen yhteydenoton jälkeen siirrämme tiedot suoraan työnkulkuun: kartoitus, tarjous, aikataulu, toteutus ja luovutus.',
    workflowSteps: ['Kohteen kartoitus', 'Tarjous ja aikataulu', 'Toteutus', 'Luovutus'],
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

      <div class="mt-8 grid gap-4 lg:grid-cols-3">
        <div
          v-for="(group, index) in copy.groups"
          :key="group.title"
          data-reveal="card"
          :style="{ '--reveal-delay': `${90 + index * 95}ms` }"
          class="relative border border-slate-200 bg-white p-7 shadow-[0_16px_32px_rgba(15,23,42,0.04)]"
        >
          <div class="absolute right-5 top-3 text-6xl font-semibold leading-none text-slate-100">
            0{{ index + 1 }}
          </div>
          <div class="h-1 w-12 bg-amber-400"></div>
          <div class="mt-5 text-xl font-semibold text-slate-950">{{ group.title }}</div>
          <p class="mt-3 text-sm leading-6 text-slate-600">{{ group.text }}</p>
          <ul class="mt-5 space-y-2 text-sm text-slate-600">
            <li v-for="item in group.items" :key="item" class="border border-slate-100 bg-slate-50 px-4 py-3">
              {{ item }}
            </li>
          </ul>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

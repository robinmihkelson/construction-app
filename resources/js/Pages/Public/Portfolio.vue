<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const CONTENT = {
  et: {
    title: 'Tehtud tööd',
    body: 'Näidisprojektid, mis näitavad meie tööde ulatust, kvaliteeti ja selget teostust.',
    openLabel: 'Vaata lähemalt',
    closeLabel: 'Sulge',
    detailLabel: 'Valminud töö',
    scopeLabel: 'Töö sisu',
    resultLabel: 'Tulemus',
    projects: [
      {
        title: 'Korteri värvimine',
        meta: 'Tartu • siseviimistlus',
        image: '/images/portfolio/renovation.webp',
        note: 'Korteri sisepindade värskendus, kus rõhk oli puhtal ettevalmistusel ja ühtlasel viimistlusel.',
        detail: 'Projekt hõlmas sisepindade ettevalmistust, parandusi ja värvimist nii, et igapäevane ruumikasutus taastuks kiiresti ja lõpptulemus jääks ühtlane.',
        scope: ['Pindade kaitsmine ja ettevalmistus', 'Pahteldus, lihvimine ja parandused', 'Viimistlusvärvimine ja puhas üleandmine'],
        result: 'Korter sai värske, puhta ja ühtlase ilme ilma üleliigse venimiseta.',
      },
      {
        title: 'Maja täielik uuendus',
        meta: 'Vantaa • välistööd',
        image: '/images/portfolio/house.webp',
        note: 'Eramu välisilme ja pindade vastupidavuse parandamine koordineeritud tööetappidega.',
        detail: 'Töö keskendus välispindade korrastamisele ja viimistlusele, et hoone näeks parem välja ning oleks ilmastiku vastu paremini kaitstud.',
        scope: ['Välispindade ülevaatus ja parandused', 'Fassaadi ettevalmistus', 'Ilmastikukindel viimistlus'],
        result: 'Maja sai korrastatud välisilme ja paremini kaitstud pinnad.',
      },
      {
        title: 'Tennisekeskuse värvimine',
        meta: 'Tali Tennisekeskus, Helsingi • välistööd',
        image: '/images/portfolio/tennis.webp',
        note: 'Suurema objekti värvimine, kus töövoog pidi sobituma keskuse kasutusega.',
        detail: 'Avaliku kasutusega objektil oli oluline hoida tööala korras, planeerida etappe selgelt ja anda pinnad üle vastupidava viimistlusega.',
        scope: ['Tööala ja etappide planeerimine', 'Välispindade ettevalmistus', 'Värvimine ja lõppkontroll'],
        result: 'Keskuse pinnad said uuendatud ning töö kulges kasutajaid võimalikult vähe häirides.',
      },
      {
        title: 'Köögi renoveerimine',
        meta: 'Helsingi • siseviimistlus',
        image: '/images/portfolio/kitchen.webp',
        note: 'Köögi uuendus, kus tähtsad olid täpne sobitamine ja igapäevane kasutusmugavus.',
        detail: 'Köögi renoveerimisel jälgisime, et ettevalmistus, paigaldus ja viimistlus moodustaksid ühe selge tööjärjekorra.',
        scope: ['Vanade pindade ettevalmistus', 'Uute lahenduste paigaldus', 'Detailide kontroll ja viimistlus'],
        result: 'Valmis praktiline ja värske köögiruum selge, puhta lõpptulemusega.',
      },
      {
        title: 'Korteri ehitus',
        meta: 'Tikkurila, Vantaa • ehitus',
        image: '/images/portfolio/katu10.webp',
        note: 'Korteri terviklik ehitustöö ettevalmistusest kuni viimistletud üleandmiseni.',
        detail: 'Projekt ühendas ehitustööde koordineerimise, sisetööd ja viimistluse tervikuks, et klient saaks ruumi valmis kujul üle võtta.',
        scope: ['Tööde planeerimine ja ajastus', 'Sisepindade ehitus ja viimistlus', 'Koristatud ning kasutusvalmis üleandmine'],
        result: 'Klient sai valminud eluruumi, kus tööjärjekord ja viimistluse kvaliteet olid kontrolli all.',
      },
      {
        title: 'Vannitoa renoveerimine',
        meta: 'Helsingi • siseviimistlus',
        image: '/images/portfolio/bathroom.webp',
        note: 'Vannitoa uuendus täpse ettevalmistuse, niiskusruumi tööde ja puhta viimistlusega.',
        detail: 'Vannitöödes oli oluline detailne ettevalmistus, sobiv tööjärjekord ja lõppviimistlus, mis peab igapäevasele kasutusele vastu.',
        scope: ['Ettevalmistus ja aluspindade kontroll', 'Niiskusruumi tööde koordineerimine', 'Viimistlus ja lõplik ülevaatus'],
        result: 'Vannituba valmis puhta ja praktilise ruumina, kus detailid kontrolliti enne üleandmist.',
      },
    ],
  },
  en: {
    title: 'Portfolio',
    body: 'Sample projects that show the scope, quality, and structure of our delivery.',
    openLabel: 'View details',
    closeLabel: 'Close',
    detailLabel: 'Completed work',
    scopeLabel: 'Scope',
    resultLabel: 'Result',
    projects: [
      {
        title: 'Apartment painting',
        meta: 'Tartu • interior finish',
        image: '/images/portfolio/renovation.webp',
        note: 'Interior repainting with careful preparation and a clean, even finish.',
        detail: 'The work covered surface preparation, repairs, and painting so the apartment could return to use quickly with a consistent final look.',
        scope: ['Surface protection and preparation', 'Filling, sanding, and repairs', 'Finish painting and clean handover'],
        result: 'The apartment received a fresh, clean, and even finish without unnecessary delay.',
      },
      {
        title: 'Full house upgrade',
        meta: 'Vantaa • exterior work',
        image: '/images/portfolio/house.webp',
        note: 'Exterior improvements for a private house, focused on appearance and durability.',
        detail: 'The project focused on repairing and finishing exterior surfaces so the building looked sharper and gained better weather protection.',
        scope: ['Exterior surface review and repairs', 'Facade preparation', 'Weather-resistant finishing'],
        result: 'The house received a cleaner exterior and better protected surfaces.',
      },
      {
        title: 'Tennis center painting',
        meta: 'Tali Tennis Center, Helsinki • exterior work',
        image: '/images/portfolio/tennis.webp',
        note: 'A larger painting project coordinated around the daily use of the center.',
        detail: 'For a public-use site, the priority was a tidy work area, clear phase planning, and durable surface finishing.',
        scope: ['Work area and phase planning', 'Exterior surface preparation', 'Painting and final checks'],
        result: 'The center surfaces were renewed while keeping disruption to users as low as possible.',
      },
      {
        title: 'Kitchen renovation',
        meta: 'Helsinki • interior finish',
        image: '/images/portfolio/kitchen.webp',
        note: 'A kitchen update focused on precise fitting and practical everyday use.',
        detail: 'The renovation kept preparation, installation, and finishing in one clear workflow so the final kitchen felt cohesive and usable.',
        scope: ['Preparation of old surfaces', 'Installation of new solutions', 'Detail checks and finishing'],
        result: 'The finished kitchen is fresh, practical, and cleanly detailed.',
      },
      {
        title: 'Apartment construction',
        meta: 'Tikkurila, Vantaa • construction',
        image: '/images/portfolio/katu10.webp',
        note: 'A complete apartment construction project from preparation to final handover.',
        detail: 'The project brought construction coordination, interior work, and finishing into one managed delivery so the client received a completed space.',
        scope: ['Work planning and scheduling', 'Interior construction and finishing', 'Clean handover ready for use'],
        result: 'The client received a finished living space with the workflow and finish quality kept under control.',
      },
      {
        title: 'Bathroom renovation',
        meta: 'Helsinki • interior finish',
        image: '/images/portfolio/bathroom.webp',
        note: 'A bathroom upgrade with careful preparation, wet-room work, and clean finishing.',
        detail: 'Bathroom work required detailed preparation, the right work sequence, and finishing that can handle daily use.',
        scope: ['Preparation and substrate checks', 'Wet-room work coordination', 'Finishing and final review'],
        result: 'The bathroom was delivered as a clean and practical space with details checked before handover.',
      },
    ],
  },
  fi: {
    title: 'Referenssit',
    body: 'Esimerkkikohteita, jotka näyttävät työn laajuuden, laadun ja hallitun toteutuksen.',
    openLabel: 'Katso tarkemmin',
    closeLabel: 'Sulje',
    detailLabel: 'Valmis kohde',
    scopeLabel: 'Työn sisältö',
    resultLabel: 'Lopputulos',
    projects: [
      {
        title: 'Asuinrakennuksen saneeraus',
        meta: 'Tartto • sisäviimeistely',
        image: '/images/portfolio/renovation.webp',
        note: 'Sisäpintojen maalaus huolellisella valmistelulla ja tasaisella viimeistelyllä.',
        detail: 'Työhön kuului pintojen valmistelu, korjaukset ja maalaus niin, että asunto saatiin nopeasti takaisin käyttöön.',
        scope: ['Pintojen suojaus ja valmistelu', 'Tasoitus, hionta ja korjaukset', 'Viimeistelymaalaus ja siisti luovutus'],
        result: 'Asunto sai raikkaan, siistin ja tasaisen ilmeen.',
      },
      {
        title: 'Asunnon täydellinen uudistus',
        meta: 'Vantaa • ulkotyöt',
        image: '/images/portfolio/house.webp',
        note: 'Omakotitalon ulkopuolen uudistus ulkonäön ja kestävyyden parantamiseksi.',
        detail: 'Projekti keskittyi ulkopintojen korjaukseen ja viimeistelyyn, jotta rakennus näyttää paremmalta ja kestää säätä paremmin.',
        scope: ['Ulkopintojen tarkistus ja korjaukset', 'Julkisivun valmistelu', 'Säänkestävä viimeistely'],
        result: 'Talo sai siistimmän ulkoasun ja paremmin suojatut pinnat.',
      },
      {
        title: 'Tenniskeskuksen Maalaus',
        meta: 'Talin Tenniskeskus, Helsinki • ulkotyöt',
        image: '/images/portfolio/tennis.webp',
        note: 'Laajempi maalauskohde, jossa työvaiheet sovitettiin keskuksen käyttöön.',
        detail: 'Julkisessa kohteessa tärkeää oli siisti työalue, selkeä vaiheistus ja kestävä pintaviimeistely.',
        scope: ['Työalueen ja vaiheiden suunnittelu', 'Ulkopintojen valmistelu', 'Maalaus ja lopputarkistus'],
        result: 'Keskuksen pinnat uudistuivat niin, että häiriö käyttäjille pysyi mahdollisimman pienenä.',
      },
      {
        title: 'Keittiöremontti',
        meta: 'Helsinki • sisäviimeistely',
        image: '/images/portfolio/kitchen.webp',
        note: 'Keittiön uudistus tarkalla sovituksella ja käytännöllisellä viimeistelyllä.',
        detail: 'Remontissa valmistelu, asennus ja viimeistely pidettiin yhtenä selkeänä työjärjestyksenä.',
        scope: ['Vanhojen pintojen valmistelu', 'Uusien ratkaisujen asennus', 'Yksityiskohtien tarkistus ja viimeistely'],
        result: 'Valmis keittiö on raikas, käytännöllinen ja siististi viimeistelty.',
      },
      {
        title: 'Asunnon rakentaminen',
        meta: 'Tikkurila, Vantaa • rakentaminen',
        image: '/images/portfolio/katu10.webp',
        note: 'Asunnon kokonaisvaltainen rakennustyö valmistelusta luovutukseen.',
        detail: 'Projekti yhdisti työn koordinoinnin, sisärakentamisen ja viimeistelyn hallituksi kokonaisuudeksi.',
        scope: ['Työn suunnittelu ja aikataulutus', 'Sisärakentaminen ja viimeistely', 'Siisti ja käyttövalmis luovutus'],
        result: 'Asiakas sai valmiin asuintilan, jossa työjärjestys ja viimeistelyn laatu olivat hallinnassa.',
      },
      {
        title: 'Kylpyhuoneremontti',
        meta: 'Helsinki • sisäviimeistely',
        image: '/images/portfolio/bathroom.webp',
        note: 'Kylpyhuoneen uudistus huolellisella valmistelulla ja siistillä viimeistelyllä.',
        detail: 'Kylpyhuonetyössä tärkeää oli oikea työjärjestys, huolellinen valmistelu ja päivittäistä käyttöä kestävä lopputulos.',
        scope: ['Valmistelu ja alustan tarkistus', 'Märkätilatöiden koordinointi', 'Viimeistely ja lopputarkistus'],
        result: 'Kylpyhuone luovutettiin siistinä ja käytännöllisenä tilana.',
      },
    ],
  },
}

const page = usePage()
const locale = computed(() => page.props.locale ?? 'et')
const copy = computed(() => CONTENT[locale.value] ?? CONTENT.et)
const selectedProjectIndex = ref(null)
const selectedProject = computed(() => {
  if (selectedProjectIndex.value === null) return null

  return copy.value.projects[selectedProjectIndex.value] ?? null
})

function openProject(index) {
  selectedProjectIndex.value = index
}

function closeProject() {
  selectedProjectIndex.value = null
}

function onKeydown(event) {
  if (event.key === 'Escape') closeProject()
}

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <PublicLayout>
    <section class="mx-auto max-w-[92rem] px-4 py-14 xl:px-6">
      <div data-nav-logo-tone="inverse" data-reveal="curtain" class="border border-slate-800 bg-slate-950 p-8 text-white shadow-[0_22px_44px_rgba(15,23,42,0.14)]">
        <div class="max-w-3xl">
          <div class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-300">{{ copy.title }}</div>
          <h1 class="mt-2 text-4xl font-semibold tracking-tight text-white">{{ copy.title }}</h1>
          <p class="mt-4 text-base leading-7 text-slate-300">{{ copy.body }}</p>
        </div>
      </div>

      <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <button
          v-for="(project, index) in copy.projects"
          :key="project.title"
          type="button"
          data-reveal="card"
          :style="{ '--reveal-delay': `${90 + index * 80}ms` }"
          class="group overflow-hidden border border-slate-200 bg-white text-left shadow-[0_16px_32px_rgba(15,23,42,0.04)] transition hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_24px_44px_rgba(15,23,42,0.08)] focus:outline-none focus:ring-4 focus:ring-amber-300/35"
          @click="openProject(index)"
        >
          <div class="relative h-56 bg-[linear-gradient(145deg,#111827,#334155)] sm:h-60">
            <img
              v-if="project.image"
              :src="project.image"
              :alt="project.title"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            />
            <div class="absolute inset-0 bg-[linear-gradient(to_top,rgba(15,23,42,0.35),transparent_55%)] opacity-0 transition group-hover:opacity-100"></div>
            <div class="absolute left-4 top-4 bg-white/90 px-3 py-1 text-xs font-semibold text-slate-900">
              0{{ index + 1 }}
            </div>
            <div class="absolute bottom-4 left-4 bg-white/90 px-3 py-1 text-xs font-semibold text-slate-900">
              {{ project.meta }}
            </div>
          </div>

          <div class="p-6">
            <div class="h-1 w-12 bg-amber-400"></div>
            <div class="mt-5 text-lg font-semibold text-slate-950">{{ project.title }}</div>
            <div class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ project.note }}</div>
            <div class="mt-5 text-xs font-semibold uppercase tracking-[0.14em] text-slate-950">
              {{ copy.openLabel }}
            </div>
          </div>
        </button>
      </div>
    </section>

    <Teleport to="body">
      <div
        v-if="selectedProject"
        class="fixed inset-0 z-[80] overflow-y-auto bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        @click.self="closeProject"
      >
        <div class="mx-auto max-w-5xl overflow-hidden bg-white shadow-[0_30px_70px_rgba(15,23,42,0.35)]">
          <div class="grid lg:grid-cols-[1.05fr_0.95fr]">
            <div class="relative min-h-[18rem] bg-slate-900 lg:min-h-[34rem]">
              <img
                :src="selectedProject.image"
                :alt="selectedProject.title"
                class="h-full w-full object-cover"
              />
              <div class="absolute inset-x-0 top-0 h-2 bg-amber-400"></div>
            </div>

            <div class="p-6 md:p-8">
              <div class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-600">
                {{ copy.detailLabel }}
              </div>
              <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">
                {{ selectedProject.title }}
              </h2>
              <div class="mt-3 text-sm font-semibold text-slate-500">
                {{ selectedProject.meta }}
              </div>
              <p class="mt-5 text-base leading-7 text-slate-600">
                {{ selectedProject.detail }}
              </p>

              <div class="mt-7 border-t border-slate-200 pt-6">
                <div class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">
                  {{ copy.scopeLabel }}
                </div>
                <ul class="mt-4 space-y-3">
                  <li
                    v-for="item in selectedProject.scope"
                    :key="item"
                    class="flex gap-3 text-sm leading-6 text-slate-700"
                  >
                    <span class="mt-2 h-2 w-2 shrink-0 bg-amber-400"></span>
                    <span>{{ item }}</span>
                  </li>
                </ul>
              </div>

              <div class="mt-7 bg-slate-950 p-5 text-white">
                <div class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-300">
                  {{ copy.resultLabel }}
                </div>
                <p class="mt-3 text-sm leading-7 text-slate-300">
                  {{ selectedProject.result }}
                </p>
              </div>

              <div class="mt-6 flex flex-wrap gap-3">
                <button
                  type="button"
                  class="inline-flex items-center border border-slate-300 bg-white px-5 py-3 text-sm font-semibold uppercase tracking-[0.12em] text-slate-950 transition hover:bg-slate-50"
                  @click="closeProject"
                >
                  {{ copy.closeLabel }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const CONTENT = {
  et: {
    badge: 'Usaldusväärne ehituspartner',
    heroTitle: 'Ehitusprojektid, mis liiguvad plaanipäraselt ja valmivad korrektselt.',
    heroBody:
      'Renoveerimine, üldehitus ja fassaaditööd era- ning ärikliendile. Üks meeskond, üks tööplaan.',
    primaryCta: 'Küsi hinnapakkumist',
    secondaryCta: 'Vaata tehtud töid',
    stats: [
      { value: 10, label: 'aastat kogemust' },
      { value: 100, suffix: '+', label: 'valminud objekti' },
    ],
    processTitle: 'Meist',
    processSteps: [
      'Asutatud 2016. aastal',
      'Tegeleme Soomes ja Eestis.',
      'Korraldame ehitust ja suhtleme kliendiga jooksvalt.',
      'Anname töö üle korrastatult ja dokumenteeritult.',
    ],
    valueTitle: 'Miks kliendid valivad meid',
    valueBody:
      'Pärlikee ei müü ainult tööjõudu. Ta peab hoidma kokkulepped, suhtlema kiiresti ja andma kliendile kindluse, et objekt on kontrolli all.',
    valuePoints: [
      'Püsiv kontakt ja selge järgmine samm',
      'Realistlik ajakava, mitte umbmäärased lubadused',
      'Korralik viimistlus ja puhas üleandmine',
    ],
    servicesTitle: 'Põhiteenused',
    servicesBody:
      'Võtame projekti algusest lõpuni või tuleme appi kindla tööetapi jaoks.',
    services: [
      { title: 'Renoveerimine', text: 'Korterid, eramud, vannitoad, siseviimistlus ja tervikremondid.' },
      { title: 'Fassaad ja välitööd', text: 'Soojustus, parandused, värvimine ja ilmastikukindel viimistlus.' },
      { title: 'Üldehitus', text: 'Ehitamine, ümberehitus, konstruktsioonitööd ja väiksemad objektid.' },
    ],
    proofBody:
      'Vaata valitud näidisprojekte meie portfooliost.',
    proofCta: 'Ava tehtud tööd',
    proofCards: [
      { title: 'Korteri ehitus', image: '/images/portfolio/katu10.webp' },
      { title: 'Köögi renoveerimine', image: '/images/portfolio/kitchen.webp' },
      { title: 'Maja täielik uuendus', image: '/images/portfolio/house.webp' },
    ],
    proofMetrics: [
      { value: '100%', label: 'selge kontakt' },
      { value: '1', label: 'vastutav töövoog' },
      { value: '24h', label: 'esimene vastus' },
    ],
    faqTitle: 'Korduma kippuvad küsimused',
    faqBody: 'Kiired vastused enne hinnapäringu saatmist.',
    faqItems: [
      { q: 'Kui kiiresti hinnapakkumise saan?', a: 'Tavaliselt 24 tunni jooksul. Mahukamate tööde korral lepime esmalt kokku objekti ülevaatuse.' },
      { q: 'Millistes piirkondades te tegutsete?', a: 'Enim tegutseme Helsingis ja Tartus.' },
      { q: 'Kas töödele kehtib garantii?', a: 'Jah, garantii sõltub töö liigist ja lepingulisest mahust.' },
    ],
    ctaTitle: 'Saada päring ja viime projekti kiiresti tööse.',
    ctaBody:
      'Kirjelda objekti, asukohta ja ajakava. Vastame kiiresti ning viime info kohe tööprotsessi.',
    ctaEyebrow: 'Järgmine samm',
    ctaPhone: 'Helista',
  },
  en: {
    badge: 'Reliable construction partner',
    heroTitle: 'Construction projects that stay on schedule and finish cleanly.',
    heroBody:
      'Renovation, general construction, and facade work for residential and commercial clients. One team, one delivery plan.',
    primaryCta: 'Request a quote',
    secondaryCta: 'View portfolio',
    stats: [
      { value: 10, label: 'years of experience' },
      { value: 100, suffix: '+', label: 'completed sites' },
    ],
    processTitle: 'About us',
    processSteps: [
      'Established in 2016',
      'Based in Finland and Estonia',
      'We organize construction and communicate with the client on an ongoing basis.',
      'We deliver the work in an organized and documented manner.',
    ],
    valueTitle: 'Why clients choose us',
    valueBody:
      'A modern construction company should do more than provide labor. It should keep commitments, communicate quickly, and give clients confidence that the site is under control.',
    valuePoints: [
      'Fast communication and a clear next step',
      'Realistic timelines instead of vague promises',
      'High-quality finishing and clean handover',
    ],
    servicesTitle: 'Core services',
    servicesBody:
      'We can take the project from start to finish or support a specific phase.',
    services: [
      { title: 'Renovation', text: 'Apartments, homes, bathrooms, interior finishing, and turnkey upgrades.' },
      { title: 'Facade and exterior work', text: 'Insulation, repairs, painting, and weather-resistant finishing.' },
      { title: 'General construction', text: 'Building, rebuilds, structural work, and smaller-scale sites.' },
    ],
    proofBody:
      'Browse selected sample projects from our portfolio to see scope, finish quality, and how different sites have been delivered step by step.',
    proofCta: 'Open portfolio',
    proofCards: [
      { title: 'Apartment construction', image: '/images/portfolio/katu10.webp' },
      { title: 'Kitchen renovation', image: '/images/portfolio/kitchen.webp' },
      { title: 'Full house upgrade', image: '/images/portfolio/house.webp' },
    ],
    proofMetrics: [
      { value: '100%', label: 'clear communication' },
      { value: '1', label: 'coordinated workflow' },
      { value: '24h', label: 'first response' },
    ],
    faqTitle: 'Frequently asked questions',
    faqBody: 'Quick answers before you send a quote request.',
    faqItems: [
      { q: 'How quickly will I receive a quote?', a: 'Usually within 24 hours. Larger projects may require a site visit first.' },
      { q: 'In which areas do you operate?', a: 'We operate mostly in Helsinki and Tartu.' },
      { q: 'Do your works include a warranty?', a: 'Yes, depending on the type and scope of the work in the contract.' },
    ],
    ctaTitle: 'Send an inquiry and we will move your project forward quickly.',
    ctaBody:
      'Describe the site, location, and expected timeline. We reply fast and move the information directly into our workflow.',
    ctaEyebrow: 'Next step',
    ctaPhone: 'Call us',
  },
  fi: {
    badge: 'Luotettava rakennuskumppani',
    heroTitle: 'Rakennusprojektit, jotka etenevät suunnitelman mukaan ja valmistuvat siististi.',
    heroBody:
      'Saneeraus, yleisrakentaminen ja julkisivutyöt asunto- ja yritysasiakkaille. Yksi tiimi, yksi toteutussuunnitelma.',
    primaryCta: 'Pyydä tarjous',
    secondaryCta: 'Katso referenssit',
    stats: [
      { value: 10, label: 'vuotta kokemusta' },
      { value: 100, suffix: '+', label: 'valmista kohdetta' },
    ],
    processTitle: 'Tietoja meistä',
    processSteps: [
      'Perustettu vuonna 2016',
      'Toimipaikka Suomessa ja Virossa',
      'Järjestämme rakentamista ja olemme jatkuvasti yhteydessä asiakkaaseen.',
      'Toimitamme työt järjestelmällisesti ja dokumentoidusti.',
    ],
    valueTitle: 'Miksi asiakkaat valitsevat meidät',
    valueBody:
      'Nykyaikainen rakennusyritys ei myy vain työvoimaa. Sen pitää pitää lupaukset, viestiä nopeasti ja antaa asiakkaalle varmuus siitä, että kohde on hallinnassa.',
    valuePoints: [
      'Nopea viestintä ja selkeä seuraava askel',
      'Realistinen aikataulu epämääräisten lupausten sijaan',
      'Laadukas viimeistely ja siisti luovutus',
    ],
    servicesTitle: 'Pääpalvelut',
    servicesBody:
      'Voimme hoitaa projektin alusta loppuun tai tulla mukaan tiettyyn työvaiheeseen.',
    services: [
      { title: 'Saneeraus', text: 'Asunnot, omakotitalot, kylpyhuoneet, sisäviimeistely ja avaimet käteen -uudistukset.' },
      { title: 'Julkisivu ja ulkotyöt', text: 'Eristys, korjaukset, maalaus ja säänkestävä viimeistely.' },
      { title: 'Yleisrakentaminen', text: 'Rakentaminen, muutostyöt, runkotyöt ja pienemmät kohteet.' },
    ],
    proofBody:
      'Tutustu valittuihin referenssikohteisiin.',
    proofCta: 'Avaa referenssit',
    proofCards: [
      { title: 'Asunnon rakentaminen', image: '/images/portfolio/katu10.webp' },
      { title: 'Keittiöremontti', image: '/images/portfolio/kitchen.webp' },
      { title: 'JAsunnon täydellinen uudistus', image: '/images/portfolio/house.webp' },
    ],
    proofMetrics: [
      { value: '100%', label: 'selkeä viestintä' },
      { value: '1', label: 'hallittu työnkulku' },
      { value: '24h', label: 'ensivastaus' },
    ],
    faqTitle: 'Usein kysytyt kysymykset',
    faqBody: 'Nopeat vastaukset ennen tarjouspyynnön lähettämistä.',
    faqItems: [
      { q: 'Kuinka nopeasti saan tarjouksen?', a: 'Yleensä 24 tunnin sisällä. Suuremmat työt voivat vaatia ensin kohdekäynnin.' },
      { q: 'Millä alueilla toimitte?', a: 'Toimimme pääasiassa Helsingissä ja Tartossa.' },
      { q: 'Sisältyykö töihin takuu?', a: 'Kyllä, työn tyypistä ja sopimuksen laajuudesta riippuen.' },
    ],
    ctaTitle: 'Lähetä yhteydenotto ja viemme projektisi nopeasti eteenpäin.',
    ctaBody:
      'Kerro kohteesta, sijainnista ja toivotusta aikataulusta. Vastaamme nopeasti ja siirrämme tiedot heti työnkulkuun.',
    ctaEyebrow: 'Seuraava askel',
    ctaPhone: 'Soita',
  },
}

const page = usePage()
const locale = computed(() => page.props.locale ?? 'et')
const copy = computed(() => CONTENT[locale.value] ?? CONTENT.et)

const faqOpen = ref(0)
let statsObserver = null

function toggleFaq(index) {
  faqOpen.value = faqOpen.value === index ? -1 : index
}

function countUp(el, to, duration = 900) {
  const start = performance.now()
  const formatter = new Intl.NumberFormat(locale.value === 'et' ? 'et-EE' : locale.value === 'fi' ? 'fi-FI' : 'en-US')

  function tick(now) {
    const progress = Math.min(1, (now - start) / duration)
    const eased = progress < 1 ? 1 - Math.pow(1 - progress, 3) : 1
    el.textContent = formatter.format(Math.round(to * eased))
    if (progress < 1) requestAnimationFrame(tick)
  }

  requestAnimationFrame(tick)
}

onMounted(() => {
  const stats = document.querySelectorAll('[data-count]')
  statsObserver = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue

        const total = Number(entry.target.getAttribute('data-count') || '0')
        countUp(entry.target, total)
        statsObserver.unobserve(entry.target)
      }
    },
    { threshold: 0.35 }
  )

  stats.forEach((stat) => statsObserver.observe(stat))
})

onBeforeUnmount(() => {
  if (statsObserver) statsObserver.disconnect()
})
</script>

<template>
  <PublicLayout>
    <section class="relative overflow-hidden border-b border-slate-800">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(251,191,36,0.18),transparent_22%),linear-gradient(115deg,rgba(2,6,23,0.95),rgba(15,23,42,0.84)_55%,rgba(30,41,59,0.74))]"></div>
      <div class="absolute inset-y-0 right-0 hidden w-[22%] bg-amber-400/90 lg:block"></div>
      <div class="relative mx-auto max-w-[92rem] px-4 py-16 md:py-24 xl:px-6">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
          <div>
            <div
              data-reveal="zoom-in"
              style="--reveal-delay: 40ms"
              class="inline-flex items-center gap-2 border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-amber-300"
            >
              <span class="h-2 w-2 rounded-full bg-amber-400"></span>
              {{ copy.badge }}
            </div>

            <h1
              data-reveal="rise"
              style="--reveal-delay: 120ms"
              class="mt-6 max-w-4xl text-4xl font-semibold tracking-tight text-white md:text-6xl md:leading-[1.02]"
            >
              {{ copy.heroTitle }}
            </h1>

            <p
              data-reveal="rise"
              style="--reveal-delay: 200ms"
              class="mt-6 max-w-2xl text-base text-slate-300 md:text-lg"
            >
              {{ copy.heroBody }}
            </p>

            <div data-reveal="rise" style="--reveal-delay: 260ms" class="mt-8 flex flex-wrap gap-3">
              <Link
                href="/contact"
                class="inline-flex items-center bg-amber-400 px-6 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-slate-950 shadow-sm transition hover:bg-amber-300"
              >
                {{ copy.primaryCta }}
              </Link>
            </div>

            <div data-reveal="zoom-in" style="--reveal-delay: 320ms" class="mt-10 grid max-w-2xl gap-3 sm:grid-cols-3">
              <div
                v-for="stat in copy.stats"
                :key="stat.label"
                class="border border-white/10 bg-white/5 p-5 backdrop-blur-sm"
              >
                <div class="text-3xl font-semibold tracking-tight text-white">
                  <span :data-count="stat.value">0</span>{{ stat.suffix }}
                </div>
                <div class="mt-1 text-sm text-slate-300">{{ stat.label }}</div>
              </div>
            </div>
          </div>

          <div data-reveal="slide-left" style="--reveal-delay: 160ms" class="grid gap-4">
            <div class="relative min-h-[22rem] overflow-hidden border border-slate-200/90 bg-[linear-gradient(160deg,#ffffff,#f8fafc)] p-6 shadow-[0_20px_40px_rgba(15,23,42,0.2)]">

              <div class="relative">
                <div class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-slate-500">
                  {{ copy.processTitle }}
                </div>

                <div class="mt-4 text-xl font-semibold leading-tight text-slate-950">
                  {{ copy.processSteps[0] }}
                </div>

                <div class="mt-5 space-y-3">
                  <div
                    v-for="(step, index) in copy.processSteps.slice(1)"
                    :key="step"
                    class="flex items-start gap-3 rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3"
                  >
                    <div class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-slate-950 text-[10px] font-semibold text-white">
                      {{ index + 1 }}
                    </div>
                    <div class="text-sm leading-6 text-slate-600">{{ step }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex min-h-[17.5rem] flex-col bg-amber-400 p-6 text-slate-950 shadow-[0_20px_40px_rgba(15,23,42,0.16)]">
              <div class="text-base font-semibold uppercase tracking-[0.14em] text-slate-900 md:text-lg">{{ copy.valueTitle }}</div>
              <div class="mt-5 space-y-2">
                <div
                  v-for="point in copy.valuePoints"
                  :key="point"
                  class="border border-slate-900/10 bg-white/25 px-4 py-3 text-sm text-slate-900"
                >
                  {{ point }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto mt-16 max-w-[92rem] px-4 xl:px-6">
      <div>
        <div data-reveal="rise" class="max-w-2xl">
          <div class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">{{ copy.servicesTitle }}</div>
          <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">
            {{ copy.servicesBody }}
          </h2>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          <div
            v-for="(service, index) in copy.services"
            :key="service.title"
            data-reveal="card"
            :style="{ '--reveal-delay': `${100 + index * 90}ms` }"
            class="relative overflow-hidden border border-slate-200 bg-white p-6 shadow-[0_16px_32px_rgba(15,23,42,0.04)] transition hover:-translate-y-1 hover:shadow-[0_22px_44px_rgba(15,23,42,0.08)]"
          >
            <div class="absolute right-4 top-3 text-6xl font-semibold leading-none text-slate-100">
              0{{ index + 1 }}
            </div>
            <div class="h-1 w-12 bg-amber-400"></div>
            <div class="mt-5 text-lg font-semibold text-slate-950">{{ service.title }}</div>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ service.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto mt-16 max-w-[92rem] px-4 xl:px-6">
      <div class="grid gap-6 lg:grid-cols-[1fr_1.1fr]">
        <div data-reveal="slide-right" class="flex min-h-[20rem] flex-col justify-between bg-slate-950 p-7 text-white shadow-[0_20px_40px_rgba(15,23,42,0.14)] md:min-h-[15rem]">
          <div class="text-base font-semibold uppercase tracking-[0.12em] text-amber-300">{{ copy.proofTitle }}</div>
          <p class="mt-4 max-w-xl text-lg leading-8 text-slate-300">
            {{ copy.proofBody }}
          </p>
          <Link
            href="/portfolio"
            class="mt-6 inline-flex w-fit items-center bg-amber-400 px-6 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-slate-950 transition hover:bg-amber-300"
          >
            {{ copy.proofCta }}
          </Link>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
          <div
            v-for="(card, index) in copy.proofCards"
            :key="card.title"
            data-reveal="card"
            :style="{ '--reveal-delay': `${140 + index * 95}ms` }"
            class="group overflow-hidden border border-slate-200 bg-white shadow-[0_16px_32px_rgba(15,23,42,0.04)]"
          >
            <div class="relative h-44 bg-[linear-gradient(140deg,#1e293b,#334155)]">
              <img
                v-if="card.image"
                :src="card.image"
                :alt="card.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
              />
              <div class="absolute inset-x-0 top-0 h-2 bg-amber-400"></div>
            </div>
            <div class="p-5">
              <div class="text-base font-semibold text-slate-950">{{ card.title }}</div>
              <div class="mt-2 text-sm text-slate-500">{{ card.detail }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto mt-16 max-w-[92rem] px-4 xl:px-6">
      <div class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <div data-reveal="slide-right" class="border border-slate-200 bg-white p-7 shadow-[0_16px_32px_rgba(15,23,42,0.04)]">
          <div class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">{{ copy.faqTitle }}</div>

          <div class="mt-6 space-y-3">
            <div
              v-for="(item, index) in copy.faqItems"
              :key="item.q"
              class="border border-slate-200"
            >
              <button
                type="button"
                class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left"
                @click="toggleFaq(index)"
              >
                <span class="text-sm font-semibold text-slate-900">{{ item.q }}</span>
                <span class="grid h-7 w-7 place-items-center bg-slate-100 text-slate-700">
                  {{ faqOpen === index ? '−' : '+' }}
                </span>
              </button>
              <div v-show="faqOpen === index" class="px-5 pb-5 text-sm leading-6 text-slate-600">
                {{ item.a }}
              </div>
            </div>
          </div>
        </div>

        <div data-reveal="slide-left" style="--reveal-delay: 130ms" class="bg-[linear-gradient(120deg,#111827,#1f2937)] p-8 text-white shadow-[0_24px_48px_rgba(15,23,42,0.14)]">
          <div class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-300">{{ copy.ctaEyebrow }}</div>
          <div class="mt-3 text-3xl font-semibold tracking-tight">{{ copy.ctaTitle }}</div>
          <p class="mt-4 text-sm leading-7 text-slate-300">
            {{ copy.ctaBody }}
          </p>
          <div class="mt-8 flex flex-wrap gap-3">
            <Link
              href="/contact"
              class="inline-flex items-center bg-amber-400 px-6 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-slate-950 transition hover:bg-amber-300"
            >
              {{ copy.primaryCta }}
            </Link>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

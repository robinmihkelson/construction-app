<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const CONTENT = {
  et: {
    title: 'Kontakt ja hinnapäring',
    body: 'Kirjelda objekti, asukohta ja soovitud ajakava. Vastame kiiresti ning viime info kohe tööprotsessi.',
    phoneLabel: 'Telefon',
    emailLabel: 'E-post',
    regionLabel: 'Piirkond',
    regionValue: 'Helsingi ja Tartu',
    promises: [
      'Vastame tavaliselt 24 tunni jooksul',
      'Saad selge järgmise sammu ja realistliku ajakava',
      'Päring läheb kohe meie sisemisse töövoogu',
    ],
    fields: {
      name: 'Nimi',
      email: 'E-post',
      phone: 'Telefon (valikuline)',
      message: 'Kirjelda tööd',
    },
    submit: 'Saada päring',
    sending: 'Saadan...',
    success: 'Aitäh! Päring saadetud.',
  },
  en: {
    title: 'Contact and quote request',
    body: 'Describe the site, location, and preferred timeline. We respond quickly and move the details directly into our workflow.',
    phoneLabel: 'Phone',
    emailLabel: 'Email',
    regionLabel: 'Coverage',
    regionValue: 'Helsinki and Tartu',
    promises: [
      'We usually respond within 24 hours',
      'You get a clear next step and a realistic timeline',
      'Your inquiry moves directly into our internal workflow',
    ],
    fields: {
      name: 'Name',
      email: 'Email',
      phone: 'Phone (optional)',
      message: 'Describe the work',
    },
    submit: 'Send inquiry',
    sending: 'Sending...',
    success: 'Thank you! Your inquiry has been sent.',
  },
  fi: {
    title: 'Yhteys ja tarjouspyyntö',
    body: 'Kerro kohteesta, sijainnista ja toivotusta aikataulusta. Vastaamme nopeasti ja siirrämme tiedot heti työnkulkuun.',
    phoneLabel: 'Puhelin',
    emailLabel: 'Sähköposti',
    regionLabel: 'Toiminta-alue',
    regionValue: 'Helsinki ja Tartto',
    promises: [
      'Vastaamme yleensä 24 tunnin sisällä',
      'Saat selkeän seuraavan askeleen ja realistisen aikataulun',
      'Yhteydenotto siirtyy suoraan sisäiseen työnkulkuumme',
    ],
    fields: {
      name: 'Nimi',
      email: 'Sähköposti',
      phone: 'Puhelin (valinnainen)',
      message: 'Kuvaile työ',
    },
    submit: 'Lähetä yhteydenotto',
    sending: 'Lähetetään...',
    success: 'Kiitos! Yhteydenotto on lähetetty.',
  },
}

const page = usePage()
const locale = computed(() => page.props.locale ?? 'et')
const copy = computed(() => CONTENT[locale.value] ?? CONTENT.et)

const form = useForm({
  name: '',
  email: '',
  phone: '',
  message: '',
})

function sanitizePhoneInput(event) {
  form.phone = event.target.value.replace(/\D+/g, '')
}

function submit() {
  form.post(route('public.contact.store'), { preserveScroll: true })
}
</script>

<template>
  <PublicLayout>
    <section class="mx-auto max-w-[92rem] px-4 py-14 xl:px-6">
      <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-6">
          <div data-nav-logo-tone="inverse" data-reveal="slide-right" class="border border-slate-800 bg-slate-950 p-8 text-white shadow-[0_22px_44px_rgba(15,23,42,0.14)]">
            <h1 class="mt-2 text-4xl font-semibold tracking-tight text-white">{{ copy.title }}</h1>
            <p class="mt-4 text-base leading-7 text-slate-300">{{ copy.body }}</p>
          </div>

          <div data-reveal="slide-right" style="--reveal-delay: 100ms" class="border border-slate-200 bg-amber-400 p-8 text-slate-950 shadow-[0_20px_44px_rgba(15,23,42,0.08)]">
            <div class="grid gap-4 text-sm">
              <div class="border border-slate-900/10 bg-white/40 px-4 py-4">
                <div class="text-slate-500">{{ copy.phoneLabel }}</div>
                <div class="mt-1 font-semibold text-slate-950">+372 5617 2802</div>
              </div>
              <div class="border border-slate-900/10 bg-white/40 px-4 py-4">
                <div class="text-slate-500">{{ copy.emailLabel }}</div>
                <div class="mt-1 font-semibold text-slate-950">raigomihkelson@gmail.com</div>
              </div>
              <div class="border border-slate-900/10 bg-white/40 px-4 py-4">
                <div class="text-slate-500">{{ copy.regionLabel }}</div>
                <div class="mt-1 font-semibold text-slate-950">{{ copy.regionValue }}</div>
              </div>
            </div>
          </div>
        </div>

        <div data-reveal="slide-left" class="border border-slate-200 bg-white p-8 shadow-[0_20px_40px_rgba(15,23,42,0.05)]">
          <form @submit.prevent="submit" class="space-y-5">
            <div>
              <label class="text-sm font-semibold text-slate-900">{{ copy.fields.name }}</label>
              <input
                v-model="form.name"
                class="mt-2 w-full border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-amber-300 focus:bg-white"
              />
              <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
              <div>
                <label class="text-sm font-semibold text-slate-900">{{ copy.fields.email }}</label>
                <input
                  v-model="form.email"
                  type="email"
                  inputmode="email"
                  autocomplete="email"
                  spellcheck="false"
                  class="mt-2 w-full border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-amber-300 focus:bg-white"
                />
                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</div>
              </div>
              <div>
                <label class="text-sm font-semibold text-slate-900">{{ copy.fields.phone }}</label>
                <input
                  v-model="form.phone"
                  type="tel"
                  inputmode="numeric"
                  autocomplete="tel-national"
                  pattern="[0-9]*"
                  @input="sanitizePhoneInput"
                  class="mt-2 w-full border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-amber-300 focus:bg-white"
                />
                <div v-if="form.errors.phone" class="mt-2 text-sm text-red-600">{{ form.errors.phone }}</div>
              </div>
            </div>

            <div>
              <label class="text-sm font-semibold text-slate-900">{{ copy.fields.message }}</label>
              <textarea
                v-model="form.message"
                rows="7"
                class="mt-2 w-full border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-amber-300 focus:bg-white"
              ></textarea>
              <div v-if="form.errors.message" class="mt-2 text-sm text-red-600">{{ form.errors.message }}</div>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex w-full items-center justify-center bg-slate-950 px-5 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ form.processing ? copy.sending : copy.submit }}
            </button>

            <div v-if="form.recentlySuccessful" class="text-sm font-medium text-emerald-700">
              {{ copy.success }}
            </div>
          </form>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { PUBLIC_MESSAGES } from '@/i18n/public'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const locale = computed(() => page.props.locale ?? 'et')
const pageUrl = computed(() => page.url ?? '/')
const mobileOpen = ref(false)
const scrolled = ref(false)
const scrollProgress = ref(0)
const headerRef = ref(null)
const logoRef = ref(null)
const logoTone = ref('default')
let revealObserver = null
let logoToneFrame = null

const locales = [
  { code: 'et', label: 'Eesti', flag: '/images/flags/et.webp' },
  { code: 'en', label: 'English', flag: '/images/flags/en.webp' },
  { code: 'fi', label: 'Suomi', flag: '/images/flags/fi.webp' },
]

const t = (key) =>
  PUBLIC_MESSAGES[locale.value]?.[key] ?? PUBLIC_MESSAGES.et[key] ?? key

const navLinks = computed(() => [
  { href: '/', label: t('nav_home') },
  { href: '/services', label: t('nav_services') },
  { href: '/portfolio', label: t('nav_portfolio') },
  { href: '/contact', label: t('nav_contact') },
])

function setLocale(code) {
  mobileOpen.value = false

  window.location.assign(
    route('locale.set', {
      locale: code,
      redirect: pageUrl.value,
    })
  )
}

function isActive(href) {
  if (href === '/') return pageUrl.value === '/'

  return pageUrl.value === href || pageUrl.value.startsWith(`${href}/`)
}

function navClasses(href) {
  return isActive(href)
    ? `inline-flex items-center whitespace-nowrap border-b-2 border-amber-400 px-1 pb-2 pt-2 text-[12px] font-semibold uppercase tracking-[0.14em] transition-colors ${
        logoTone.value === 'inverse' ? 'text-white' : 'text-slate-950'
      }`
    : 'inline-flex items-center whitespace-nowrap border-b-2 border-transparent px-1 pb-2 pt-2 text-[12px] font-medium uppercase tracking-[0.14em] text-slate-500 transition hover:text-slate-950'
}

function localeButtonClasses(code) {
  return locale.value === code
    ? 'inline-flex h-9 w-11 items-center justify-center overflow-hidden rounded-full border border-slate-950 bg-white p-0.5 shadow-[0_0_0_1px_rgba(15,23,42,0.08)]'
    : 'inline-flex h-9 w-11 items-center justify-center overflow-hidden rounded-full border border-slate-300/80 bg-white p-0.5 transition hover:border-slate-400 hover:shadow-[0_0_0_1px_rgba(148,163,184,0.2)]'
}

function onScroll() {
  scrolled.value = window.scrollY > 8
  const doc = document.documentElement
  const range = doc.scrollHeight - window.innerHeight
  scrollProgress.value = range > 0 ? Math.min(1, Math.max(0, window.scrollY / range)) : 0
  scheduleLogoToneCheck()
}

function resolveLogoToneFromPoint(x, y) {
  const stack = document
    .elementsFromPoint(x, y)
    .filter((element) => !headerRef.value?.contains(element))

  for (const element of stack) {
    const toneHost = element.closest?.('[data-nav-logo-tone]')
    const tone = toneHost?.getAttribute('data-nav-logo-tone')

    if (tone === 'inverse' || tone === 'default') {
      return tone
    }
  }

  return 'default'
}

function updateLogoTone() {
  if (typeof window === 'undefined' || !logoRef.value) return

  const rect = logoRef.value.getBoundingClientRect()
  if (!rect.width || !rect.height) return

  const samplePoints = [
    [rect.left + rect.width * 0.2, rect.top + rect.height * 0.5],
    [rect.left + rect.width * 0.5, rect.top + rect.height * 0.5],
    [rect.left + rect.width * 0.8, rect.top + rect.height * 0.5],
  ]

  let inverseHits = 0

  for (const [x, y] of samplePoints) {
    if (resolveLogoToneFromPoint(x, y) === 'inverse') {
      inverseHits += 1
    }
  }

  logoTone.value = inverseHits >= 2 ? 'inverse' : 'default'
}

function scheduleLogoToneCheck() {
  if (typeof window === 'undefined' || logoToneFrame) return

  logoToneFrame = window.requestAnimationFrame(() => {
    logoToneFrame = null
    updateLogoTone()
  })
}

function setupScrollReveals() {
  if (typeof window === 'undefined') return

  const targets = Array.from(document.querySelectorAll('[data-reveal]'))

  if (revealObserver) revealObserver.disconnect()

  if (!targets.length) return

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    targets.forEach((el) => el.classList.add('is-visible'))
    return
  }

  revealObserver = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue

        entry.target.classList.add('is-visible')

        if (!entry.target.hasAttribute('data-reveal-repeat')) {
          revealObserver?.unobserve(entry.target)
        }
      }
    },
    { threshold: 0.16, rootMargin: '0px 0px -8% 0px' }
  )

  targets.forEach((el) => revealObserver?.observe(el))
}

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', scheduleLogoToneCheck, { passive: true })
  nextTick(() => {
    setupScrollReveals()
    updateLogoTone()
  })
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('resize', scheduleLogoToneCheck)
  if (revealObserver) revealObserver.disconnect()
  if (logoToneFrame) window.cancelAnimationFrame(logoToneFrame)
})

watch(pageUrl, () => {
  mobileOpen.value = false
  nextTick(() => {
    setupScrollReveals()
    updateLogoTone()
  })
})
</script>

<template>
  <div class="min-h-screen bg-[linear-gradient(180deg,#f7f3ec_0px,#f5f2ea_170px,#ffffff_170px,#ffffff_100%)] md:bg-[linear-gradient(180deg,#f7f3ec_0px,#f5f2ea_210px,#ffffff_210px,#ffffff_100%)] text-slate-900">
    

    <header
      ref="headerRef"
      class="relative sticky top-0 z-50 transition-all duration-200"
      :class="scrolled ? 'bg-white/92 backdrop-blur-xl shadow-sm' : 'bg-white/84 backdrop-blur-md'"
    >
      <div class="mx-auto flex min-h-[5.25rem] max-w-[90rem] items-center justify-between gap-4 px-4 py-3 xl:px-6">
        <Link href="/" class="flex shrink-0 items-center gap-3">
          <div
            ref="logoRef"
            class="leading-tight px-2 py-1 transition-all duration-200"
            :class="
              logoTone === 'inverse'
                ? 'bg-slate-950/92 shadow-[0_10px_24px_rgba(15,23,42,0.2)]'
                : 'bg-transparent shadow-none'
            "
          >
            <div
              class="whitespace-nowrap text-2xl font-semibold tracking-[0.06em] transition-colors duration-200"
              :class="logoTone === 'inverse' ? 'text-white' : 'text-slate-950'"
            >
              {{ t('brand_name') }}
            </div>
            <div
              class="hidden whitespace-nowrap text-[11px] uppercase tracking-[0.16em] transition-colors duration-200 xl:block"
              :class="logoTone === 'inverse' ? 'text-slate-300' : 'text-slate-500'"
            >
              {{ t('brand_tagline') }}
            </div>
          </div>
        </Link>

        <nav class="hidden min-w-0 flex-1 items-center justify-center gap-4 px-3 xl:flex 2xl:gap-6 2xl:px-5">
          <Link
            v-for="item in navLinks"
            :key="item.href"
            :href="item.href"
            :class="navClasses(item.href)"
          >
            {{ item.label }}
          </Link>
        </nav>

        <div class="hidden shrink-0 items-center gap-2 xl:flex">
          <div class="flex items-center gap-1 rounded-2xl border border-slate-300 bg-white px-1.5 py-1.5">
            <span class="hidden px-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 2xl:inline">{{ t('locale_label') }}</span>
            <button
              v-for="item in locales"
              :key="item.code"
              type="button"
              :aria-label="item.label"
              :aria-pressed="locale === item.code"
              :title="item.label"
              :class="localeButtonClasses(item.code)"
              @click="setLocale(item.code)"
            >
              <img
                :src="item.flag"
                alt=""
                class="block h-full w-full rounded-full object-cover"
              />
              <span class="sr-only">{{ item.label }}</span>
            </button>
          </div>

          <Link
            :href="user ? '/dashboard' : '/login'"
            class="inline-flex items-center whitespace-nowrap border border-slate-300 bg-white px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.1em] text-slate-900 transition hover:bg-slate-50 2xl:px-4 2xl:text-[11px]"
          >
            {{ user ? t('nav_app') : t('nav_login') }}
          </Link>

          <Link
            href="/contact"
            class="hidden items-center whitespace-nowrap bg-slate-950 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.1em] text-white transition hover:bg-slate-800 2xl:inline-flex 2xl:px-4 2xl:text-[11px]"
          >
            {{ t('nav_quote') }}
          </Link>
        </div>

        <button
          type="button"
          class="inline-flex h-11 w-11 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-50 xl:hidden"
          aria-label="Toggle menu"
          @click="mobileOpen = !mobileOpen"
        >
          <span v-if="!mobileOpen">☰</span>
          <span v-else>✕</span>
        </button>
      </div>

      <div v-if="mobileOpen" class="border-t border-slate-200 bg-white xl:hidden">
        <div class="mx-auto max-w-[94rem] px-4 py-4 xl:px-6">
          <div class="grid gap-2">
            <Link
              v-for="item in navLinks"
              :key="item.href"
              :href="item.href"
              class="rounded-sm border px-4 py-3 text-sm font-semibold uppercase tracking-[0.14em]"
              :class="isActive(item.href) ? 'border-amber-300 bg-amber-50 text-slate-950' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
              @click="mobileOpen = false"
            >
              {{ item.label }}
            </Link>
          </div>

          <div class="mt-4 grid gap-2 sm:grid-cols-2">
            <Link
              href="/contact"
              class="inline-flex items-center justify-center rounded-sm bg-slate-950 px-4 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-white"
              @click="mobileOpen = false"
            >
              {{ t('nav_quote') }}
            </Link>
            <Link
              :href="user ? '/dashboard' : '/login'"
              class="inline-flex items-center justify-center rounded-sm border border-slate-300 bg-white px-4 py-3 text-sm font-semibold uppercase tracking-[0.14em] text-slate-900"
              @click="mobileOpen = false"
            >
              {{ user ? t('nav_app') : t('nav_login') }}
            </Link>
          </div>

          <div class="mt-4 flex flex-wrap items-center justify-center gap-2 border-t border-slate-200 pt-4">
            <button
              v-for="item in locales"
              :key="item.code"
              type="button"
              :aria-label="item.label"
              :aria-pressed="locale === item.code"
              :title="item.label"
              :class="localeButtonClasses(item.code)"
              @click="setLocale(item.code)"
            >
              <img
                :src="item.flag"
                alt=""
                class="block h-full w-full rounded-full object-cover"
              />
              <span class="sr-only">{{ item.label }}</span>
            </button>
          </div>
        </div>
      </div>

      <div
        class="public-scroll-progress pointer-events-none absolute inset-x-0 bottom-0 h-[2px] origin-left bg-[linear-gradient(90deg,#fbbf24,#334155,#fbbf24)] transition-transform duration-200"
        :style="{ transform: `scaleX(${scrollProgress})` }"
      ></div>
    </header>

    <main>
      <slot />
    </main>

    <footer data-nav-logo-tone="inverse" class="mt-20 border-t border-slate-800 bg-slate-950 text-white">
      <div class="mx-auto grid max-w-[94rem] gap-10 px-4 py-14 md:grid-cols-[1.4fr_0.8fr_0.8fr] xl:px-6">
        <div>
          <div class="flex items-center gap-3">
            <div>
              <div class="text-2xl font-semibold text-white">{{ t('brand_name') }}</div>
              <div class="text-[11px] uppercase tracking-[0.16em] text-slate-400">{{ t('brand_tagline') }}</div>
            </div>
          </div>
          <div class="mt-5 grid gap-2 text-sm text-slate-300">
            <a class="transition hover:text-white" href="tel:+3720000000">+372 5617 2802</a>
            <a class="transition hover:text-white" href="mailto:info@nordline.ee">raigomihkelson@gmail.com</a>
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold uppercase tracking-[0.14em] text-white">{{ t('footer_menu') }}</div>
          <div class="mt-4 grid gap-3 text-sm text-slate-300">
            <Link class="transition hover:text-white" href="/">{{ t('footer_home') }}</Link>
            <Link class="transition hover:text-white" href="/services">{{ t('footer_services') }}</Link>
            <Link class="transition hover:text-white" href="/portfolio">{{ t('footer_portfolio') }}</Link>
            <Link class="transition hover:text-white" href="/contact">{{ t('footer_contact') }}</Link>
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold uppercase tracking-[0.14em] text-white">{{ t('footer_app') }}</div>
          <div class="mt-4 grid gap-3 text-sm text-slate-300">
            <Link class="transition hover:text-white" href="/login">{{ t('footer_login') }}</Link>
          </div>
        </div>
      </div>

      <div class="border-t border-slate-800">
        <div class="mx-auto flex max-w-[92rem] flex-col gap-2 px-4 py-5 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between xl:px-6">
          <span>© {{ new Date().getFullYear() }} {{ t('brand_name') }}</span>
        </div>
      </div>
    </footer>
  </div>
</template>

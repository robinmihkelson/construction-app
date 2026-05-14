import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

const VARIANTS = {
    rise:          { y: 26, scale: 0.985 },
    'slide-right': { x: -34, y: 16, scale: 0.99 },
    'slide-left':  { x: 34,  y: 16, scale: 0.99 },
    'zoom-in':     { y: 20, scale: 0.9 },
    card:          { y: 30, scale: 0.96, rotationX: 6, transformOrigin: 'center bottom' },
    curtain:       { y: 44, scale: 0.97 },
}

const RESET = {
    opacity: 1,
    x: 0,
    y: 0,
    scale: 1,
    rotationX: 0,
    filter: 'blur(0px)',
}

const HIDDEN_BASE = {
    opacity: 0,
    filter: 'blur(2px)',
}

const VIEWPORT_THRESHOLD = 0.94

export function applyReveals(root = document.body) {
    if (!root || typeof window === 'undefined') return

    const elements = Array.from(root.querySelectorAll('[data-reveal]'))
    if (!elements.length) return

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const viewportHeight = window.innerHeight

    elements.forEach((el) => {
        const name = el.getAttribute('data-reveal') || 'rise'
        const variant = VARIANTS[name] ?? VARIANTS.rise
        const rawDelay = el.style.getPropertyValue('--reveal-delay').trim()
        const delaySec = rawDelay ? parseFloat(rawDelay) / 1000 : 0

        if (prefersReduced) {
            gsap.set(el, { ...RESET, clearProps: 'filter,transform,opacity' })
            return
        }

        const fromState = { ...HIDDEN_BASE, ...variant }
        const toState = {
            ...RESET,
            duration: 0.85,
            ease: 'power3.out',
            delay: delaySec,
            force3D: true,
        }

        // If an element is already on screen at apply-time, animate it
        // straight away. Going through ScrollTrigger here is unreliable —
        // when the trigger initialises already past its start position the
        // tween can stall until the next refresh/scroll event, which
        // produces a long "frozen, then suddenly pops in" delay.
        const rect = el.getBoundingClientRect()
        const aboveFold = rect.top < viewportHeight * VIEWPORT_THRESHOLD && rect.bottom > 0

        if (aboveFold) {
            gsap.fromTo(el, fromState, { ...toState, immediateRender: true })
            return
        }

        gsap.fromTo(el, fromState, {
            ...toState,
            immediateRender: true,
            scrollTrigger: {
                trigger: el,
                start: 'top 86%',
                toggleActions: 'play none none none',
                once: true,
            },
        })
    })

    ScrollTrigger.refresh()
}

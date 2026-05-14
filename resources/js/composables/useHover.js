import { gsap } from 'gsap'

/**
 * Attach a soft "lift" hover micro-interaction to every element matching
 * `data-hover-lift` inside `root`. Returns a cleanup function.
 */
export function attachHoverLift(root = document) {
    const cleanups = []
    if (!root || typeof window === 'undefined') return () => {}

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return () => {}
    }

    root.querySelectorAll('[data-hover-lift]').forEach((card) => {
        const setY = gsap.quickTo(card, 'y', { duration: 0.35, ease: 'power3.out' })
        const onEnter = () => setY(-6)
        const onLeave = () => setY(0)
        card.addEventListener('mouseenter', onEnter)
        card.addEventListener('mouseleave', onLeave)
        cleanups.push(() => {
            card.removeEventListener('mouseenter', onEnter)
            card.removeEventListener('mouseleave', onLeave)
            gsap.set(card, { clearProps: 'transform' })
        })
    })

    return () => cleanups.forEach((fn) => fn())
}

/**
 * Attach a magnetic-cursor effect to a single element. Movement is scaled
 * down (25% horizontal, 40% vertical) so the cue stays subtle.
 */
export function attachMagnetic(el) {
    if (!el || typeof window === 'undefined') return () => {}

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return () => {}
    }

    const setX = gsap.quickTo(el, 'x', { duration: 0.5, ease: 'power3.out' })
    const setY = gsap.quickTo(el, 'y', { duration: 0.5, ease: 'power3.out' })

    const onMove = (e) => {
        const r = el.getBoundingClientRect()
        setX((e.clientX - r.left - r.width / 2) * 0.25)
        setY((e.clientY - r.top - r.height / 2) * 0.4)
    }
    const onLeave = () => {
        setX(0)
        setY(0)
    }

    el.addEventListener('mousemove', onMove)
    el.addEventListener('mouseleave', onLeave)

    return () => {
        el.removeEventListener('mousemove', onMove)
        el.removeEventListener('mouseleave', onLeave)
        gsap.set(el, { clearProps: 'transform' })
    }
}

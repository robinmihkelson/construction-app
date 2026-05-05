<script setup>
import { computed } from 'vue';
import { useTheme } from '@/composables/useTheme';

const { theme, setTheme } = useTheme();

const segments = [
    { value: 'light',  label: 'Light theme'  },
    { value: 'system', label: 'System theme' },
    { value: 'dark',   label: 'Dark theme'   },
];

const cycleOrder = ['light', 'dark', 'system'];

const currentLabel = computed(() => {
    const found = segments.find((s) => s.value === theme.value);
    return found ? found.label : 'Theme';
});

function cycleTheme() {
    const i = cycleOrder.indexOf(theme.value);
    const next = cycleOrder[(i + 1) % cycleOrder.length] ?? 'system';
    setTheme(next);
}
</script>

<template>
    <button
        type="button"
        @click="cycleTheme"
        :aria-label="currentLabel"
        :title="currentLabel"
        class="flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)] sm:hidden"
    >
        <svg v-if="theme === 'light'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="4" />
            <path stroke-linecap="round" d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4 7 17M17 7l1.4-1.4" />
        </svg>
        <svg v-else-if="theme === 'system'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="4" width="18" height="12" rx="2" />
            <path stroke-linecap="round" d="M8 20h8M12 16v4" />
        </svg>
        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z" />
        </svg>
    </button>

    <div
        class="hidden items-center rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] p-0.5 sm:inline-flex"
        role="radiogroup"
        aria-label="Theme"
    >
        <button
            v-for="seg in segments"
            :key="seg.value"
            type="button"
            role="radio"
            :aria-checked="theme === seg.value"
            :aria-label="seg.label"
            :title="seg.label"
            @click="setTheme(seg.value)"
            class="flex h-7 w-7 items-center justify-center rounded-md text-[var(--slate-soft)] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
            :class="theme === seg.value
                ? 'bg-[var(--panel-bg)] text-[var(--ink)] shadow-[var(--shadow-xs)]'
                : 'hover:text-[var(--ink)]'"
        >
            <svg v-if="seg.value === 'light'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="4" />
                <path stroke-linecap="round" d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4 7 17M17 7l1.4-1.4" />
            </svg>
            <svg v-else-if="seg.value === 'system'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="4" width="18" height="12" rx="2" />
                <path stroke-linecap="round" d="M8 20h8M12 16v4" />
            </svg>
            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z" />
            </svg>
        </button>
    </div>
</template>

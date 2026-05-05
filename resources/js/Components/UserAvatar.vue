<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    name: { type: String, default: '' },
    email: { type: String, default: '' },
    url: { type: [String, null], default: null },
    seed: { type: [Number, String, null], default: null },
    size: { type: Number, default: 32 },
    accent: { type: Boolean, default: false },
});

const imageFailed = ref(false);
watch(() => props.url, () => { imageFailed.value = false; });

const initials = computed(() => {
    const source = (props.name || '').trim() || (props.email || '').split('@')[0] || '?';
    const parts = source.split(/\s+/).filter(Boolean);
    if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    return source.slice(0, 2).toUpperCase();
});

const palette = [
    'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
    'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
    'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300',
];

function hashCode(str) {
    let h = 0;
    for (let i = 0; i < str.length; i++) h = ((h << 5) - h) + str.charCodeAt(i);
    return Math.abs(h);
}

const fallbackClass = computed(() => {
    if (props.accent) return 'bg-[var(--accent-soft)] text-[var(--accent)]';
    const seed = props.seed != null ? String(props.seed) : (props.email || props.name || 'x');
    return palette[hashCode(seed) % palette.length];
});

const showImage = computed(() => !!props.url && !imageFailed.value);

const sizeStyle = computed(() => ({
    width: `${props.size}px`,
    height: `${props.size}px`,
    fontSize: `${Math.max(10, Math.round(props.size * 0.36))}px`,
}));
</script>

<template>
    <span
        class="inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full font-bold leading-none"
        :class="showImage ? 'bg-[var(--panel-muted)]' : fallbackClass"
        :style="sizeStyle"
        :title="name || email"
    >
        <img
            v-if="showImage"
            :src="url"
            :alt="name || email"
            class="h-full w-full object-cover"
            @error="imageFailed = true"
        />
        <span v-else>{{ initials }}</span>
    </span>
</template>

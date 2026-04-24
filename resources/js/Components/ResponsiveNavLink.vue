<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full rounded-lg bg-[color:var(--accent-soft)] px-3 py-2.5 text-start text-sm font-semibold text-[color:var(--accent)] transition duration-150 ease-in-out'
        : 'block w-full rounded-lg px-3 py-2.5 text-start text-sm font-medium text-[color:var(--slate-soft)] transition duration-150 ease-in-out hover:bg-[color:var(--panel-muted)] hover:text-[color:var(--ink)]',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

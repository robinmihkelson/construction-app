<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({ projects: Array })

const avatarColors = [
    'bg-blue-100 text-blue-700',
    'bg-violet-100 text-violet-700',
    'bg-emerald-100 text-emerald-700',
    'bg-amber-100 text-amber-700',
    'bg-rose-100 text-rose-700',
    'bg-cyan-100 text-cyan-700',
]

function avatarColor(id) {
    return avatarColors[id % avatarColors.length]
}

function initial(name) {
    return (name ?? '?').charAt(0).toUpperCase()
}
</script>

<template>
    <div class="space-y-6">

        <!-- Page header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="app-label">Messaging</div>
                <h1 class="mt-1 text-2xl font-bold text-[var(--ink)]">Chat channels</h1>
                <p class="mt-1 text-sm text-[var(--slate-soft)]">Project threads, file sharing, and team updates in one place.</p>
            </div>
            <div class="shrink-0 rounded-xl border border-[var(--line)] bg-white px-6 py-3 text-center shadow-sm">
                <div class="app-label">Channels</div>
                <div class="mt-0.5 text-3xl font-bold text-[var(--ink)]">{{ projects?.length ?? 0 }}</div>
            </div>
        </div>

        <!-- Channel list -->
        <div class="app-panel overflow-hidden">

            <!-- Empty state -->
            <div v-if="!projects || projects.length === 0" class="flex flex-col items-center gap-4 px-6 py-16 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--panel-muted)]">
                    <svg class="h-6 w-6 text-[var(--slate-soft)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-[var(--ink)]">No channels yet</div>
                    <div class="mt-1 text-sm text-[var(--slate-soft)]">You are not a member of any projects with chat.</div>
                </div>
            </div>

            <div v-else class="divide-y divide-[var(--line)]">
                <Link
                    v-for="p in projects"
                    :key="p.id"
                    :href="route('chat.show', p.id)"
                    class="group flex items-center gap-4 px-5 py-4 transition hover:bg-[var(--panel-strong)]"
                >
                    <!-- Project avatar -->
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold"
                        :class="avatarColor(p.id)"
                    >
                        {{ initial(p.name) }}
                    </div>

                    <!-- Project info -->
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-[var(--ink)]">{{ p.name }}</div>
                        <div class="mt-0.5 text-xs text-[var(--slate-soft)]">Project channel</div>
                    </div>

                    <!-- Unread or open -->
                    <div class="shrink-0">
                        <span
                            v-if="(p.unread_count ?? 0) > 0"
                            class="inline-flex items-center rounded-full bg-[var(--accent)] px-2.5 py-1 text-xs font-bold text-white"
                        >
                            {{ p.unread_count }} new
                        </span>
                        <span v-else class="flex items-center gap-1 text-xs font-medium text-[var(--slate-soft)] transition group-hover:text-[var(--ink)]">
                            Open
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </Link>
            </div>

        </div>
    </div>
</template>

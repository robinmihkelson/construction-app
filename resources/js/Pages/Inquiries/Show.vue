<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({ inquiry: Object })

function convertToProject() {
    router.post(route('inquiries.convert', props.inquiry.id), {}, { preserveScroll: true })
}

function setStatus(status) {
    router.patch(route('inquiries.status', props.inquiry.id), { status }, { preserveScroll: true })
}

function statusBadge(status) {
    if (status === 'new')       return 'bg-blue-50 text-blue-700 border border-blue-200'
    if (status === 'contacted') return 'bg-amber-50 text-amber-700 border border-amber-200'
    if (status === 'converted') return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    return 'bg-[var(--panel-muted)] text-[var(--slate)] border border-[var(--line)]'
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <h1 class="truncate text-xl font-bold text-[var(--ink)]">Inquiry #{{ inquiry.id }}</h1>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[0.64rem] font-bold uppercase tracking-wide" :class="statusBadge(inquiry.status)">
                        {{ inquiry.status }}
                    </span>
                </div>
                <Link
                    :href="route('inquiries.index', { status: inquiry.status })"
                    class="app-button-secondary shrink-0 gap-1.5"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </Link>
            </div>
        </template>

        <div class="space-y-5">

            <!-- Action buttons -->
            <div class="app-panel p-4">
                <div class="mb-3">
                    <div class="app-label">Actions</div>
                    <p class="mt-1 text-xs text-[var(--slate-soft)]">Update the inquiry status or convert it into a project.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="inquiry.status !== 'contacted'"
                        @click="setStatus('contacted')"
                        class="app-button-secondary"
                    >
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Mark contacted
                    </button>

                    <button
                        v-if="inquiry.status !== 'converted'"
                        @click="convertToProject()"
                        class="app-button-primary"
                    >
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Convert to project
                    </button>

                    <Link
                        v-if="inquiry.project_id"
                        :href="route('projects.show', inquiry.project_id)"
                        class="app-button-secondary"
                    >
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                        </svg>
                        Open project
                    </Link>

                    <button
                        v-if="inquiry.status !== 'new'"
                        @click="setStatus('new')"
                        class="app-button-secondary"
                    >
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Set back to new
                    </button>
                </div>
            </div>

            <!-- Inquiry details -->
            <div class="app-panel overflow-hidden">
                <div class="border-b border-[var(--line)] px-5 py-3.5">
                    <h2 class="text-sm font-bold text-[var(--ink)]">Contact details</h2>
                </div>

                <div class="divide-y divide-[var(--line)]">
                    <!-- Name -->
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-[9rem_1fr]">
                        <div class="app-label self-center">Name</div>
                        <div class="font-semibold text-[var(--ink)]">{{ inquiry.name }}</div>
                    </div>

                    <!-- Email -->
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-[9rem_1fr]">
                        <div class="app-label self-center">Email</div>
                        <a :href="`mailto:${inquiry.email}`" class="text-[var(--accent)] underline underline-offset-2 hover:text-[var(--accent-deep)]">
                            {{ inquiry.email }}
                        </a>
                    </div>

                    <!-- Phone -->
                    <div v-if="inquiry.phone" class="grid gap-1 px-5 py-4 sm:grid-cols-[9rem_1fr]">
                        <div class="app-label self-center">Phone</div>
                        <a :href="`tel:${inquiry.phone}`" class="text-[var(--ink)] hover:text-[var(--accent)]">{{ inquiry.phone }}</a>
                    </div>

                    <!-- Message -->
                    <div class="grid gap-2 px-5 py-4 sm:grid-cols-[9rem_1fr]">
                        <div class="app-label pt-1">Message</div>
                        <pre class="whitespace-pre-wrap break-words rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] px-4 py-3 text-sm text-[var(--ink)] [overflow-wrap:anywhere]">{{ inquiry.message }}</pre>
                    </div>

                    <!-- Date -->
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-[9rem_1fr]">
                        <div class="app-label self-center">Received</div>
                        <div class="text-sm text-[var(--slate-soft)]">{{ inquiry.created_at }}</div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout,
}
</script>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'


defineProps({
    projects: Array
})

const form = useForm({
    name: ''
})
</script>

<template>
    <div class="space-y-6">
        <section
            class="rounded-3xl border border-[color:var(--line)] bg-white/90 p-6 shadow-[0_18px_40px_rgba(15,23,42,0.05)]">
            <div class="grid gap-6 lg:grid-cols-[1.3fr_0.9fr] lg:items-end">
                <div>
                    <div class="text-sm font-medium text-[color:var(--slate-soft)]">
                        Projects
                    </div>
                    <h1 class="mt-2 text-3xl font-semibold text-[color:var(--ink)]">Manage active projects in one place.</h1>
                    <p class="mt-3 max-w-2xl text-sm text-[color:var(--slate-soft)]">
                        Create projects here, then open the same detail view for tasks, members, and comments.
                    </p>
                </div>

                <div class="rounded-2xl border border-[color:var(--line)] bg-[color:var(--panel-muted)] p-5">
                    <div class="text-xs font-medium text-[color:var(--slate-soft)]">
                        Total Projects
                    </div>
                    <div class="mt-3 text-4xl font-semibold text-[color:var(--ink)]">{{ projects.length }}</div>
                    <div class="mt-2 text-sm text-[color:var(--slate-soft)]">
                        Every project entry still opens the same detail screen and actions.
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-[color:var(--line)] bg-[color:var(--panel-bg)] p-6 shadow-[0_18px_50px_rgba(31,41,51,0.08)]">
            <div class="mb-4 text-[0.72rem] font-semibold uppercase tracking-[0.28em] text-[color:var(--slate-soft)]">
                Start a new project
            </div>
            <form @submit.prevent="form.post(route('projects.store'))" class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <input
                    v-model="form.name"
                    placeholder="Enter project name"
                    class="w-full rounded-2xl border border-[color:var(--line)] bg-white px-4 py-3 text-[color:var(--ink)] shadow-sm focus:border-[color:var(--accent)] focus:outline-none focus:ring-0"
                />
                <button class="rounded-2xl bg-[color:var(--accent)] px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[color:var(--accent-deep)]">
                    Add project
                </button>
            </form>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="route('projects.show', project.id)"
                class="group block rounded-[1.75rem] border border-[color:var(--line)] bg-white/90 p-5 shadow-[0_16px_40px_rgba(31,41,51,0.07)] transition hover:-translate-y-1 hover:border-[color:var(--line-strong)] hover:shadow-[0_22px_48px_rgba(31,41,51,0.12)]"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-[0.65rem] font-semibold uppercase tracking-[0.26em] text-[color:var(--accent)]">
                            Project #{{ project.id }}
                        </div>
                        <div class="mt-2 text-xl font-semibold text-[color:var(--ink)]">
                            {{ project.name }}
                        </div>
                    </div>
                    <span class="rounded-full border border-[color:var(--line)] bg-[color:var(--panel-strong)] px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[color:var(--slate)]">
                        {{ project.status }}
                    </span>
                </div>

                <div class="mt-6 flex items-center justify-between text-sm text-[color:var(--slate-soft)]">
                    <span>Open field notes and tasks</span>
                    <span class="font-semibold uppercase tracking-[0.18em] text-[color:var(--accent)] transition group-hover:translate-x-1">
                        Enter
                    </span>
                </div>
            </Link>
        </section>
    </div>
</template>

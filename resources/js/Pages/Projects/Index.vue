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
    <div class="space-y-5">
        <section class="app-panel p-5">
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <div class="app-label">Projects</div>
                    <h1 class="mt-1 text-2xl font-semibold text-[color:var(--ink)]">Manage active projects</h1>
                    <p class="mt-2 max-w-2xl text-sm text-[color:var(--slate-soft)]">
                        Open a project to manage tasks, progress photos, comments, and team members.
                    </p>
                </div>

                <div class="app-panel-muted min-w-36 px-4 py-3">
                    <div class="app-label">Total</div>
                    <div class="mt-1 text-3xl font-semibold text-[color:var(--ink)]">{{ projects.length }}</div>
                </div>
            </div>
        </section>

        <section class="app-panel p-5">
            <div class="mb-3">
                <h2 class="app-section-title">Start a new project</h2>
            </div>
            <form @submit.prevent="form.post(route('projects.store'))" class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <input
                    v-model="form.name"
                    placeholder="Enter project name"
                    class="app-input"
                />
                <button class="app-button-primary">
                    Add project
                </button>
            </form>
        </section>

        <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="route('projects.show', project.id)"
                class="app-panel group block p-4 transition hover:border-[color:var(--line-strong)] hover:bg-[color:var(--panel-strong)]"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-semibold uppercase tracking-[0.08em] text-[color:var(--accent)]">
                            Project #{{ project.id }}
                        </div>
                        <div class="mt-1 truncate text-lg font-semibold text-[color:var(--ink)]">
                            {{ project.name }}
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full bg-[color:var(--panel-muted)] px-2.5 py-1 text-xs font-semibold capitalize text-[color:var(--slate)]">
                        {{ project.status }}
                    </span>
                </div>

                <div class="mt-5 flex items-center justify-between text-sm">
                    <span class="text-[color:var(--slate-soft)]">Tasks and progress</span>
                    <span class="font-semibold text-[color:var(--accent)]">
                        Open
                    </span>
                </div>
            </Link>
        </section>
    </div>
</template>

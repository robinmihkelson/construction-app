<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const query = ref('');
const results = ref([]);
const isLoading = ref(false);
const isOpen = ref(false);
const activeIndex = ref(-1);

const mobileOpen = ref(false);

const containerEl = ref(null);
const inputEl = ref(null);
const mobileInputEl = ref(null);

let abortCtl = null;
let debounceTimer = null;

function open() { isOpen.value = true; }
function close() { isOpen.value = false; activeIndex.value = -1; }

async function openMobile() {
    mobileOpen.value = true;
    if (query.value.trim().length >= 2) open();
    await nextTick();
    mobileInputEl.value?.focus();
}

function closeMobile() {
    mobileOpen.value = false;
    close();
    query.value = '';
    results.value = [];
}

async function search() {
    const q = query.value.trim();
    if (q.length < 2) {
        results.value = [];
        isLoading.value = false;
        return;
    }

    if (abortCtl) abortCtl.abort();
    abortCtl = new AbortController();
    isLoading.value = true;

    try {
        const { data } = await window.axios.get(route('search.index'), {
            params: { q },
            signal: abortCtl.signal,
        });
        results.value = data.results ?? [];
        activeIndex.value = results.value.length > 0 ? 0 : -1;
    } catch (e) {
        if (e?.code !== 'ERR_CANCELED' && e?.name !== 'CanceledError') {
            results.value = [];
        }
    } finally {
        isLoading.value = false;
    }
}

watch(query, (val) => {
    if (debounceTimer) clearTimeout(debounceTimer);
    if (val.trim().length >= 2) open();
    debounceTimer = setTimeout(search, 220);
});

function onFocus() {
    if (query.value.trim().length >= 2) open();
}

function onClickOutside(e) {
    if (mobileOpen.value) return;
    if (containerEl.value && !containerEl.value.contains(e.target)) close();
}

function onKeyDown(e) {
    if (e.key === 'Escape') {
        if (mobileOpen.value) closeMobile();
        else { close(); inputEl.value?.blur(); }
        return;
    }

    if (results.value.length === 0) return;

    if (e.key === 'ArrowDown') {
        if (!isOpen.value) open();
        activeIndex.value = (activeIndex.value + 1) % results.value.length;
        e.preventDefault();
    } else if (e.key === 'ArrowUp') {
        if (!isOpen.value) open();
        activeIndex.value = (activeIndex.value - 1 + results.value.length) % results.value.length;
        e.preventDefault();
    } else if (e.key === 'Enter') {
        if (activeIndex.value >= 0 && activeIndex.value < results.value.length) {
            navigate(results.value[activeIndex.value]);
            e.preventDefault();
        }
    }
}

function navigate(item) {
    mobileOpen.value = false;
    close();
    query.value = '';
    results.value = [];
    router.visit(item.url);
}

const groupBoundaries = computed(() => {
    const seen = new Set();
    return results.value.map((r) => {
        const isFirst = !seen.has(r.type);
        seen.add(r.type);
        return isFirst;
    });
});

const typeLabel = {
    project: 'Projects',
    task: 'Tasks',
    message: 'Chat messages',
    inquiry: 'Inquiries',
};

onMounted(() => document.addEventListener('mousedown', onClickOutside, true));
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside, true));
</script>

<template>
    <div ref="containerEl" class="relative hidden flex-1 sm:block" style="max-width: 22rem;">
        <svg
            v-if="!isLoading"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--slate-soft)]"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
        </svg>
        <svg
            v-else
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-[var(--accent)]"
            fill="none" viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>

        <input
            ref="inputEl"
            v-model="query"
            type="search"
            placeholder="Search projects, tasks, chat…"
            autocomplete="off"
            spellcheck="false"
            @focus="onFocus"
            @keydown="onKeyDown"
            class="w-full rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] py-2 pl-9 pr-3 text-sm text-[var(--ink)] placeholder:text-[var(--slate-soft)] transition focus:border-[var(--accent)] focus:bg-[var(--panel-bg)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/12"
        />

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen && query.trim().length >= 2"
                class="absolute left-0 right-0 top-full z-50 mt-2 max-h-[min(70vh,28rem)] overflow-y-auto rounded-xl border border-[var(--line)] bg-[var(--panel-bg)] py-1 shadow-[var(--shadow-md)]"
            >
                <div
                    v-if="!isLoading && results.length === 0"
                    class="px-4 py-6 text-center text-xs text-[var(--slate-soft)]"
                >
                    No matches for "<span class="font-semibold text-[var(--ink)]">{{ query }}</span>"
                </div>

                <template v-for="(item, idx) in results" :key="'d-' + item.type + '-' + item.id">
                    <div
                        v-if="groupBoundaries[idx]"
                        class="mt-1 px-3 py-1 text-[0.6rem] font-bold uppercase tracking-[0.12em] text-[var(--slate-soft)]"
                    >
                        {{ typeLabel[item.type] ?? item.type }}
                    </div>
                    <button
                        type="button"
                        @click="navigate(item)"
                        @mouseenter="activeIndex = idx"
                        class="flex w-full items-start gap-3 px-3 py-2 text-left transition"
                        :class="activeIndex === idx ? 'bg-[var(--panel-strong)]' : 'hover:bg-[var(--panel-strong)]'"
                    >
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[var(--panel-muted)] text-[var(--slate-soft)]">
                            <svg v-if="item.type === 'project'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                            </svg>
                            <svg v-else-if="item.type === 'task'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <svg v-else-if="item.type === 'message'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-[var(--ink)]">{{ item.title }}</div>
                            <div class="mt-0.5 truncate text-xs text-[var(--slate-soft)]">{{ item.subtitle }}</div>
                        </div>
                    </button>
                </template>
            </div>
        </Transition>
    </div>

    <button
        type="button"
        @click="openMobile"
        aria-label="Search"
        class="flex h-9 w-9 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--accent)] sm:hidden"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
        </svg>
    </button>

    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileOpen" class="fixed inset-0 z-50 sm:hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="closeMobile" />

                <div class="absolute inset-x-0 top-0 max-h-screen overflow-hidden bg-[var(--panel-bg)] shadow-[var(--shadow-md)]">
                    <div class="flex items-center gap-2 border-b border-[var(--line)] px-3 py-2.5">
                        <div class="relative flex-1">
                            <svg
                                v-if="!isLoading"
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--slate-soft)]"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                            </svg>
                            <svg
                                v-else
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-[var(--accent)]"
                                fill="none" viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <input
                                ref="mobileInputEl"
                                v-model="query"
                                type="search"
                                placeholder="Search projects, tasks, chat…"
                                autocomplete="off"
                                spellcheck="false"
                                @keydown="onKeyDown"
                                class="w-full rounded-lg border border-[var(--line)] bg-[var(--panel-strong)] py-2 pl-9 pr-3 text-sm text-[var(--ink)] placeholder:text-[var(--slate-soft)] transition focus:border-[var(--accent)] focus:bg-[var(--panel-bg)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/12"
                            />
                        </div>
                        <button
                            type="button"
                            @click="closeMobile"
                            aria-label="Close search"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--slate-soft)] transition hover:bg-[var(--panel-muted)] hover:text-[var(--ink)]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="query.trim().length >= 2" class="max-h-[calc(100vh-4rem)] overflow-y-auto py-1">
                        <div
                            v-if="!isLoading && results.length === 0"
                            class="px-4 py-6 text-center text-xs text-[var(--slate-soft)]"
                        >
                            No matches for "<span class="font-semibold text-[var(--ink)]">{{ query }}</span>"
                        </div>

                        <template v-for="(item, idx) in results" :key="'m-' + item.type + '-' + item.id">
                            <div
                                v-if="groupBoundaries[idx]"
                                class="mt-1 px-3 py-1 text-[0.6rem] font-bold uppercase tracking-[0.12em] text-[var(--slate-soft)]"
                            >
                                {{ typeLabel[item.type] ?? item.type }}
                            </div>
                            <button
                                type="button"
                                @click="navigate(item)"
                                class="flex w-full items-start gap-3 px-3 py-2.5 text-left transition hover:bg-[var(--panel-strong)] active:bg-[var(--panel-strong)]"
                            >
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[var(--panel-muted)] text-[var(--slate-soft)]">
                                    <svg v-if="item.type === 'project'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    </svg>
                                    <svg v-else-if="item.type === 'task'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <svg v-else-if="item.type === 'message'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.85">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold text-[var(--ink)]">{{ item.title }}</div>
                                    <div class="mt-0.5 truncate text-xs text-[var(--slate-soft)]">{{ item.subtitle }}</div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

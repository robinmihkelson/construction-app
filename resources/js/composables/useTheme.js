import { computed, ref, watch } from 'vue';

const STORAGE_KEY = 'theme';
const VALID = ['light', 'dark', 'system'];

function readStored() {
    try {
        const v = localStorage.getItem(STORAGE_KEY);
        return VALID.includes(v) ? v : 'system';
    } catch (e) {
        return 'system';
    }
}

function systemPrefersDark() {
    return typeof window !== 'undefined'
        && typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

const theme = ref(readStored());
const systemDark = ref(systemPrefersDark());

const resolvedTheme = computed(() =>
    theme.value === 'system'
        ? (systemDark.value ? 'dark' : 'light')
        : theme.value,
);

function applyClass() {
    if (typeof document === 'undefined') return;
    document.documentElement.classList.toggle('dark', resolvedTheme.value === 'dark');
}

if (typeof window !== 'undefined' && typeof window.matchMedia === 'function') {
    const mql = window.matchMedia('(prefers-color-scheme: dark)');
    const handler = (e) => { systemDark.value = e.matches; };
    if (mql.addEventListener) mql.addEventListener('change', handler);
    else if (mql.addListener) mql.addListener(handler);
}

applyClass();

watch(theme, (val) => {
    try { localStorage.setItem(STORAGE_KEY, val); } catch (e) {}
    applyClass();
});

watch(systemDark, () => {
    if (theme.value === 'system') applyClass();
});

export function useTheme() {
    function setTheme(value) {
        if (!VALID.includes(value)) return;
        theme.value = value;
    }

    return {
        theme,
        resolvedTheme,
        setTheme,
        options: VALID,
    };
}

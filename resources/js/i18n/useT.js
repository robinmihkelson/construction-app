import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { WORKSPACE_MESSAGES } from '@/i18n/workspace';

export function useT() {
    const page = usePage();
    const locale = computed(() => page.props.locale ?? 'et');

    const t = (key) =>
        WORKSPACE_MESSAGES[locale.value]?.[key]
        ?? WORKSPACE_MESSAGES.et[key]
        ?? key;

    return { t, locale };
}

import { reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Lógica compartida para páginas de índice con filtros + tabla paginada (Inertia).
 *
 * @param {object}   opts
 * @param {string}   opts.routeName   nombre de ruta Ziggy del índice
 * @param {object}   opts.initial     estado inicial de los filtros (del prop `filters`)
 * @param {string[]} opts.debounced   claves que se recargan con debounce (texto)
 * @param {string[]} [opts.only]      props de Inertia a recargar (partial reload)
 */
export function useIndex({ routeName, initial = {}, debounced = [], only = null }) {
    const filters = reactive({ ...initial });
    const loading = ref(false);
    let timer = null;
    let suspended = false;

    function params(extra = {}) {
        const out = {};
        for (const [k, v] of Object.entries(filters)) {
            if (v !== '' && v !== null && v !== undefined) out[k] = v;
        }
        return { ...out, ...extra };
    }

    function go(extra = {}) {
        router.get(route(routeName), params(extra), {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            ...(only ? { only } : {}),
            onStart: () => (loading.value = true),
            onFinish: () => (loading.value = false),
        });
    }

    // watchers
    for (const key of Object.keys(initial)) {
        if (debounced.includes(key)) {
            watch(() => filters[key], () => {
                if (suspended) return;
                clearTimeout(timer);
                timer = setTimeout(() => go(), 350);
            });
        } else {
            watch(() => filters[key], () => {
                if (!suspended) go();
            });
        }
    }

    function reset() {
        suspended = true;
        for (const key of Object.keys(filters)) {
            filters[key] = typeof initial[key] === 'string' ? '' : undefined;
        }
        suspended = false;
        clearTimeout(timer);
        go();
    }

    function toPage(page) {
        go({ page });
    }

    const hasActiveFilters = () =>
        Object.entries(filters).some(([, v]) => v !== '' && v !== null && v !== undefined);

    return { filters, loading, go, reset, toPage, hasActiveFilters };
}

/**
 * Construye el objeto `pagination` de Ant Design a partir de un paginador de Laravel.
 */
export function laravelPagination(paginator, unit = 'registros') {
    return {
        current: paginator.current_page,
        pageSize: paginator.per_page,
        total: paginator.total,
        showSizeChanger: false,
        hideOnSinglePage: false,
        showTotal: (t) => `${t.toLocaleString('es-MX')} ${unit}`,
    };
}

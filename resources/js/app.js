import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import dayjs from 'dayjs';
import 'dayjs/locale/es-mx';
import { activarMayusculasGlobales } from '@/lib/mayusculas';

import 'ant-design-vue/dist/reset.css';
import '../css/app.css';

// Calendarios / fechas relativas en español (México).
dayjs.locale('es-mx');

// Todo lo que se escribe en el sistema se vuelve mayúsculas, excepto contraseñas.
activarMayusculasGlobales();

const appName = import.meta.env.VITE_APP_NAME || 'SAINS';

/* Tras un deploy, los chunks viejos dejan de existir: recargar en vez de romper. */
window.addEventListener('vite:preloadError', () => window.location.reload());
router.on('exception', (event) => {
    const msg = event.detail?.exception?.message ?? '';
    if (/dynamically imported module|Importing a module script failed/i.test(msg)) {
        event.preventDefault();
        window.location.reload();
    }
});

/* Red de seguridad: si el servidor responde algo que NO es Inertia, la librería
   mostraría el HTML crudo dentro de un modal a pantalla completa (se ve como un
   "dashboard flotante" tras iniciar sesión). Suele pasar cuando:
   - una redirección http→https pierde la cabecera X-Inertia (proxy/APP_URL), o
   - el token CSRF venció y llega un 419 "Page Expired".
   En vez del modal, navegamos de verdad a la URL final o recargamos. */
router.on('invalid', (event) => {
    const res = event.detail?.response;
    const status = res?.status ?? 0;

    if (status === 419) {
        event.preventDefault();
        window.location.reload();
        return;
    }

    // Redirección válida (2xx/3xx) que llegó como documento HTML completo:
    // seguimos a esa URL con una navegación normal.
    if (status >= 200 && status < 400) {
        event.preventDefault();
        const finalUrl = res?.request?.responseURL || window.location.href;
        window.location.assign(finalUrl);
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },

    progress: {
        color: '#4f46e5',
        showSpinner: false,
    },
});

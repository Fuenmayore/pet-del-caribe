import '../css/app.css';
import './bootstrap';

// 1. Agregamos 'router' a la importación
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// --- ESCUDO ANTI ERRORES 419/403 DE SESIÓN CADUCADA ---
router.on('invalid', (event) => {
    const status = event.detail.response?.status;
    if (status === 419 || status === 403 || status === 401) {
        event.preventDefault(); // Evita que salga el cuadro de error feo
        window.location.reload(); // Recarga silenciosamente
    }
});
// ------------------------------------------------------

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // --- INICIO DE NUESTRO CÓDIGO DE PERMISOS ---
        // Creamos la función global $can
        vueApp.config.globalProperties.$can = function (permission) {
            const user = props.initialPage.props.auth?.user;
            // Validación de seguridad para la vista de Login (cuando no hay usuario)
            if (!user || !user.permissions) return false; 
            return user.permissions.includes(permission);
        };

        // Creamos la función global $hasRole (por si la necesitas)
        vueApp.config.globalProperties.$hasRole = function (role) {
            const user = props.initialPage.props.auth?.user;
            // Validación de seguridad para la vista de Login
            if (!user || !user.roles) return false; 
            return user.roles.includes(role);
        };
        // --- FIN DE NUESTRO CÓDIGO ---

        vueApp.mount(el);
    },
    progress: {
        color: '#8bc53f', // Aproveché para poner la barra de carga de arriba en tu pet-green
    },
});
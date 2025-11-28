import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import {
    tienePermiso,
    tieneRol,
    tieneAlgunPermiso,
    tieneTodosLosPermisos,
} from "./Permisos_Ayuda/permisos";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Agregar el helper de permisos a las propiedades globales de Vue
        app.config.globalProperties.tienePermiso = tienePermiso;
        app.config.globalProperties.tieneRol = tieneRol;
        app.config.globalProperties.tieneAlgunPermiso = tieneAlgunPermiso;
        app.config.globalProperties.tieneTodosLosPermisos =
            tieneTodosLosPermisos;

        // También disponible en window para acceso directo
        window.tienePermiso = tienePermiso;
        window.tieneRol = tieneRol;
        window.tieneAlgunPermiso = tieneAlgunPermiso;
        window.tieneTodosLosPermisos = tieneTodosLosPermisos;
         // 👇 Alias para depurar y para los helpers
        window.$page = () => app.config.globalProperties.$page;

        return app.mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});

import '../css/app.css';
import './bootstrap';
import Aura from '../css/presets/aura'
import "vue-scroll-picker/lib/style.css";

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import { i18nVue, trans } from 'laravel-vue-i18n';
import iosZoomFix from '../js/Composables/ios-zoom-fix.js';
import VueScrollPicker from "vue-scroll-picker";
import ConfirmationService from 'primevue/confirmationservice';

createInertiaApp({
    title: (title) => `${title} - ${trans('public.luckyant_mall')}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18nVue, {
                lang: 'cn',
                resolve: async lang => {
                    const langs = import.meta.glob('../../lang/*.json');
                    if (typeof langs[`../../lang/${lang}.json`] == "undefined") return; //Temporary workaround
                    return await langs[`../../lang/${lang}.json`]();
                }
            })
            .use(PrimeVue, {
                unstyled: true,
                pt: Aura
            })
            .use(ConfirmationService)
            .use(VueScrollPicker);

        app.mount(el);

        // Run the iOS zoom fix
        iosZoomFix();
    },
    progress: {
        color: '#3b82f6',
    },
});

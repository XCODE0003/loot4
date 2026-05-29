import { createInertiaApp } from '@inertiajs/vue3';
import { createSSRApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import Loot4Layout from '@/layouts/Loot4Layout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
// @ts-expect-error - plain JS i18n module
import { i18n } from '@/loot4/i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Loot4You';

createInertiaApp({
    // Pages already include the brand in their own <Head> titles, so use the
    // page title verbatim and fall back to the brand name on the home page.
    title: (title) => title || appName,
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('loot4/'):
                return Loot4Layout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    setup({ el, App, props, plugin }) {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        i18n.global.locale.value = (props.initialPage?.props as any)?.locale ?? 'en';

        const app = createSSRApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(i18n);

        if (el) {
            app.mount(el);
        }

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

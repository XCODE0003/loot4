import { createInertiaApp } from '@inertiajs/vue3';
import { createSSRApp, h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import Loot4Layout from '@/layouts/Loot4Layout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
// @ts-expect-error - plain JS i18n module
import { i18n } from '@/loot4/i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Loot4You';

// Server-side counterpart of app.ts. Page resolution is injected by the
// @inertiajs/vite plugin (same as the client), and the plugin wraps this call
// with createServer + renderToString during the --ssr build. The title/layout/
// i18n setup MUST match app.ts exactly so the client hydrates without mismatch.
// No app.mount() / DOM access here — this runs in Node.
createInertiaApp({
    title: (title) => title || appName,
    layout: (name: string) => {
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
    setup({ App, props, plugin }) {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        i18n.global.locale.value = (props.initialPage?.props as any)?.locale ?? 'en';

        const app = createSSRApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(i18n);

        return app;
    },
});

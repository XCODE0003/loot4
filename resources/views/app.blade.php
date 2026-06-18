<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- Tell the browser to paint a DARK default canvas (not white) before any
             CSS/JS loads — this is what stops the white flash on cold reloads. --}}
        <meta name="color-scheme" content="dark">
        <meta name="theme-color" content="#020202">

        {{-- Microsoft Clarity --}}
        <script type="text/javascript">
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", "x5nr2lbzif");
        </script>

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Avoid white flash on first paint before storefront CSS/JS loads, and
             show a dark branded loader while the client-rendered app mounts. --}}
        <style>
            :root {
                color-scheme: dark;
            }
            html,
            body {
                background-color: #020202;
            }
            html.dark,
            html.dark body {
                background-color: #020202;
            }
            /* Full-screen loader shown until Inertia mounts (removed in app.ts).
               If JS fails to load, this stays dark with a spinner instead of a
               blank white window. */
            #app-splash {
                position: fixed;
                inset: 0;
                z-index: 2147483647;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #020202;
            }
            #app-splash .app-splash__spinner {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                border: 3px solid rgba(255, 255, 255, 0.14);
                border-top-color: #2bff95;
                opacity: 0;
                /* spin always; fade the spinner in only after 180ms so fast loads
                   don't flash it */
                animation: app-splash-spin 0.8s linear infinite,
                    app-splash-fade 0.01s linear 0.18s forwards;
            }
            @keyframes app-splash-spin {
                to {
                    transform: rotate(360deg);
                }
            }
            @keyframes app-splash-fade {
                to {
                    opacity: 1;
                }
            }
        </style>
        @php($gtmId = env('GTM_CONTAINER_ID'))
        @if($gtmId)
            <!-- Google Tag Manager -->
            <script>
                (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','{{ $gtmId }}');
            </script>
            <!-- End Google Tag Manager -->
        @endif

        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">
        @php($customFavicon = rescue(fn () => \App\Models\Setting::get('favicon'), null, false))
        @if($customFavicon)
            <link rel="icon" href="{{ $customFavicon }}">
        @endif

        {{-- Base SEO / social sharing defaults (per-page <Head> may override the title) --}}
        @php($seoDescription = 'Unlock premium in-game assets, rare accounts, and exclusive items with Loot4You. Experience a secure, fast, and simple purchasing process for all your gaming needs.')
        @php($seoTitle = 'Loot4You - Your Trusted Marketplace for Digital Gaming Products')
        @php($seoImage = url('/preview.png'))
        <meta name="description" content="{{ $seoDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Loot4You">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        <meta name="twitter:image" content="{{ $seoImage }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        @if($gtmId)
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
        @endif
        <x-inertia::app />
        <div id="app-splash" aria-hidden="true"><div class="app-splash__spinner"></div></div>
    </body>
</html>

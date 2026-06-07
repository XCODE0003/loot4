// Marketing pixels (Google Ads / GA4 via gtag, Facebook Pixel, TikTok Pixel).
//
// Loaders are consent-gated and idempotent. Purchase conversions fire on the
// success page with a per-platform localStorage dedup, and EVERY attempt is
// reported to POST /checkout/conversion so the admin "Conversion Logs" page
// shows what was sent and why (sent / localStorage-skip / no-consent / blocked).

const CONSENT_COOKIE = 'cookie_consent';

function readCookie(name) {
    const match = document.cookie.match(
        new RegExp('(?:^|; )' + name + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[1]) : null;
}

export function hasMarketingConsent() {
    if (typeof document === 'undefined') {
        return false;
    }

    return readCookie(CONSENT_COOKIE) === 'accepted';
}

/** Load every configured tracker (gtag for GA4 + Google Ads, fbq, ttq). */
export function loadTrackers(tracking = {}) {
    if (typeof window === 'undefined') {
        return;
    }

    loadGtag(tracking);
    loadFacebookPixel(tracking.facebookPixelId);
    loadTikTokPixel(tracking.tiktokPixelId);
}

function loadGtag({ gaId, googleAdsId }) {
    const ids = [gaId, googleAdsId].filter(Boolean);

    if (!ids.length || window.__gtagLoaded) {
        return;
    }

    window.__gtagLoaded = true;

    const s = document.createElement('script');
    s.async = true;
    s.src = `https://www.googletagmanager.com/gtag/js?id=${ids[0]}`;
    document.head.appendChild(s);

    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };
    window.gtag('js', new Date());

    for (const id of ids) {
        window.gtag(
            'config',
            id,
            id.startsWith('G-') ? { anonymize_ip: true } : {},
        );
    }
}

function loadFacebookPixel(pixelId) {
    if (!pixelId || window.__fbqLoaded) {
        return;
    }

    window.__fbqLoaded = true;

    // Standard Meta Pixel bootstrap (stub queues events until the script loads).
    const n = (window.fbq = function () {
        if (n.callMethod) {
            n.callMethod.apply(n, arguments);
        } else {
            n.queue.push(arguments);
        }
    });

    if (!window._fbq) {
        window._fbq = n;
    }

    n.push = n;
    n.loaded = true;
    n.version = '2.0';
    n.queue = [];

    const s = document.createElement('script');
    s.async = true;
    s.src = 'https://connect.facebook.net/en_US/fbevents.js';
    document.head.appendChild(s);

    window.fbq('init', pixelId);
    window.fbq('track', 'PageView');
}

function loadTikTokPixel(pixelId) {
    if (!pixelId || window.__ttqLoaded) {
        return;
    }

    window.__ttqLoaded = true;

    // Standard TikTok Pixel bootstrap (queue-based stub, same idea as fbq).
    const ttq = (window.ttq = window.ttq || []);
    ttq.methods = [
        'page',
        'track',
        'identify',
        'instances',
        'debug',
        'on',
        'off',
        'once',
        'ready',
        'alias',
        'group',
        'enableCookie',
        'disableCookie',
    ];
    ttq.setAndDefer = function (t, e) {
        t[e] = function () {
            t.push([e].concat(Array.prototype.slice.call(arguments, 0)));
        };
    };

    for (const method of ttq.methods) {
        ttq.setAndDefer(ttq, method);
    }

    ttq.load = function (id) {
        ttq._i = ttq._i || {};
        ttq._i[id] = [];
        ttq._t = ttq._t || {};
        ttq._t[id] = +new Date();
        ttq._o = ttq._o || {};
        const s = document.createElement('script');
        s.async = true;
        s.src = `https://analytics.tiktok.com/i18n/pixel/events.js?sdkid=${id}&lib=ttq`;
        document.head.appendChild(s);
    };
    ttq.load(pixelId);
    ttq.page();
}

function xsrfToken() {
    return readCookie('XSRF-TOKEN') ?? '';
}

/** Fire-and-forget debug log; logging must never break the success page. */
function logConversion(payload) {
    try {
        fetch('/checkout/conversion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': xsrfToken(),
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            keepalive: true,
            body: JSON.stringify(payload),
        }).catch(() => {});
    } catch {
        /* ignore */
    }
}

function purchasePlatforms(tracking, order) {
    const value = Number(order.total ?? 0);
    const currency = order.currency ?? 'USD';

    return [
        {
            key: 'google_ads',
            configured: Boolean(
                tracking.googleAdsId && tracking.googleAdsLabel,
            ),
            ready: () => typeof window.gtag === 'function',
            blockedReason: 'gtag-not-loaded',
            fire: () =>
                window.gtag('event', 'conversion', {
                    send_to: `${tracking.googleAdsId}/${tracking.googleAdsLabel}`,
                    value,
                    currency,
                    transaction_id: order.number,
                }),
        },
        {
            key: 'facebook_pixel',
            configured: Boolean(tracking.facebookPixelId),
            ready: () => typeof window.fbq === 'function',
            blockedReason: 'fbq-not-loaded',
            fire: () =>
                window.fbq(
                    'track',
                    'Purchase',
                    { value, currency },
                    { eventID: order.number },
                ),
        },
        {
            key: 'tiktok',
            configured: Boolean(tracking.tiktokPixelId),
            ready: () =>
                Boolean(window.ttq && typeof window.ttq.track === 'function'),
            blockedReason: 'ttq-not-loaded',
            fire: () =>
                window.ttq.track(
                    'CompletePayment',
                    { value, currency, content_id: order.number },
                    { event_id: order.number },
                ),
        },
    ];
}

/**
 * Fire the Purchase conversion on every configured platform exactly once per
 * order (localStorage dedup) and report each attempt to the conversion log.
 */
export function firePurchaseConversions(tracking = {}, order) {
    if (typeof window === 'undefined' || !order?.number) {
        return;
    }

    const consent = hasMarketingConsent();

    if (consent) {
        loadTrackers(tracking);
    }

    for (const platform of purchasePlatforms(tracking, order)) {
        if (!platform.configured) {
            // Settings missing (e.g. Google Ads conversion ID/label) — log it
            // so the admin sees WHY nothing fired instead of an empty table.
            logConversion({
                order: order.number,
                platform: platform.key,
                event: 'Purchase',
                sent: false,
                reason: 'not-configured',
                url: window.location.href,
            });

            continue;
        }

        const dedupKey = `l4_conv_${platform.key}_${order.number}`;
        let alreadySent = false;

        try {
            alreadySent = window.localStorage.getItem(dedupKey) === '1';
        } catch {
            /* private mode — fall through and fire */
        }

        let sent = false;
        let reason;

        if (!consent) {
            reason = 'no-consent';
        } else if (alreadySent) {
            reason = 'localStorage-skip';
        } else if (!platform.ready()) {
            reason = platform.blockedReason;
        } else {
            try {
                platform.fire();

                try {
                    window.localStorage.setItem(dedupKey, '1');
                } catch {
                    /* ignore */
                }

                sent = true;
                reason = 'sent';
            } catch (error) {
                reason = `error: ${error?.message ?? 'unknown'}`.slice(0, 100);
            }
        }

        logConversion({
            order: order.number,
            platform: platform.key,
            event: 'Purchase',
            sent,
            reason,
            url: window.location.href,
        });
    }
}

// Captures where the visitor came from (UTM params, ad click IDs, referrer)
// so the order can be attributed to the real traffic source instead of the
// hardcoded "storefront". First-touch wins; fresh ad-click params are merged
// in so a later campaign click on a returning visitor is not lost.
const STORAGE_KEY = 'l4_attribution';

// URL params copied verbatim into the attribution snapshot.
const PARAM_KEYS = {
    utm_source: 'source',
    utm_medium: 'medium',
    utm_campaign: 'campaign',
    utm_content: 'content',
    utm_term: 'term',
    gclid: 'gclid', // Google Ads
    fbclid: 'fbclid', // Facebook / Meta
    ttclid: 'ttclid', // TikTok
};

function read() {
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);

        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function write(data) {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch {
        // Storage unavailable (private mode) — attribution is best-effort.
    }
}

/** Snapshot the current URL/referrer once per visitor; merge fresh ad params. */
export function captureAttribution() {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        const params = new URLSearchParams(window.location.search);
        const fresh = {};

        for (const [param, key] of Object.entries(PARAM_KEYS)) {
            const value = params.get(param);

            if (value) {
                fresh[key] = value;
            }
        }

        const existing = read();

        if (existing) {
            if (Object.keys(fresh).length > 0) {
                write({ ...existing, ...fresh });
            }

            return;
        }

        write({
            ...fresh,
            referrer: document.referrer || null,
            landing_page: window.location.href,
            first_visit_at: new Date().toISOString(),
        });
    } catch {
        // Tracking must never break the storefront.
    }
}

/** The stored snapshot for the checkout payload, or null when nothing captured. */
export function getAttribution() {
    if (typeof window === 'undefined') {
        return null;
    }

    return read();
}

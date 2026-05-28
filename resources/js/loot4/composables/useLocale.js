import { ref } from 'vue'
import { setLocale, labelForCode } from '@/loot4/i18n'

export const langs = ['EN', 'ES', 'NL', 'AR', 'DE', 'IT', 'FR']

export const currencyList = [
  { code: 'USD', symbol: '$'   },
  { code: 'EUR', symbol: '€'   },
  { code: 'GBP', symbol: '£'   },
  { code: 'CAD', symbol: 'CA$' },
  { code: 'NZD', symbol: 'NZ$' },
  { code: 'AUD', symbol: 'A$'  },
  { code: 'AED', symbol: 'AED '},
  { code: 'SAR', symbol: 'SAR '},
]

// Fallback rates used until the FetchExchangeRates job warms the cache.
const FALLBACK_RATES = { EUR: 0.92, GBP: 0.79, CAD: 1.36, NZD: 1.64, AUD: 1.53, AED: 3.67, SAR: 3.75 }

const liveRates = ref({ ...FALLBACK_RATES })

/** Called from Loot4Layout when exchangeRates prop arrives from the server. */
export function setRates(rates) {
  if (rates && typeof rates === 'object' && Object.keys(rates).length > 0) {
    liveRates.value = { ...FALLBACK_RATES, ...rates }
  }
}

function readCookie(name) {
  if (typeof document === 'undefined') return null
  const m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'))
  return m ? decodeURIComponent(m[1]) : null
}

function writeCookie(name, value) {
  if (typeof document !== 'undefined') {
    document.cookie = `${name}=${encodeURIComponent(value)};path=/;max-age=${60 * 60 * 24 * 365}`
  }
}

const savedLocale = readCookie('locale')
const lang = ref(savedLocale ? labelForCode(savedLocale) : 'EN')

const savedCurrency = readCookie('currency')
const initCurrency = currencyList.find(c => c.code === savedCurrency) ?? currencyList[0]
const currency = ref(initCurrency.code)

export function useLocale() {
  function setLang(label) {
    if (!langs.includes(label)) return
    lang.value = label
    setLocale(label)
  }

  function setCurrency(code) {
    if (!currencyList.find(c => c.code === code)) return
    currency.value = code
    writeCookie('currency', code)
  }

  function formatPrice(usdAmount) {
    const entry = currencyList.find(c => c.code === currency.value) ?? currencyList[0]
    const rate = entry.code === 'USD' ? 1 : (liveRates.value[entry.code] ?? FALLBACK_RATES[entry.code] ?? 1)
    const converted = Math.round(Number(usdAmount) * rate * 100) / 100
    return `${entry.symbol}${converted.toFixed(2)}`
  }

  return { lang, currency, langs, currencyList, setLang, setCurrency, formatPrice }
}

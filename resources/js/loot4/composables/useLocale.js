import { ref } from 'vue'
import { setLocale, labelForCode } from '@/loot4/i18n'

export const langs = ['EN', 'RU', 'DE', 'ES', 'NL', 'AR', 'IT', 'FR']

export const currencyList = [
  { code: 'USD', symbol: '$',    rate: 1     },
  { code: 'EUR', symbol: '€',    rate: 0.92  },
  { code: 'GBP', symbol: '£',    rate: 0.79  },
  { code: 'CAD', symbol: 'CA$',  rate: 1.36  },
  { code: 'NZD', symbol: 'NZ$',  rate: 1.64  },
  { code: 'AUD', symbol: 'A$',   rate: 1.53  },
  { code: 'AED', symbol: 'AED ', rate: 3.67  },
  { code: 'SAR', symbol: 'SAR ', rate: 3.75  },
]

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
    const entry = currencyList.find(c => c.code === code)
    if (!entry) return
    currency.value = code
    writeCookie('currency', code)
  }

  function formatPrice(usdAmount) {
    const entry = currencyList.find(c => c.code === currency.value) ?? currencyList[0]
    const converted = Math.round(Number(usdAmount) * entry.rate * 100) / 100
    return `${entry.symbol}${converted.toFixed(2)}`
  }

  return { lang, currency, langs, currencyList, setLang, setCurrency, formatPrice }
}

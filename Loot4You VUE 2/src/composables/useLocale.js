import { ref } from 'vue'

export const langs = ['EN', 'DE']
export const currencies = ['$', '€', '£']

const lang = ref('EN')
const currency = ref('$')

export function useLocale() {
  function setLang(value) {
    if (langs.includes(value)) lang.value = value
  }

  function setCurrency(value) {
    if (currencies.includes(value)) currency.value = value
  }

  return { lang, currency, langs, currencies, setLang, setCurrency }
}

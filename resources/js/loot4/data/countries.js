import { allCountries } from 'country-region-data'

/**
 * ISO-3166 countries as `{ code, name }`, sorted by name, for the checkout
 * Country <select>. `code` is the 2-letter ISO code (stored on the order,
 * matching the IP-geolocation format).
 *
 * @type {{ code: string, name: string }[]}
 */
export const countries = allCountries
  .map(([name, code]) => ({ code, name }))
  .sort((a, b) => a.name.localeCompare(b.name))

/**
 * Regions/states for a country ISO code, as `{ code, name }`. Empty array when
 * the country has no sub-divisions — the UI then shows a free-text input.
 *
 * @param {string} code 2-letter ISO country code
 * @returns {{ code: string, name: string }[]}
 */
export function regionsFor(code) {
  const row = allCountries.find((c) => c[1] === code)
  return (row?.[2] ?? []).map(([name, shortCode]) => ({ name, code: shortCode }))
}

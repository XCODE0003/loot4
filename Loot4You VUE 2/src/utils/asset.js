export function asset(path) {
  return new URL(`../assets/img/${path}`, import.meta.url).href
}

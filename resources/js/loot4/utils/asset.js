// Build a { filename: resolvedUrl } map at build time. Using import.meta.glob
// (instead of `new URL(..., import.meta.url)`) keeps this SSR-safe: under Inertia
// SSR / Vite dev, import.meta.url is a file:// path, which the browser blocks and
// which causes hydration mismatches. Glob URLs resolve identically on server & client.
const modules = import.meta.glob('../assets/img/*', { eager: true, query: '?url', import: 'default' })

const images = {}
for (const key in modules) {
  images[key.slice(key.lastIndexOf('/') + 1)] = modules[key]
}

export function asset(path) {
  if (!path) return ''
  if (/^https?:\/\//.test(path) || path.startsWith('/')) return path
  return images[path] ?? ''
}

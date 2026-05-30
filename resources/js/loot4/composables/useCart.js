import { computed, ref, watch } from 'vue'

const STORAGE_KEY = 'loot4_cart'
const COUPON_KEY = 'loot4_cart_coupon'

function load(key) {
  if (typeof window === 'undefined') return null
  try {
    return JSON.parse(window.localStorage.getItem(key))
  } catch {
    return null
  }
}

/**
 * Stable, order-independent string identity for a set of option selections, so
 * identical configurations merge into one cart line regardless of the order
 * fields/checkboxes were chosen, while different configs stay separate.
 *
 * @param {Record<string, string | string[]>} selections
 * @returns {string}
 */
function canonicalSelections(selections) {
  if (!selections || typeof selections !== 'object') return ''
  return Object.keys(selections)
    .sort()
    .map((k) => {
      const v = selections[k]
      const val = Array.isArray(v) ? [...v].map(String).sort().join(',') : String(v ?? '')
      return `${k}=${val}`
    })
    .join('|')
}

// Module-level singleton state shared across all components.
// Starts empty so SSR / first client render match; real data is loaded from
// localStorage via hydrate() after mount (client only) to avoid hydration mismatches.
const items = ref([])
const coupon = ref(null)
const isOpen = ref(false)
let hydrated = false

function hydrate() {
  if (hydrated || typeof window === 'undefined') return
  hydrated = true
  const storedItems = load(STORAGE_KEY)
  if (Array.isArray(storedItems)) items.value = storedItems
  const storedCoupon = load(COUPON_KEY)
  if (storedCoupon) coupon.value = storedCoupon
}

if (typeof window !== 'undefined') {
  watch(items, (value) => window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value)), { deep: true })
  watch(coupon, (value) => window.localStorage.setItem(COUPON_KEY, JSON.stringify(value)))
}

export function useCart() {
  const count = computed(() => items.value.reduce((sum, i) => sum + i.qty, 0))
  const subtotal = computed(() => items.value.reduce((sum, i) => sum + i.price * i.qty, 0))

  const discount = computed(() => {
    if (!coupon.value) return 0
    const value = Number(coupon.value.value) || 0
    const raw = coupon.value.type === 'percentage' ? (subtotal.value * value) / 100 : value
    return Math.min(raw, subtotal.value)
  })

  const total = computed(() => Math.max(0, subtotal.value - discount.value))

  function add(item) {
    const selections = item.selections ?? {}
    const key = `${item.slug}#${canonicalSelections(selections)}`
    const existing = items.value.find((i) => i.key === key)
    if (existing) {
      existing.qty += item.qty ?? 1
    } else {
      items.value = [...items.value, { ...item, selections, key, qty: item.qty ?? 1 }]
    }
    open()
  }

  function remove(key) {
    items.value = items.value.filter((i) => i.key !== key)
  }

  function setQty(key, qty) {
    const item = items.value.find((i) => i.key === key)
    if (!item) return
    if (qty <= 0) return remove(key)
    item.qty = qty
  }

  function clear() {
    items.value = []
    coupon.value = null
  }

  function applyCoupon(payload) {
    coupon.value = payload
  }

  function clearCoupon() {
    coupon.value = null
  }

  function open() {
    isOpen.value = true
  }

  function close() {
    isOpen.value = false
  }

  function toggle() {
    isOpen.value = !isOpen.value
  }

  /**
   * Payload sent to the server at checkout (prices are recomputed server-side).
   */
  function checkoutPayload(email) {
    return {
      email,
      coupon: coupon.value?.code ?? null,
      items: items.value.map((i) => ({
        slug: i.slug,
        selections: i.selections ?? {},
        option: i.option ?? null,
        qty: i.qty,
      })),
    }
  }

  return {
    items,
    coupon,
    isOpen,
    count,
    subtotal,
    discount,
    total,
    add,
    remove,
    setQty,
    clear,
    applyCoupon,
    clearCoupon,
    open,
    close,
    toggle,
    checkoutPayload,
    hydrate,
  }
}

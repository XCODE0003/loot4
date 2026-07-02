<script setup>
import { onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link } from '@inertiajs/vue3'
import { useCart } from '@/loot4/composables/useCart'
import { useLocale } from '@/loot4/composables/useLocale'
import { asset } from '@/loot4/utils/asset'

const { t } = useI18n()

const { items, isOpen, count, subtotal, discount, total, coupon, remove, setQty, close, applyCoupon, clearCoupon } = useCart()
const { formatPrice } = useLocale()

// Teleport + Transition is rendered on the client only — SSR has no <body>
// teleport target, which would otherwise produce a hydration mismatch.
const mounted = ref(false)
onMounted(() => {
  mounted.value = true
})

// Lock background scroll while the cart is open. Uses the position:fixed
// technique so iOS Safari can't scroll the page behind (overflow:hidden alone
// doesn't hold there) — this is what caused the "double scroll".
let savedScrollY = 0
watch(isOpen, (open) => {
  if (typeof document === 'undefined') return
  const body = document.body
  if (open) {
    savedScrollY = window.scrollY
    body.style.top = `-${savedScrollY}px`
    body.classList.add('cart-open')
  } else {
    body.classList.remove('cart-open')
    body.style.top = ''
    window.scrollTo(0, savedScrollY)
  }
})

const promoOpen = ref(true)
const promoInput = ref('')
const promoError = ref('')
const promoLoading = ref(false)


function money(value) {
  return formatPrice(value)
}

async function applyPromo() {
  const code = promoInput.value.trim()
  if (!code) return
  promoError.value = ''
  promoLoading.value = true
  try {
    const res = await fetch(`/cart/coupon?code=${encodeURIComponent(code)}&subtotal=${subtotal.value}`, {
      headers: { Accept: 'application/json' },
    })
    const data = await res.json()
    if (data.valid) {
      applyCoupon({ code: data.code, type: data.type, value: data.value })
      promoError.value = ''
    } else {
      clearCoupon()
      promoError.value = data.message || t('cart.invalid')
    }
  } catch {
    promoError.value = t('cart.invalid')
  } finally {
    promoLoading.value = false
  }
}
</script>

<template>
  <Teleport v-if="mounted" to="body">
    <transition name="cart-fade">
      <div v-if="isOpen" class="cart_overlay" @click="close" />
    </transition>
    <transition name="cart-slide">
      <aside v-if="isOpen" class="cart" role="dialog" aria-label="Shopping cart">
        <div class="cart_head">
          <h2 class="cart_title">{{ $t('cart.title') }} <span>({{ $t('cart.items', { n: count }) }})</span></h2>
          <button type="button" class="cart_close" aria-label="Close cart" @click="close">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </button>
        </div>

        <div class="cart_body">
          <p v-if="!items.length" class="cart_empty">{{ $t('cart.empty') }}</p>

          <div v-for="item in items" :key="item.key" class="cart_item">
            <div class="cart_item_media">
              <button type="button" class="cart_item_remove" aria-label="Remove item" @click="remove(item.key)">
                <svg width="12" height="12" viewBox="0 0 14 14" fill="none"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              </button>
              <img :src="asset(item.image)" :alt="item.title" />
            </div>
            <div class="cart_item_info">
              <p class="cart_item_title">{{ item.title }}</p>
              <p v-if="item.option" class="cart_item_option">{{ item.option }}</p>
              <div class="cart_qty">
                <button type="button" class="cart_qty_btn" aria-label="Decrease quantity" @click="setQty(item.key, item.qty - 1)">−</button>
                <span class="cart_qty_value">{{ item.qty }}</span>
                <button type="button" class="cart_qty_btn" aria-label="Increase quantity" @click="setQty(item.key, item.qty + 1)">+</button>
              </div>
            </div>
            <div class="cart_item_prices">
              <span v-if="item.priceOld && item.priceOld > item.price" class="cart_item_old">{{ money(item.priceOld) }}</span>
              <span class="cart_item_price">{{ money(item.price * item.qty) }}</span>
            </div>
          </div>
        </div>

        <div class="cart_foot">
          <div class="cart_row">
            <span>{{ $t('cart.totalItems') }}</span>
            <span class="cart_row_value">{{ count }}</span>
          </div>

          <div class="cart_divider" />

          <button type="button" class="cart_promo_toggle" @click="promoOpen = !promoOpen">
            {{ $t('cart.promocode') }}
            <span>{{ promoOpen ? '−' : '+' }}</span>
          </button>
          <div v-if="promoOpen" class="cart_promo">
            <input v-model="promoInput" type="text" class="cart_promo_input" :placeholder="$t('cart.promocodePlaceholder')" @keyup.enter="applyPromo" />
            <button type="button" class="cart_promo_apply" :disabled="promoLoading" @click="applyPromo">{{ $t('cart.apply') }}</button>
          </div>
          <p v-if="promoError" class="cart_promo_error">{{ promoError }}</p>
          <p v-if="coupon" class="cart_promo_ok">{{ $t('cart.applied', { code: coupon.code }) }} · −{{ money(discount) }}</p>

          <div class="cart_row cart_total">
            <span>{{ $t('cart.total') }}</span>
            <span class="cart_row_value">{{ money(total) }}</span>
          </div>

          <Link href="/checkout" class="cart_checkout" :class="{ 'is-disabled': !items.length }" @click="close">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 2l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V5l8-3z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ $t('cart.checkout') }}
          </Link>

          <div class="cart_payments">
            <img src="/payment.svg" alt="Accepted payment methods" class="cart_payments_strip" loading="lazy" />
          </div>
        </div>
      </aside>
    </transition>
  </Teleport>
</template>

<style scoped>
.cart_overlay {
  position: fixed;
  inset: 0;
  background: rgba(3, 6, 20, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1000;
}
.cart {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 1001;
  display: flex;
  flex-direction: column;
  width: 460px;
  max-width: 100vw;
  height: 100dvh;
  padding: 28px;
  background: #0b1020;
  color: #fff;
  box-shadow: -20px 0 60px rgba(0, 0, 0, 0.45);
}
.cart_head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
}
.cart_title {
  font-size: 26px;
  font-weight: 700;
}
.cart_title span {
  font-size: 15px;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.5);
}
.cart_close {
  display: grid;
  place-items: center;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  cursor: pointer;
}
.cart_close:hover {
  background: rgba(255, 255, 255, 0.16);
}
.cart_body {
  flex: 1;
  overflow-y: auto;
  overscroll-behavior: contain;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.cart_empty {
  color: rgba(255, 255, 255, 0.5);
  padding: 24px 0;
}
.cart_item {
  display: grid;
  grid-template-columns: 76px 1fr auto;
  gap: 16px;
  align-items: center;
  padding: 16px;
  border-radius: 16px;
  background: #161c33;
}
.cart_item_media {
  position: relative;
  width: 76px;
  height: 76px;
}
.cart_item_media img {
  width: 76px;
  height: 76px;
  object-fit: cover;
  border-radius: 12px;
}
.cart_item_remove {
  position: absolute;
  top: -8px;
  left: -8px;
  display: grid;
  place-items: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #0b1020;
  color: #fff;
  border: 2px solid #161c33;
  cursor: pointer;
}
.cart_item_title {
  font-weight: 600;
}
.cart_item_option {
  margin-top: 4px;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.45);
}
.cart_qty {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  margin-top: 10px;
  padding: 4px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.cart_qty_btn {
  display: grid;
  place-items: center;
  width: 26px;
  height: 26px;
  border-radius: 7px;
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  font-size: 17px;
  line-height: 1;
  cursor: pointer;
  transition: background 0.15s;
}
.cart_qty_btn:hover {
  background: rgba(43, 255, 149, 0.25);
}
.cart_qty_value {
  min-width: 18px;
  text-align: center;
  font-weight: 600;
  font-size: 15px;
}
.cart_item_prices {
  text-align: right;
  white-space: nowrap;
}
.cart_item_old {
  display: block;
  font-size: 13px;
  color: #ff6b6b;
  text-decoration: line-through;
}
.cart_item_price {
  font-size: 18px;
  font-weight: 700;
}
.cart_foot {
  padding-top: 18px;
}
.cart_row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: rgba(255, 255, 255, 0.75);
  margin: 10px 0;
}
.cart_row_value {
  color: #fff;
  font-weight: 700;
}
.cart_total {
  font-size: 20px;
  margin: 14px 0;
}
.cart_total .cart_row_value {
  font-size: 22px;
}
.cart_divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 14px 0;
}
.cart_promo_toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  color: #fff;
  font-size: 16px;
  cursor: pointer;
  margin-bottom: 12px;
}
.cart_promo {
  display: flex;
  gap: 10px;
  margin-bottom: 8px;
}
.cart_promo_input {
  flex: 1;
  padding: 14px 18px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
}
.cart_promo_input::placeholder {
  color: rgba(255, 255, 255, 0.45);
}
.cart_promo_apply {
  padding: 0 26px;
  border-radius: 12px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 22px 32px 0 rgba(81, 255, 214, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}
.cart_promo_apply:disabled {
  opacity: 0.6;
  cursor: default;
}
.cart_promo_error {
  color: #ff6b6b;
  font-size: 13px;
  margin-bottom: 6px;
}
.cart_promo_ok {
  color: #4ade80;
  font-size: 13px;
  margin-bottom: 6px;
}
.cart_checkout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  margin-top: 8px;
  padding: 20px;
  border-radius: 16px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 22px 32px 0 rgba(81, 255, 214, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
}
.cart_checkout.is-disabled {
  opacity: 0.5;
  pointer-events: none;
}
.cart_payments {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 22px;
  opacity: 0.6;
}
.cart_payments_strip {
  width: 100%;
  max-width: 100%;
  height: auto;
}
.cart-fade-enter-active,
.cart-fade-leave-active {
  transition: opacity 0.25s ease;
}
.cart-fade-enter-from,
.cart-fade-leave-to {
  opacity: 0;
}
.cart-slide-enter-active,
.cart-slide-leave-active {
  transition: transform 0.3s ease;
}
.cart-slide-enter-from,
.cart-slide-leave-to {
  transform: translateX(100%);
}
@media (max-width: 600px) {
  .cart {
    width: 100%;
    padding: 20px;
  }
}
</style>

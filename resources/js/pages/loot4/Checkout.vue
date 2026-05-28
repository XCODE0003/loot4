<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, Link, router } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import { useCart } from '@/loot4/composables/useCart'

defineOptions({ layout: null })
import { useLocale } from '@/loot4/composables/useLocale'
import { asset } from '@/loot4/utils/asset'
import headerLogo from '@/loot4/assets/img/header_logo.svg'
import visaIcon from '@/loot4/assets/img/product_visa.png'
import masterIcon from '@/loot4/assets/img/product_master.png'
import paypalIcon from '@/loot4/assets/img/product_paypal.png'
import payIcon from '@/loot4/assets/img/product_pay.png'

const { t } = useI18n()
const { items, subtotal, discount, total, coupon, count, remove, applyCoupon, clearCoupon, checkoutPayload, hydrate } = useCart()
const { formatPrice } = useLocale()

onMounted(() => hydrate())

const email = ref('')
const method = ref('card')
const processing = ref(false)
const formError = ref('')

const promoOpen = ref(false)
const promoInput = ref(coupon.value?.code ?? '')
const promoError = ref('')
const promoLoading = ref(false)

const paymentMethods = [
  { value: 'card',     label: 'Credit Card',       feeLabel: '(+5%)', icons: [visaIcon, masterIcon, payIcon] },
  { value: 'applepay', label: 'Apple Pay',         feeLabel: '(+5%)', icons: [payIcon] },
  { value: 'cashapp',  label: 'Cash App',          feeLabel: '(+5%)', icons: [payIcon] },
  { value: 'klarna',   label: 'Klarna (US)',       feeLabel: '(+5%)', icons: [paypalIcon] },
  { value: 'gpay',     label: 'Google Pay',        feeLabel: '(+5%)', icons: [payIcon] },
  { value: 'amazon',   label: 'Amazon Pay',        feeLabel: '(+5%)', icons: [payIcon] },
  { value: 'ideal',    label: 'iDEAL',             feeLabel: '(+5%)', icons: [paypalIcon] },
  { value: 'afterpay', label: 'Afterpay / Clearpay', feeLabel: '(+5%)', icons: [paypalIcon] },
  { value: 'crypto',   label: 'Crypto',            feeLabel: '(-5%)', extra: '5% off', icons: [visaIcon] },
]

const FEE_RATE = 0.05

const activeMethod = computed(() => paymentMethods.find((m) => m.value === method.value) ?? paymentMethods[0])
const feeMultiplier = computed(() => (method.value === 'crypto' ? -FEE_RATE : FEE_RATE))
const feeAmount = computed(() => total.value * feeMultiplier.value)
const grandTotal = computed(() => total.value + feeAmount.value)

function money(value) {
  return formatPrice(value)
}

function signedMoney(value) {
  const sign = value < 0 ? '−' : '+'
  return `${sign}${formatPrice(Math.abs(Number(value)))}`
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

function placeOrder() {
  if (!items.value.length || processing.value) return
  formError.value = ''
  processing.value = true
  router.post('/checkout', { ...checkoutPayload(email.value), method: method.value }, {
    onError: (errors) => {
      formError.value = errors.email || errors.items || errors.payment || 'Please check your details'
    },
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>

<template>
  <Head title="Checkout — Loot4you" />
  <section class="co">
    <div class="co_inner">
      <header class="co_brand">
        <Link href="/" aria-label="Loot4you">
          <img :src="headerLogo" alt="Loot4you" />
        </Link>
      </header>

      <div v-if="!items.length" class="co_empty">
        <p>{{ $t('checkout.empty') }}</p>
        <Link href="/game" class="co_btn">{{ $t('checkout.browse') }}</Link>
      </div>

      <div v-else class="co_grid">
        <div class="co_left">
          <h1 class="co_title">Secure checkout</h1>

          <div class="co_section">
            <p class="co_section_label">Customer information</p>
            <input
              id="co-email"
              v-model="email"
              type="email"
              class="co_field"
              placeholder="Email *"
              @keyup.enter="placeOrder"
            />
          </div>

          <div class="co_section">
            <h2 class="co_section_title">Pay with</h2>
            <div class="co_methods">
              <label
                v-for="m in paymentMethods"
                :key="m.value"
                class="co_method"
                :class="{ 'is-active': method === m.value }"
              >
                <input v-model="method" type="radio" name="method" :value="m.value" />
                <span class="co_method_radio" aria-hidden="true"></span>
                <span class="co_method_label">
                  {{ m.label }} {{ m.feeLabel }}
                  <span v-if="m.extra" class="co_method_extra">{{ m.extra }}</span>
                </span>
                <span class="co_method_icons">
                  <img v-for="(icon, idx) in m.icons" :key="idx" :src="icon" alt="" />
                </span>
              </label>
            </div>
          </div>
        </div>

        <aside class="co_right">
          <div class="co_items">
            <div v-for="item in items" :key="item.key" class="co_item">
              <button
                type="button"
                class="co_item_remove"
                aria-label="Remove item"
                @click="remove(item.key)"
              >
                <svg width="11" height="11" viewBox="0 0 14 14" fill="none">
                  <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
              <img class="co_item_img" :src="asset(item.image)" :alt="item.title" />
              <div class="co_item_body">
                <p class="co_item_title">{{ item.title }}</p>
                <p v-if="item.option" class="co_item_option">{{ item.option }}</p>
              </div>
              <div class="co_item_price">
                <span v-if="item.compareAt && item.compareAt > item.price" class="co_item_price_old">
                  {{ money(item.compareAt * item.qty) }}
                </span>
                <span class="co_item_price_now">{{ money(item.price * item.qty) }}</span>
              </div>
            </div>
          </div>

          <div class="co_summary">
            <div class="co_row">
              <span>Total items</span>
              <span>{{ count }}</span>
            </div>
            <div class="co_row">
              <span>{{ activeMethod.label }} {{ activeMethod.feeLabel }}</span>
              <span :class="{ 'co_row_pos': feeAmount > 0, 'co_row_neg': feeAmount < 0 }">
                {{ signedMoney(feeAmount) }}
              </span>
            </div>
            <div v-if="coupon" class="co_row co_row_neg">
              <span>Discount ({{ coupon.code }})</span>
              <span>−{{ money(discount) }}</span>
            </div>

            <div class="co_promo">
              <button type="button" class="co_promo_toggle" @click="promoOpen = !promoOpen">
                <span>I have a promocode</span>
                <span class="co_promo_sign">{{ promoOpen ? '−' : '+' }}</span>
              </button>
              <div v-if="promoOpen" class="co_promo_row">
                <input
                  v-model="promoInput"
                  type="text"
                  class="co_field co_promo_input"
                  placeholder="Promocode"
                  @keyup.enter.prevent="applyPromo"
                />
                <button
                  type="button"
                  class="co_promo_apply"
                  :disabled="promoLoading"
                  @click="applyPromo"
                >
                  {{ promoLoading ? '…' : 'Apply' }}
                </button>
              </div>
              <p v-if="promoError" class="co_promo_err">{{ promoError }}</p>
            </div>

            <div class="co_row co_row_total">
              <span>Total</span>
              <span>{{ money(grandTotal) }}</span>
            </div>

            <p v-if="formError" class="co_error">{{ formError }}</p>

            <button
              type="button"
              class="co_pay"
              :disabled="processing"
              @click="placeOrder"
            >
              <svg width="16" height="18" viewBox="0 0 16 18" fill="none" aria-hidden="true">
                <path d="M8 1l6 2v6c0 4-3 6.5-6 8-3-1.5-6-4-6-8V3l6-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
              </svg>
              <span>{{ processing ? $t('checkout.processing') : `Pay now  ${money(grandTotal)}` }}</span>
            </button>

            <p class="co_terms">
              By placing an order on loot4you.com, you agree to our
              <a href="/legal/terms" target="_blank" rel="noopener">Terms of Service</a>
              and <a href="/legal/privacy" target="_blank" rel="noopener">Privacy Policy</a>.
              After completing your purchase, we may send you emails with offers related to similar products or services.
              You can unsubscribe at any time using the link provided or directly from the email.
            </p>

            <div class="co_trust">
              <div class="co_trust_pilot">
                <svg width="14" height="14" viewBox="0 0 20 20" aria-hidden="true">
                  <polygon points="10,1 12.6,7 19,7.5 14,11.8 15.8,18 10,14.5 4.2,18 6,11.8 1,7.5 7.4,7" fill="#00b67a"/>
                </svg>
                <span>Trustpilot</span>
              </div>
              <div class="co_trust_badges">
                <span class="co_trust_badge co_trust_mc">
                  <span class="co_trust_mc_dots">
                    <span class="co_trust_mc_red"></span>
                    <span class="co_trust_mc_yellow"></span>
                  </span>
                  <span class="co_trust_mc_text">ID Check</span>
                </span>
                <span class="co_trust_badge co_trust_visa">
                  <span class="co_trust_visa_top">Verified by</span>
                  <span class="co_trust_visa_bot">VISA</span>
                </span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>
</template>

<style scoped>
.co {
  min-height: 100vh;
  background: transparent;
  color: #fff;
  padding: 32px 24px 80px;
}
.co_inner {
  max-width: 1240px;
  margin: 0 auto;
}
.co_brand {
  margin-bottom: 28px;
}
.co_brand img {
  height: 36px;
  display: block;
}

.co_empty {
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  flex-direction: column;
  gap: 18px;
  align-items: flex-start;
}

.co_grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 80px;
  align-items: start;
}

.co_title {
  font-size: 30px;
  font-weight: 700;
  margin: 0 0 24px;
  color: #fff;
}

.co_section {
  margin-bottom: 28px;
}
.co_section_label {
  color: rgba(255, 255, 255, 0.6);
  font-size: 14px;
  margin: 0 0 10px;
}
.co_section_title {
  font-size: 22px;
  font-weight: 700;
  margin: 0 0 14px;
  color: #fff;
}

.co_field {
  width: 100%;
  height: 52px;
  padding: 0 22px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #fff;
  font-size: 15px;
  outline: none;
  transition: border-color 0.15s ease;
}
.co_field::placeholder {
  color: rgba(255, 255, 255, 0.45);
}
.co_field:focus {
  border-color: rgba(43, 255, 149, 0.5);
}

.co_methods {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.co_method {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;
}
.co_method:hover {
  border-color: rgba(43, 255, 149, 0.3);
}
.co_method input[type='radio'] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
.co_method_radio {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  background: transparent;
  display: inline-block;
  position: relative;
  flex-shrink: 0;
}
.co_method.is-active {
  background: rgba(43, 255, 149, 0.08);
  border-color: rgba(43, 255, 149, 0.5);
  color: #fff;
}
.co_method.is-active .co_method_radio {
  border-color: #2bff95;
  background: #2bff95;
  box-shadow: inset 0 0 0 4px #060b15;
}
.co_method_label {
  font-size: 15px;
  font-weight: 500;
}
.co_method_extra {
  color: #4ade80;
  font-weight: 600;
  margin-left: 8px;
}
.co_method_icons {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.co_method_icons img {
  height: 18px;
  width: auto;
  opacity: 0.85;
}

/* Right column */
.co_right {
  display: flex;
  flex-direction: column;
  gap: 18px;
  position: sticky;
  top: 32px;
}

.co_items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.co_item {
  position: relative;
  display: grid;
  grid-template-columns: 64px 1fr auto;
  gap: 14px;
  align-items: center;
  padding: 14px 18px 14px 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.05);
}
.co_item_remove {
  position: absolute;
  top: 10px;
  left: 10px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 1;
}
.co_item_img {
  width: 64px;
  height: 64px;
  border-radius: 10px;
  object-fit: cover;
}
.co_item_title {
  margin: 0;
  font-weight: 600;
  font-size: 15px;
  color: #fff;
}
.co_item_option {
  margin: 4px 0 0;
  color: rgba(255, 255, 255, 0.55);
  font-size: 13px;
}
.co_item_price {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
  white-space: nowrap;
}
.co_item_price_old {
  color: rgba(255, 255, 255, 0.4);
  font-size: 13px;
  text-decoration: line-through;
}
.co_item_price_now {
  color: #fff;
  font-weight: 700;
  font-size: 16px;
}

.co_summary {
  padding: 4px 2px 0;
}
.co_row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 14px 0;
  color: rgba(255, 255, 255, 0.85);
  font-size: 15px;
}
.co_row_pos { color: rgba(255, 255, 255, 0.85); }
.co_row_neg { color: #4ade80; }
.co_row_total {
  font-size: 22px;
  font-weight: 700;
  color: #fff;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 18px;
  margin-top: 8px;
}

.co_promo {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 14px;
}
.co_promo_toggle {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.85);
  font-size: 15px;
  padding: 4px 0;
  cursor: pointer;
}
.co_promo_sign {
  font-size: 18px;
  color: rgba(255, 255, 255, 0.6);
}
.co_promo_row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  margin-top: 12px;
}
.co_promo_input {
  height: 48px;
}
.co_promo_apply {
  height: 48px;
  padding: 0 22px;
  border-radius: 999px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  color: #fff;
  font-weight: 600;
  border: none;
  cursor: pointer;
}
.co_promo_apply:disabled {
  opacity: 0.6;
  cursor: default;
}
.co_promo_err {
  color: #ff6b6b;
  font-size: 13px;
  margin-top: 8px;
}

.co_pay {
  width: 100%;
  height: 56px;
  margin-top: 16px;
  border-radius: 999px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  color: #fff;
  font-weight: 700;
  font-size: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 24px rgba(43, 255, 149, 0.2);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.co_pay:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 10px 28px rgba(43, 255, 149, 0.3);
}
.co_pay:disabled {
  opacity: 0.7;
  cursor: default;
}

.co_error {
  color: #ff6b6b;
  font-size: 13px;
  margin-top: 10px;
  text-align: center;
}

.co_terms {
  color: rgba(255, 255, 255, 0.5);
  font-size: 12px;
  line-height: 1.6;
  margin: 16px 0 0;
  text-align: center;
}
.co_terms a {
  color: rgba(255, 255, 255, 0.75);
  text-decoration: underline;
}

.co_trust {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  margin-top: 22px;
}
.co_trust_pilot {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: rgba(255, 255, 255, 0.85);
  font-size: 14px;
  font-weight: 600;
}
.co_trust_badges {
  display: inline-flex;
  align-items: center;
  gap: 18px;
}
.co_trust_badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 11px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}
.co_trust_mc {
  flex-direction: column;
  gap: 2px;
}
.co_trust_mc_dots {
  display: inline-flex;
}
.co_trust_mc_red,
.co_trust_mc_yellow {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: inline-block;
}
.co_trust_mc_red { background: #eb001b; }
.co_trust_mc_yellow { background: #f79e1b; margin-left: -7px; mix-blend-mode: multiply; }
.co_trust_mc_text {
  color: rgba(255, 255, 255, 0.55);
  font-size: 10px;
}
.co_trust_visa {
  flex-direction: column;
  gap: 0;
}
.co_trust_visa_top {
  color: rgba(255, 255, 255, 0.6);
  font-size: 9px;
  font-style: italic;
  text-transform: none;
  letter-spacing: 0;
}
.co_trust_visa_bot {
  color: #1a1f71;
  font-size: 16px;
  font-weight: 800;
  font-style: italic;
  background: linear-gradient(180deg, #fff 50%, #f7b600 50%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.co_btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 14px 26px;
  border-radius: 999px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  color: #fff;
  font-weight: 600;
  text-decoration: none;
}

@media (max-width: 900px) {
  .co_grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .co_right {
    position: static;
  }
}
</style>

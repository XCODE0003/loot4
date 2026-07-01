<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, Link, router } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import { useCart } from '@/loot4/composables/useCart'
import { useLocale } from '@/loot4/composables/useLocale'
import { asset } from '@/loot4/utils/asset'
import { countries, regionsFor } from '@/loot4/data/countries'

const props = defineProps({
  // Server-authoritative Express delivery fee (config/checkout.php), so the
  // displayed total matches what the server charges.
  expressFee: { type: Number, default: 20.99 },
})

const { t } = useI18n()
const { items, subtotal, discount, total, coupon, count, remove, setQty, applyCoupon, clearCoupon, checkoutPayload, hydrate } = useCart()
const { formatPrice } = useLocale()

onMounted(() => hydrate())

const email = ref('')
const emailInput = ref(null)
const emailError = ref('')
const method = ref('')
const processing = ref(false)
const formError = ref('')
const agreed = ref(true)

// Billing details (compact 2-column form). State is required only for countries
// that have regions; everything else except phone is required.
const billing = ref({
  first_name: '',
  last_name: '',
  phone: '',
  country: '',
  state: '',
  town: '',
  address: '',
  postal_code: '',
})
const billingErrors = ref({})

const regions = computed(() => regionsFor(billing.value.country))
const hasRegions = computed(() => regions.value.length > 0)

// Reset a stale region when the country changes.
watch(() => billing.value.country, () => {
  billing.value.state = ''
  delete billingErrors.value.country
})

// Delivery choice — Standard is free, Express adds the server-provided fee.
const delivery = ref('standard')
const deliveryFee = computed(() => (delivery.value === 'express' ? props.expressFee : 0))

// Collapsible "Order Summary" shutter (mobile only — always expanded on desktop).
const summaryOpen = ref(false)

const promoOpen = ref(false)
const promoInput = ref(coupon.value?.code ?? '')
const promoError = ref('')
const promoLoading = ref(false)

// IceNox payment-method identifiers (pay.icenox.com/docs).
const paymentMethods = [
  { value: 'stripe-cards',       label: 'Credit / Debit Card', icons: ['/payment_methods/cards.svg'] },
  { value: 'stripe-apple-pay',   label: 'Apple Pay',           icons: ['/payment_methods/apple-pay.svg'] },
  { value: 'stripe-google-pay',  label: 'Google Pay',          icons: ['/payment_methods/google-pay.svg'] },
  { value: 'stripe-link',        label: 'Link',                icons: ['/payment_methods/link-white.svg'] },
  { value: 'stripe-klarna',      label: 'Klarna',              icons: ['/payment_methods/klarna.svg'] },
  { value: 'stripe-amazon-pay',  label: 'Amazon Pay',          icons: ['/payment_methods/amazon-pay-white.svg'] },
  { value: 'stripe-bancontact',  label: 'Bancontact',          icons: ['/payment_methods/bancontact.svg'] },
  { value: 'stripe-eps',         label: 'EPS',                 icons: ['/payment_methods/eps-white.svg'] },
  { value: 'stripe-revolut-pay', label: 'Revolut Pay',         icons: ['/payment_methods/revolut-white.svg'] },
]

// Collapsible "Pay with" dropdown (mobile only). No method is pre-selected, so
// it starts open with the list shown; choosing a method collapses it (animated)
// to a select header showing the chosen method.
const methodsOpen = ref(true)
const methodError = ref(false)
const methodsRef = ref(null)
const selectedMethod = computed(
  () => paymentMethods.find((m) => m.value === method.value) ?? null,
)

function onMethodPick() {
  methodsOpen.value = false
  methodError.value = false
}

function scrollToMethods() {
  methodsRef.value?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

const grandTotal = computed(() => total.value + deliveryFee.value)

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

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

function focusEmail() {
  const el = emailInput.value
  if (!el) return
  el.focus()
  el.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

function validateEmail() {
  const value = email.value.trim()
  if (!value) {
    emailError.value = 'The email field is required.'
    return false
  }
  if (!EMAIL_RE.test(value)) {
    emailError.value = 'Please enter a valid email address.'
    return false
  }
  emailError.value = ''
  return true
}

// Required billing fields (state only when the country has regions). Returns
// true when all pass; otherwise fills billingErrors and returns false.
function validateBilling() {
  const b = billing.value
  const errs = {}
  if (!b.first_name.trim()) errs.first_name = 'Required'
  if (!b.last_name.trim()) errs.last_name = 'Required'
  if (!b.country) errs.country = 'Required'
  if (hasRegions.value && !b.state) errs.state = 'Required'
  if (!b.town.trim()) errs.town = 'Required'
  if (!b.address.trim()) errs.address = 'Required'
  if (!b.postal_code.trim()) errs.postal_code = 'Required'
  billingErrors.value = errs
  return Object.keys(errs).length === 0
}

function placeOrder() {
  if (!items.value.length || processing.value) return
  if (!validateEmail()) {
    focusEmail()
    return
  }
  if (!validateBilling()) {
    formError.value = 'Please fill in all required billing fields.'
    return
  }
  if (!method.value) {
    methodError.value = true
    methodsOpen.value = true
    scrollToMethods()
    return
  }
  if (!agreed.value) {
    formError.value = 'Please read and agree to the terms and conditions.'
    return
  }
  formError.value = ''
  processing.value = true
  router.post(
    '/checkout',
    {
      ...checkoutPayload(email.value),
      ...billing.value,
      delivery: delivery.value,
      method: method.value,
    },
    {
      onError: (errors) => {
        if (errors.email) {
          emailError.value = errors.email
          formError.value = ''
          focusEmail()
        } else {
          formError.value = errors.items || errors.payment || 'Please check your details'
        }
      },
      onFinish: () => {
        processing.value = false
      },
    },
  )
}
</script>

<template>
  <Head title="Checkout — Loot4you" />
  <section class="co">
    <div class="co_inner">
      <div v-if="!items.length" class="co_empty">
        <p>{{ $t('checkout.empty') }}</p>
        <Link href="/" class="co_btn">{{ $t('checkout.browse') }}</Link>
      </div>

      <div v-else class="co_grid">
        <div class="co_left">
          <h1 class="co_title">Checkout</h1>

          <div class="co_section co_section--billing">
            <p class="co_section_label">Customer information</p>
            <div class="co_fields">
              <div class="co_field_wrap">
                <input
                  v-model="billing.first_name"
                  type="text"
                  class="co_field"
                  :class="{ 'co_field--error': billingErrors.first_name }"
                  placeholder="First name *"
                  aria-label="First name"
                  @input="delete billingErrors.first_name"
                />
              </div>
              <div class="co_field_wrap">
                <input
                  v-model="billing.last_name"
                  type="text"
                  class="co_field"
                  :class="{ 'co_field--error': billingErrors.last_name }"
                  placeholder="Last name *"
                  aria-label="Last name"
                  @input="delete billingErrors.last_name"
                />
              </div>
              <div class="co_field_wrap co_field_wrap--full">
                <input
                  id="co-email"
                  ref="emailInput"
                  v-model="email"
                  type="email"
                  class="co_field"
                  :class="{ 'co_field--error': emailError }"
                  placeholder="Email *"
                  aria-label="Email"
                  :aria-invalid="emailError ? 'true' : 'false'"
                  @input="emailError = ''"
                  @keyup.enter="placeOrder"
                />
                <p v-if="emailError" class="co_field_err">{{ emailError }}</p>
              </div>
              <div class="co_field_wrap co_field_wrap--full">
                <input
                  v-model="billing.phone"
                  type="tel"
                  class="co_field"
                  placeholder="Phone (optional)"
                  aria-label="Phone"
                />
              </div>
              <div class="co_field_wrap">
                <select
                  v-model="billing.country"
                  class="co_field co_select"
                  :class="{ 'co_field--error': billingErrors.country, 'is-placeholder': !billing.country }"
                  aria-label="Country"
                >
                  <option value="" disabled>Country *</option>
                  <option v-for="c in countries" :key="c.code" :value="c.code">{{ c.name }}</option>
                </select>
              </div>
              <div class="co_field_wrap">
                <select
                  v-if="hasRegions"
                  v-model="billing.state"
                  class="co_field co_select"
                  :class="{ 'co_field--error': billingErrors.state, 'is-placeholder': !billing.state }"
                  aria-label="State or region"
                  @change="delete billingErrors.state"
                >
                  <option value="" disabled>State / region *</option>
                  <option v-for="r in regions" :key="r.code" :value="r.name">{{ r.name }}</option>
                </select>
                <input
                  v-else
                  v-model="billing.state"
                  type="text"
                  class="co_field"
                  placeholder="State / region"
                  aria-label="State or region"
                />
              </div>
              <div class="co_field_wrap">
                <input
                  v-model="billing.town"
                  type="text"
                  class="co_field"
                  :class="{ 'co_field--error': billingErrors.town }"
                  placeholder="Town / City *"
                  aria-label="Town"
                  @input="delete billingErrors.town"
                />
              </div>
              <div class="co_field_wrap">
                <input
                  v-model="billing.postal_code"
                  type="text"
                  class="co_field"
                  :class="{ 'co_field--error': billingErrors.postal_code }"
                  placeholder="Postal code *"
                  aria-label="Postal code"
                  @input="delete billingErrors.postal_code"
                />
              </div>
              <div class="co_field_wrap co_field_wrap--full">
                <input
                  v-model="billing.address"
                  type="text"
                  class="co_field"
                  :class="{ 'co_field--error': billingErrors.address }"
                  placeholder="Address *"
                  aria-label="Address"
                  @input="delete billingErrors.address"
                />
              </div>
            </div>
          </div>

          <div class="co_section co_section--delivery">
            <h2 class="co_section_title">Delivery</h2>
            <div class="co_delivery">
              <label class="co_delivery_opt" :class="{ 'is-active': delivery === 'standard' }">
                <input v-model="delivery" type="radio" name="delivery" value="standard" />
                <span class="co_method_radio" aria-hidden="true"></span>
                <span class="co_delivery_label">Standard <span class="co_delivery_time">1–24 hours</span></span>
                <span class="co_delivery_price">Free</span>
              </label>
              <label class="co_delivery_opt" :class="{ 'is-active': delivery === 'express' }">
                <input v-model="delivery" type="radio" name="delivery" value="express" />
                <span class="co_method_radio" aria-hidden="true"></span>
                <span class="co_delivery_label">Express <span class="co_delivery_time">1–12 hours</span></span>
                <span class="co_delivery_price">{{ money(expressFee) }}</span>
              </label>
            </div>
          </div>

          <div ref="methodsRef" class="co_section co_section--methods" :class="{ 'is-open': methodsOpen, 'has-error': methodError }">
            <h2 class="co_section_title">Pay with</h2>
            <!-- Mobile-only collapsed header showing the chosen method. -->
            <button type="button" class="co_methods_head" @click="methodsOpen = !methodsOpen">
              <span class="co_methods_head_label">{{ selectedMethod ? selectedMethod.label : 'Select payment method' }}</span>
              <span v-if="selectedMethod" class="co_method_icons co_methods_head_icons">
                <img v-for="(icon, idx) in selectedMethod.icons" :key="idx" :src="icon" alt="" />
              </span>
              <svg class="co_methods_chevron" width="13" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1.5 6 6.5 11 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
            <p v-if="methodError" class="co_method_err">Please choose a payment method.</p>
            <div class="co_methods">
              <label
                v-for="m in paymentMethods"
                :key="m.value"
                class="co_method"
                :class="{ 'is-active': method === m.value }"
                @click="onMethodPick"
              >
                <input v-model="method" type="radio" name="method" :value="m.value" />
                <span class="co_method_radio" aria-hidden="true"></span>
                <span class="co_method_label">
                  {{ m.label }}
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
          <div class="co_orderbox" :class="{ 'is-open': summaryOpen }">
            <button type="button" class="co_orderbox_toggle" @click="summaryOpen = !summaryOpen">
              <span class="co_orderbox_toggle_label">Order Summary</span>
              <span class="co_orderbox_toggle_total">{{ money(grandTotal) }}</span>
              <svg class="co_orderbox_chevron" width="13" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1.5 6 6.5 11 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
            <div class="co_orderbox_body">
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
                <div class="co_qty">
                  <button type="button" class="co_qty_btn" aria-label="Decrease quantity" @click="setQty(item.key, item.qty - 1)">−</button>
                  <span class="co_qty_value">{{ item.qty }}</span>
                  <button type="button" class="co_qty_btn" aria-label="Increase quantity" @click="setQty(item.key, item.qty + 1)">+</button>
                </div>
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

            <div class="co_row">
              <span>Delivery{{ delivery === 'express' ? ' (Express)' : '' }}</span>
              <span>{{ deliveryFee > 0 ? money(deliveryFee) : 'Free' }}</span>
            </div>

            <div class="co_row co_row_total">
              <span>Total</span>
              <span>{{ money(grandTotal) }}</span>
            </div>
            </div>
            </div>
          </div>

          <div class="co_paybox">
            <div class="co_consent">
              <p class="co_consent_text">
                Your personal data will be used to process your order, support your experience throughout this
                website, and for other purposes described in our
                <a href="/privacy" target="_blank" rel="noopener">privacy policy</a>.
              </p>
              <label class="co_agree">
                <input v-model="agreed" type="checkbox" />
                <span class="co_agree_box" aria-hidden="true"></span>
                <span class="co_agree_label">
                  I have read and agree to the website
                  <a href="/terms" target="_blank" rel="noopener">terms and conditions</a>
                  <span class="co_agree_req">*</span>
                </span>
              </label>
            </div>

            <p v-if="formError" class="co_error">{{ formError }}</p>

            <button
              type="button"
              class="co_pay"
              :disabled="processing || !agreed"
              @click="placeOrder"
            >
              <span>{{ processing ? $t('checkout.processing') : 'Pay Now' }}</span>
            </button>

            <p class="co_secure">
              <svg width="13" height="14" viewBox="0 0 16 18" fill="none" aria-hidden="true">
                <path d="M8 1l6 2v6c0 4-3 6.5-6 8-3-1.5-6-4-6-8V3l6-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
              </svg>
              Your payment is encrypted and secure.
            </p>
          </div>
        </aside>
      </div>
    </div>
  </section>
</template>

<style scoped>
.co {
  background: transparent;
  color: #fff;
  padding: 32px 24px 80px;
}
.co_inner {
  max-width: 1240px;
  margin: 0 auto;
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

/* Compact 2-column billing grid so the form doesn't stretch down the page. */
.co_fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.co_field_wrap {
  min-width: 0;
}
.co_field_wrap--full {
  grid-column: 1 / -1;
}

/* <select> styled to match .co_field, with a custom chevron. */
.co_select {
  appearance: none;
  -webkit-appearance: none;
  cursor: pointer;
  padding-right: 44px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8' fill='none'%3E%3Cpath d='M1 1.5 6 6.5 11 1.5' stroke='%23ffffff' stroke-opacity='0.6' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 20px center;
}
.co_select.is-placeholder {
  color: rgba(255, 255, 255, 0.45);
}
.co_select option {
  color: #111;
}

/* Delivery method options (reuse the payment-method radio visual). */
.co_delivery {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.co_delivery_opt {
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 20px;
  height: 56px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  cursor: pointer;
  transition: border-color 0.15s ease;
}
.co_delivery_opt.is-active {
  border-color: rgba(43, 255, 149, 0.5);
}
.co_delivery_opt input[type='radio'] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
.co_delivery_opt.is-active .co_method_radio {
  border-color: transparent;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 0 0 4px #060b15;
}
.co_delivery_label {
  flex: 1;
  color: #fff;
  font-size: 15px;
  font-weight: 600;
}
.co_delivery_time {
  color: rgba(255, 255, 255, 0.5);
  font-weight: 400;
  font-size: 13px;
  margin-left: 6px;
}
.co_delivery_price {
  color: #2bff95;
  font-weight: 700;
  font-size: 15px;
}
.co_field--error,
.co_field--error:focus {
  border-color: #ff6b6b;
  box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.18);
}
.co_field_err {
  margin: 8px 4px 0;
  color: #ff6b6b;
  font-size: 13px;
}

.co_methods {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
/* The dropdown header is a mobile-only affordance. */
.co_methods_head {
  display: none;
}
.co_method_err {
  margin: 8px 4px 0;
  color: #ff6b6b;
  font-size: 13px;
}
.co_method {
  position: relative;
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
  border-color: transparent;
  background: linear-gradient(135deg, rgba(43, 255, 149, 0.12), rgba(5, 71, 146, 0.12)), rgba(255, 255, 255, 0.04);
  color: #fff;
}
/* brand-gradient border on the selected method (mask keeps the 1px ring) */
.co_method.is-active::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 14px;
  padding: 1px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  -webkit-mask:
    linear-gradient(#000 0 0) content-box,
    linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
}
.co_method.is-active .co_method_radio {
  border-color: transparent;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
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
  max-height: 18px;
  max-width: 120px;
  object-fit: contain;
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
.co_qty {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
  padding: 3px;
  border-radius: 9px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.co_qty_btn {
  display: grid;
  place-items: center;
  width: 24px;
  height: 24px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  font-size: 16px;
  line-height: 1;
  cursor: pointer;
  transition: background 0.15s;
}
.co_qty_btn:hover {
  background: rgba(43, 255, 149, 0.25);
}
.co_qty_value {
  min-width: 16px;
  text-align: center;
  font-weight: 600;
  font-size: 14px;
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
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 22px 32px 0 rgba(81, 255, 214, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
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
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 22px 32px 0 rgba(81, 255, 214, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
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

.co_consent {
  margin-top: 18px;
  padding: 16px 18px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.02);
}
.co_consent_text {
  margin: 0 0 14px;
  font-size: 12.5px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.55);
}
.co_consent_text a {
  display: inline;
  color: #2bff95;
  text-decoration: none;
}
.co_consent_text a:hover {
  text-decoration: underline;
}
.co_agree {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}
.co_agree input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}
.co_agree_box {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  margin-top: 1px;
  border-radius: 6px;
  border: 1.5px solid rgba(255, 255, 255, 0.25);
  background: transparent;
  position: relative;
  transition: border-color 0.15s, background 0.15s;
}
.co_agree input:checked + .co_agree_box {
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  border-color: transparent;
}
.co_agree input:checked + .co_agree_box::after {
  content: '';
  position: absolute;
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid #fff;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}
.co_agree input:focus-visible + .co_agree_box {
  outline: 2px solid rgba(43, 255, 149, 0.6);
  outline-offset: 2px;
}
.co_agree_label {
  font-size: 13px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.75);
}
.co_agree_label a {
  display: inline;
  color: #2bff95;
  text-decoration: none;
}
.co_agree_label a:hover {
  text-decoration: underline;
}
.co_agree_req {
  color: #ff6b6b;
  margin-left: 2px;
}
.co_secure {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  margin: 14px 0 0;
  font-size: 12.5px;
  color: rgba(255, 255, 255, 0.5);
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

.co_btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 14px 26px;
  border-radius: 999px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 22px 32px 0 rgba(81, 255, 214, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
  font-weight: 600;
  text-decoration: none;
}

/* Order summary box: a collapsible shutter on mobile, always open on desktop. */
.co_orderbox_toggle {
  display: none;
}
.co_orderbox_body {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

@media (max-width: 900px) {
  /* Flatten the two columns into one stream so the blocks can be reordered:
     Order Summary (shutter) → Email → Pay with + consent + Pay Now. */
  .co_grid {
    display: flex;
    flex-direction: column;
    /* stretch (not the desktop "start") so email + methods fill the width */
    align-items: stretch;
    gap: 22px;
  }
  .co_left,
  .co_right {
    display: contents;
  }
  /* Order Summary → Email → Pay with (dropdown) → consent + Pay Now. */
  .co_title { order: 0; }
  .co_orderbox { order: 1; }
  .co_section--email { order: 2; }
  .co_section--methods { order: 3; }
  .co_paybox { order: 4; }

  /* "Pay with" becomes a dropdown: open by default, collapses to the chosen
     method's header once a method is picked. */
  .co_methods_head {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 14px 18px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #fff;
    font-family: var(--font-family);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
  }
  .co_methods_head_label {
    flex: 1;
    text-align: left;
  }
  .co_methods_head_icons img {
    height: 18px;
    width: auto;
    max-height: 18px;
    object-fit: contain;
    opacity: 0.85;
  }
  .co_methods_chevron {
    flex-shrink: 0;
    color: rgba(255, 255, 255, 0.55);
    transition: transform 0.25s ease;
  }
  .co_section--methods.is-open .co_methods_chevron {
    transform: rotate(180deg);
  }
  /* No method chosen on a blocked "Pay Now" — mark the select red. */
  .co_section--methods.has-error .co_methods_head {
    border-color: #ff6b6b;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.18);
  }
  /* Animated expand/collapse (display:none can't transition). */
  .co_methods {
    display: flex;
    max-height: 0;
    margin-top: 0;
    opacity: 0;
    overflow: hidden;
    transition: max-height 0.32s ease, opacity 0.25s ease, margin-top 0.32s ease;
  }
  .co_section--methods.is-open .co_methods {
    max-height: 820px;
    margin-top: 10px;
    opacity: 1;
  }
  /* rely on the flex gap, not per-section margins, so spacing stays even */
  .co_section {
    margin-bottom: 0;
  }

  .co_orderbox {
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03);
    overflow: hidden;
  }
  .co_orderbox_toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 16px 18px;
    background: transparent;
    color: #fff;
    font-family: var(--font-family);
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
  }
  .co_orderbox_toggle_label {
    flex: 1;
    text-align: left;
  }
  .co_orderbox_chevron {
    color: rgba(255, 255, 255, 0.55);
    transition: transform 0.2s ease;
  }
  .co_orderbox.is-open .co_orderbox_chevron {
    transform: rotate(180deg);
  }
  /* Animated expand/collapse of the order summary (matches the Pay-with dropdown). */
  .co_orderbox_body {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    padding: 0 14px;
    transition: max-height 0.34s ease, opacity 0.25s ease, padding 0.34s ease;
  }
  .co_orderbox.is-open .co_orderbox_body {
    max-height: 1200px;
    opacity: 1;
    padding: 0 14px 14px;
  }
}
</style>

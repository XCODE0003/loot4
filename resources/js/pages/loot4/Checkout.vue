<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'
import { useCart } from '@/loot4/composables/useCart'
import { useLocale } from '@/loot4/composables/useLocale'
import { asset } from '@/loot4/utils/asset'

const { items, subtotal, discount, total, coupon, count, checkoutPayload } = useCart()
const { currency } = useLocale()

const email = ref('')
const method = ref('card')
const processing = ref(false)
const formError = ref('')

const methods = [
  { value: 'card', tkey: 'checkout.card' },
  { value: 'paypal', tkey: 'checkout.paypal' },
]

function money(value) {
  return `${currency.value}${Number(value).toFixed(2)}`
}

function placeOrder() {
  if (!items.value.length || processing.value) return
  formError.value = ''
  processing.value = true
  // The cart is cleared on the success page (payment may redirect externally to IceNox).
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
  <section class="checkout">
    <Container>
      <h1 class="checkout_title">{{ $t('checkout.title') }}</h1>

      <div v-if="!items.length" class="checkout_empty">
        <p>{{ $t('checkout.empty') }}</p>
        <Link href="/game" class="checkout_btn">{{ $t('checkout.browse') }}</Link>
      </div>

      <div v-else class="checkout_grid">
        <div class="checkout_items">
          <div v-for="item in items" :key="item.key" class="checkout_item">
            <img :src="asset(item.image)" :alt="item.title" />
            <div class="checkout_item_info">
              <p class="checkout_item_title">{{ item.title }}</p>
              <p v-if="item.option" class="checkout_item_option">{{ item.option }}</p>
              <p class="checkout_item_qty">{{ $t('checkout.items') }}: {{ item.qty }}</p>
            </div>
            <span class="checkout_item_price">{{ money(item.price * item.qty) }}</span>
          </div>
        </div>

        <div class="checkout_summary">
          <h2 class="checkout_summary_title">{{ $t('checkout.summary') }}</h2>
          <div class="checkout_row"><span>{{ $t('checkout.items') }}</span><span>{{ count }}</span></div>
          <div class="checkout_row"><span>{{ $t('checkout.subtotal') }}</span><span>{{ money(subtotal) }}</span></div>
          <div v-if="coupon" class="checkout_row checkout_row--discount">
            <span>{{ $t('checkout.discount') }} ({{ coupon.code }})</span><span>−{{ money(discount) }}</span>
          </div>
          <div class="checkout_row checkout_row--total"><span>{{ $t('checkout.total') }}</span><span>{{ money(total) }}</span></div>

          <label class="checkout_label" for="checkout-email">{{ $t('checkout.email') }}</label>
          <input
            id="checkout-email"
            v-model="email"
            type="email"
            class="checkout_input"
            placeholder="you@example.com"
            @keyup.enter="placeOrder"
          />

          <label class="checkout_label">{{ $t('checkout.method') }}</label>
          <div class="checkout_methods">
            <label
              v-for="m in methods"
              :key="m.value"
              class="checkout_method"
              :class="{ 'is-active': method === m.value }"
            >
              <input v-model="method" type="radio" name="method" :value="m.value" />
              <span>{{ $t(m.tkey) }}</span>
            </label>
          </div>

          <p v-if="formError" class="checkout_error">{{ formError }}</p>

          <button type="button" class="checkout_btn checkout_btn--full" :disabled="processing" @click="placeOrder">
            {{ processing ? $t('checkout.processing') : $t('checkout.pay', { total: money(total) }) }}
          </button>
          <p class="checkout_note">{{ $t('checkout.note') }}</p>
        </div>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.checkout {
  padding: 60px 0 100px;
  min-height: 60vh;
}
.checkout_title {
  font-size: 36px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 28px;
}
.checkout_empty {
  color: rgba(255, 255, 255, 0.6);
  display: flex;
  flex-direction: column;
  gap: 18px;
  align-items: flex-start;
}
.checkout_grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 28px;
  align-items: start;
}
.checkout_items {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.checkout_item {
  display: grid;
  grid-template-columns: 72px 1fr auto;
  gap: 16px;
  align-items: center;
  padding: 16px;
  border-radius: 16px;
  background: #161c33;
}
.checkout_item img {
  width: 72px;
  height: 72px;
  object-fit: cover;
  border-radius: 12px;
}
.checkout_item_title {
  color: #fff;
  font-weight: 600;
}
.checkout_item_option,
.checkout_item_qty {
  color: rgba(255, 255, 255, 0.45);
  font-size: 14px;
  margin-top: 2px;
}
.checkout_item_price {
  color: #fff;
  font-weight: 700;
  white-space: nowrap;
}
.checkout_summary {
  padding: 24px;
  border-radius: 18px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.checkout_summary_title {
  color: #fff;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 18px;
}
.checkout_row {
  display: flex;
  justify-content: space-between;
  color: rgba(255, 255, 255, 0.7);
  margin: 10px 0;
}
.checkout_row--discount {
  color: #4ade80;
}
.checkout_row--total {
  color: #fff;
  font-size: 20px;
  font-weight: 700;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 14px;
  margin-top: 14px;
}
.checkout_label {
  display: block;
  color: rgba(255, 255, 255, 0.7);
  margin: 20px 0 8px;
}
.checkout_input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 12px;
  background: rgba(43, 74, 168, 0.18);
  border: 1px solid rgba(99, 130, 230, 0.25);
  color: #fff;
}
.checkout_error {
  color: #ff6b6b;
  font-size: 13px;
  margin-top: 8px;
}
.checkout_methods {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.checkout_method {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 13px 16px;
  border-radius: 12px;
  border: 1px solid rgba(99, 130, 230, 0.2);
  background: rgba(43, 74, 168, 0.12);
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
}
.checkout_method.is-active {
  border-color: #4f7bff;
  background: rgba(79, 123, 255, 0.18);
  color: #fff;
}
.checkout_btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 16px 28px;
  border-radius: 14px;
  background: linear-gradient(90deg, #2b50d8, #4f7bff);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}
.checkout_btn--full {
  width: 100%;
  margin-top: 18px;
}
.checkout_btn:disabled {
  opacity: 0.6;
  cursor: default;
}
.checkout_note {
  color: rgba(255, 255, 255, 0.4);
  font-size: 13px;
  margin-top: 12px;
  text-align: center;
}
@media (max-width: 900px) {
  .checkout_grid {
    grid-template-columns: 1fr;
  }
}
</style>

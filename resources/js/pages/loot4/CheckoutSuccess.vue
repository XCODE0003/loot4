<script setup>
import { computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'
import { useCart } from '@/loot4/composables/useCart'
import { useLocale } from '@/loot4/composables/useLocale'

const props = defineProps({
  order: { type: Object, required: true },
  authed: { type: Boolean, default: false },
})

const { formatPrice } = useLocale()
const { clear } = useCart()
const purchaseKey = computed(() => `l4_purchase_sent_${props.order?.number ?? ''}`)

const discordUrl = 'https://discord.gg/AyTrerusGZ'
const whatsappUrl = computed(() => {
  const message = `Hello! I want to get my order #${props.order?.number ?? ''} (${props.order?.email ?? ''})`
  return `https://wa.me/380730882668?text=${encodeURIComponent(message)}`
})

// Order placed — empty the cart once we reach the confirmation page.
onMounted(() => {
  clear()
  pushPurchaseEvent()
})

function money(value) {
  return formatPrice(value)
}

function detailLabel(key) {
  return key.replace(/[_-]+/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}

function pushPurchaseEvent() {
  const orderNumber = props.order?.number
  if (!orderNumber || typeof window === 'undefined') return

  // Prevent duplicate purchase events on refresh/revisit of success page.
  if (window.sessionStorage.getItem(purchaseKey.value) === '1') return

  window.dataLayer = window.dataLayer || []

  const items = (props.order?.items ?? []).map((item) => ({
    item_name: item.name ?? 'Product',
    item_id: item.id ?? '',
    price: Number(item.price ?? 0),
    quantity: Number(item.qty ?? 1),
  }))

  window.dataLayer.push({ ecommerce: null })
  window.dataLayer.push({
    event: 'purchase',
    ecommerce: {
      transaction_id: orderNumber,
      affiliation: 'Online Store',
      value: Number(props.order?.total ?? 0),
      currency: props.order?.currency ?? 'USD',
      items,
    },
  })

  window.sessionStorage.setItem(purchaseKey.value, '1')
}
</script>

<template>
  <Head title="Order confirmed — Loot4you" />
  <section class="ty">
    <Container>
      <div class="ty_head">
        <div class="ty_check">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="11" stroke="currentColor" stroke-width="1.5"/><path d="M7 12.5l3.2 3.2L17 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1 class="ty_title">Thank You, Your Order Has Been Received.</h1>
        <p class="ty_sub">Your order confirmation has been sent to your email</p>
      </div>

      <div class="ty_summary">
        <div class="ty_meta">
          <span class="ty_meta_label">📦 Order Number:</span>
          <span class="ty_meta_value">#{{ order.number }}</span>
        </div>
        <div class="ty_meta">
          <span class="ty_meta_label">🗓️ Order Date:</span>
          <span class="ty_meta_value">{{ order.date }}</span>
        </div>
        <div class="ty_meta">
          <span class="ty_meta_label">✉️ Order Email:</span>
          <span class="ty_meta_value">{{ order.email }}</span>
        </div>
        <div class="ty_meta">
          <span class="ty_meta_label">🧾 Order Total:</span>
          <span class="ty_meta_value ty_meta_value--accent">{{ money(order.total) }}</span>
        </div>
        <div class="ty_meta">
          <span class="ty_meta_label">💳 Payment Method:</span>
          <span class="ty_meta_value">{{ order.paymentMethod }}</span>
        </div>
      </div>

      <div class="ty_contact">
        <p class="ty_contact_title">To receive your order, please contact us:</p>
        <div class="ty_contact_links">
          <a :href="discordUrl" target="_blank" rel="noopener noreferrer" class="ty_contact_btn ty_contact_btn--discord">
            <svg width="18" height="18" viewBox="0 0 640 640" fill="currentColor"><path d="M524.5 133.8C485.6 115.6 445.3 103.1 404 96c-.7-.1-1.4.2-1.8 1-5.5 9.9-10.5 20.2-14.9 30.6-44.6-6.8-89.9-6.8-134.4 0-4.5-10.5-9.5-20.7-15.1-30.6-.4-.7-1.1-1.1-1.8-1-41.3 7.1-81.6 19.6-119.7 37.1-.3.1-.6.4-.8.7C39.1 247.5 18.2 358.6 28.4 468.2c0 .3.2.6.5.8 44.4 32.9 94 58 146.8 74.2.7.2 1.5 0 1.9-.6 11.3-15.4 21.4-31.8 30-48.8.5-1-.1-2.2-1.1-2.6-16.5-6.3-32.2-13.9-47.3-22.8-1.1-.6-1.2-2.2-.2-3 3.2-2.4 6.4-4.9 9.4-7.4.5-.4 1.2-.5 1.8-.2 99.2 45.3 206.6 45.3 304.6 0 .6-.3 1.3-.2 1.8.2 3 2.5 6.1 5 9.4 7.4 1 .8.9 2.4-.2 3-15.1 8.9-30.8 16.5-47.3 22.8-1 .4-1.5 1.6-1 2.6 8.8 17 18.9 33.3 30 48.8.4.6 1.2.8 1.9.6 52.9-16.2 102.6-41.3 147-74.2.3-.2.5-.5.5-.8 12.2-126.7-20.6-236.8-87-341.4-.2-.3-.5-.6-.8-.7zM222.5 401.5c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.4 59.2-52.8 59.2zm195.4 0c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.2 59.2-52.8 59.2z"/></svg>
            Discord
          </a>
          <a :href="whatsappUrl" target="_blank" rel="noopener noreferrer" class="ty_contact_btn ty_contact_btn--whatsapp">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
            WhatsApp
          </a>
        </div>
      </div>

      <div class="ty_block">
        <h2 class="ty_block_title">📦 Order Details</h2>
        <div v-for="(item, i) in order.items" :key="i" class="ty_item">
          <div class="ty_item_head">
            <div>
              <p class="ty_item_name">{{ item.name }}</p>
              <p class="ty_item_qty">Quantity: x{{ item.qty }}</p>
            </div>
            <span class="ty_item_price">{{ money(item.price * item.qty) }} {{ order.currency }}</span>
          </div>
          <div v-if="item.option || Object.keys(item.details || {}).length" class="ty_badges">
            <span v-if="item.option" class="ty_badge">{{ item.option }}</span>
            <span v-for="(val, key) in item.details" :key="key" class="ty_badge">
              {{ detailLabel(key) }}: <strong>{{ val }}</strong>
            </span>
          </div>
        </div>

        <div class="ty_totals">
          <div class="ty_total_row"><span>Subtotal:</span><span>{{ money(order.subtotal) }} {{ order.currency }}</span></div>
          <div v-if="order.discount > 0" class="ty_total_row ty_total_row--discount"><span>Discount:</span><span>−{{ money(order.discount) }} {{ order.currency }}</span></div>
          <div class="ty_total_row ty_total_row--grand"><span>Total:</span><span>{{ money(order.total) }} {{ order.currency }}</span></div>
        </div>
      </div>

      <div class="ty_block">
        <h2 class="ty_block_title">Billing Details</h2>
        <p class="ty_billing">Email: {{ order.email }}</p>
        <p v-if="order.name" class="ty_billing">Name: {{ order.name }}</p>
      </div>

      <div class="ty_actions">
        <Link v-if="authed" href="/account/orders" class="ty_btn ty_btn--primary">View My Orders</Link>
        <Link href="/" class="ty_btn ty_btn--ghost">Continue Shopping</Link>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.ty {
  padding: 56px 0 100px;
}
.ty_head {
  text-align: center;
  margin-bottom: 36px;
}
.ty_check {
  display: grid;
  place-items: center;
  width: 64px;
  height: 64px;
  margin: 0 auto 20px;
  border-radius: 50%;
  border: 1.5px solid rgba(43, 255, 149, 0.4);
  background: rgba(43, 255, 149, 0.1);
  color: #2bff95;
}
.ty_title {
  color: #fff;
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -0.02em;
}
.ty_sub {
  margin-top: 10px;
  color: rgba(255, 255, 255, 0.5);
}
.ty_summary,
.ty_block {
  max-width: 880px;
  margin: 0 auto 20px;
  padding: 24px 28px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.02);
}
.ty_summary {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}
.ty_meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.ty_meta_label {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.45);
}
.ty_meta_value {
  font-size: 17px;
  font-weight: 700;
  color: #fff;
}
.ty_meta_value--accent {
  color: #2bff95;
}
.ty_block_title {
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 18px;
}
.ty_item_head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}
.ty_item_name {
  color: #fff;
  font-weight: 600;
}
.ty_item_qty {
  color: rgba(255, 255, 255, 0.45);
  font-size: 13px;
  margin-top: 2px;
}
.ty_item_price {
  color: #fff;
  font-weight: 700;
  white-space: nowrap;
}
.ty_badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}
.ty_badge {
  padding: 6px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.07);
  font-size: 12px;
  color: rgba(255, 255, 255, 0.6);
}
.ty_badge strong {
  color: #fff;
  font-weight: 600;
}
.ty_totals {
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.ty_total_row {
  display: flex;
  justify-content: space-between;
  color: rgba(255, 255, 255, 0.65);
  margin: 8px 0;
  font-size: 14px;
}
.ty_total_row--discount {
  color: #2bff95;
}
.ty_total_row--grand {
  color: #fff;
  font-size: 18px;
  font-weight: 800;
}
.ty_total_row--grand span:last-child {
  color: #2bff95;
}
.ty_billing {
  color: rgba(255, 255, 255, 0.7);
  margin: 4px 0;
  font-size: 14px;
}
.ty_actions {
  max-width: 880px;
  margin: 0 auto 24px;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 16px;
}
.ty_btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 240px;
  padding: 0 28px;
  height: 52px;
  border-radius: 14px;
  font-weight: 700;
}
.ty_btn--primary {
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
}
.ty_btn--ghost {
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #fff;
}
.ty_btn--ghost:hover {
  background: rgba(255, 255, 255, 0.05);
}
.ty_contact {
  max-width: 880px;
  margin: 0 auto 20px;
  padding: 24px 28px;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.02);
}
.ty_contact_title {
  color: #fff;
  font-weight: 600;
  margin-bottom: 16px;
}
.ty_contact_links {
  display: flex;
  gap: 12px;
  justify-content: center;
}
.ty_contact_btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 11px 22px;
  border-radius: 10px;
  font-weight: 600;
  color: #fff;
}
.ty_contact_btn--discord {
  background: #5865f2;
}
.ty_contact_btn--whatsapp {
  background: #25d366;
}
.ty_contact_btn:hover {
  opacity: 0.9;
}

@media (max-width: 768px) {
  .ty_title {
    font-size: 24px;
  }
  .ty_summary {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  .ty_actions {
    grid-template-columns: 1fr;
  }
}
</style>

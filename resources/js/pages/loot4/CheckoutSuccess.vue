<script setup>
import { onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'
import { useCart } from '@/loot4/composables/useCart'
import { useLocale } from '@/loot4/composables/useLocale'

defineProps({
  order: { type: Object, required: true },
})

const { formatPrice } = useLocale()
const { clear } = useCart()

// Order placed — empty the cart once we reach the confirmation page.
onMounted(() => clear())

function money(value) {
  return formatPrice(value)
}
</script>

<template>
  <Head title="Order confirmed — Loot4you" />
  <section class="success">
    <Container>
      <div class="success_card">
        <div class="success_icon">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="11" stroke="currentColor" stroke-width="1.5"/><path d="M7 12.5l3.2 3.2L17 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1 class="success_title">{{ $t('success.thanks') }}</h1>
        <p class="success_sub">{{ $t('success.order') }} <strong>{{ order.number }}</strong> — {{ $t('success.sentTo', { email: order.email }) }}</p>

        <div class="success_items">
          <div v-for="(item, i) in order.items" :key="i" class="success_item">
            <div>
              <p class="success_item_name">{{ item.name }}</p>
              <p v-if="item.option" class="success_item_option">{{ item.option }}</p>
            </div>
            <span>{{ item.qty }} × {{ money(item.price) }}</span>
          </div>
        </div>

        <div class="success_totals">
          <div class="success_row"><span>{{ $t('success.subtotal') }}</span><span>{{ money(order.subtotal) }}</span></div>
          <div v-if="order.discount > 0" class="success_row success_row--discount"><span>{{ $t('success.discount') }}</span><span>−{{ money(order.discount) }}</span></div>
          <div class="success_row success_row--total"><span>{{ $t('success.total') }}</span><span>{{ money(order.total) }}</span></div>
        </div>

        <Link href="/game" class="success_btn">{{ $t('success.continue') }}</Link>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.success {
  padding: 80px 0 120px;
  min-height: 60vh;
}
.success_card {
  max-width: 560px;
  margin: 0 auto;
  padding: 40px;
  border-radius: 22px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
}
.success_icon {
  display: grid;
  place-items: center;
  width: 76px;
  height: 76px;
  margin: 0 auto 20px;
  border-radius: 50%;
  background: rgba(74, 222, 128, 0.12);
  color: #4ade80;
}
.success_title {
  color: #fff;
  font-size: 28px;
  font-weight: 700;
}
.success_sub {
  color: rgba(255, 255, 255, 0.6);
  margin-top: 10px;
}
.success_items {
  margin: 28px 0 8px;
  text-align: left;
}
.success_item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.85);
}
.success_item_name {
  color: #fff;
  font-weight: 600;
}
.success_item_option {
  color: rgba(255, 255, 255, 0.45);
  font-size: 14px;
}
.success_totals {
  text-align: left;
  margin: 18px 0 28px;
}
.success_row {
  display: flex;
  justify-content: space-between;
  color: rgba(255, 255, 255, 0.7);
  margin: 8px 0;
}
.success_row--discount {
  color: #4ade80;
}
.success_row--total {
  color: #fff;
  font-size: 20px;
  font-weight: 700;
}
.success_btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 16px 32px;
  border-radius: 14px;
  background: linear-gradient(110deg, #2bff95 0%, #054792 100%);
  color: #fff;
  font-weight: 700;
}
</style>

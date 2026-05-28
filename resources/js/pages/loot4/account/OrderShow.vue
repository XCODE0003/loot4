<script setup>
import { Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import AccountShell from '@/loot4/components/account/AccountShell.vue'
import { useLocale } from '@/loot4/composables/useLocale'

defineProps({
  order: { type: Object, required: true },
})

const { currency } = useLocale()

function money(value) {
  return `${currency.value}${Number(value).toFixed(2)}`
}
</script>

<template>
  <Head :title="`Order ${order.number} — Loot4you`" />
  <AccountShell :title="`Order ${order.number}`">
    <Link href="/account/orders" class="os_back">{{ $t('account.back') }}</Link>

    <div class="os_grid">
      <div class="os_panel">
        <h2 class="os_panel_title">{{ $t('account.items') }}</h2>
        <div v-for="(item, i) in order.items" :key="i" class="os_item">
          <div>
            <p class="os_item_name">{{ item.name }}</p>
            <p v-if="item.option" class="os_item_option">{{ item.option }}</p>
          </div>
          <span class="os_item_price">{{ item.qty }} × {{ money(item.price) }}</span>
        </div>

        <div class="os_totals">
          <div class="os_row"><span>{{ $t('account.total') }}</span><span>{{ money(order.subtotal) }}</span></div>
          <div v-if="order.discount > 0" class="os_row os_row--discount"><span>{{ $t('checkout.discount') }}</span><span>−{{ money(order.discount) }}</span></div>
          <div class="os_row os_row--total"><span>{{ $t('account.total') }}</span><span>{{ money(order.total) }}</span></div>
        </div>
      </div>

      <div class="os_panel">
        <h2 class="os_panel_title">{{ $t('account.details') }}</h2>
        <div class="os_meta"><span>{{ $t('account.date') }}</span><span>{{ order.date }}</span></div>
        <div class="os_meta"><span>{{ $t('account.status') }}</span><span>{{ order.statusLabel }}</span></div>
        <div class="os_meta"><span>{{ $t('account.payment') }}</span><span>{{ order.paymentStatusLabel }}</span></div>
        <div class="os_meta"><span>{{ $t('account.delivery') }}</span><span class="os_cap">{{ order.deliveryStatus }}</span></div>
        <div class="os_meta"><span>{{ $t('account.email') }}</span><span>{{ order.email }}</span></div>
      </div>
    </div>
  </AccountShell>
</template>

<style scoped>
.os_back {
  display: inline-block;
  color: #2bff95;
  margin-bottom: 18px;
}
.os_grid {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px;
  align-items: start;
}
.os_panel {
  padding: 24px;
  border-radius: 18px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.os_panel_title {
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 16px;
}
.os_item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
}
.os_item:first-of-type {
  border-top: none;
}
.os_item_name {
  color: #fff;
  font-weight: 600;
}
.os_item_option {
  color: rgba(255, 255, 255, 0.45);
  font-size: 13px;
}
.os_item_price {
  color: rgba(255, 255, 255, 0.85);
  white-space: nowrap;
}
.os_totals {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}
.os_row {
  display: flex;
  justify-content: space-between;
  color: rgba(255, 255, 255, 0.7);
  margin: 8px 0;
}
.os_row--discount {
  color: #4ade80;
}
.os_row--total {
  color: #fff;
  font-size: 18px;
  font-weight: 700;
}
.os_meta {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  color: rgba(255, 255, 255, 0.75);
}
.os_meta:first-of-type {
  border-top: none;
}
.os_meta span:last-child {
  color: #fff;
}
.os_cap {
  text-transform: capitalize;
}
@media (max-width: 860px) {
  .os_grid {
    grid-template-columns: 1fr;
  }
}
</style>

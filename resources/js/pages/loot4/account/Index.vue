<script setup>
import { Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import AccountShell from '@/loot4/components/account/AccountShell.vue'
import { useLocale } from '@/loot4/composables/useLocale'

defineProps({
  stats: { type: Object, required: true },
  recentOrders: { type: Array, default: () => [] },
})

const { formatPrice } = useLocale()

function money(value) {
  return formatPrice(value)
}

const statusColors = {
  completed: '#4ade80',
  processing: '#60a5fa',
  pending: '#fbbf24',
  cancelled: '#9ca3af',
  refunded: '#f87171',
}
</script>

<template>
  <Head title="My Account — Loot4you" />
  <AccountShell :title="$t('account.overview')">
    <div class="ov_stats">
      <div class="ov_stat">
        <p class="ov_stat_label">{{ $t('account.orders') }}</p>
        <p class="ov_stat_value">{{ stats.orders }}</p>
      </div>
      <div class="ov_stat">
        <p class="ov_stat_label">{{ $t('account.totalSpent') }}</p>
        <p class="ov_stat_value">{{ money(stats.spent) }}</p>
      </div>
      <div class="ov_stat">
        <p class="ov_stat_label">{{ $t('account.pending') }}</p>
        <p class="ov_stat_value">{{ stats.pending }}</p>
      </div>
    </div>

    <div class="ov_panel">
      <div class="ov_panel_head">
        <h2>{{ $t('account.recentOrders') }}</h2>
        <Link href="/account/orders" class="ov_link">{{ $t('account.viewAll') }}</Link>
      </div>

      <p v-if="!recentOrders.length" class="ov_empty">
        {{ $t('account.noOrders') }} <Link href="/" class="ov_link">{{ $t('account.startShopping') }}</Link>
      </p>

      <Link
        v-for="order in recentOrders"
        :key="order.id"
        :href="`/account/orders/${order.id}`"
        class="ov_order"
      >
        <div>
          <p class="ov_order_num">{{ order.number }}</p>
          <p class="ov_order_meta">{{ order.date }} · {{ order.itemsCount }} item(s)</p>
        </div>
        <span class="ov_pill" :style="{ color: statusColors[order.status] }">{{ order.status }}</span>
        <span class="ov_order_total">{{ money(order.total) }}</span>
      </Link>
    </div>
  </AccountShell>
</template>

<style scoped>
.ov_stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.ov_stat {
  padding: 22px;
  border-radius: 16px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.ov_stat_label {
  color: rgba(255, 255, 255, 0.5);
  font-size: 14px;
}
.ov_stat_value {
  color: #fff;
  font-size: 30px;
  font-weight: 700;
  margin-top: 6px;
}
.ov_panel {
  padding: 24px;
  border-radius: 18px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.ov_panel_head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}
.ov_panel_head h2 {
  color: #fff;
  font-size: 20px;
  font-weight: 700;
}
.ov_link {
  color: #2bff95;
}
.ov_empty {
  color: rgba(255, 255, 255, 0.55);
  padding: 18px 0;
}
.ov_order {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 16px;
  align-items: center;
  padding: 16px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
}
.ov_order_num {
  color: #fff;
  font-weight: 600;
}
.ov_order_meta {
  color: rgba(255, 255, 255, 0.45);
  font-size: 13px;
  margin-top: 2px;
}
.ov_pill {
  font-size: 13px;
  font-weight: 600;
  text-transform: capitalize;
}
.ov_order_total {
  color: #fff;
  font-weight: 700;
}
</style>

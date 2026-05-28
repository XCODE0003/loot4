<script setup>
import { Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import AccountShell from '@/loot4/components/account/AccountShell.vue'
import { useLocale } from '@/loot4/composables/useLocale'

defineProps({
  orders: { type: Array, default: () => [] },
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
  <Head title="My Orders — Loot4you" />
  <AccountShell :title="$t('account.myOrders')">
    <div class="ord_panel">
      <p v-if="!orders.length" class="ord_empty">
        {{ $t('account.noOrdersList') }} <Link href="/game" class="ord_link">{{ $t('account.browseCatalog') }}</Link>
      </p>

      <table v-else class="ord_table">
        <thead>
          <tr>
            <th>{{ $t('account.order') }}</th>
            <th>{{ $t('account.date') }}</th>
            <th>{{ $t('account.items') }}</th>
            <th>{{ $t('account.status') }}</th>
            <th>{{ $t('account.payment') }}</th>
            <th class="ord_right">{{ $t('account.total') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td class="ord_num">{{ order.number }}</td>
            <td>{{ order.date }}</td>
            <td>{{ order.itemsCount }}</td>
            <td><span class="ord_pill" :style="{ color: statusColors[order.status] }">{{ order.status }}</span></td>
            <td class="ord_payment">{{ order.paymentStatus }}</td>
            <td class="ord_right ord_total">{{ money(order.total) }}</td>
            <td class="ord_right"><Link :href="`/account/orders/${order.id}`" class="ord_link">{{ $t('account.view') }}</Link></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AccountShell>
</template>

<style scoped>
.ord_panel {
  padding: 24px;
  border-radius: 18px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.ord_empty {
  color: rgba(255, 255, 255, 0.55);
  padding: 12px 0;
}
.ord_link {
  color: #2bff95;
}
.ord_table {
  width: 100%;
  border-collapse: collapse;
}
.ord_table th {
  text-align: left;
  color: rgba(255, 255, 255, 0.45);
  font-weight: 500;
  font-size: 13px;
  padding: 0 0 14px;
}
.ord_table td {
  padding: 16px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  color: rgba(255, 255, 255, 0.8);
}
.ord_num {
  color: #fff;
  font-weight: 600;
}
.ord_payment {
  text-transform: capitalize;
}
.ord_pill {
  font-weight: 600;
  text-transform: capitalize;
}
.ord_total {
  color: #fff;
  font-weight: 700;
}
.ord_right {
  text-align: right;
}
</style>

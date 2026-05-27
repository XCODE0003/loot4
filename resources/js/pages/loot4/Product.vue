<script setup>
import { Head } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import ProductView from '@/loot4/views/ProductView.vue'

defineProps({
  product: { type: Object, default: null },
})
</script>

<template>
  <Head :title="product?.title ? `${product.title} — Loot4you` : 'Product — Loot4you'" />
  <ProductView :product="product" />
</template>

<script setup>
import { computed } from 'vue'
import ProductDetails from '@/loot4/components/product/ProductDetails.vue'
import FaqSection from '@/loot4/components/ui/FaqSection.vue'
import { product as fallbackProduct } from '@/loot4/data/product'

const props = defineProps({
  product: { type: Object, default: null },
})

const data = computed(() => props.product ?? fallbackProduct)
</script>

<template>
  <ProductDetails :data="data" />
  <FaqSection />
</template>

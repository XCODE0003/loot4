<script setup>
import ProductDetails from '@/components/product/ProductDetails.vue'
import FaqSection from '@/components/ui/FaqSection.vue'
import { product } from '@/data/product'
</script>

<template>
  <ProductDetails :data="product" />
  <FaqSection />
</template>

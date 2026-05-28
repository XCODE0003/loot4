<script setup>
import { router } from '@inertiajs/vue3'
import { asset } from '@/loot4/utils/asset'
import { useLocale } from '@/loot4/composables/useLocale'

const props = defineProps({
  id: { type: String, required: true },
  title: { type: String, required: true },
  image: { type: String, default: null },
  priceOld: { type: Number, default: null },
  priceNew: { type: Number, required: true },
  filterValues: { type: Array, default: () => [] },
  slug: { type: String, default: '' },
  category: { type: String, default: '' },
  hidden: { type: Boolean, default: false },
})

const { formatPrice } = useLocale()

function productUrl() {
  return props.slug ? `/product/${props.slug}` : '/product'
}

function goProduct() {
  router.visit(productUrl())
}

function buy(e) {
  e.stopPropagation()
  router.visit(productUrl())
}
</script>

<template>
  <div
    class="game_cards_block"
    :class="{ 'is-hidden': hidden }"
    @click="goProduct"
  >
    <img v-if="image" :src="asset(image)" :alt="title" class="game_cards_block_image" />
    <h4 class="game_cards_block_title">{{ title }}</h4>
    <div class="game_cards_block_bottom">
      <div class="game_cards_block_bottom_texts">
        <p v-if="priceOld !== null" class="game_cards_block_bottom_price_old">{{ formatPrice(priceOld) }}</p>
        <p class="game_cards_block_bottom_price_new">{{ formatPrice(priceNew) }}</p>
      </div>
      <button type="button" class="game_cards_block_bottom_button" @click="buy">Buy now</button>
    </div>
  </div>
</template>

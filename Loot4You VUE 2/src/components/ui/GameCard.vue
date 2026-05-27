<script setup>
import { useRouter } from 'vue-router'
import { asset } from '@/utils/asset'

const props = defineProps({
  id: { type: String, required: true },
  title: { type: String, required: true },
  image: { type: String, required: true },
  priceOld: { type: Number, required: true },
  priceNew: { type: Number, required: true },
  hidden: { type: Boolean, default: false },
})

const router = useRouter()

function formatPrice(value) {
  return `$${value.toFixed(2)}`
}

function goProduct(e) {
  if (e.target.closest('.game_cards_block_bottom_button')) return
  router.push({ name: 'product' })
}

function buy(e) {
  e.stopPropagation()
  router.push({ name: 'product' })
}
</script>

<template>
  <div
    class="game_cards_block"
    :class="{ 'is-hidden': hidden }"
    @click="goProduct"
  >
    <img :src="asset(image)" :alt="title" class="game_cards_block_image" />
    <h4 class="game_cards_block_title">{{ title }}</h4>
    <div class="game_cards_block_bottom">
      <div class="game_cards_block_bottom_texts">
        <p class="game_cards_block_bottom_price_old">{{ formatPrice(priceOld) }}</p>
        <p class="game_cards_block_bottom_price_new">{{ formatPrice(priceNew) }}</p>
      </div>
      <button type="button" class="game_cards_block_bottom_button" @click="buy">Buy now</button>
    </div>
  </div>
</template>

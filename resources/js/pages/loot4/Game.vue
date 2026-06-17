<script setup>
import { Head } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import GameView from '@/loot4/views/GameView.vue'

defineProps({
  products: { type: Array, default: null },
  gameFilters: { type: Object, default: null },
  gamePage: { type: Object, default: null },
  showSearch: { type: Boolean, default: true },
})
</script>

<template>
  <Head title="Choose Game — Loot4you" />
  <GameView :products="products" :game-filters="gameFilters" :game-page="gamePage" :show-search="showSearch" />
</template>

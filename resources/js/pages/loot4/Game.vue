<script setup>
import { Head } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import GameView from '@/loot4/views/GameView.vue'

defineProps({
  products: { type: Array, default: null },
  gameFilters: { type: Object, default: null },
  gamePage: { type: Object, default: null },
  showSearch: { type: Boolean, default: true },
  // Full document title from the server: the game name on /game/{slug},
  // "Choose Game — Loot4you" on the catalog landing.
  metaTitle: { type: String, default: 'Choose Game — Loot4you' },
})
</script>

<template>
  <Head :title="metaTitle" />
  <GameView :products="products" :game-filters="gameFilters" :game-page="gamePage" :show-search="showSearch" />
</template>

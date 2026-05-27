<script setup>
import { Head } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import GameView from '@/loot4/views/GameView.vue'

defineProps({
  products: { type: Array, default: null },
  filters: { type: Array, default: null },
  gamePage: { type: Object, default: null },
})
</script>

<template>
  <Head title="Choose Game — Loot4you" />
  <GameView :products="products" :filters="filters" :game-page="gamePage" />
</template>

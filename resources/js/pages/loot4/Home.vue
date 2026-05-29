<script setup>
import { Head } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import HomeView from '@/loot4/views/HomeView.vue'

defineProps({
  discoverGames: { type: Array, default: null },
})
</script>

<template>
  <Head title="Loot4You - Your Trusted Marketplace for Digital Gaming Products" />
  <HomeView :discover-games="discoverGames" />
</template>

<script setup>
import { computed } from 'vue'
import Container from '@/loot4/components/layout/Container.vue'
import GameIntroSection from '@/loot4/components/game/GameIntroSection.vue'
import GameCatalog from '@/loot4/components/ui/GameCatalog.vue'
import { games as fallbackGames } from '@/loot4/data/catalog'

const props = defineProps({
  products: { type: Array, default: null },
  gameFilters: { type: Object, default: null },
  gamePage: { type: Object, default: null },
})

const items = computed(() => props.products ?? fallbackGames)
</script>

<template>
  <GameIntroSection :game-page="gamePage" />
  <section class="game_cards">
    <Container>
      <GameCatalog :items="items" :game-filters="gameFilters" />
    </Container>
  </section>
</template>

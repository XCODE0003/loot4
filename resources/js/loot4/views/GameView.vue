<script setup>
import { computed } from 'vue'
import Container from '@/loot4/components/layout/Container.vue'
import GameIntroSection from '@/loot4/components/game/GameIntroSection.vue'
import GameCatalog from '@/loot4/components/ui/GameCatalog.vue'
import TrustpilotWidget from '@/loot4/components/ui/TrustpilotWidget.vue'
import { games as fallbackGames } from '@/loot4/data/catalog'

const props = defineProps({
  products: { type: Array, default: null },
  gameFilters: { type: Object, default: null },
  gamePage: { type: Object, default: null },
})

const items = computed(() => props.products ?? fallbackGames)
</script>

<style scoped>
.game_cards_trust {
  display: flex;
  justify-content: flex-start;
}
.game_cards--with-trust :deep(.game_cards_up) {
  margin-top: 24px;
}
</style>

<template>
  <GameIntroSection :game-page="gamePage" />
  <section class="game_cards game_cards--with-trust">
    <Container>
      <div class="game_cards_trust">
        <TrustpilotWidget />
      </div>
      <GameCatalog :items="items" :game-filters="gameFilters" />
    </Container>
  </section>
</template>

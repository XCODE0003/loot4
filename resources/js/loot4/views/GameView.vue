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
  showSearch: { type: Boolean, default: true },
})

const items = computed(() => props.products ?? fallbackGames)

// "Buy right now!" CTA in the intro smooth-scrolls to the start of the products.
function scrollToProducts() {
  if (typeof document === 'undefined') return
  document.getElementById('game-products')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
</script>

<style scoped>
.game_cards_trust {
  display: flex;
  justify-content: flex-start;
}
.game_cards--with-trust :deep(.game_cards_up) {
  margin-top: 24px;
}
/* On mobile the Trustpilot badge moves up next to the game title (rendered
   inside GameIntroSection), so the standalone one here is hidden — and the big
   gap above the filter/products is tightened. */
@media (max-width: 1100px) {
  .game_cards_trust {
    display: none;
  }
  .game_cards--with-trust :deep(.game_cards_up) {
    margin-top: 10px;
  }
}
</style>

<template>
  <GameIntroSection :game-page="gamePage" @scroll-to-products="scrollToProducts" />
  <section class="game_cards game_cards--with-trust">
    <Container>
      <div class="game_cards_trust">
        <TrustpilotWidget />
      </div>
      <GameCatalog :items="items" :game-filters="gameFilters" :show-search="showSearch" />
    </Container>
  </section>
</template>

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
  align-items: center;
  gap: 18px;
}
/* Desktop CTA sitting to the right of the Trustpilot badge → scroll to products. */
.game_cta_pc {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 14px 32px;
  border: 0;
  border-radius: 12px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
  font-family: var(--font-family);
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  transition: transform 0.15s ease, filter 0.15s ease;
}
.game_cta_pc:hover {
  transform: translateY(-1px);
  filter: brightness(1.05);
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
        <button type="button" class="game_cta_pc" @click="scrollToProducts">Buy right now!</button>
      </div>
      <GameCatalog :items="items" :game-filters="gameFilters" :show-search="showSearch" />
    </Container>
  </section>
</template>

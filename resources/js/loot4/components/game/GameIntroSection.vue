<script setup>
import { computed } from 'vue'
import Container from '@/loot4/components/layout/Container.vue'
import TrustpilotWidget from '@/loot4/components/ui/TrustpilotWidget.vue'
import { gamePage as fallbackGamePage } from '@/loot4/data/catalog'
import { asset } from '@/loot4/utils/asset'

const props = defineProps({
  gamePage: { type: Object, default: null },
})

const page = computed(() => props.gamePage ?? fallbackGamePage)
</script>

<template>
  <section class="game_intro">
    <Container>
      <div class="game_intro_blocks">
        <div class="game_intro_block">
          <div class="game_intro_head">
            <h1 class="game_intro_block_title">{{ page.title }}</h1>
            <!-- Beside the title on mobile only; desktop keeps the standalone badge in GameView. -->
            <div class="game_intro_trust"><TrustpilotWidget /></div>
          </div>
          <div v-if="page.guarantees && page.guarantees.length" class="game_intro_block_texts">
            <ul class="game_intro_block_texts_items">
              <li v-for="(text, i) in page.guarantees" :key="i" class="game_intro_block_texts_item">{{ text }}</li>
            </ul>
          </div>
        </div>
        <div class="game_intro_block">
          <img v-if="page.image" :src="asset(page.image)" alt="" class="game_intro_block_image" />
        </div>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.game_intro_head {
  display: flex;
  align-items: center;
  gap: 16px;
}
/* The Trustpilot badge only sits beside the title on mobile. */
.game_intro_trust {
  display: none;
}

@media (max-width: 1100px) {
  /* Tighten the whole intro so the catalog rises up the page. */
  .game_intro {
    margin-top: 16px;
  }
  .game_intro_head {
    justify-content: space-between;
    gap: 12px;
  }
  .game_intro_block_title {
    margin: 0;
    font-size: 22px;
    line-height: 1.2;
    text-align: left;
  }
  .game_intro_trust {
    display: block;
    flex-shrink: 0;
  }
  /* Compact, left-aligned description. */
  .game_intro_block_texts {
    margin: 10px auto 0 0;
    max-width: 100%;
  }
  .game_intro_block_texts_item {
    font-size: 13px;
    line-height: 1.4;
    margin-left: 18px;
  }
  /* Keep the badge compact next to the title (override its full-width mobile rule). */
  .game_intro_trust :deep(.catalog-hero__trustpilot-mobile) {
    margin-top: 0;
  }
  .game_intro_trust :deep(.catalog-hero__trustpilot-mobile .catalog-hero__trustpilot) {
    width: auto;
    padding: 9px 12px;
  }
}

@media (max-width: 480px) {
  .game_intro_block_title {
    font-size: 18px;
  }
  .game_intro_trust :deep(.catalog-hero__trustpilot-mobile .catalog-hero__trustpilot) {
    padding: 7px 9px;
  }
  .game_intro_trust :deep(.catalog-hero__trustpilot-left) {
    font-size: 11px;
  }
  .game_intro_trust :deep(.catalog-hero__trustpilot-brand) {
    font-size: 12px;
  }
  .game_intro_trust :deep(.catalog-hero__trustpilot-stars svg) {
    width: 78px;
    height: auto;
  }
}
</style>

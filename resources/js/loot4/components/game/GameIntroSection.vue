<script setup>
import { computed } from 'vue'
import Container from '@/loot4/components/layout/Container.vue'
import TrustpilotWidget from '@/loot4/components/ui/TrustpilotWidget.vue'
import { gamePage as fallbackGamePage } from '@/loot4/data/catalog'
import { asset } from '@/loot4/utils/asset'

const props = defineProps({
  gamePage: { type: Object, default: null },
})

defineEmits(['scrollToProducts'])

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
          <!-- Mobile CTA that fills the freed space and jumps to the products. -->
          <button type="button" class="game_intro_cta" @click="$emit('scrollToProducts')">Buy right now!</button>
        </div>
        <div class="game_intro_block">
          <img
            v-if="page.image"
            :src="asset(page.image)"
            alt=""
            class="game_intro_block_image"
            loading="lazy"
            decoding="async"
            @click="$emit('scrollToProducts')"
          />
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

/* The "Buy right now!" CTA only shows on mobile (where the banner is hidden). */
.game_intro_cta {
  display: none;
}

/* The banner is a big CTA on desktop — click it to jump to the products. */
.game_intro_block_image {
  cursor: pointer;
  transition: filter 0.15s ease, transform 0.15s ease;
}
.game_intro_block_image:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
}

@media (max-width: 1100px) {
  /* Tighten the whole intro so the catalog rises up the page. */
  .game_intro {
    margin-top: 16px;
  }
  /* Drop the big intro banner on mobile — it only added dead space above the
     filter/products (the cards already carry their own banners). */
  .game_intro_blocks {
    gap: 0;
  }
  .game_intro_block_image {
    display: none;
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
  .game_intro_cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    margin-top: 14px;
    padding: 13px 22px;
    border: 0;
    border-radius: 12px;
    background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
    box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
    color: #fff;
    font-family: var(--font-family);
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: filter 0.15s ease;
  }
  .game_intro_cta:active {
    filter: brightness(1.08);
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

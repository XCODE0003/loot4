<script setup>
import { computed } from 'vue'
import Container from '@/loot4/components/layout/Container.vue'
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
          <img :src="asset('intro_trust.png')" alt="" class="game_intro_block_trust" />
          <h1 class="game_intro_block_title">{{ page.title }}</h1>
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

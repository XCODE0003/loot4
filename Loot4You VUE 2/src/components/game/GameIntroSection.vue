<script setup>
import Container from '@/components/layout/Container.vue'
import { gamePage } from '@/data/catalog'
import { asset } from '@/utils/asset'

function scrollToFooter() {
  document.getElementById('footer')?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<template>
  <section class="game_intro">
    <Container>
      <div class="game_intro_blocks">
        <div class="game_intro_block">
          <img :src="asset('intro_trust.png')" alt="" class="game_intro_block_trust" />
          <h1 class="game_intro_block_title">{{ gamePage.title }}</h1>
          <div class="game_intro_block_texts">
            <p class="game_intro_block_texts_title">Our Guarantees</p>
            <ul class="game_intro_block_texts_items">
              <li v-for="(text, i) in gamePage.guarantees" :key="i" class="game_intro_block_texts_item">{{ text }}</li>
            </ul>
          </div>
          
        </div>
        <div class="game_intro_block">
          <img :src="asset(gamePage.image)" alt="" class="game_intro_block_image" />
        </div>
      </div>
    </Container>
  </section>
</template>

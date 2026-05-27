<script setup>
import Container from '@/components/layout/Container.vue'
import SectionTag from '@/components/ui/SectionTag.vue'
import { discoverGames } from '@/data/home'
import { asset } from '@/utils/asset'
</script>

<template>
  <section class="discover">
    <Container>
      <SectionTag>Elite Gaming</SectionTag>
      <div class="discover_up">
        <div class="discover_up_texts">
          <h4 class="discover_up_title">Discover Epic Games</h4>
          <p class="discover_up_text">
            Explore our elite collection of accounts, currency, and boosting services.
          </p>
        </div>
        <RouterLink to="/game" class="discover_up_more">
          Explore more
          <svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M1 6.36395C0.447715 6.36395 0 6.81167 0 7.36395C0 7.91624 0.447715 8.36395 1 8.36395V7.36395V6.36395ZM19.7071 8.07106C20.0976 7.68054 20.0976 7.04737 19.7071 6.65685L13.3431 0.292885C12.9526 -0.0976396 12.3195 -0.0976396 11.9289 0.292885C11.5384 0.683409 11.5384 1.31657 11.9289 1.7071L17.5858 7.36395L11.9289 13.0208C11.5384 13.4113 11.5384 14.0445 11.9289 14.435C12.3195 14.8255 12.9526 14.8255 13.3431 14.435L19.7071 8.07106ZM1 7.36395V8.36395H19V7.36395V6.36395H1V7.36395Z"
              fill="white"
            />
          </svg>
        </RouterLink>
      </div>
      <div class="discover_blocks">
        <RouterLink v-for="(game, i) in discoverGames" :key="i" to="/game" class="discover_block_link">
          <img :src="asset(game.image)" :alt="game.alt" class="discover_block_link_image" />
        </RouterLink>
      </div>
    </Container>
  </section>
</template>

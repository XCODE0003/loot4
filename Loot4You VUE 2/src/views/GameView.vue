<script setup>
import Container from '@/components/layout/Container.vue'
import GameIntroSection from '@/components/game/GameIntroSection.vue'
import GameCatalog from '@/components/ui/GameCatalog.vue'
import { games } from '@/data/catalog'
</script>

<template>
  <GameIntroSection />
  <section class="game_cards">
    <Container>
      <GameCatalog :items="games" show-logo />
    </Container>
  </section>
</template>

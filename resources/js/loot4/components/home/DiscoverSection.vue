<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import Container from '@/loot4/components/layout/Container.vue'
import SectionTag from '@/loot4/components/ui/SectionTag.vue'
import { discoverGames as fallbackGames } from '@/loot4/data/home'
import { asset } from '@/loot4/utils/asset'
import { useGameMenu } from '@/loot4/composables/useGameMenu'

const props = defineProps({
  games: { type: Array, default: null },
})

const games = computed(() => (props.games ?? fallbackGames).slice(0, 4))

const { openGameMenuFromAnywhere } = useGameMenu()
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
        <div class="discover_controls">
          <button type="button" class="discover_up_more" @click="openGameMenuFromAnywhere">
            View All Games
            <svg width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M1 6.364C.448 6.364 0 6.812 0 7.364s.448 1 1 1V6.364zm18.707.707a1 1 0 000-1.414L13.343.293a1 1 0 10-1.414 1.414L17.586 7.364l-5.657 5.657a1 1 0 101.414 1.414l6.364-6.364zM1 7.364v1h18v-2H1v1z" fill="white"/></svg>
          </button>
        </div>
      </div>

      <div class="discover_grid">
        <component
          :is="g.slug ? Link : 'div'"
          v-for="(g, i) in games"
          :key="i"
          :href="g.slug ? `/game/${g.slug}` : undefined"
          class="discover_card"
        >
          <img v-if="g.image" :src="asset(g.image)" :alt="g.alt" class="discover_card_img" />
          <span class="discover_card_label">{{ g.alt }}</span>
        </component>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.discover_up {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 32px;
  flex-wrap: wrap;
}
.discover_up_texts { flex: 1; }
.discover_up_title {
  color: #fff;
  font-size: 36px;
  font-weight: 700;
  line-height: 1.2;
}
.discover_up_text {
  color: rgba(255, 255, 255, 0.5);
  margin-top: 8px;
  max-width: 480px;
}
.discover_controls {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}
.discover_up_more {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #fff;
  font-weight: 500;
  white-space: nowrap;
  padding: 10px 20px;
  border-radius: 93px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: transparent;
  cursor: pointer;
  transition: background 0.2s;
}
.discover_up_more:hover { background: rgba(255, 255, 255, 0.06); }

.discover_grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.discover_card {
  height: 420px;
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  display: block;
  transition: transform 0.3s, box-shadow 0.3s;
}
.discover_card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5);
}
.discover_card_img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.discover_card_label {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 40px 20px 20px;
  background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 100%);
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.02em;
}

@media (max-width: 1100px) {
  .discover_grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 760px) {
  .discover_card { height: 340px; }
  .discover_up_title { font-size: 26px; }
}
@media (max-width: 560px) {
  .discover_grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  .discover_card { height: 230px; }
  .discover_card_label {
    font-size: 14px;
    padding: 28px 14px 14px;
  }
}
</style>

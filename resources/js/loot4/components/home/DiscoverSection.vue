<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import Container from '@/loot4/components/layout/Container.vue'
import SectionTag from '@/loot4/components/ui/SectionTag.vue'
import { discoverGames as fallbackGames } from '@/loot4/data/home'
import { asset } from '@/loot4/utils/asset'

const props = defineProps({
  games: { type: Array, default: null },
})

const games = computed(() => props.games ?? fallbackGames)

const VISIBLE = 4
const GAP = 20

const viewportRef = ref(null)
const cardWidth = ref(300)

function measureCard() {
  if (!viewportRef.value) return
  const w = viewportRef.value.offsetWidth
  cardWidth.value = Math.floor((w - GAP * (VISIBLE - 1)) / VISIBLE)
}

let ro = null
onMounted(() => {
  measureCard()
  ro = new ResizeObserver(measureCard)
  ro.observe(viewportRef.value)
  timer = setInterval(next, 3500)
})
onBeforeUnmount(() => {
  ro?.disconnect()
  clearInterval(timer)
})

const current = ref(0)
const total = computed(() => games.value.length)

function prev() { current.value = (current.value - 1 + total.value) % total.value }
function next() { current.value = (current.value + 1) % total.value }
function goTo(i) { current.value = i }

let timer = null
function pause() { clearInterval(timer) }
function resume() { timer = setInterval(next, 3500) }

const offset = computed(() => {
  const max = Math.max(0, total.value - VISIBLE)
  return Math.min(current.value, max)
})

const trackStyle = computed(() => ({
  transform: `translateX(-${offset.value * (cardWidth.value + GAP)}px)`,
}))

const cardStyle = computed(() => ({
  width: `${cardWidth.value}px`,
}))
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
          <button type="button" class="discover_arrow" aria-label="Previous" @click="prev">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <button type="button" class="discover_arrow" aria-label="Next" @click="next">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <Link href="/game" class="discover_up_more">
            Explore more
            <svg width="20" height="15" viewBox="0 0 20 15" fill="none"><path d="M1 6.364C.448 6.364 0 6.812 0 7.364s.448 1 1 1V6.364zm18.707.707a1 1 0 000-1.414L13.343.293a1 1 0 10-1.414 1.414L17.586 7.364l-5.657 5.657a1 1 0 101.414 1.414l6.364-6.364zM1 7.364v1h18v-2H1v1z" fill="white"/></svg>
          </Link>
        </div>
      </div>

      <div ref="viewportRef" class="discover_viewport" @mouseenter="pause" @mouseleave="resume">
        <div class="discover_track" :style="trackStyle">
          <component
            :is="g.slug ? Link : 'div'"
            v-for="(g, i) in games"
            :key="i"
            :href="g.slug ? `/game/${g.slug}` : undefined"
            class="discover_card"
            :class="{ 'is-active': i === current }"
            :style="cardStyle"
          >
            <img :src="asset(g.image)" :alt="g.alt" class="discover_card_img" />
            <span class="discover_card_label">{{ g.alt }}</span>
          </component>
        </div>
      </div>

      <div class="discover_dots">
        <button
          v-for="(_, i) in games"
          :key="i"
          type="button"
          class="discover_dot"
          :class="{ 'is-active': i === current }"
          :aria-label="`Go to slide ${i + 1}`"
          @click="goTo(i)"
        />
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
.discover_arrow {
  display: grid;
  place-items: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  cursor: pointer;
  transition: background 0.2s;
}
.discover_arrow:hover { background: rgba(255, 255, 255, 0.16); }
.discover_up_more {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #fff;
  font-weight: 500;
  white-space: nowrap;
  padding: 10px 20px;
  border-radius: 93px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: background 0.2s;
}
.discover_up_more:hover { background: rgba(255, 255, 255, 0.06); }

.discover_viewport { overflow: hidden; }
.discover_track {
  display: flex;
  gap: 20px;
  transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: transform;
}
.discover_card {
  flex-shrink: 0;
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
.discover_card.is-active {
  box-shadow: 0 0 0 2px #2bff95, 0 16px 40px rgba(43, 255, 149, 0.15);
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

.discover_dots {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
}
.discover_dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.25);
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
}
.discover_dot.is-active {
  background: #2bff95;
  transform: scale(1.3);
}

@media (max-width: 760px) {
  .discover_card { height: 340px; }
  .discover_up_title { font-size: 26px; }
}
</style>

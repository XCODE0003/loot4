<script setup>
import { computed, ref } from 'vue'
import GameCard from '@/loot4/components/ui/GameCard.vue'
import { asset } from '@/loot4/utils/asset'

const props = defineProps({
  items: { type: Array, required: true },
  gameFilters: { type: Object, default: null },
  showLogo: { type: Boolean, default: false },
  gridClass: { type: String, default: '' },
})

// Game-specific configurable filter (product.filterValues), set up per game in admin.
const activeGameFilter = ref('all')

const searchQuery = ref('')

const filtered = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  return props.items.filter((item) => {
    const matchGameFilter =
      activeGameFilter.value === 'all' ||
      (Array.isArray(item.filterValues) && item.filterValues.includes(activeGameFilter.value))
    const matchSearch = !q || item.title.toLowerCase().includes(q)
    return matchGameFilter && matchSearch
  })
})

const isEmpty = computed(() => filtered.value.length === 0)

function setGameFilter(value) {
  activeGameFilter.value = value
}
</script>

<template>
  <div class="game_cards_up">
    <div v-if="gameFilters" class="catalog_filter">
      <span class="catalog_filter_label">{{ gameFilters.label }}</span>
      <div class="catalog_filter_options">
        <button
          type="button"
          class="catalog_filter_opt"
          :class="{ 'is-active': activeGameFilter === 'all' }"
          @click="setGameFilter('all')"
        >
          <span class="catalog_filter_opt_text">All</span>
        </button>
        <button
          v-for="val in gameFilters.values"
          :key="val"
          type="button"
          class="catalog_filter_opt"
          :class="{ 'is-active': activeGameFilter === val }"
          @click="setGameFilter(val)"
        >
          <span class="catalog_filter_opt_text">{{ val }}</span>
        </button>
      </div>
    </div>
    <div class="game_cards_up_item">
      <input v-model="searchQuery" type="text" class="game_cards_up_item_search" placeholder="Search" />
    </div>
  </div>
  <div class="game_cards_blocks" :class="[gridClass, { 'is-empty': isEmpty }]">
    <GameCard
      v-for="item in items"
      :key="item.id"
      v-bind="item"
      :hidden="!filtered.some((f) => f.id === item.id)"
    />
  </div>
  <img v-if="showLogo" :src="asset('game_logo.png')" alt="" class="game_cards_logo" />
</template>

<style scoped>
/* Segmented filter control (admin-configured per-game filter) */
.catalog_filter {
  display: flex;
  align-items: stretch;
  flex: 1;
  min-width: 0;
  margin-right: 16px;
  padding: 6px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  background: #0a0a12;
}
.catalog_filter_label {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  padding: 0 20px;
  font-family: var(--font-family);
  font-size: 16px;
  color: rgba(255, 255, 255, 0.5);
  white-space: nowrap;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
}
.catalog_filter_options {
  display: flex;
  align-items: stretch;
  flex: 1;
  min-width: 0;
  overflow-x: auto;
  scrollbar-width: none;
}
.catalog_filter_options::-webkit-scrollbar {
  display: none;
}
.catalog_filter_opt {
  position: relative;
  flex: 1 0 auto;
  overflow: hidden;
  padding: 14px 28px;
  border: 0;
  border-radius: 11px;
  background: transparent;
  font-family: var(--font-family);
  font-weight: 500;
  font-size: 16px;
  color: rgba(255, 255, 255, 0.62);
  white-space: nowrap;
  cursor: pointer;
  transition: color 0.25s;
}
/* divider between options */
.catalog_filter_opt + .catalog_filter_opt::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 1px;
  height: 18px;
  background: rgba(255, 255, 255, 0.1);
  z-index: 1;
}
/* the selected pill — sits below the cell and slides up when active */
.catalog_filter_opt::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 11px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 6px 18px rgba(43, 255, 149, 0.28);
  transform: translateY(110%);
  opacity: 0;
  z-index: 0;
  /* leaving: drops straight back down with no delay */
  transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.22s ease;
}
.catalog_filter_opt_text {
  position: relative;
  z-index: 2;
}
.catalog_filter_opt:hover {
  color: #fff;
}
.catalog_filter_opt.is-active {
  color: #fff;
  font-weight: 600;
}
.catalog_filter_opt.is-active::after {
  transform: translateY(0);
  opacity: 1;
  /* entering: waits for the previous pill to drop, then rises up */
  transition: transform 0.36s cubic-bezier(0.34, 1.3, 0.64, 1) 0.12s, opacity 0.2s ease 0.12s;
}
/* hide dividers touching the active pill */
.catalog_filter_opt.is-active::before,
.catalog_filter_opt.is-active + .catalog_filter_opt::before {
  display: none;
}

@media (max-width: 1100px) {
  .catalog_filter {
    width: 100%;
    margin-right: 0;
  }
}
@media (max-width: 480px) {
  .catalog_filter_label {
    padding: 0 14px;
    font-size: 14px;
  }
  .catalog_filter_opt {
    padding: 12px 18px;
    font-size: 14px;
  }
}
</style>

<script setup>
import { computed, ref } from 'vue'
import { categoryFilters } from '@/loot4/data/navigation'
import GameCard from '@/loot4/components/ui/GameCard.vue'
import { asset } from '@/loot4/utils/asset'

const props = defineProps({
  items: { type: Array, required: true },
  filters: { type: Array, default: null },
  gameFilters: { type: Object, default: null },
  showLogo: { type: Boolean, default: false },
  gridClass: { type: String, default: '' },
})

// Type-based category filters (product.category)
const categoryFiltersList = computed(() => props.filters ?? categoryFilters)
const activeCategory = ref('all')

// Game-specific configurable filter (product.filterValues)
const activeGameFilter = ref('all')

const searchQuery = ref('')

const filtered = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  return props.items.filter((item) => {
    const matchCat = activeCategory.value === 'all' || item.category === activeCategory.value
    const matchGameFilter =
      activeGameFilter.value === 'all' ||
      (Array.isArray(item.filterValues) && item.filterValues.includes(activeGameFilter.value))
    const matchSearch = !q || item.title.toLowerCase().includes(q)
    return matchCat && matchGameFilter && matchSearch
  })
})

const isEmpty = computed(() => filtered.value.length === 0)

function setCategory(value) {
  activeCategory.value = value
}

function setGameFilter(value) {
  activeGameFilter.value = value
}
</script>

<template>
  <div class="game_cards_up">
    <div class="game_cards_up_item">
      <p class="game_cards_up_item_title">Platform</p>
      <div class="game_cards_up_item_buttons">
        <button
          v-for="filter in categoryFiltersList"
          :key="filter.value"
          type="button"
          class="game_cards_up_item_button"
          :class="{ game_cards_up_item_button_active: activeCategory === filter.value }"
          @click="setCategory(filter.value)"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>
    <div v-if="gameFilters" class="game_cards_up_item">
      <p class="game_cards_up_item_title">{{ gameFilters.label }}</p>
      <div class="game_cards_up_item_buttons">
        <button
          type="button"
          class="game_cards_up_item_button"
          :class="{ game_cards_up_item_button_active: activeGameFilter === 'all' }"
          @click="setGameFilter('all')"
        >
          All
        </button>
        <button
          v-for="val in gameFilters.values"
          :key="val"
          type="button"
          class="game_cards_up_item_button"
          :class="{ game_cards_up_item_button_active: activeGameFilter === val }"
          @click="setGameFilter(val)"
        >
          {{ val }}
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

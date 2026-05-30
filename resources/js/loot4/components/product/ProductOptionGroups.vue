<script setup>
import { computed, reactive, watch } from 'vue'
import { useLocale } from '@/loot4/composables/useLocale'

const props = defineProps({
  // [{ key, label, type: 'single'|'multi', pricingMode: 'absolute'|'addon', required, options: [{ value, label, price, tooltip, popular }] }]
  groups: { type: Array, default: () => [] },
  // Base price used when there is no price-selector (absolute) group.
  productPrice: { type: Number, default: 0 },
})

const emit = defineEmits(['change'])
const { formatPrice } = useLocale()

// selections: { [groupKey]: string (single) | string[] (multi) }
const selections = reactive({})

function cheapestValue(group) {
  let best = null
  for (const opt of group.options) {
    if (best === null || opt.price < best.price) best = opt
  }
  return best?.value ?? null
}

// Defaults: preselect the cheapest option of price-selector groups (so the shown
// price matches the "from" price) and the first option of any other required
// single-select group. Multi-select add-ons start empty.
function initSelections() {
  for (const group of props.groups) {
    if (group.type === 'multi') {
      selections[group.key] = []
    } else if (group.pricingMode === 'absolute') {
      selections[group.key] = cheapestValue(group)
    } else if (group.required) {
      selections[group.key] = group.options[0]?.value ?? null
    } else {
      selections[group.key] = ''
    }
  }
}

function resetSelections() {
  for (const key of Object.keys(selections)) delete selections[key]
  initSelections()
}

resetSelections()
watch(() => props.groups, resetSelections)

function optionByValue(group, value) {
  return group.options.find((o) => o.value === value) ?? null
}

function selectedValues(group) {
  const current = selections[group.key]
  if (group.type === 'multi') return current ?? []
  return current ? [current] : []
}

function isSelected(group, value) {
  return selectedValues(group).includes(value)
}

function select(group, value) {
  if (group.type === 'multi') {
    const current = selections[group.key] ?? []
    selections[group.key] = current.includes(value)
      ? current.filter((v) => v !== value)
      : [...current, value]
  } else {
    selections[group.key] = value
  }
}

function priceLabel(group, opt) {
  if (group.pricingMode === 'absolute') return formatPrice(opt.price)
  if (opt.price > 0) return `+${formatPrice(opt.price)}`
  return ''
}

// Mirrors the server-side ProductPricing formula exactly so the displayed price
// always matches the recomputed checkout price.
const price = computed(() => {
  let base = props.productPrice
  let hasAbsolute = false
  let addons = 0

  for (const group of props.groups) {
    if (group.pricingMode === 'absolute') {
      const opt = optionByValue(group, selections[group.key])
      if (opt) {
        if (!hasAbsolute) {
          base = 0
          hasAbsolute = true
        }
        base += opt.price
      }
      continue
    }
    for (const v of selectedValues(group)) {
      const opt = optionByValue(group, v)
      if (opt) addons += opt.price
    }
  }

  return Math.round((base + addons) * 100) / 100
})

const summary = computed(() => {
  const labels = []
  for (const group of props.groups) {
    for (const v of selectedValues(group)) {
      const opt = optionByValue(group, v)
      if (opt) labels.push(opt.label)
    }
  }
  return labels.join(' · ')
})

function plainSelections() {
  const out = {}
  for (const group of props.groups) {
    const v = selections[group.key]
    if (group.type === 'multi') {
      if (v && v.length) out[group.key] = [...v]
    } else if (v) {
      out[group.key] = v
    }
  }
  return out
}

watch(
  [price, summary, () => JSON.stringify(selections)],
  () => emit('change', { selections: plainSelections(), price: price.value, summary: summary.value }),
  { immediate: true },
)
</script>

<template>
  <div v-if="groups.length" class="pog">
    <div v-for="group in groups" :key="group.key" class="pog_group">
      <p class="pog_group_title">{{ group.label }}</p>
      <div class="pog_list">
        <button
          v-for="opt in group.options"
          :key="opt.value"
          type="button"
          class="pog_row"
          :class="{ 'is-active': isSelected(group, opt.value) }"
          @click="select(group, opt.value)"
        >
          <span class="pog_mark" :class="{ 'pog_mark--radio': group.type === 'single' }">
            <svg
              v-if="isSelected(group, opt.value)"
              class="pog_mark_icon"
              width="11"
              height="9"
              viewBox="0 0 10 8"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M9.298 1.673 3.798 7.173a.57.57 0 0 1-.398.165.57.57 0 0 1-.399-.165L.376 4.548a.563.563 0 0 1 0-.797.563.563 0 0 1 .797 0L3.4 5.978 8.502.877a.564.564 0 0 1 .797.797z" fill="currentColor" />
            </svg>
          </span>
          <span class="pog_name">
            {{ opt.label }}
            <span v-if="opt.popular" class="pog_tag">Popular</span>
          </span>
          <span v-if="priceLabel(group, opt)" class="pog_price">{{ priceLabel(group, opt) }}</span>
          <span v-if="opt.tooltip" class="pog_info" @click.stop>
            <svg width="16" height="16" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="9" cy="9" r="8" fill="currentColor" opacity="0.18" />
              <path d="M9 7.6v4M9 5.3h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <span class="pog_tooltip">{{ opt.tooltip }}</span>
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pog {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin: 8px 0 4px;
}
.pog_group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.pog_group_title {
  margin: 0;
  font-family: var(--font-family);
  font-size: 16px;
  font-weight: 600;
  color: #fff;
}
.pog_list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pog_row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 13px 16px;
  text-align: left;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  color: #fff;
  transition: border-color 0.15s ease, background 0.15s ease;
}
.pog_row:hover {
  border-color: rgba(43, 255, 149, 0.4);
}
.pog_row.is-active {
  border-color: #2bff95;
  background: rgba(43, 255, 149, 0.08);
}
.pog_mark {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  border-radius: 6px;
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  display: grid;
  place-items: center;
  color: #0b0b0f;
  transition: background 0.15s ease, border-color 0.15s ease;
}
.pog_mark--radio {
  border-radius: 50%;
}
.pog_row.is-active .pog_mark {
  background: #2bff95;
  border-color: #2bff95;
}
.pog_mark_icon {
  display: block;
}
.pog_name {
  flex: 1;
  min-width: 0;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.pog_tag {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #2bff95;
  border: 1px solid rgba(43, 255, 149, 0.5);
  border-radius: 999px;
  padding: 2px 8px;
}
.pog_price {
  flex-shrink: 0;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 600;
  color: #fff;
}
.pog_info {
  position: relative;
  flex-shrink: 0;
  display: inline-flex;
  color: rgba(255, 255, 255, 0.55);
  cursor: help;
}
.pog_info:hover {
  color: #2bff95;
}
.pog_tooltip {
  position: absolute;
  bottom: calc(100% + 8px);
  right: 0;
  z-index: 5;
  width: max-content;
  max-width: 260px;
  padding: 10px 12px;
  background: #16161d;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.85);
  font-family: var(--font-family);
  font-size: 12px;
  line-height: 1.45;
  white-space: normal;
  opacity: 0;
  visibility: hidden;
  transform: translateY(4px);
  transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
  pointer-events: none;
}
.pog_info:hover .pog_tooltip {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}
@media (max-width: 768px) {
  .pog_tooltip {
    max-width: 200px;
  }
}
</style>

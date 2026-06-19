<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useLocale } from '@/loot4/composables/useLocale'

const props = defineProps({
  // [{ key, label, type: 'single'|'multi', control: 'select'|'radio'|'checkbox',
  //    pricingMode: 'absolute'|'addon', required, options: [{ value, label, price, tooltip, popular, default }] }]
  groups: { type: Array, default: () => [] },
  // Base price used when there is no price-selector (absolute) group selected.
  productPrice: { type: Number, default: 0 },
  // 'single' = all groups on one page, 'steps' = one group per step.
  layout: { type: String, default: 'single' },
  // When true, highlight required groups that have no selection (set after a
  // blocked "Buy now").
  showErrors: { type: Boolean, default: false },
})

const emit = defineEmits(['change'])
const { formatPrice } = useLocale()

// Custom dropdown (replaces the native <select> so there's no ugly iOS/Android
// picker — a styled popover list opens instead). Only one open at a time.
const openSelect = ref(null)
function toggleSelect(group) {
  openSelect.value = openSelect.value === group.key ? null : group.key
}
function chooseOption(group, value) {
  select(group, value)
  openSelect.value = null
}
function selectedOptionText(group) {
  const v = selections[group.key]
  const opt = v ? optionByValue(group, v) : null
  return opt ? optionText(group, opt) : 'Choose…'
}
function onDocClick(e) {
  if (!e.target?.closest?.('.pog_dd')) openSelect.value = null
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))

// Group the flat list into blocks (one block per admin "form"). In step-by-step
// layout each block becomes a single step holding all of its fields.
const blocks = computed(() => {
  const byBlock = new Map()
  for (const group of props.groups) {
    const key = group.block ?? 0
    if (!byBlock.has(key)) byBlock.set(key, [])
    byBlock.get(key).push(group)
  }
  return [...byBlock.values()]
})

const isSteps = computed(() => props.layout === 'steps' && blocks.value.length > 1)

// Free-form fields the customer types into (no predefined options).
const INPUT_CONTROLS = ['text', 'number', 'textarea']
function isInput(group) {
  return INPUT_CONTROLS.includes(group.control)
}
function inputValue(group) {
  return String(selections[group.key] ?? '').trim()
}

// selections: { [groupKey]: string (single) | string[] (multi) }
const selections = reactive({})
const step = ref(0)
const stepAttempted = ref(false)

// Defaults come only from options explicitly marked "default" — nothing is
// preselected otherwise.
function initSelections() {
  for (const group of props.groups) {
    if (group.type === 'multi') {
      selections[group.key] = group.options.filter((o) => o.default).map((o) => o.value)
    } else {
      const def = group.options.find((o) => o.default)
      selections[group.key] = def ? def.value : ''
    }
  }
}

function resetSelections() {
  for (const key of Object.keys(selections)) delete selections[key]
  initSelections()
  step.value = 0
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

function isGroupSatisfied(group) {
  if (isInput(group)) return !group.required || inputValue(group) !== ''
  return !group.required || selectedValues(group).length > 0
}

function isBlockSatisfied(block) {
  return block.every(isGroupSatisfied)
}

// A required group with no selection shows an error once the customer tried to
// advance (steps) or buy (single page). `bi` is the group's block index.
function groupHasError(group, bi) {
  if (isGroupSatisfied(group)) return false
  if (props.showErrors) return true
  return isSteps.value && bi === step.value && stepAttempted.value
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

function optionText(group, opt) {
  const label = priceLabel(group, opt)
  return label ? `${opt.label} — ${label}` : opt.label
}

// Mirrors the server-side ProductPricing formula so the displayed price always
// matches the recomputed checkout price.
const price = computed(() => {
  let base = props.productPrice
  let hasAbsolute = false
  let addons = 0

  for (const group of props.groups) {
    if (isInput(group)) {
      // Input fields add their base extra price once filled.
      if (inputValue(group) !== '') addons += group.price || 0
      continue
    }
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
    if (isInput(group)) {
      const v = inputValue(group)
      if (v) labels.push(`${group.label}: ${v}`)
      continue
    }
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

const valid = computed(() => props.groups.every(isGroupSatisfied))
// In steps mode the buy button only shows on the final step (last block).
const isLastStep = computed(() => !isSteps.value || step.value >= blocks.value.length - 1)

watch(
  [price, summary, () => JSON.stringify(selections), () => step.value],
  () => emit('change', {
    selections: plainSelections(),
    price: price.value,
    summary: summary.value,
    valid: valid.value,
    isLastStep: isLastStep.value,
  }),
  { immediate: true },
)

// When a buy is blocked, jump the wizard to the first unsatisfied block.
watch(
  () => props.showErrors,
  (on) => {
    if (!on || !isSteps.value) return
    const idx = blocks.value.findIndex((b) => !isBlockSatisfied(b))
    if (idx >= 0) step.value = idx
  },
)

function next() {
  const block = blocks.value[step.value]
  if (block && !isBlockSatisfied(block)) {
    stepAttempted.value = true
    return
  }
  if (step.value < blocks.value.length - 1) {
    step.value += 1
    stepAttempted.value = false
  }
}
function back() {
  if (step.value > 0) {
    step.value -= 1
    stepAttempted.value = false
  }
}
</script>

<template>
  <div v-if="groups.length" class="pog" :class="{ 'pog--steps': isSteps }">
    <div v-if="isSteps" class="pog_steps" aria-hidden="true">
      <template v-for="(b, i) in blocks" :key="`step-${i}`">
        <div class="pog_step" :class="{ 'is-active': i === step, 'is-done': i < step }">
          <svg v-if="i < step" width="12" height="10" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.298 1.673 3.798 7.173a.57.57 0 0 1-.398.165.57.57 0 0 1-.399-.165L.376 4.548a.563.563 0 0 1 0-.797.563.563 0 0 1 .797 0L3.4 5.978 8.502.877a.564.564 0 0 1 .797.797z" fill="currentColor" />
          </svg>
          <span v-else>{{ i + 1 }}</span>
        </div>
        <div v-if="i < blocks.length - 1" class="pog_step_line" :class="{ 'is-done': i < step }" />
      </template>
    </div>

    <template v-for="(block, bi) in blocks" :key="`block-${bi}`">
    <div
      v-for="group in block"
      v-show="!isSteps || bi === step"
      :key="group.key"
      class="pog_group"
      :class="{ 'has-error': groupHasError(group, bi) }"
    >
      <p class="pog_group_title">
        <span v-if="!isSteps" class="pog_group_num">{{ groups.indexOf(group) + 1 }}</span>
        <span class="pog_group_label">{{ group.label }}</span>
        <span v-if="isInput(group) && group.price > 0" class="pog_title_price">+{{ formatPrice(group.price) }}</span>
      </p>

      <textarea
        v-if="isInput(group) && group.control === 'textarea'"
        class="pog_input pog_textarea"
        rows="3"
        :value="selections[group.key] ?? ''"
        :placeholder="group.label"
        :maxlength="group.maxLength"
        @input="select(group, $event.target.value)"
      ></textarea>
      <input
        v-else-if="isInput(group)"
        class="pog_input"
        :type="group.control === 'number' ? 'number' : 'text'"
        :value="selections[group.key] ?? ''"
        :placeholder="group.label"
        :maxlength="group.maxLength"
        @input="select(group, $event.target.value)"
      />

      <div
        v-else-if="group.control === 'select'"
        class="pog_dd"
        :class="{ 'is-open': openSelect === group.key, 'is-active': (selections[group.key] ?? '') !== '' }"
      >
        <button type="button" class="pog_dd_head" @click.stop="toggleSelect(group)">
          <span class="pog_dd_head_label" :class="{ 'is-placeholder': !(selections[group.key] ?? '') }">
            {{ selectedOptionText(group) }}
          </span>
          <svg class="pog_dd_arrow" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M1 1.5 6 6.5 11 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <div v-show="openSelect === group.key" class="pog_dd_menu">
          <button
            v-for="opt in group.options"
            :key="opt.value"
            type="button"
            class="pog_dd_opt"
            :class="{ 'is-active': selections[group.key] === opt.value }"
            @click="chooseOption(group, opt.value)"
          >
            <span class="pog_dd_opt_name">{{ opt.label }}</span>
            <span v-if="priceLabel(group, opt)" class="pog_dd_opt_price">{{ priceLabel(group, opt) }}</span>
            <svg v-if="selections[group.key] === opt.value" class="pog_dd_opt_check" width="12" height="10" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9.298 1.673 3.798 7.173a.57.57 0 0 1-.398.165.57.57 0 0 1-.399-.165L.376 4.548a.563.563 0 0 1 0-.797.563.563 0 0 1 .797 0L3.4 5.978 8.502.877a.564.564 0 0 1 .797.797z" fill="currentColor" />
            </svg>
          </button>
        </div>
      </div>

      <div v-else class="pog_list" :class="{ 'pog_list--cols2': group.columns === 2 }">
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
          <span class="pog_name">{{ opt.label }}</span>
          <span v-if="opt.popular" class="pog_tag">Popular</span>
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

      <p v-if="isInput(group) && group.tooltip" class="pog_hint">{{ group.tooltip }}</p>

      <p v-if="groupHasError(group, bi)" class="pog_error">
        {{ isInput(group) ? 'Please fill in this field.' : 'Please select an option to continue.' }}
      </p>
    </div>
    </template>

    <div v-if="isSteps" class="pog_nav">
      <button type="button" class="pog_nav_btn" :disabled="step === 0" @click="back">Back</button>
      <button
        v-if="step < blocks.length - 1"
        type="button"
        class="pog_nav_btn pog_nav_btn--next"
        @click="next"
      >
        Next
      </button>
    </div>
  </div>
</template>

<style scoped>
.pog {
  --pog-grad: radial-gradient(136.56% 99.31% at 37.02% 26.55%, rgb(43, 255, 149) 0%, rgb(5, 71, 146) 100%);
  display: flex;
  max-width: 100%;
  flex-direction: column;
  gap: 24px;
  margin: 0 0 4px;
}
.pog_group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.pog_group_title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  font-family: var(--font-family);
  font-size: 16px;
  font-weight: 600;
  color: #fff;
}
/* Numbered step badge (single layout) — brand gradient circle. */
.pog_group_num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--pog-grad);
  color: #06281b;
  font-size: 12px;
  font-weight: 800;
}
.pog_group_label {
  min-width: 0;
}
.pog_error {
  margin: 0;
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 500;
  color: #ff6b6b;
}
.pog_group.has-error .pog_row,
.pog_group.has-error .pog_dd_head,
.pog_group.has-error .pog_input {
  border-color: #ff6b6b;
}
.pog_title_price {
  margin-left: 6px;
  font-weight: 600;
  color: #2bff95;
}
.pog_hint {
  margin: 0;
  font-family: var(--font-family);
  font-size: 13px;
  color: rgba(255, 255, 255, 0.55);
}
.pog_input {
  width: 100%;
  padding: 14px 16px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  color: #fff;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 500;
}
.pog_input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}
.pog_input:focus {
  outline: none;
  border-color: #2bff95;
}
.pog_textarea {
  resize: vertical;
  min-height: 88px;
  line-height: 1.45;
}
.pog_list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
/* Two options per row (admin per-field setting). */
.pog_list--cols2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}
.pog_list--cols2 .pog_row {
  padding: 11px 12px;
}
.pog_row {
  position: relative;
  display: flex;
  align-items: center;
  gap: 10px;
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
  border-color: transparent;
  background: linear-gradient(135deg, rgba(43, 255, 149, 0.12), rgba(5, 71, 146, 0.12)), rgba(255, 255, 255, 0.03);
}
/* Brand-gradient border on the selected row (mask keeps only the 1px ring). */
.pog_row.is-active::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  padding: 1px;
  background: var(--pog-grad);
  -webkit-mask:
    linear-gradient(#000 0 0) content-box,
    linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
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
  background: var(--pog-grad);
  border-color: transparent;
}
.pog_mark_icon {
  display: block;
}
/* Name then price sit together on the left; no gap pushing price to the edge. */
.pog_name {
  flex: 0 1 auto;
  min-width: 0;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 500;
}
.pog_price {
  flex-shrink: 0;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 600;
  color: #fff;
}
.pog_tag {
  flex-shrink: 0;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #2bff95;
  border: 1px solid rgba(43, 255, 149, 0.5);
  border-radius: 999px;
  padding: 2px 8px;
}
.pog_info {
  position: relative;
  flex-shrink: 0;
  margin-left: auto;
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

/* Custom dropdown (no native <select> picker) */
.pog_dd {
  position: relative;
}
.pog_dd_head {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 14px 16px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  color: #fff;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  transition: border-color 0.15s ease;
}
.pog_dd_head:hover {
  border-color: rgba(43, 255, 149, 0.4);
}
.pog_dd_head_label {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.pog_dd_head_label.is-placeholder {
  color: rgba(255, 255, 255, 0.45);
}
.pog_dd_arrow {
  flex-shrink: 0;
  color: rgba(255, 255, 255, 0.6);
  transition: transform 0.2s ease;
}
.pog_dd.is-open .pog_dd_arrow {
  transform: rotate(180deg);
}
/* Brand-gradient ring on the open/selected dropdown head (mask keeps the 1px). */
.pog_dd.is-active .pog_dd_head,
.pog_dd.is-open .pog_dd_head {
  border-color: transparent;
}
.pog_dd.is-active::after,
.pog_dd.is-open::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 12px;
  padding: 1px;
  background: var(--pog-grad);
  -webkit-mask:
    linear-gradient(#000 0 0) content-box,
    linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
}
.pog_dd_menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  z-index: 20;
  max-height: 320px;
  overflow-y: auto;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  background: #11131c;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  box-shadow: 0 18px 44px rgba(0, 0, 0, 0.55);
}
.pog_dd_opt {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 11px 12px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(255, 255, 255, 0.85);
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  transition: background 0.12s ease, color 0.12s ease;
}
.pog_dd_opt:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
}
.pog_dd_opt.is-active {
  background: linear-gradient(135deg, rgba(43, 255, 149, 0.14), rgba(5, 71, 146, 0.14));
  color: #fff;
}
.pog_dd_opt_name {
  flex: 1;
  min-width: 0;
}
.pog_dd_opt_price {
  flex-shrink: 0;
  font-weight: 700;
  color: #fff;
}
.pog_dd_opt_check {
  flex-shrink: 0;
  color: #2bff95;
}

/* Step progress + nav */
.pog_steps {
  display: flex;
  align-items: center;
  gap: 6px;
}
.pog_step {
  flex-shrink: 0;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
}
.pog_step.is-active {
  color: #0b0b0f;
  background: var(--pog-grad);
  border-color: transparent;
}
.pog_step.is-done {
  color: #2bff95;
  border-color: rgba(43, 255, 149, 0.5);
}
.pog_step_line {
  flex: 1;
  height: 2px;
  background: rgba(255, 255, 255, 0.12);
}
.pog_step_line.is-done {
  background: rgba(43, 255, 149, 0.5);
}
.pog_nav {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}
.pog_nav_btn {
  padding: 11px 22px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.04);
  color: #fff;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease, opacity 0.15s ease;
}
.pog_nav_btn:hover:not(:disabled) {
  border-color: rgba(43, 255, 149, 0.4);
}
.pog_nav_btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.pog_nav_btn--next {
  margin-left: auto;
  border-color: transparent;
  color: #fff;
  background: var(--pog-grad);
}
.pog_nav_btn--next:hover:not(:disabled) {
  filter: brightness(1.05);
}

@media (max-width: 768px) {
  .pog_tooltip {
    max-width: 200px;
  }
  /* Compact option rows on phones — 95% of traffic is mobile. */
  .pog {
    gap: 14px;
    margin: 4px 0;
  }
  .pog_group {
    gap: 6px;
  }
  .pog_group_title {
    font-size: 13px;
  }
  .pog_list,
  .pog_list--cols2 {
    gap: 5px;
  }
  .pog_row {
    padding: 7px 11px;
    gap: 7px;
    border-radius: 9px;
  }
  .pog_list--cols2 .pog_row {
    padding: 7px 9px;
  }
  .pog_mark {
    width: 15px;
    height: 15px;
    border-radius: 5px;
  }
  .pog_mark--radio {
    border-radius: 50%;
  }
  .pog_name,
  .pog_price {
    font-size: 13px;
  }
  .pog_dd_head,
  .pog_input {
    padding: 11px 14px;
    font-size: 13px;
  }
  .pog_dd_opt {
    padding: 10px 12px;
    font-size: 13px;
  }
  .pog_group_num {
    width: 22px;
    height: 22px;
    font-size: 11px;
  }
  .pog_tag {
    font-size: 9px;
    padding: 1px 6px;
  }
}
</style>

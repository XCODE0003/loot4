<script setup>
import { computed, reactive, ref, watch } from 'vue'
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

const isSteps = computed(() => props.layout === 'steps' && props.groups.length > 1)

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

// A required group with no selection shows an error once the customer tried to
// advance (steps) or buy (single page).
function groupHasError(group, gi) {
  if (isGroupSatisfied(group)) return false
  if (props.showErrors) return true
  return isSteps.value && gi === step.value && stepAttempted.value
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
// In steps mode the buy button only shows on the final step.
const isLastStep = computed(() => !isSteps.value || step.value >= props.groups.length - 1)

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

// When a buy is blocked, jump the wizard to the first unsatisfied required group.
watch(
  () => props.showErrors,
  (on) => {
    if (!on || !isSteps.value) return
    const idx = props.groups.findIndex((g) => !isGroupSatisfied(g))
    if (idx >= 0) step.value = idx
  },
)

function next() {
  const group = props.groups[step.value]
  if (group && !isGroupSatisfied(group)) {
    stepAttempted.value = true
    return
  }
  if (step.value < props.groups.length - 1) {
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
      <template v-for="(g, i) in groups" :key="`step-${g.key}`">
        <div class="pog_step" :class="{ 'is-active': i === step, 'is-done': i < step }">
          <svg v-if="i < step" width="12" height="10" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.298 1.673 3.798 7.173a.57.57 0 0 1-.398.165.57.57 0 0 1-.399-.165L.376 4.548a.563.563 0 0 1 0-.797.563.563 0 0 1 .797 0L3.4 5.978 8.502.877a.564.564 0 0 1 .797.797z" fill="currentColor" />
          </svg>
          <span v-else>{{ i + 1 }}</span>
        </div>
        <div v-if="i < groups.length - 1" class="pog_step_line" :class="{ 'is-done': i < step }" />
      </template>
    </div>

    <div
      v-for="(group, gi) in groups"
      v-show="!isSteps || gi === step"
      :key="group.key"
      class="pog_group"
      :class="{ 'has-error': groupHasError(group, gi) }"
    >
      <p class="pog_group_title">
        {{ group.label }}
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
        class="pog_select_wrap"
        :class="{ 'is-active': (selections[group.key] ?? '') !== '' }"
      >
        <select
          class="pog_select"
          :value="selections[group.key] ?? ''"
          @change="select(group, $event.target.value)"
        >
          <option value="" disabled>Choose…</option>
          <option v-for="opt in group.options" :key="opt.value" :value="opt.value">
            {{ optionText(group, opt) }}
          </option>
        </select>
        <svg class="pog_select_arrow" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M1 1.5 6 6.5 11 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>

      <div v-else class="pog_list">
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

      <p v-if="groupHasError(group, gi)" class="pog_error">
        {{ isInput(group) ? 'Please fill in this field.' : 'Please select an option to continue.' }}
      </p>
    </div>

    <div v-if="isSteps" class="pog_nav">
      <button type="button" class="pog_nav_btn" :disabled="step === 0" @click="back">Back</button>
      <button
        v-if="step < groups.length - 1"
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
  max-width: 400px;
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
.pog_error {
  margin: 0;
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 500;
  color: #ff6b6b;
}
.pog_group.has-error .pog_row,
.pog_group.has-error .pog_select,
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

/* Dropdown */
.pog_select_wrap {
  position: relative;
}
.pog_select {
  width: 100%;
  appearance: none;
  -webkit-appearance: none;
  padding: 14px 44px 14px 16px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  color: #fff;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
}
.pog_select:focus {
  outline: none;
  border-color: transparent;
}
.pog_select_wrap.is-active .pog_select {
  border-color: transparent;
}
/* Brand-gradient border on the selected/focused dropdown (mask keeps the 1px ring). */
.pog_select_wrap.is-active::after,
.pog_select_wrap:focus-within::after {
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
.pog_select option {
  background: #16161d;
  color: #fff;
}
.pog_select_arrow {
  position: absolute;
  top: 50%;
  right: 16px;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.6);
  pointer-events: none;
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
}
</style>

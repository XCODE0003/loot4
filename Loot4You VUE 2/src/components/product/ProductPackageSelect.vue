<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  packages: { type: Array, required: true },
  basePrice: { type: Number, default: null },
})

const emit = defineEmits(['select'])

const basePackage = computed(() => {
  if (props.basePrice != null) {
    const match = props.packages.find((p) => p.price === props.basePrice)
    if (match) return match
  }
  return props.packages[props.packages.length - 1] || props.packages[0]
})

const addons = computed(() => props.packages.filter((p) => p.id !== basePackage.value?.id))

const selectedAddonIds = ref([])

const selectedAddons = computed(() =>
  addons.value.filter((p) => selectedAddonIds.value.includes(p.id)),
)

const addonsTotal = computed(() =>
  selectedAddons.value.reduce((sum, pkg) => sum + pkg.price, 0),
)

const totalPrice = computed(
  () => (basePackage.value?.price ?? 0) + addonsTotal.value,
)

function isAddonSelected(id) {
  return selectedAddonIds.value.includes(id)
}

watch(
  [basePackage, selectedAddons, totalPrice],
  () => {
    emit('select', {
      base: basePackage.value,
      addons: selectedAddons.value,
      price: totalPrice.value,
    })
  },
  { immediate: true },
)
</script>

<template>
  <div class="product_main_section_chose">
    <p class="product_main_section_chose_title">Chose Package</p>
    <p class="product_main_section_chose_selected">
      <template v-for="(part, i) in basePackage.parts" :key="`base-${i}`">
        <span v-if="i === 0 || i === 2">{{ part }}</span>
        <template v-else>{{ part }} </template>
      </template>
      <span>| ${{ basePackage.price.toFixed(2) }}</span>
    </p>
    <div class="product_main_section_chose_select_items">
      <div class="product_main_section_chose_select_items_content">
        <p class="product_main_section_chose_select_items_title">Add-ons</p>
        <div class="product_main_section_chose_select_items_blocks">
          <label
            v-for="pkg in addons"
            :key="pkg.id"
            class="product_main_section_chose_select_item"
            :class="{ 'is-active': isAddonSelected(pkg.id) }"
          >
            <input
              v-model="selectedAddonIds"
              type="checkbox"
              class="product_option_radio_input"
              :value="pkg.id"
            />
            <span class="product_option_radio" aria-hidden="true" />
            <span class="product_main_section_chose_select_item_text">
              <template v-for="(part, i) in pkg.parts" :key="`addon-${pkg.id}-${i}`">
                <span v-if="i === 0 || i === 2">{{ part }}</span>
                <template v-else>{{ part }} </template>
              </template>
              <span>| +${{ pkg.price.toFixed(2) }}</span>
            </span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

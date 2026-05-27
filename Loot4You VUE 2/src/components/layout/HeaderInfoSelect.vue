<script setup>
import { onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
  modelValue: { type: String, required: true },
  options: { type: Array, required: true },
  variant: { type: String, default: 'lang' },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

function pick(option) {
  emit('update:modelValue', option)
  close()
}

function onDocClick(e) {
  if (!e.target.closest(`.header_info_select--${props.variant}`)) close()
}

onMounted(() => document.addEventListener('click', onDocClick))
onUnmounted(() => document.removeEventListener('click', onDocClick))
</script>

<template>
  <div
    class="header_info_select"
    :class="[
      variant === 'lang' ? 'header_info_lang' : 'header_info_valute',
      `header_info_select--${variant}`,
      { 'is-open': isOpen },
    ]"
  >
    <button
      type="button"
      class="header_info_select_trigger"
      :class="variant === 'lang' ? 'header_info_lang_title' : 'header_info_valute_title'"
      :aria-expanded="isOpen"
      aria-haspopup="listbox"
      @click.stop="toggle"
    >
      {{ modelValue }}
      <svg
        class="header_info_select_arrow"
        width="10"
        height="7"
        viewBox="0 0 10 7"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
      >
        <path d="M0.5 0.5L5 5.5L9.5 0.5" stroke="currentColor" stroke-linecap="round" />
      </svg>
    </button>
    <ul class="header_info_select_menu" role="listbox" :aria-label="variant === 'lang' ? 'Language' : 'Currency'">
      <li
        v-for="option in options"
        :key="option"
        role="option"
        :aria-selected="option === modelValue"
        class="header_info_select_option"
        :class="{ 'is-active': option === modelValue }"
        @click.stop="pick(option)"
      >
        {{ option }}
      </li>
    </ul>
  </div>
</template>

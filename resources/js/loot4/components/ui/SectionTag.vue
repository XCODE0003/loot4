<script setup>
defineProps({
  withIcon: { type: Boolean, default: false },
})
</script>

<template>
  <p class="section_teg">
    <svg
      v-if="withIcon"
      width="22"
      height="22"
      viewBox="0 0 22 22"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M10.8191 19.8375C15.7987 19.8375 19.8355 15.8007 19.8355 10.8211C19.8355 5.84146 15.7987 1.80469 10.8191 1.80469C5.83951 1.80469 1.80273 5.84146 1.80273 10.8211C1.80273 15.8007 5.83951 19.8375 10.8191 19.8375Z"
        stroke="#E4E4E7"
        stroke-width="1.80328"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M8.19531 8.11591C8.40729 7.51331 8.8257 7.00518 9.37642 6.68152C9.92714 6.35785 10.5746 6.23954 11.2042 6.34753C11.8338 6.45553 12.4049 6.78286 12.8163 7.27155C13.2277 7.76024 13.4528 8.37876 13.4519 9.01755C13.4519 10.8208 10.747 11.7225 10.747 11.7225"
        stroke="#E4E4E7"
        stroke-width="1.80328"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M10.8184 15.3281H10.8266"
        stroke="#E4E4E7"
        stroke-width="1.80328"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
    <slot />
  </p>
</template>

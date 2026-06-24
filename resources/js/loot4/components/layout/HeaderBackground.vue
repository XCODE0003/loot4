<script setup>
import { asset } from '@/loot4/utils/asset'
</script>

<template>
  <img
    :src="asset('background_header.png')"
    alt=""
    class="header_background"
    width="2000"
    height="2000"
    loading="lazy"
    decoding="async"
  />
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import FaqSection from '@/loot4/components/ui/FaqSection.vue'
import { faqGameItems } from '@/loot4/data/faq'
</script>

<template>
  <Head title="FAQ — Loot4you" />
  <FaqSection :items="faqGameItems" />
</template>

<script setup>
import IntroSection from '@/loot4/components/home/IntroSection.vue'
import DiscoverSection from '@/loot4/components/home/DiscoverSection.vue'
import WhySection from '@/loot4/components/home/WhySection.vue'
import HowSection from '@/loot4/components/home/HowSection.vue'
import FaqSection from '@/loot4/components/ui/FaqSection.vue'

defineProps({
  discoverGames: { type: Array, default: null },
})
</script>

<template>
  <IntroSection />
  <DiscoverSection :games="discoverGames" />
  <WhySection />
  <HowSection />
  <FaqSection />
</template>

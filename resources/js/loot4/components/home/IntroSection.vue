<script setup>
import Container from '@/loot4/components/layout/Container.vue'
import TrustpilotWidget from '@/loot4/components/ui/TrustpilotWidget.vue'
import { introStats } from '@/loot4/data/home'
import { asset } from '@/loot4/utils/asset'
</script>

<template>
  <section class="intro">
    <Container>
      <div class="intro_blocks">
        <div class="intro_block">
          <h1 class="intro_block_title">Your Ultimate Gaming Arsenal</h1>
          <h2 class="intro_block_subtitle">
            Power up your <span>favorite games</span> with <span>premium</span> currency, levels, and elite gear.
          </h2>
          <div class="intro_block_trustpilot">
            <TrustpilotWidget />
          </div>
          <div class="intro_block_stats">
            <div v-for="stat in introStats" :key="stat.label" class="intro_block_stat">
              <p class="intro_block_stat_text">{{ stat.value }}</p>
              <p class="intro_block_stat_title">{{ stat.label }}</p>
            </div>
          </div>
        </div>
        <div class="intro_block">
          <img :src="asset('intro_image.png')" alt="" class="intro_block_image" />
        </div>
      </div>
    </Container>
  </section>
</template>

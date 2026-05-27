<script setup>
import Container from '@/loot4/components/layout/Container.vue'
import SectionTag from '@/loot4/components/ui/SectionTag.vue'
import { whyBlocks } from '@/loot4/data/home'
import { asset } from '@/loot4/utils/asset'
</script>

<template>
  <section class="why">
    <Container>
      <SectionTag>Features</SectionTag>
      <h4 class="why_title">Why Choose Us?</h4>
      <div class="why_blocks">
        <div v-for="block in whyBlocks" :key="block.title" class="why_block">
          <img :src="asset(block.image)" :alt="block.title" class="why_block_image" />
          <p class="why_block_title">{{ block.title }}</p>
          <p class="why_block_text">{{ block.text }}</p>
        </div>
      </div>
    </Container>
  </section>
</template>

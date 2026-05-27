<script setup>
import { ref } from 'vue'
import Container from '@/components/layout/Container.vue'
import SectionTag from '@/components/ui/SectionTag.vue'
import { howBlocks } from '@/data/home'

const activeIndex = ref(null)

function activate(index) {
  activeIndex.value = activeIndex.value === index ? null : index
}
</script>

<template>
  <section class="how">
    <Container>
      <SectionTag>Features</SectionTag>
      <h4 class="how_title">How Does It Work? <br />Safe and faster.</h4>
      <p class="how_subtitle">
        Everything you need to know about our marketing services, workflows, and how we support gaming brands.
      </p>
      <div class="how_blocks">
        <div
          v-for="(block, index) in howBlocks"
          :key="block.title"
          class="how_block"
          :class="{ 'is-active': activeIndex === index }"
          role="button"
          tabindex="0"
          @click="activate(index)"
          @keydown.enter="activate(index)"
        >
          <div class="how_block_content">
            <h4 class="how_block_title">{{ block.title }}</h4>
            <p class="how_block_text" v-html="block.html" />
          </div>
        </div>
      </div>
    </Container>
  </section>
</template>

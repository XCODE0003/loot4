<script setup>
import { ref } from 'vue'
import Container from '@/components/layout/Container.vue'
import SectionTag from '@/components/ui/SectionTag.vue'
import { faqItems } from '@/data/faq'

const openIndex = ref(0)

function toggle(index) {
  openIndex.value = openIndex.value === index ? -1 : index
}

</script>

<template>
  <section class="faq">
    <Container>
      <SectionTag with-icon>FAQ</SectionTag>
      <h4 class="faq_title">Frequently asked questions</h4>
      <p class="faq_subtitle">
        Find everything you need to know about our digital keys, accounts, delivery, and guarantees.
      </p>
      <div class="faq_blocks">
        <div
          v-for="(item, index) in faqItems"
          :key="index"
          class="faq_block"
          :class="{ 'is-open': openIndex === index }"
        >
          <div class="faq_block_content">
            <div class="faq_block_up" role="button" tabindex="0" @click="toggle(index)" @keydown.enter="toggle(index)">
              <h4 class="faq_block_title">{{ item.question }}</h4>
              <svg class="faq_block_up_arrow" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.92383 7.38281L9.84646 12.3054L14.7691 7.38281" stroke="#A1A1AA" stroke-width="2.1097" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <p class="faq_block_text">{{ item.answer }}</p>
          </div>
        </div>
      </div>
    </Container>
  </section>
</template>

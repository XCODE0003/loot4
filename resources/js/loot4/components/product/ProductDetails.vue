<script setup>
import { computed, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import Container from '@/loot4/components/layout/Container.vue'
import ProductOptionGroups from '@/loot4/components/product/ProductOptionGroups.vue'
import GameCard from '@/loot4/components/ui/GameCard.vue'
import TrustpilotWidget from '@/loot4/components/ui/TrustpilotWidget.vue'
import { asset } from '@/loot4/utils/asset'
import { useCart } from '@/loot4/composables/useCart'
import { useLocale } from '@/loot4/composables/useLocale'

const props = defineProps({
  data: { type: Object, required: true },
})

const { add } = useCart()
const { formatPrice } = useLocale()

const hasGroups = computed(() => (props.data.optionGroups?.length ?? 0) > 0)

// Gallery: main image first, then any extra gallery images.
const images = computed(() => {
  const list = props.data.gallery?.length ? props.data.gallery : []
  if (list.length) return list
  return props.data.image ? [props.data.image] : []
})
const activeImage = ref(images.value[0] ?? null)
watch(images, (imgs) => {
  if (!imgs.includes(activeImage.value)) activeImage.value = imgs[0] ?? null
})

const displayPrice = ref(props.data.price)
const selectedSummary = ref('')
const selectedSelections = ref({})
const optionsValid = ref(true)
const buyAttempted = ref(false)
const optionsRef = ref(null)
// In step-by-step mode the Buy button only appears on the last step.
const canBuy = ref(true)

function onOptionsChange({ selections, price, summary, valid, isLastStep }) {
  displayPrice.value = price
  selectedSummary.value = summary
  selectedSelections.value = selections
  optionsValid.value = valid !== false
  canBuy.value = isLastStep !== false
}

function buy() {
  // Block purchase until every required option group has a selection.
  if (hasGroups.value && !optionsValid.value) {
    buyAttempted.value = true
    optionsRef.value?.$el?.scrollIntoView?.({ behavior: 'smooth', block: 'center' })
    return
  }
  add({
    id: props.data.slug,
    slug: props.data.slug,
    title: props.data.title,
    image: props.data.image,
    option: selectedSummary.value,
    selections: selectedSelections.value,
    price: displayPrice.value,
    priceOld: props.data.priceOld,
  })
}
</script>

<template>
  <section class="product_main">
    <Container>
      <div class="product_main_navs">
        <Link href="/" class="product_main_navs_link">Home</Link>
        <svg class="product_main_navs_arrow" width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.418457 0.418457L4.56196 4.56196L0.418457 8.70545" stroke="white" stroke-opacity="0.54" stroke-width="1.18386" />
        </svg>
        <img v-if="data.breadcrumb.gameIcon" :src="data.breadcrumb.gameIcon" alt="" class="product_main_navs_platform" />
        <Link :href="data.breadcrumb.gameTo" class="product_main_navs_link">{{ data.breadcrumb.game }}</Link>
        <svg class="product_main_navs_icon" width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.418457 0.418457L4.56196 4.56196L0.418457 8.70545" stroke="white" stroke-opacity="0.54" stroke-width="1.18386" />
        </svg>
        <p class="product_main_navs_text">{{ data.breadcrumb.name }}</p>
      </div>

      <div class="product_main_sections">
        <div class="product_main_section">
          <img v-if="activeImage" :src="asset(activeImage)" alt="" class="product_main_section_image" />
          <div v-if="images.length > 1" class="product_gallery_thumbs">
            <button
              v-for="(img, i) in images"
              :key="i"
              type="button"
              class="product_gallery_thumb"
              :class="{ 'is-active': img === activeImage }"
              @click="activeImage = img"
            >
              <img :src="asset(img)" alt="" />
            </button>
          </div>
          <div class="product_main_section_payments">
            <img :src="asset('/payment.svg')" alt="Payment methods" class="product_main_section_payments_strip" />
          </div>
          <div class="product_main_section_trustpilot">
            <TrustpilotWidget />
          </div>
        </div>
        <div class="product_main_section">
          <h1 class="product_main_section_title">{{ data.title }}</h1>
          <ProductOptionGroups
            v-if="hasGroups"
            ref="optionsRef"
            :groups="data.optionGroups"
            :product-price="data.price"
            :layout="data.optionsLayout"
            :show-errors="buyAttempted"
            @change="onOptionsChange"
          />
          <div class="product_main_section_price">
            <div class="product_main_section_price_items">
              <p class="product_main_section_price_new">
                {{ formatPrice(displayPrice) }}
                <span v-if="data.priceOld != null">{{ formatPrice(data.priceOld) }}</span>
              </p>
              <button v-if="canBuy" type="button" class="product_main_section_price_button" @click="buy">Buy now</button>
            </div>
          </div>
        </div>
      </div>

      <div class="product_main_text">
        <span class="product_main_text_label">Description</span>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-if="data.descriptionHtml" class="product_main_text_html" v-html="data.descriptionHtml" />
        <p v-else class="product_main_text_plain">{{ data.description }}</p>
      </div>

      <div class="product_main_recommended product">
        <h4 class="product_main_recommended_title">Recommended products</h4>
        <div class="game_cards_blocks">
          <GameCard v-for="item in data.recommended" :key="item.id" v-bind="item" :category="'action'" />
        </div>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.product_gallery_thumbs {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 14px;
}
.product_gallery_thumb {
  width: 72px;
  height: 72px;
  padding: 0;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  transition: border-color 0.15s ease;
}
.product_gallery_thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.product_gallery_thumb:hover {
  border-color: rgba(43, 255, 149, 0.4);
}
.product_gallery_thumb.is-active {
  border-color: #2bff95;
}
</style>

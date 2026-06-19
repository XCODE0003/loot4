<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
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
  // Add to the (persisted) cart but skip the drawer — go straight to checkout.
  add(
    {
      id: props.data.slug,
      slug: props.data.slug,
      title: props.data.title,
      image: props.data.image,
      option: selectedSummary.value,
      selections: selectedSelections.value,
      price: displayPrice.value,
      priceOld: props.data.priceOld,
    },
    { open: false },
  )
  router.visit('/checkout')
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
          <div class="product_config_card">
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
              <div class="product_price_left">
                <p class="product_main_section_price_new">
                  {{ formatPrice(displayPrice) }}
                  <span v-if="data.priceOld != null">{{ formatPrice(data.priceOld) }}</span>
                </p>
                <p class="product_secure">
                  <svg width="13" height="14" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="2" y="6.8" width="10" height="7.5" rx="1.6" stroke="currentColor" stroke-width="1.3" />
                    <path d="M4.3 6.8V4.6a2.7 2.7 0 0 1 5.4 0v2.2" stroke="currentColor" stroke-width="1.3" />
                  </svg>
                  Secure Checkout
                </p>
              </div>
              <button v-if="canBuy" type="button" class="product_main_section_price_button" @click="buy">
                Buy now <span class="product_buy_arrow" aria-hidden="true">→</span>
              </button>
            </div>
            <div class="product_trust_row">
              <span class="product_trust_item">
                <svg width="15" height="16" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M8 1l6 2v6c0 4-3 6.5-6 8-3-1.5-6-4-6-8V3l6-2z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />
                  <path d="M5.4 9l1.8 1.8L10.8 7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                100% Safe &amp; Secure
              </span>
              <span class="product_trust_dot" aria-hidden="true"></span>
              <span class="product_trust_item">
                <svg width="15" height="15" viewBox="0 0 18 18" fill="currentColor" aria-hidden="true">
                  <path d="M9 1.5l2.06 4.17 4.6.67-3.33 3.24.79 4.58L9 12.98l-4.12 2.16.79-4.58L2.34 6.34l4.6-.67L9 1.5z" />
                </svg>
                Excellent 5.0 on Trustpilot
              </span>
            </div>
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

/* Bordered configurator card wrapping the options + price + trust row. */
.product_config_card {
  margin-top: 18px;
  padding: 26px 28px 24px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.015);
}

/* Price area extras (mockup): "Secure Checkout" under the price + a trust row. */
.product_price_left {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.product_secure {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  margin: 0;
  font-family: var(--font-family);
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
}
.product_buy_arrow {
  margin-left: 4px;
}
.product_trust_row {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 14px;
  margin-top: 20px;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.product_trust_item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.82);
}
.product_trust_item svg {
  color: #2bff95;
}
.product_trust_dot {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.25);
}

/* Mobile: move the payments + Trustpilot block below the Buy button, and shrink
   the title + banner image so the options sit higher. 95% of traffic is mobile. */
@media (max-width: 1100px) {
  .product_main_sections {
    display: flex;
    flex-direction: column;
  }
  /* Flatten the left column so its children can be reordered individually. */
  .product_main_section:first-child {
    display: contents;
  }
  .product_main_section_image,
  .product_gallery_thumbs {
    order: 1;
  }
  .product_main_section:last-child {
    order: 2;
  }
  .product_main_section_payments {
    order: 3;
    margin-top: 24px;
  }
  .product_main_section_trustpilot {
    order: 4;
    margin-top: 16px;
  }
  .product_main_section_title {
    font-size: 26px;
  }
  .product_main_section_image {
    max-height: 300px;
  }
}
@media (max-width: 768px) {
  .product_main_section_title {
    font-size: 22px;
  }
  .product_main_section_image {
    max-height: 240px;
  }
  .product_config_card {
    margin-top: 12px;
    padding: 16px 16px 18px;
    border-radius: 16px;
  }
}
@media (max-width: 480px) {
  .product_main_section_title {
    font-size: 19px;
    line-height: 1.25;
  }
  .product_main_section_image {
    max-height: 200px;
  }
}

/* Compact breadcrumb on mobile — keep it on a single (scrollable) line so the
   long product name no longer wraps to a second row. */
@media (max-width: 768px) {
  .product_main_navs {
    justify-content: flex-start;
    flex-wrap: nowrap;
    overflow-x: auto;
    scrollbar-width: none;
    /* One even gap between every crumb instead of the mixed per-element margins
       (those caused the uneven spaces on mobile). */
    gap: 6px;
  }
  .product_main_navs::-webkit-scrollbar {
    display: none;
  }
  .product_main_navs > * {
    flex-shrink: 0;
    white-space: nowrap;
    margin: 0;
  }
  .product_main_navs_link,
  .product_main_navs_text {
    font-size: 11px;
  }
  .product_main_navs_platform {
    width: 15px;
    height: 15px;
  }
}
</style>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import Container from '@/loot4/components/layout/Container.vue'
import ProductPackageSelect from '@/loot4/components/product/ProductPackageSelect.vue'
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

const hasPlatforms = computed(() => props.data.platforms?.length > 0)
const hasPackages = computed(() => props.data.packages?.length > 0)

const activePlatform = ref(hasPlatforms.value ? props.data.platforms[0] : null)
const displayPrice = ref(props.data.price)
const displayPriceOld = ref(props.data.priceOld)
const selectedOption = ref('')

function onPackageSelect({ base, addons, price }) {
  displayPrice.value = price
  const labels = [base, ...(addons ?? [])]
    .filter(Boolean)
    .map((pkg) => pkg.parts.join(' '))
  selectedOption.value = [activePlatform.value, ...labels].filter(Boolean).join(' · ')
}

function buy() {
  add({
    id: props.data.slug,
    slug: props.data.slug,
    title: props.data.title,
    image: props.data.image,
    option: selectedOption.value,
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
          <img v-if="data.image" :src="asset(data.image)" alt="" class="product_main_section_image" />
          <div class="product_main_section_payments">
            <img :src="asset('/payment.svg')" alt="Payment methods" class="product_main_section_payments_strip" />
          </div>
          <div class="product_main_section_trustpilot">
            <TrustpilotWidget />
          </div>
        </div>
        <div class="product_main_section">
          <h1 class="product_main_section_title">{{ data.title }}</h1>
          <div v-if="hasPlatforms" class="product_main_section_platforms">
            <p class="product_main_section_platforms_title">Your platform</p>
            <ul class="product_main_section_platforms_items">
              <li v-for="platform in data.platforms" :key="platform">
                <label
                  class="product_main_section_platforms_item"
                  :class="{ 'is-active': activePlatform === platform }"
                >
                  <input
                    v-model="activePlatform"
                    type="radio"
                    name="product-platform"
                    class="product_option_radio_input"
                    :value="platform"
                  />
                  <span class="product_option_radio" aria-hidden="true" />
                  {{ platform }}
                </label>
              </li>
            </ul>
          </div>
          <div v-if="hasPlatforms || hasPackages" class="product_main_section_line" />
          <ProductPackageSelect
            v-if="hasPackages"
            :packages="data.packages"
            :base-price="data.price"
            @select="onPackageSelect"
          />
          <div class="product_main_section_price">
            <div class="product_main_section_price_items">
              <p class="product_main_section_price_new">
                {{ formatPrice(displayPrice) }}
                <span v-if="displayPriceOld !== null">{{ formatPrice(displayPriceOld) }}</span>
              </p>
              <button type="button" class="product_main_section_price_button" @click="buy">Buy now</button>
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

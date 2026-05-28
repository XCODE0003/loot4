<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import Container from '@/loot4/components/layout/Container.vue'
import ProductPackageSelect from '@/loot4/components/product/ProductPackageSelect.vue'
import GameCard from '@/loot4/components/ui/GameCard.vue'
import { asset } from '@/loot4/utils/asset'
import { useCart } from '@/loot4/composables/useCart'
import { useLocale } from '@/loot4/composables/useLocale'

const props = defineProps({
  data: { type: Object, required: true },
})

const { add } = useCart()
const { formatPrice } = useLocale()

const activePlatform = ref(props.data.platforms[0])
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
            <p class="product_main_section_payments_text">We accept</p>
            <div class="product_main_section_payments_icons">
              <img
                v-for="icon in data.payments"
                :key="icon"
                :src="asset(icon)"
                alt=""
                class="product_main_section_payments_icon"
              />
            </div>
            <p class="product_main_section_payments_text">and many more</p>
          </div>
          <img :src="asset(data.trustImage)" alt="" class="product_main_section_trust" />
        </div>
        <div class="product_main_section">
          <h1 class="product_main_section_title">{{ data.title }}</h1>
          <div class="product_main_section_platforms">
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
          <div class="product_main_section_line" />
          <ProductPackageSelect
            :packages="data.packages"
            :base-price="data.price"
            @select="onPackageSelect"
          />
          <div class="product_main_section_price">
            <div class="product_main_section_price_items">
              <p class="product_main_section_price_new">
                {{ formatPrice(displayPrice) }}
                <span>{{ formatPrice(displayPriceOld) }}</span>
              </p>
              <button type="button" class="product_main_section_price_button" @click="buy">Buy now</button>
            </div>
          </div>
        </div>
      </div>

      <p class="product_main_text">
        <span>Description</span>
        <br />
        {{ data.description }}
      </p>

      <div class="product_main_recommended product">
        <h4 class="product_main_recommended_title">Recommended products</h4>
        <div class="game_cards_blocks">
          <GameCard v-for="item in data.recommended" :key="item.id" v-bind="item" :category="'action'" />
        </div>
      </div>
    </Container>
  </section>
</template>

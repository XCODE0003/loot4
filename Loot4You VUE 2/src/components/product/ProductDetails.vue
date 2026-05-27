<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Container from '@/components/layout/Container.vue'
import ProductPackageSelect from '@/components/product/ProductPackageSelect.vue'
import GameCard from '@/components/ui/GameCard.vue'
import { asset } from '@/utils/asset'

const props = defineProps({
  data: { type: Object, required: true },
})

const router = useRouter()
const activePlatform = ref(props.data.platforms[0])
const displayPrice = ref(props.data.price)
const displayPriceOld = ref(props.data.priceOld)

function formatPrice(value) {
  return `$${value.toFixed(2)}`
}

function onPackageSelect({ price }) {
  displayPrice.value = price
}

function buy() {
  router.push({ name: 'product' })
}
</script>

<template>
  <section class="product_main">
    <Container>
      <div class="product_main_navs">
        <RouterLink to="/" class="product_main_navs_link">Home</RouterLink>
        <svg class="product_main_navs_arrow" width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.418457 0.418457L4.56196 4.56196L0.418457 8.70545" stroke="white" stroke-opacity="0.54" stroke-width="1.18386" />
        </svg>
        <img :src="asset(data.breadcrumb.platformIcon)" alt="" class="product_main_navs_platform" />
        <RouterLink :to="data.breadcrumb.gameTo" class="product_main_navs_link">{{ data.breadcrumb.game }}</RouterLink>
        <svg class="product_main_navs_icon" width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.418457 0.418457L4.56196 4.56196L0.418457 8.70545" stroke="white" stroke-opacity="0.54" stroke-width="1.18386" />
        </svg>
        <p class="product_main_navs_text">{{ data.breadcrumb.name }}</p>
      </div>

      <div class="product_main_sections">
        <div class="product_main_section">
          <img :src="asset(data.image)" alt="" class="product_main_section_image" />
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

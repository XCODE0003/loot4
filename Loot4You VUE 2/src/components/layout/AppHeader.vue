<script setup>
import { navLinks } from '@/data/navigation'
import { useMobileMenu } from '@/composables/useMobileMenu'
import { useLocale } from '@/composables/useLocale'
import { asset } from '@/utils/asset'
import HeaderInfoSelect from '@/components/layout/HeaderInfoSelect.vue'

const { menuOpen, toggleMenu, closeMenu } = useMobileMenu()
const { lang, currency, langs, currencies, setLang, setCurrency } = useLocale()
</script>

<template>
  <header class="header">
    <div class="container">
      <div class="header_items">
        <RouterLink to="/" class="header_logo" @click="closeMenu">
          <img :src="asset('header_logo.svg')" alt="Loot4you" />
        </RouterLink>
        <button
          type="button"
          class="header_burger"
          :aria-expanded="menuOpen"
          :aria-label="menuOpen ? 'Close menu' : 'Open menu'"
          @click="toggleMenu"
        >
          <span class="header_burger_line" />
          <span class="header_burger_line" />
          <span class="header_burger_line" />
        </button>
        <div class="header_menu">
          <ul class="header_navs">
            <li v-for="link in navLinks" :key="link.label" class="header_nav_item">
              <RouterLink :to="link.to" class="header_nav_link" @click="closeMenu">
                {{ link.label }}
                <svg
                  v-if="link.hasIcon"
                  width="10"
                  height="7"
                  viewBox="0 0 10 7"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path d="M0.5 0.5L5 5.5L9.5 0.5" stroke="white" stroke-linecap="round" />
                </svg>
              </RouterLink>
            </li>
          </ul>
          <div class="header_info">
            <HeaderInfoSelect
              :model-value="lang"
              :options="langs"
              variant="lang"
              @update:model-value="setLang"
            />
            <HeaderInfoSelect
              :model-value="currency"
              :options="currencies"
              variant="valute"
              @update:model-value="setCurrency"
            />
          </div>
          <button type="button" class="header_login">Login</button>
        </div>
      </div>
    </div>
    <div class="header_overlay" aria-hidden="true" @click="closeMenu" />
  </header>
</template>

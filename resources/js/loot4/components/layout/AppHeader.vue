<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { navLinks } from '@/loot4/data/navigation'
import { useMobileMenu } from '@/loot4/composables/useMobileMenu'
import { useCart } from '@/loot4/composables/useCart'
import { asset } from '@/loot4/utils/asset'

const { menuOpen, toggleMenu, closeMenu } = useMobileMenu()
const { count: cartCount, open: openCart } = useCart()

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const games = computed(() => page.props.navGames ?? [])
</script>

<template>
  <header class="header">
    <div class="container">
      <div class="header_items">
        <Link href="/" class="header_logo" @click="closeMenu">
          <img :src="asset('header_logo.svg')" alt="Loot4you" />
        </Link>
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
            <li
              v-for="link in navLinks"
              :key="link.label"
              class="header_nav_item"
              :class="{ 'header_nav_item--dropdown': link.hasIcon && games.length }"
            >
              <Link :href="link.to" class="header_nav_link" @click="closeMenu">
                {{ $t(link.tkey) }}
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
              </Link>
              <div v-if="link.hasIcon && games.length" class="header_nav_dropdown">
                <Link
                  v-for="g in games"
                  :key="g.name"
                  href="/game"
                  class="header_nav_dropdown_item"
                  @click="closeMenu"
                >
                  <img v-if="g.image" :src="g.image" :alt="g.name" />
                  <span>{{ g.name }}</span>
                </Link>
              </div>
            </li>
          </ul>
          <button type="button" class="header_cart" aria-label="Open cart" @click="openCart">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2.5 3h2l2.4 12.2a1 1 0 0 0 1 .8h8.7a1 1 0 0 0 1-.8L20.5 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="9.5" cy="20" r="1.4" fill="currentColor"/>
              <circle cx="17.5" cy="20" r="1.4" fill="currentColor"/>
            </svg>
            <span v-if="cartCount" class="header_cart_badge">{{ cartCount }}</span>
          </button>
          <Link v-if="!user" href="/login" class="header_login" @click="closeMenu">{{ $t('header.login') }}</Link>
          <div v-else class="header_account">
            <Link href="/account" class="header_account_name" @click="closeMenu">{{ user.name }}</Link>
            <Link href="/logout" method="post" as="button" class="header_logout" @click="closeMenu">{{ $t('header.logout') }}</Link>
          </div>
        </div>
      </div>
    </div>
    <div class="header_overlay" aria-hidden="true" @click="closeMenu" />
  </header>
</template>

<style scoped>
.header_cart {
  position: relative;
  display: grid;
  place-items: center;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  cursor: pointer;
  margin-left: 32px;
  margin-right: 12px;
}
.header_cart:hover {
  background: rgba(255, 255, 255, 0.16);
}
.header_cart_badge {
  position: absolute;
  top: -6px;
  right: -6px;
  min-width: 20px;
  height: 20px;
  padding: 0 5px;
  display: grid;
  place-items: center;
  border-radius: 10px;
  background: #0fa854;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
}
.header_login {
  margin-left: 0;
  padding: 11px 26px;
  border-radius: 93px;
  background: linear-gradient(90deg, #0fa854, #2bff95);
  box-shadow: none;
  color: #fff;
  font-family: var(--font-family);
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: opacity 0.2s;
}
.header_login:hover {
  opacity: 0.8;
}
.header_account {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-left: 10px;
}
.header_account_name {
  color: rgba(255, 255, 255, 0.92);
  font-family: var(--font-family);
  font-weight: 500;
  font-size: 16px;
  line-height: 1;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.header_account_name:hover {
  opacity: 0.7;
}
.header_logout {
  padding: 11px 22px;
  border-radius: 93px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: transparent;
  color: #fff;
  font-family: var(--font-family);
  font-size: 16px;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s;
}
.header_logout:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* "Choose Game" hover dropdown */
.header_nav_item--dropdown {
  position: relative;
}
.header_nav_dropdown {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%) translateY(8px);
  min-width: 260px;
  padding: 10px;
  background: #06080f;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.8);
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s ease, transform 0.2s ease;
  z-index: 20;
}
.header_nav_item--dropdown:hover .header_nav_dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}
.header_nav_dropdown_item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 9px 10px;
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 15px;
}
.header_nav_dropdown_item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
}
.header_nav_dropdown_item img {
  flex-shrink: 0;
  width: 34px;
  height: 34px;
  border-radius: 8px;
  object-fit: cover;
}
</style>

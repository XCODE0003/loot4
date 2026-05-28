<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { navLinks } from '@/loot4/data/navigation'
import { useMobileMenu } from '@/loot4/composables/useMobileMenu'
import { useCart } from '@/loot4/composables/useCart'
import { useGameMenu } from '@/loot4/composables/useGameMenu'
import { useLocale } from '@/loot4/composables/useLocale'
import { asset } from '@/loot4/utils/asset'

const { menuOpen, toggleMenu, closeMenu } = useMobileMenu()
const { count: cartCount, open: openCart } = useCart()
const { gameMenuOpen, toggleGameMenu, closeGameMenu } = useGameMenu()
const { lang, currency, langs, currencyList, setLang, setCurrency } = useLocale()

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const games = computed(() => page.props.navGames ?? [])

function onNavClick(link, e) {
  if (link.hasIcon && games.value.length) {
    e.preventDefault()
    toggleGameMenu()
    return
  }
  closeMenu()
}

function onDocClick(e) {
  if (!gameMenuOpen.value) return
  if (!e.target.closest('.header_nav_item--dropdown')) {
    closeGameMenu()
  }
}

function onEsc(e) {
  if (e.key === 'Escape') closeGameMenu()
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onEsc)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onEsc)
})
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
              :class="{
                'header_nav_item--dropdown': link.hasIcon && games.length,
                'is-open': link.hasIcon && gameMenuOpen,
              }"
            >
              <Link
                v-if="!link.hasIcon"
                :href="link.to"
                class="header_nav_link"
                @click="closeMenu"
              >
                {{ $t(link.tkey) }}
              </Link>
              <button
                v-else
                type="button"
                class="header_nav_link header_nav_link--button"
                :aria-expanded="gameMenuOpen"
                @click="onNavClick(link, $event)"
              >
                {{ $t(link.tkey) }}
                <svg
                  width="10"
                  height="7"
                  viewBox="0 0 10 7"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path d="M0.5 0.5L5 5.5L9.5 0.5" stroke="white" stroke-linecap="round" />
                </svg>
              </button>
              <div v-if="link.hasIcon && games.length" class="header_nav_dropdown">
                <Link
                  v-for="g in games"
                  :key="g.name"
                  :href="g.slug ? `/game/${g.slug}` : '/game'"
                  class="header_nav_dropdown_item"
                  @click="closeGameMenu(); closeMenu()"
                >
                  <img v-if="g.image" :src="g.image" :alt="g.name" />
                  <span>{{ g.name }}</span>
                </Link>
              </div>
            </li>
          </ul>
          <div class="header_locale">
            <div class="header_pick">
              <button type="button" class="header_pick_btn">
                {{ lang }}
                <svg width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M0.5 0.5L4 4L7.5 0.5" stroke="currentColor" stroke-linecap="round"/></svg>
              </button>
              <div class="header_pick_drop">
                <button
                  v-for="l in langs"
                  :key="l"
                  type="button"
                  class="header_pick_opt"
                  :class="{ 'is-active': l === lang }"
                  @click="setLang(l)"
                >{{ l }}</button>
              </div>
            </div>
            <div class="header_pick">
              <button type="button" class="header_pick_btn">
                {{ currency }}
                <svg width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M0.5 0.5L4 4L7.5 0.5" stroke="currentColor" stroke-linecap="round"/></svg>
              </button>
              <div class="header_pick_drop">
                <button
                  v-for="c in currencyList"
                  :key="c.code"
                  type="button"
                  class="header_pick_opt"
                  :class="{ 'is-active': c.code === currency }"
                  @click="setCurrency(c.code)"
                >{{ c.code }}</button>
              </div>
            </div>
          </div>
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
.header_locale {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-left: 20px;
}
.header_pick {
  position: relative;
}
.header_pick_btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.07);
  color: rgba(255, 255, 255, 0.75);
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s, color 0.2s;
}
.header_pick_btn:hover {
  background: rgba(255, 255, 255, 0.13);
  color: #fff;
}
.header_pick_drop {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  background: #06080f;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.8);
  padding: 6px;
  min-width: 72px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(4px);
  transition: opacity 0.18s, transform 0.18s, visibility 0.18s;
  z-index: 30;
}
.header_pick:hover .header_pick_drop {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}
.header_pick_opt {
  display: block;
  width: 100%;
  text-align: left;
  padding: 6px 10px;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.65);
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
}
.header_pick_opt:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
}
.header_pick_opt.is-active {
  color: #2bff95;
}
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
  margin-left: 14px;
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
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
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
.header_nav_item--dropdown:hover .header_nav_dropdown,
.header_nav_item--dropdown.is-open .header_nav_dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}
.header_nav_link--button {
  background: transparent;
  border: 0;
  padding: 0;
  font: inherit;
  color: inherit;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
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

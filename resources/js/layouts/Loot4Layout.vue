<script setup>
import { onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppHeader from '@/loot4/components/layout/AppHeader.vue'
import HeaderBackground from '@/loot4/components/layout/HeaderBackground.vue'
import AppFooter from '@/loot4/components/layout/AppFooter.vue'
import CartDrawer from '@/loot4/components/cart/CartDrawer.vue'
import ContactWidget from '@/loot4/components/layout/ContactWidget.vue'
import CookieConsent from '@/loot4/components/layout/CookieConsent.vue'
import { useMobileMenu } from '@/loot4/composables/useMobileMenu'
import { useCart } from '@/loot4/composables/useCart'
import { setRates } from '@/loot4/composables/useLocale'
import { captureAttribution } from '@/loot4/composables/useAttribution'

const page = usePage()
const { closeMenu } = useMobileMenu()
const { hydrate: hydrateCart } = useCart()

// Load the persisted cart on the client only, after hydration. The landing
// URL (utm/gclid/fbclid) is snapshotted here for order attribution.
onMounted(() => {
  hydrateCart()
  captureAttribution()
})

watch(() => page.url, () => closeMenu())

// Sync live exchange rates from the server on every navigation.
watch(() => page.props.exchangeRates, setRates, { immediate: true })
</script>

<template>
  <AppHeader />
  <HeaderBackground />
  <main>
    <slot />
  </main>
  <AppFooter />
  <CartDrawer />
  <ContactWidget />
  <CookieConsent />
</template>

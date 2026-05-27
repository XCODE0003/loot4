<script setup>
import { watch } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import HeaderBackground from '@/components/layout/HeaderBackground.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import { useMobileMenu } from '@/composables/useMobileMenu'

const route = useRoute()
const { closeMenu } = useMobileMenu()

watch(() => route.path, () => closeMenu())
</script>

<template>
  <AppHeader />
  <HeaderBackground />
  <main>
    <RouterView />
  </main>
  <AppFooter />
</template>

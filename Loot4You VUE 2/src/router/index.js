import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue'),
      meta: { title: 'Loot4you' },
    },
    {
      path: '/game',
      name: 'game',
      component: () => import('@/views/GameView.vue'),
      meta: { title: 'Choose Game — Loot4you' },
    },
    {
      path: '/product/:slug?',
      name: 'product',
      component: () => import('@/views/ProductView.vue'),
      meta: { title: 'Product — Loot4you' },
    },
  ],
  scrollBehavior(to) {
    if (to.hash) {
      return new Promise((resolve) => {
        requestAnimationFrame(() => {
          resolve({ el: to.hash, behavior: 'smooth', top: 0 })
        })
      })
    }
    return { top: 0 }
  },
})

router.afterEach((to) => {
  document.title = to.meta.title || 'Loot4you'
})

export default router

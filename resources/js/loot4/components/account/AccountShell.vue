<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Container from '@/loot4/components/layout/Container.vue'

defineProps({
  title: { type: String, default: 'My Account' },
})

const page = usePage()
const url = computed(() => page.url.split('?')[0])
const user = computed(() => page.props.auth?.user ?? null)

const nav = [
  { tkey: 'account.overview', href: '/account', icon: 'M3 12l9-9 9 9M5 10v10h14V10' },
  { tkey: 'account.myOrders', href: '/account/orders', icon: 'M3 3h2l2 12h10l2-8H6M9 20a1 1 0 100 2 1 1 0 000-2zm8 0a1 1 0 100 2 1 1 0 000-2z' },
  { tkey: 'account.profile', href: '/account/profile', icon: 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0' },
]

function isActive(href) {
  return href === '/account' ? url.value === '/account' : url.value.startsWith(href)
}
</script>

<template>
  <section class="acc">
    <Container>
      <h1 class="acc_title">{{ title }}</h1>

      <div class="acc_grid">
        <aside class="acc_nav">
          <div class="acc_user">
            <div class="acc_avatar">{{ (user?.name || '?').charAt(0).toUpperCase() }}</div>
            <div class="acc_user_meta">
              <p class="acc_user_name">{{ user?.name }}</p>
              <p class="acc_user_email">{{ user?.email }}</p>
            </div>
          </div>

          <nav class="acc_links">
            <Link
              v-for="link in nav"
              :key="link.href"
              :href="link.href"
              class="acc_link"
              :class="{ 'is-active': isActive(link.href) }"
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path :d="link.icon" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              {{ $t(link.tkey) }}
            </Link>
            <Link href="/logout" method="post" as="button" class="acc_link acc_link--logout">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              {{ $t('account.logout') }}
            </Link>
          </nav>
        </aside>

        <div class="acc_content">
          <slot />
        </div>
      </div>
    </Container>
  </section>
</template>

<style scoped>
.acc {
  padding: 56px 0 100px;
  min-height: 60vh;
}
.acc_title {
  color: #fff;
  font-size: 34px;
  font-weight: 700;
  margin-bottom: 28px;
}
.acc_grid {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 28px;
  align-items: start;
}
.acc_nav {
  padding: 20px;
  border-radius: 18px;
  background: #0b1020;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.acc_user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 18px;
  margin-bottom: 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  min-width: 0;
}
.acc_user_meta {
  min-width: 0;
  flex: 1;
}
.acc_avatar {
  display: grid;
  place-items: center;
  flex-shrink: 0;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0fa854, #2bff95);
  color: #fff;
  font-weight: 700;
  font-size: 18px;
}
.acc_user_name {
  color: #fff;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.acc_user_email {
  color: rgba(255, 255, 255, 0.45);
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.acc_links {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.acc_link {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 14px;
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
  cursor: pointer;
  text-align: left;
}
.acc_link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
}
.acc_link.is-active {
  background: rgba(15, 168, 84, 0.2);
  color: #fff;
}
.acc_link--logout {
  margin-top: 8px;
  color: #ff8c8c;
}
.acc_content {
  min-width: 0;
}
@media (max-width: 860px) {
  .acc_grid {
    grid-template-columns: 1fr;
  }
}
</style>

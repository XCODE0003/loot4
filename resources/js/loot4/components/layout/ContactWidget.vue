<script setup>
import { onMounted, ref } from 'vue'

// Teleport renders on the client only (SSR has no <body> target).
const mounted = ref(false)
const open = ref(false)

onMounted(() => {
  mounted.value = true
})

function toggle() {
  open.value = !open.value
}

const contacts = [
  {
    key: 'email',
    label: 'Email',
    desc: 'Fast, friendly, and reliable support anytime.',
    href: 'mailto:support@loot4you.gg',
    external: false,
  },
  {
    key: 'discord',
    label: 'Discord',
    desc: 'Join our Discord group for real-time support and community.',
    href: 'https://discord.gg/AyTrerusGZ',
    external: true,
  },
  {
    key: 'whatsapp',
    label: 'WhatsApp',
    desc: 'Quick, friendly, and hassle-free support on WhatsApp.',
    href: 'https://wa.me/380730882668',
    external: true,
  },
]
</script>

<template>
  <Teleport v-if="mounted" to="body">
    <transition name="cw-overlay">
      <div v-if="open" class="cw_overlay" @click="open = false" />
    </transition>
    <div class="cw">
      <transition name="cw-pop">
        <div v-if="open" class="cw_panel" role="dialog" aria-label="Contact us">
          <h3 class="cw_title">Contact Us</h3>
          <p class="cw_subtitle">Choose how you'd like to reach us</p>
          <div class="cw_list">
            <a
              v-for="c in contacts"
              :key="c.key"
              :href="c.href"
              :target="c.external ? '_blank' : undefined"
              :rel="c.external ? 'noopener noreferrer' : undefined"
              class="cw_item"
            >
              <span class="cw_item_icon">
                <svg v-if="c.key === 'email'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect width="20" height="16" x="2" y="4" rx="2" />
                  <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
                <svg v-else-if="c.key === 'discord'" width="18" height="18" viewBox="0 0 640 640" fill="currentColor">
                  <path d="M524.5 133.8C485.6 115.6 445.3 103.1 404 96c-.7-.1-1.4.2-1.8 1-5.5 9.9-10.5 20.2-14.9 30.6-44.6-6.8-89.9-6.8-134.4 0-4.5-10.5-9.5-20.7-15.1-30.6-.4-.7-1.1-1.1-1.8-1-41.3 7.1-81.6 19.6-119.7 37.1-.3.1-.6.4-.8.7C39.1 247.5 18.2 358.6 28.4 468.2c0 .3.2.6.5.8 44.4 32.9 94 58 146.8 74.2.7.2 1.5 0 1.9-.6 11.3-15.4 21.4-31.8 30-48.8.5-1-.1-2.2-1.1-2.6-16.5-6.3-32.2-13.9-47.3-22.8-1.1-.6-1.2-2.2-.2-3 3.2-2.4 6.4-4.9 9.4-7.4.5-.4 1.2-.5 1.8-.2 99.2 45.3 206.6 45.3 304.6 0 .6-.3 1.3-.2 1.8.2 3 2.5 6.1 5 9.4 7.4 1 .8.9 2.4-.2 3-15.1 8.9-30.8 16.5-47.3 22.8-1 .4-1.5 1.6-1 2.6 8.8 17 18.9 33.3 30 48.8.4.6 1.2.8 1.9.6 52.9-16.2 102.6-41.3 147-74.2.3-.2.5-.5.5-.8 12.2-126.7-20.6-236.8-87-341.4-.2-.3-.5-.6-.8-.7zM222.5 401.5c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.4 59.2-52.8 59.2zm195.4 0c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.2 59.2-52.8 59.2z" />
                </svg>
                <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884" />
                </svg>
              </span>
              <span class="cw_item_text">
                <span class="cw_item_title">{{ c.label }}</span>
                <span class="cw_item_desc">{{ c.desc }}</span>
              </span>
            </a>
          </div>
        </div>
      </transition>

      <button type="button" class="cw_fab" :class="{ 'is-open': open }" :aria-expanded="open" aria-label="Contact us" @click="toggle">
        <svg v-if="!open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
        </svg>
        <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
          <path d="M5 5l14 14M19 5L5 19" />
        </svg>
      </button>
    </div>
  </Teleport>
</template>

<style scoped>
.cw_overlay {
  position: fixed;
  inset: 0;
  z-index: 899;
  background: transparent;
}
.cw {
  position: fixed;
  right: 24px;
  bottom: 24px;
  z-index: 900;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
.cw_fab {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: #1c1c24;
  color: #fff;
  display: grid;
  place-items: center;
  cursor: pointer;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.5);
  transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}
.cw_fab:hover {
  transform: translateY(-2px);
  border-color: rgba(43, 255, 149, 0.4);
}
.cw_fab.is-open {
  background: #26262f;
}
.cw_panel {
  position: absolute;
  bottom: 70px;
  right: 0;
  width: 360px;
  max-width: calc(100vw - 32px);
  padding: 22px;
  border-radius: 20px;
  background: #0e0f15;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.6);
}
.cw_title {
  margin: 0;
  text-align: center;
  font-family: var(--font-family);
  font-size: 20px;
  font-weight: 700;
  color: #fff;
}
.cw_subtitle {
  margin: 4px 0 16px;
  text-align: center;
  font-family: var(--font-family);
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
}
.cw_list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.cw_item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.06);
  transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease;
}
.cw_item:hover {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(43, 255, 149, 0.35);
  transform: translateY(-1px);
}
.cw_item_icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: rgba(255, 255, 255, 0.07);
  color: #fff;
}
.cw_item_text {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}
.cw_item_title {
  font-family: var(--font-family);
  font-size: 15px;
  font-weight: 600;
  color: #fff;
}
.cw_item_desc {
  font-family: var(--font-family);
  font-size: 12.5px;
  line-height: 1.45;
  color: rgba(255, 255, 255, 0.5);
}

.cw-pop-enter-active,
.cw-pop-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
  transform-origin: bottom right;
}
.cw-pop-enter-from,
.cw-pop-leave-to {
  opacity: 0;
  transform: translateY(10px) scale(0.96);
}
.cw-overlay-enter-active,
.cw-overlay-leave-active {
  transition: opacity 0.2s ease;
}
.cw-overlay-enter-from,
.cw-overlay-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .cw {
    right: 16px;
    bottom: 16px;
  }
  .cw_fab {
    width: 50px;
    height: 50px;
  }
}
</style>

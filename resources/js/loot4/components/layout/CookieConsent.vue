<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const gaId = computed(() => page.props.gaId ?? null)

const CONSENT_COOKIE = 'cookie_consent'
const visible = ref(false)

function readConsent() {
  const match = document.cookie.match(new RegExp('(?:^|; )' + CONSENT_COOKIE + '=([^;]*)'))
  return match ? decodeURIComponent(match[1]) : null
}

function writeConsent(value) {
  const oneYear = 60 * 60 * 24 * 365
  document.cookie = `${CONSENT_COOKIE}=${value}; path=/; max-age=${oneYear}; SameSite=Lax`
}

// Inject Google Analytics (gtag) once, only after consent and only if configured.
function loadAnalytics() {
  if (!gaId.value || window.__gaLoaded) return
  window.__gaLoaded = true

  const s = document.createElement('script')
  s.async = true
  s.src = `https://www.googletagmanager.com/gtag/js?id=${gaId.value}`
  document.head.appendChild(s)

  window.dataLayer = window.dataLayer || []
  window.gtag = function gtag() {
    window.dataLayer.push(arguments)
  }
  window.gtag('js', new Date())
  window.gtag('config', gaId.value, { anonymize_ip: true })
}

function accept() {
  writeConsent('accepted')
  visible.value = false
  loadAnalytics()
}

function decline() {
  writeConsent('declined')
  visible.value = false
}

onMounted(() => {
  const consent = readConsent()
  if (consent === 'accepted') {
    loadAnalytics()
  } else if (consent !== 'declined') {
    visible.value = true
  }
})
</script>

<template>
  <Transition name="cc">
    <div v-if="visible" class="cc" role="dialog" aria-label="Cookie consent">
      <div class="cc_inner">
        <div class="cc_text">
          <p class="cc_title">We use cookies 🍪</p>
          <p class="cc_body">
            We use cookies to improve your experience, analyse traffic and for marketing. Read our
            <Link href="/cookies-policy" class="cc_link">Cookies Policy</Link>.
          </p>
        </div>
        <div class="cc_actions">
          <button type="button" class="cc_btn cc_btn--ghost" @click="decline">Decline</button>
          <button type="button" class="cc_btn cc_btn--accept" @click="accept">Accept all</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.cc {
  position: fixed;
  left: 16px;
  right: 16px;
  bottom: 16px;
  /* Below the cart drawer (1000/1001) and the open mobile menu (1100) so
     those modals stay usable while the banner is ignored, above page content. */
  z-index: 999;
  display: flex;
  justify-content: center;
  /* The full-width wrapper must not swallow clicks on its empty sides. */
  pointer-events: none;
}
.cc_inner {
  width: 100%;
  max-width: 760px;
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 18px 22px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  background: rgba(10, 10, 14, 0.96);
  backdrop-filter: blur(12px);
  box-shadow: 0 16px 50px rgba(0, 0, 0, 0.6);
  pointer-events: auto;
}
.cc_title {
  color: #fff;
  font-weight: 600;
  font-size: 15px;
  margin-bottom: 4px;
}
.cc_body {
  color: rgba(255, 255, 255, 0.55);
  font-size: 13px;
  line-height: 1.6;
}
.cc_link {
  display: inline;
  color: #2bff95;
  text-decoration: none;
}
.cc_link:hover {
  text-decoration: underline;
}
.cc_actions {
  display: flex;
  gap: 10px;
  flex-shrink: 0;
}
.cc_btn {
  padding: 11px 20px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  white-space: nowrap;
  border: 0;
}
.cc_btn--ghost {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: rgba(255, 255, 255, 0.8);
}
.cc_btn--ghost:hover {
  background: rgba(255, 255, 255, 0.06);
}
.cc_btn--accept {
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  color: #fff;
}
.cc_btn--accept:hover {
  opacity: 0.9;
}
.cc-enter-active,
.cc-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.cc-enter-from,
.cc-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

@media (max-width: 640px) {
  .cc_inner {
    flex-direction: column;
    align-items: stretch;
    gap: 14px;
  }
  .cc_actions {
    justify-content: stretch;
  }
  .cc_btn {
    flex: 1;
  }
}
</style>

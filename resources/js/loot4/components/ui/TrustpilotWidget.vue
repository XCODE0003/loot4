<script setup>
import { onMounted, ref } from 'vue'

// Trustpilot TrustBox. Template IDs are public Trustpilot constants; the
// businessUnitId is Loot4You's (from trustpilot.com/review/loot4you.gg).
const props = defineProps({
  templateId: { type: String, default: '53aa8807dec7e10d38f59f32' }, // "Mini"
  businessUnitId: { type: String, default: '6a10bc9e35e14f13e8954dce' },
  height: { type: String, default: '130px' },
  width: { type: String, default: '100%' },
  theme: { type: String, default: 'dark' },
  locale: { type: String, default: 'en-US' },
  reviewUrl: { type: String, default: 'https://www.trustpilot.com/review/loot4you.gg' },
})

const SCRIPT_SRC = 'https://widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js'
const box = ref(null)

function ensureScript() {
  return new Promise((resolve) => {
    if (window.Trustpilot) return resolve()
    const existing = document.querySelector(`script[src="${SCRIPT_SRC}"]`)
    if (existing) {
      existing.addEventListener('load', () => resolve(), { once: true })
      if (window.Trustpilot) resolve()
      return
    }
    const script = document.createElement('script')
    script.src = SCRIPT_SRC
    script.async = true
    script.addEventListener('load', () => resolve(), { once: true })
    document.head.appendChild(script)
  })
}

onMounted(async () => {
  await ensureScript()
  if (window.Trustpilot && box.value) {
    window.Trustpilot.loadFromElement(box.value, true)
  }
})
</script>

<template>
  <div
    ref="box"
    class="trustpilot-widget"
    :data-locale="locale"
    :data-template-id="templateId"
    :data-businessunit-id="businessUnitId"
    :data-style-height="height"
    :data-style-width="width"
    :data-theme="theme"
  >
    <a :href="reviewUrl" target="_blank" rel="noopener noreferrer">Trustpilot</a>
  </div>
</template>

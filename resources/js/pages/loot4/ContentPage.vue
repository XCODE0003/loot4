<script setup>
import { Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'

defineProps({
  title: { type: String, required: true },
  updated: { type: String, default: null },
  blocks: { type: Array, default: () => [] },
})

// Static (non-user) SVG markup for the contact cards — safe to render via v-html.
const CONTACT_ICONS = {
  discord:
    '<svg viewBox="0 0 640 640" fill="currentColor"><path d="M524.5 133.8C485.6 115.6 445.3 103.1 404 96c-.7-.1-1.4.2-1.8 1-5.5 9.9-10.5 20.2-14.9 30.6-44.6-6.8-89.9-6.8-134.4 0-4.5-10.5-9.5-20.7-15.1-30.6-.4-.7-1.1-1.1-1.8-1-41.3 7.1-81.6 19.6-119.7 37.1-.3.1-.6.4-.8.7C39.1 247.5 18.2 358.6 28.4 468.2c0 .3.2.6.5.8 44.4 32.9 94 58 146.8 74.2.7.2 1.5 0 1.9-.6 11.3-15.4 21.4-31.8 30-48.8.5-1-.1-2.2-1.1-2.6-16.5-6.3-32.2-13.9-47.3-22.8-1.1-.6-1.2-2.2-.2-3 3.2-2.4 6.4-4.9 9.4-7.4.5-.4 1.2-.5 1.8-.2 99.2 45.3 206.6 45.3 304.6 0 .6-.3 1.3-.2 1.8.2 3 2.5 6.1 5 9.4 7.4 1 .8.9 2.4-.2 3-15.1 8.9-30.8 16.5-47.3 22.8-1 .4-1.5 1.6-1 2.6 8.8 17 18.9 33.3 30 48.8.4.6 1.2.8 1.9.6 52.9-16.2 102.6-41.3 147-74.2.3-.2.5-.5.5-.8 12.2-126.7-20.6-236.8-87-341.4-.2-.3-.5-.6-.8-.7zM222.5 401.5c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.4 59.2-52.8 59.2zm195.4 0c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.2 59.2-52.8 59.2z"/></svg>',
  email:
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
  whatsapp:
    '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>',
}

function cardIcon(key) {
  return CONTACT_ICONS[key] ?? ''
}
</script>

<template>
  <Head :title="`${title} — Loot4you`" />
  <section class="page">
    <Container>
      <header class="page_head">
        <h1 class="page_title">{{ title }}</h1>
        <p v-if="updated" class="page_updated">{{ updated }}</p>
      </header>

      <article class="page_body">
        <template v-for="(block, i) in blocks" :key="i">
          <p v-if="block.type === 'lead'" class="page_lead">{{ block.text }}</p>
          <p v-else-if="block.type === 'p'" class="page_p">{{ block.text }}</p>
          <h2 v-else-if="block.type === 'h'" class="page_h">{{ block.text }}</h2>

          <ul v-else-if="block.type === 'list'" class="page_list">
            <li v-for="(item, j) in block.items" :key="j">{{ item }}</li>
          </ul>

          <figure v-else-if="block.type === 'image'" class="page_figure">
            <img :src="block.src" :alt="block.alt || ''" loading="lazy" />
          </figure>

          <div v-else-if="block.type === 'contact_cards'" class="page_cc">
            <a
              v-for="card in block.items"
              :key="card.key"
              :href="card.href"
              :target="card.external ? '_blank' : undefined"
              :rel="card.external ? 'noopener noreferrer' : undefined"
              class="page_cc_card"
            >
              <div class="page_cc_top">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <span class="page_cc_ic page_cc_ic--sm" aria-hidden="true" v-html="cardIcon(card.key)" />
                <span class="page_cc_top_text">
                  <span class="page_cc_top_title">{{ card.name }}</span>
                  <span class="page_cc_top_sub">{{ card.subtitle }}</span>
                </span>
              </div>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <span class="page_cc_ic page_cc_ic--lg" aria-hidden="true" v-html="cardIcon(card.key)" />
              <span class="page_cc_now">Chat with our support now</span>
              <span class="page_cc_hours">Available Monday–Friday, 8am–5pm</span>
              <span class="page_cc_name">{{ card.name }}</span>
              <span class="page_cc_chat">Chat with us now</span>
              <span class="page_cc_btn">{{ card.button }}</span>
              <span v-if="card.footer" class="page_cc_foot">Email: <strong>{{ card.footer }}</strong></span>
            </a>
          </div>

          <div v-else-if="block.type === 'steps'" class="page_steps">
            <div v-for="(step, j) in block.items" :key="j" class="page_step">
              <span class="page_step_n">{{ step.n }}</span>
              <div>
                <h3 class="page_step_title">{{ step.title }}</h3>
                <p class="page_step_text">{{ step.text }}</p>
              </div>
            </div>
          </div>

          <div v-else-if="block.type === 'stats'" class="page_stats">
            <div v-for="(stat, j) in block.items" :key="j" class="page_stat">
              <span class="page_stat_value">{{ stat.value }}</span>
              <span class="page_stat_label">{{ stat.label }}</span>
            </div>
          </div>

          <div v-else-if="block.type === 'contacts'" class="page_contacts">
            <div v-for="(c, j) in block.items" :key="j" class="page_contact">
              <span class="page_contact_label">{{ c.label }}</span>
              <a v-if="c.href" :href="c.href" class="page_contact_value page_contact_value--link">{{ c.value }}</a>
              <span v-else class="page_contact_value">{{ c.value }}</span>
            </div>
          </div>

          <div v-else-if="block.type === 'cta'" class="page_cta">
            <p class="page_cta_text">{{ block.text }}</p>
            <Link :href="block.href" class="page_cta_btn">{{ block.label }}</Link>
          </div>
        </template>
      </article>
    </Container>
  </section>
</template>

<style scoped>
.page {
  padding: 64px 0 100px;
  min-height: 60vh;
}
.page_head {
  margin-bottom: 36px;
}
.page_title {
  color: #fff;
  font-family: var(--font-family);
  font-size: 44px;
  font-weight: 700;
  letter-spacing: -0.02em;
}
.page_updated {
  margin-top: 10px;
  color: rgba(255, 255, 255, 0.4);
  font-size: 14px;
}
.page_body {
  max-width: 820px;
}
.page_lead {
  color: rgba(255, 255, 255, 0.85);
  font-size: 20px;
  line-height: 1.6;
  margin-bottom: 24px;
}
.page_p {
  color: rgba(255, 255, 255, 0.6);
  font-size: 16px;
  line-height: 1.75;
  margin-bottom: 18px;
}
.page_h {
  color: #fff;
  font-size: 22px;
  font-weight: 600;
  margin: 34px 0 14px;
}
.page_list {
  list-style: none;
  margin: 0 0 18px;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.page_list li {
  position: relative;
  padding-left: 22px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 16px;
  line-height: 1.65;
}
.page_list li::before {
  content: '';
  position: absolute;
  left: 2px;
  top: 9px;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #2bff95;
}
.page_figure {
  margin: 4px 0 30px;
}
.page_figure img {
  display: block;
  width: 100%;
  height: auto;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.page_cc {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
  margin: 10px 0 26px;
}
.page_cc_card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 22px 22px 26px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.07);
  transition: border-color 0.2s ease, transform 0.2s ease;
}
.page_cc_card:hover {
  border-color: rgba(43, 255, 149, 0.35);
  transform: translateY(-2px);
}
.page_cc_top {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  width: 100%;
  text-align: left;
}
.page_cc_ic {
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.07);
  color: #fff;
}
.page_cc_ic--sm {
  width: 36px;
  height: 36px;
}
.page_cc_ic--sm svg {
  width: 18px;
  height: 18px;
}
.page_cc_ic--lg {
  width: 66px;
  height: 66px;
  margin: 24px 0 16px;
}
.page_cc_ic--lg svg {
  width: 30px;
  height: 30px;
}
.page_cc_top_text {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}
.page_cc_top_title {
  font-size: 16px;
  font-weight: 700;
  color: #fff;
}
.page_cc_top_sub {
  font-size: 12.5px;
  line-height: 1.45;
  color: rgba(255, 255, 255, 0.5);
}
.page_cc_now {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
}
.page_cc_hours {
  margin-top: 4px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
}
.page_cc_name {
  margin-top: 16px;
  font-size: 20px;
  font-weight: 700;
  color: #fff;
}
.page_cc_chat {
  margin-top: 18px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.55);
}
.page_cc_btn {
  margin-top: 12px;
  display: inline-block;
  padding: 11px 30px;
  border-radius: 999px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
  font-weight: 700;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.page_cc_foot {
  margin-top: 16px;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.4);
}
.page_cc_foot strong {
  color: rgba(255, 255, 255, 0.7);
}
@media (max-width: 860px) {
  .page_cc {
    grid-template-columns: 1fr;
  }
}
.page_steps {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin: 8px 0 24px;
}
.page_step {
  display: flex;
  gap: 18px;
  padding: 22px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.02);
}
.page_step_n {
  flex-shrink: 0;
  font-size: 22px;
  font-weight: 700;
  color: #2bff95;
  width: 44px;
}
.page_step_title {
  color: #fff;
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 6px;
}
.page_step_text {
  color: rgba(255, 255, 255, 0.55);
  font-size: 15px;
  line-height: 1.7;
}
.page_stats {
  display: flex;
  flex-wrap: wrap;
  gap: 40px;
  margin: 36px 0 8px;
}
.page_stat {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.page_stat_value {
  font-size: 38px;
  font-weight: 700;
  background: linear-gradient(135deg, #2bff95, #0a6e8f);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
.page_stat_label {
  color: rgba(255, 255, 255, 0.5);
  font-size: 14px;
}
.page_contacts {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 8px;
}
.page_contact {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 16px 20px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.02);
}
.page_contact_label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.35);
}
.page_contact_value {
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
}
.page_contact_value--link {
  color: #2bff95;
  width: fit-content;
  transition: opacity 0.2s;
}
.page_contact_value--link:hover {
  opacity: 0.75;
}
.page_cta {
  margin-top: 36px;
  padding: 28px;
  border: 1px solid rgba(43, 255, 149, 0.18);
  border-radius: 16px;
  background: rgba(43, 255, 149, 0.04);
  display: flex;
  flex-direction: column;
  gap: 16px;
  align-items: flex-start;
}
.page_cta_text {
  color: rgba(255, 255, 255, 0.75);
  font-size: 16px;
}
.page_cta_btn {
  padding: 12px 28px;
  border-radius: 93px;
  background: radial-gradient(136.56% 99.31% at 37.02% 26.55%, #2bff95 0%, #054792 100%);
  box-shadow: inset 0 6px 32px 0 rgba(81, 255, 159, 0.25), inset 0 -4px 6px 0 rgba(0, 0, 0, 0.25);
  color: #fff;
  font-weight: 600;
  transition: opacity 0.2s;
}
.page_cta_btn:hover {
  opacity: 0.85;
}

@media (max-width: 768px) {
  .page {
    padding: 40px 0 72px;
  }
  .page_title {
    font-size: 32px;
  }
  .page_lead {
    font-size: 18px;
  }
  .page_stats {
    gap: 28px;
  }
}
</style>

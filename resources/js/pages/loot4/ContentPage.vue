<script setup>
import { Head, Link } from '@inertiajs/vue3'
import '@/loot4/assets/styles/style.css'
import Container from '@/loot4/components/layout/Container.vue'

defineProps({
  title: { type: String, required: true },
  updated: { type: String, default: null },
  blocks: { type: Array, default: () => [] },
})
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

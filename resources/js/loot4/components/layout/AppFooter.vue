<script setup>
import { Link } from '@inertiajs/vue3'
import { footerColumns, footerPayments } from '@/loot4/data/footer'
import { asset } from '@/loot4/utils/asset'

const year = new Date().getFullYear()
</script>

<template>
  <footer id="footer" class="footer">
    <div class="footer_glow" aria-hidden="true" />
    <div class="container footer_inner">
      <div class="footer_grid">
        <div class="footer_brand">
          <Link href="/" class="footer_brand_logo">
            <img :src="asset('header_logo.svg')" alt="Loot4You" />
          </Link>
          <p class="footer_brand_text">
            The fastest, safest way to buy gaming credits and coins.
            <span class="footer_brand_text_accent">Delivered in minutes — every time.</span>
          </p>
          <div class="footer_socials">
            <a class="footer_social" href="#" aria-label="Discord" target="_blank" rel="noopener noreferrer">
              <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path fill="currentColor" d="M524.5 133.8C485.6 115.6 445.3 103.1 404 96c-.7-.1-1.4.2-1.8 1-5.5 9.9-10.5 20.2-14.9 30.6-44.6-6.8-89.9-6.8-134.4 0-4.5-10.5-9.5-20.7-15.1-30.6-.4-.7-1.1-1.1-1.8-1-41.3 7.1-81.6 19.6-119.7 37.1-.3.1-.6.4-.8.7C39.1 247.5 18.2 358.6 28.4 468.2c0 .3.2.6.5.8 44.4 32.9 94 58 146.8 74.2.7.2 1.5 0 1.9-.6 11.3-15.4 21.4-31.8 30-48.8.5-1-.1-2.2-1.1-2.6-16.5-6.3-32.2-13.9-47.3-22.8-1.1-.6-1.2-2.2-.2-3 3.2-2.4 6.4-4.9 9.4-7.4.5-.4 1.2-.5 1.8-.2 99.2 45.3 206.6 45.3 304.6 0 .6-.3 1.3-.2 1.8.2 3 2.5 6.1 5 9.4 7.4 1 .8.9 2.4-.2 3-15.1 8.9-30.8 16.5-47.3 22.8-1 .4-1.5 1.6-1 2.6 8.8 17 18.9 33.3 30 48.8.4.6 1.2.8 1.9.6 52.9-16.2 102.6-41.3 147-74.2.3-.2.5-.5.5-.8 12.2-126.7-20.6-236.8-87-341.4-.2-.3-.5-.6-.8-.7zM222.5 401.5c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.4 59.2-52.8 59.2zm195.4 0c-29 0-52.8-26.6-52.8-59.2s23.4-59.2 52.8-59.2c29.7 0 53.3 26.8 52.8 59.2 0 32.7-23.2 59.2-52.8 59.2z" />
              </svg>
            </a>
            <a class="footer_social" href="#" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
              <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path fill="currentColor" d="M320.3 205C256.8 204.8 205.2 256.2 205 319.7 204.8 383.2 256.2 434.8 319.7 435 383.2 435.2 434.8 383.8 435 320.3 435.2 256.8 383.8 205.2 320.3 205zm-.6 230c-41.2.2-74.7-33.1-74.9-74.3-.2-41.2 33.1-74.7 74.3-74.9 41.2-.2 74.7 33.1 74.9 74.3.2 41.2-33.1 74.7-74.3 74.9zm93.4-200.7c0-14.8 12-26.8 26.8-26.8 14.8 0 26.8 12 26.8 26.8 0 14.8-12 26.8-26.8 26.8-14.8 0-26.8-12-26.8-26.8zm129.7 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1-26.3 26.2-34.4 58-36.2 93.9-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9 26.3 26.2 58 34.4 93.9 36.2 37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM495 452c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.6 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
              </svg>
            </a>
            <a class="footer_social" href="#" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer">
              <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
              </svg>
            </a>
          </div>
        </div>

        <div v-for="column in footerColumns" :key="column.title" class="footer_col">
          <p class="footer_col_title">{{ column.title }}</p>
          <nav class="footer_col_links">
            <a v-for="link in column.links" :key="link.to" :href="link.to" class="footer_col_link">
              {{ link.label }}
            </a>
          </nav>
        </div>
      </div>

      <div class="footer_divider" />

      <div class="footer_bottom">
        <div class="footer_payments">
          <img v-for="p in footerPayments" :key="p" :src="asset(p)" alt="" class="footer_payment" />
        </div>
        <p class="footer_copy">© {{ year }} Loot4You — All rights reserved.</p>
        <p class="footer_disclaimer">
          Loot4You is an independent platform not affiliated with, endorsed by, or sponsored by any video
          game developers or publishers, including Rockstar Games, Take-Two Interactive, Riot Games,
          Activision Blizzard, Electronic Arts, Epic Games, Tencent, Bungie, Grinding Gear Games, Square
          Enix, Valve, Battlestate Games, Wargaming.net, Amazon, Jagex, Smilegate RPG, Digital Extremes,
          and Ubisoft. All trademarks, game titles, logos and images featured on this website are property
          of their respective publishers and owners. The views and opinions expressed by Loot4You do not
          reflect those of any game developers or publishers.
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  position: relative;
  width: 100%;
  margin-top: 120px;
  padding-bottom: 24px;
  background: #0a0a0b;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  overflow: hidden;
}
.footer_glow {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 700px;
  max-width: 90%;
  height: 1px;
  pointer-events: none;
  background: linear-gradient(90deg, transparent, rgba(43, 255, 149, 0.4), rgba(5, 71, 146, 0.3), transparent);
}
.footer_inner {
  padding-top: 56px;
}
.footer_grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 40px;
}
.footer_brand_logo {
  display: inline-flex;
  transition: opacity 0.3s;
}
.footer_brand_logo:hover {
  opacity: 0.7;
}
.footer_brand_logo img {
  height: 30px;
  width: auto;
}
.footer_brand_text {
  margin-top: 20px;
  max-width: 280px;
  font-family: var(--font-family);
  font-size: 13px;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.38);
}
.footer_brand_text_accent {
  color: rgba(43, 255, 149, 0.6);
}
.footer_socials {
  display: flex;
  gap: 8px;
  margin-top: 24px;
}
.footer_social {
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.07);
  background: rgba(255, 255, 255, 0.03);
  color: rgba(255, 255, 255, 0.32);
  transition: color 0.2s, border-color 0.2s, background 0.2s;
}
.footer_social:hover {
  color: rgba(255, 255, 255, 0.7);
  border-color: rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.06);
}
.footer_col_title {
  font-family: var(--font-family);
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: rgba(255, 255, 255, 0.28);
  margin-bottom: 18px;
}
.footer_col_links {
  display: flex;
  flex-direction: column;
  gap: 11px;
}
.footer_col_link {
  width: fit-content;
  font-family: var(--font-family);
  font-size: 13px;
  color: rgba(255, 255, 255, 0.42);
  transition: color 0.2s;
}
.footer_col_link:hover {
  color: rgba(255, 255, 255, 0.78);
}
.footer_divider {
  height: 1px;
  margin: 40px 0 0;
  background: rgba(255, 255, 255, 0.05);
}
.footer_bottom {
  padding-top: 28px;
}
.footer_payments {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 12px;
  opacity: 0.45;
}
.footer_payment {
  height: 24px;
  width: auto;
  object-fit: contain;
}
.footer_copy {
  margin-top: 24px;
  text-align: center;
  font-family: var(--font-family);
  font-size: 11px;
  color: rgba(255, 255, 255, 0.22);
}
.footer_disclaimer {
  margin-top: 14px;
  text-align: center;
  font-family: var(--font-family);
  font-size: 11px;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.13);
}

@media (max-width: 1000px) {
  .footer_grid {
    grid-template-columns: 1fr 1fr;
    gap: 32px;
  }
  .footer_brand {
    grid-column: 1 / -1;
  }
}
@media (max-width: 768px) {
  .footer {
    margin-top: 80px;
  }
  .footer_inner {
    padding-top: 40px;
  }
}
@media (max-width: 480px) {
  .footer_grid {
    grid-template-columns: 1fr;
    gap: 28px;
  }
}
</style>

<script setup>
const props = defineProps({
  reviewUrl: { type: String, default: 'https://www.trustpilot.com/review/loot4you.gg' },
  ratingText: { type: String, default: 'Excellent 4.9 out of 5.0' },
})
</script>

<template>
  <div class="catalog-hero__trustpilot-mobile">
    <a :href="reviewUrl" target="_blank" rel="noopener noreferrer" class="catalog-hero__trustpilot shadow-bg">
      <div class="catalog-hero__trustpilot-left">{{ ratingText }}</div>
      <div class="catalog-hero__trustpilot-right">
        <div class="catalog-hero__trustpilot-stars" aria-hidden="true">
          <svg width="93" height="18" viewBox="0 0 93 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_trustpilot_stars)">
              <path d="M0 0.00439453H17.4375V17.9954H0V0.00439453ZM18.8904 0.00439453H36.3279V17.9954H18.8904V0.00439453ZM37.7816 0.00439453H55.2184V17.9954H37.7824L37.7816 0.00439453ZM56.6713 0.00439453H74.1103V17.9954H56.6721L56.6713 0.00439453ZM75.5625 0.00439453H93V17.9954H75.5625V0.00439453Z" fill="#00B67A" />
              <path d="M8.71915 12.1298L11.3712 11.436L12.4788 14.9595L8.71915 12.1298ZM14.8221 7.57578H10.1538L8.71915 3.04053L7.28374 7.57578H2.61621L6.39415 10.3868L4.95874 14.922L8.73744 12.111L11.0624 10.3868L14.8221 7.57578ZM27.6096 12.1298L30.2616 11.436L31.37 14.9595L27.6096 12.1298ZM33.7125 7.57578H29.045L27.6096 3.04053L26.1749 7.57578H21.5066L25.2846 10.3868L23.8499 14.922L27.6279 12.111L29.9529 10.3868L33.7125 7.57578ZM46.5 12.1298L49.1528 11.436L50.2604 14.9595L46.5 12.1298ZM52.6037 7.57578H47.9354L46.5 3.04053L45.0654 7.57578H40.3971L44.175 10.3868L42.7404 14.922L46.5183 12.111L48.8433 10.3868L52.6037 7.57578ZM65.3912 12.1298L68.0425 11.436L69.1509 14.9595L65.3912 12.1298ZM71.4941 7.57578H66.8259L65.3912 3.04053L63.9558 7.57578H59.2875L63.0662 10.3868L61.6308 14.922L65.4087 12.111L67.7337 10.3868L71.4941 7.57578ZM84.2817 12.1298L86.9337 11.436L88.0413 14.9595L84.2817 12.1298ZM90.3846 7.57578H85.7163L84.2817 3.04053L82.8463 7.57578H78.1787L81.9566 10.3868L80.5212 14.922L84.2999 12.111L86.6249 10.3868L90.3846 7.57578Z" fill="white" />
            </g>
            <defs>
              <clipPath id="clip0_trustpilot_stars">
                <rect width="93" height="18" fill="white" />
              </clipPath>
            </defs>
          </svg>
        </div>
        <div class="catalog-hero__trustpilot-brand">Trustpilot</div>
      </div>
    </a>
  </div>
</template>

<style scoped>
.catalog-hero__trustpilot-mobile {
  margin-top: 20px;
}

.catalog-hero__trustpilot {
  border-radius: 16px;
  align-self: flex-start;
  align-items: center;
  padding: 16px 20px 18px;
  display: inline-flex;
  background: #15142b33;
}

.shadow-bg {
  position: relative;
  backdrop-filter: blur(20px) saturate(1.4);
  border: none;
}

.shadow-bg::after {
  content: '';
  border-radius: inherit;
  pointer-events: none;
  background: linear-gradient(135deg, #ffffff59 0%, #ffffff14 30%, #ffffff08 50%, #ffffff14 70%, #ffffff40 100%);
  padding: 1px;
  position: absolute;
  inset: 0;
  -webkit-mask-image: linear-gradient(#fff 0 0), linear-gradient(#fff 0 0);
  mask-image: linear-gradient(#fff 0 0), linear-gradient(#fff 0 0);
  -webkit-mask-clip: content-box, border-box;
  mask-clip: content-box, border-box;
  -webkit-mask-origin: content-box, border-box;
  mask-origin: content-box, border-box;
  -webkit-mask-composite: xor;
  mask-composite: exclude;
}

.catalog-hero__trustpilot-left {
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.3;
}

.catalog-hero__trustpilot-right {
  flex-shrink: 0;
  align-items: center;
  margin-left: 12px;
  display: flex;
}

.catalog-hero__trustpilot-stars {
  margin-right: 8px;
  display: flex;
}

.catalog-hero__trustpilot-brand {
  color: #fff;
  font-size: 15px;
  font-weight: 700;
  line-height: 1;
}

@media (max-width: 767px) {
  .catalog-hero__trustpilot-mobile .catalog-hero__trustpilot {
    justify-content: center;
    width: 100%;
    display: flex;
  }

  .catalog-hero__trustpilot {
    padding: 20px 18px;
  }
}
</style>

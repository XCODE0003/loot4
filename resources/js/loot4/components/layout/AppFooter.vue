<script setup>
import { useId } from 'vue'
import { Link } from '@inertiajs/vue3'
import { footerColumns } from '@/loot4/data/footer'
import { asset } from '@/loot4/utils/asset'
import FooterUpSocial from '@/loot4/components/layout/footer/FooterUpSocial.vue'
import FooterBottomSocial from '@/loot4/components/layout/footer/FooterBottomSocial.vue'

const uid = useId().replace(/:/g, '')
const clipPhone = `clip-${uid}-1129`
</script>

<template>
  <footer id="footer">
    <div class="container">
      <div class="footer_up">
        <div class="footer_up_item">
          <img :src="asset('intro_trust.png')" alt="" class="footer_up_item_trust" />
          <Link href="/" class="footer_up_item_logo">
            <img :src="asset('header_logo.svg')" alt="" />
          </Link>
        </div>
        <div class="footer_up_item">
          <FooterUpSocial />
        </div>
      </div>
      <div class="footer_main">
        <div class="footer_main_item">
          <h4 class="footer_main_item_title">Get in Touch</h4>
          <ul class="footer_main_contacts">
            <li class="footer_main_contact">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M5 14.2864C3.14864 15.1031 2 16.2412 2 17.5C2 19.9853 6.47715 22 12 22C17.5228 22 22 19.9853 22 17.5C22 16.2412 20.8514 15.1031 19 14.2864M18 8C18 12.0637 13.5 14 12 17C10.5 14 6 12.0637 6 8C6 4.68629 8.68629 2 12 2C15.3137 2 18 4.68629 18 8ZM13 8C13 8.55228 12.5523 9 12 9C11.4477 9 11 8.55228 11 8C11 7.44772 11.4477 7 12 7C12.5523 7 13 7.44772 13 8Z"
                  stroke="white"
                  stroke-opacity="0.54"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              1111B S Governors Ave STE 27835 <br />Dover, DE 19904 <br />United States
            </li>
            <li class="footer_main_contact">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g :clip-path="`url(#${clipPhone})`">
                  <path
                    d="M8.45059 7.29866C9.14659 8.74828 10.0954 10.1069 11.297 11.3085C12.4985 12.5101 13.8572 13.4589 15.3068 14.1549C15.4315 14.2147 15.4938 14.2447 15.5727 14.2677C15.8531 14.3494 16.1973 14.2907 16.4348 14.1207C16.5016 14.0728 16.5587 14.0157 16.673 13.9014C17.0226 13.5518 17.1975 13.377 17.3732 13.2627C18.0361 12.8317 18.8907 12.8317 19.5536 13.2627C19.7294 13.377 19.9042 13.5518 20.2538 13.9014L20.4486 14.0962C20.9801 14.6277 21.2458 14.8934 21.3902 15.1788C21.6772 15.7463 21.6772 16.4166 21.3902 16.9842C21.2458 17.2695 20.9801 17.5353 20.4486 18.0667L20.291 18.2243C19.7614 18.754 19.4966 19.0188 19.1365 19.221C18.737 19.4455 18.1165 19.6068 17.6583 19.6054C17.2454 19.6042 16.9632 19.5241 16.3987 19.3639C13.3653 18.5029 10.503 16.8785 8.11497 14.4905C5.72699 12.1025 4.10252 9.24014 3.24155 6.20675C3.08134 5.6423 3.00124 5.36008 3.00001 4.94713C2.99864 4.48891 3.16001 3.86842 3.38443 3.46891C3.58668 3.10888 3.85149 2.84407 4.38111 2.31445L4.53874 2.15681C5.07019 1.62537 5.33591 1.35965 5.62129 1.2153C6.18885 0.928232 6.85912 0.928232 7.42668 1.2153C7.71206 1.35965 7.97778 1.62537 8.50922 2.15681L8.70409 2.35168C9.0537 2.70129 9.2285 2.87609 9.34279 3.05187C9.77378 3.71476 9.77378 4.56934 9.34278 5.23223C9.2285 5.40801 9.0537 5.58281 8.70409 5.93242C8.58978 6.04673 8.53262 6.10388 8.48478 6.1707C8.31477 6.40813 8.25607 6.75239 8.33779 7.03274C8.36079 7.11163 8.39072 7.17397 8.45059 7.29866Z"
                    stroke="white"
                    stroke-opacity="0.54"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </g>
                <defs>
                  <clipPath :id="clipPhone">
                    <rect width="24" height="24" fill="white" />
                  </clipPath>
                </defs>
              </svg>
              +41 91 239 14 85
            </li>
            <li class="footer_main_contact">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M2 7L10.1649 12.7154C10.8261 13.1783 11.1567 13.4097 11.5163 13.4993C11.8339 13.5785 12.1661 13.5785 12.4837 13.4993C12.8433 13.4097 13.1739 13.1783 13.8351 12.7154L22 7M6.8 20H17.2C18.8802 20 19.7202 20 20.362 19.673C20.9265 19.3854 21.3854 18.9265 21.673 18.362C22 17.7202 22 16.8802 22 15.2V8.8C22 7.11984 22 6.27976 21.673 5.63803C21.3854 5.07354 20.9265 4.6146 20.362 4.32698C19.7202 4 18.8802 4 17.2 4H6.8C5.11984 4 4.27976 4 3.63803 4.32698C3.07354 4.6146 2.6146 5.07354 2.32698 5.63803C2 6.27976 2 7.11984 2 8.8V15.2C2 16.8802 2 17.7202 2.32698 18.362C2.6146 18.9265 3.07354 19.3854 3.63803 19.673C4.27976 20 5.11984 20 6.8 20Z"
                  stroke="white"
                  stroke-opacity="0.54"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              hello@evomi.com
            </li>
          </ul>
        </div>
        <div v-for="column in footerColumns" :key="column.title" class="footer_main_item">
          <h4 class="footer_main_item_title">{{ column.title }}</h4>
          <ul class="footer_main_item_navs">
            <li v-for="link in column.links" :key="link" class="footer_main_item_nav">
              <a href="#!">{{ link }}</a>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer_bottom">
        <div class="footer_bottom_items">
          <div class="footer_bottom_item">
            <p class="footer_bottom_item_copy">© 2025 loot4you. All rights reserved.</p>
          </div>
          <div class="footer_bottom_item">
            <FooterBottomSocial />
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

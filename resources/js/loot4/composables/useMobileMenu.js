import { ref, onMounted, onUnmounted } from 'vue'

const MOBILE_BREAKPOINT = 1100
const menuOpen = ref(false)

export function useMobileMenu() {
  function openMenu() {
    menuOpen.value = true
    document.body.classList.add('menu-open')
  }

  function closeMenu() {
    menuOpen.value = false
    document.body.classList.remove('menu-open')
  }

  function toggleMenu() {
    menuOpen.value ? closeMenu() : openMenu()
  }

  function onKeydown(e) {
    if (e.key === 'Escape') closeMenu()
  }

  function onResize() {
    if (window.innerWidth > MOBILE_BREAKPOINT) closeMenu()
  }

  onMounted(() => {
    document.addEventListener('keydown', onKeydown)
    window.addEventListener('resize', onResize)
  })

  onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
    window.removeEventListener('resize', onResize)
    closeMenu()
  })

  return { menuOpen, openMenu, closeMenu, toggleMenu }
}

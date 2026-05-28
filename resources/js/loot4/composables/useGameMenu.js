import { ref } from 'vue'

const gameMenuOpen = ref(false)

export function useGameMenu() {
  function openGameMenu() {
    gameMenuOpen.value = true
  }

  function closeGameMenu() {
    gameMenuOpen.value = false
  }

  function toggleGameMenu() {
    gameMenuOpen.value = !gameMenuOpen.value
  }

  function openGameMenuFromAnywhere() {
    if (typeof window !== 'undefined') {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
    openGameMenu()
  }

  return {
    gameMenuOpen,
    openGameMenu,
    closeGameMenu,
    toggleGameMenu,
    openGameMenuFromAnywhere,
  }
}

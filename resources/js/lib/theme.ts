import { computed, ref } from 'vue'

type Theme = 'light' | 'dark'

const THEME_STORAGE_KEY = 'budgetbeacon-theme'
const theme = ref<Theme>('light')

const resolveTheme = (): Theme => {
  const storedTheme = window.localStorage.getItem(THEME_STORAGE_KEY)
  if (storedTheme === 'light' || storedTheme === 'dark') {
    return storedTheme
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

const applyTheme = (nextTheme: Theme) => {
  theme.value = nextTheme
  document.documentElement.classList.toggle('dark', nextTheme === 'dark')
  window.localStorage.setItem(THEME_STORAGE_KEY, nextTheme)
  window.dispatchEvent(new CustomEvent('theme-changed', { detail: nextTheme }))
}

export const initTheme = () => {
  if (typeof window === 'undefined') {
    return
  }

  applyTheme(resolveTheme())
}

export const useTheme = () => {
  const toggleTheme = () => {
    applyTheme(theme.value === 'dark' ? 'light' : 'dark')
  }

  return {
    theme,
    isDark: computed(() => theme.value === 'dark'),
    setTheme: applyTheme,
    toggleTheme,
  }
}

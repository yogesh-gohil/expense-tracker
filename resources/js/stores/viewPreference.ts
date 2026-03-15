import { defineStore } from 'pinia'

type ViewMode = 'table' | 'card'

const STORAGE_KEYS = {
  expense: 'viewPreference.expense',
  income: 'viewPreference.income',
} as const

const isMobile = () => {
  if (typeof window === 'undefined') return false
  return window.matchMedia('(max-width: 767px)').matches
}

const normalize = (value: string | null): ViewMode | null => {
  if (value === 'table' || value === 'card') return value
  return null
}

const readStored = (key: string): ViewMode | null => {
  if (typeof window === 'undefined') return null
  return normalize(window.localStorage.getItem(key))
}

const writeStored = (key: string, value: ViewMode) => {
  if (typeof window === 'undefined') return
  window.localStorage.setItem(key, value)
}

export const useViewPreferenceStore = defineStore('viewPreference', {
  state: () => ({
    expenseView: 'table' as ViewMode,
    incomeView: 'table' as ViewMode,
  }),
  actions: {
    initExpenseView() {
      if (isMobile()) {
        this.expenseView = 'card'
        writeStored(STORAGE_KEYS.expense, this.expenseView)
        return
      }
      const stored = readStored(STORAGE_KEYS.expense)
      this.expenseView = stored ?? 'table'
      writeStored(STORAGE_KEYS.expense, this.expenseView)
    },
    initIncomeView() {
      if (isMobile()) {
        this.incomeView = 'card'
        writeStored(STORAGE_KEYS.income, this.incomeView)
        return
      }
      const stored = readStored(STORAGE_KEYS.income)
      this.incomeView = stored ?? 'table'
      writeStored(STORAGE_KEYS.income, this.incomeView)
    },
    setExpenseView(view: ViewMode) {
      this.expenseView = view
      writeStored(STORAGE_KEYS.expense, view)
    },
    setIncomeView(view: ViewMode) {
      this.incomeView = view
      writeStored(STORAGE_KEYS.income, view)
    },
  },
})

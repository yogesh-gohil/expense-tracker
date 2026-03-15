<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch, nextTick } from 'vue'
import { useCopilotStore } from '@/js/stores/copilot'
import { useCategoryStore } from '@/js/stores/category'
import { useExpenseStore } from '@/js/stores/expense'
import { useIncomeStore } from '@/js/stores/income'
import { useAuthStore } from '@/js/stores/auth'
import { useToast } from '@/js/components/ui/toast/use-toast'
import { Button } from '@/js/components/ui/button'
import { Badge } from '@/js/components/ui/badge'
import { Sparkles, Loader2, ArrowRight, X, Square, Command } from 'lucide-vue-next'
import { formatCurrencyFromCents } from '@/js/lib/currency'
import TypingTip from '@/js/components/copilot/TypingTip.vue'

const copilotStore = useCopilotStore()
const categoryStore = useCategoryStore()
const expenseStore = useExpenseStore()
const incomeStore = useIncomeStore()
const authStore = useAuthStore()
const { toast } = useToast()

const inputRef = ref<HTMLInputElement | null>(null)
const progressIndex = ref(0)
let progressTimer: ReturnType<typeof setInterval> | null = null

const result = computed(() => copilotStore.result)
const isExpense = computed(() => result.value?.type !== 'income')
const typeLabel = computed(() => (isExpense.value ? 'Expense' : 'Income'))

const today = () => new Date().toISOString().slice(0, 10)
const isIsoDate = (value?: string | null) => !!value && /^\d{4}-\d{2}-\d{2}$/.test(value)
const resolvedDate = computed(() => (isIsoDate(result.value?.date) ? result.value?.date : today()))

const amountCents = computed(() => {
  const amount = Number(result.value?.amount)
  if (!Number.isFinite(amount)) return 0
  return Math.round(amount * 100)
})

const categoryName = computed(() => result.value?.category_match?.name?.trim() || result.value?.category?.trim() || '')
const categoryMatchId = computed(() => result.value?.category_match?.id || null)
const isNewCategory = computed(() => !!categoryName.value && result.value?.category_match?.exists === false)

const toTitleCase = (value: string) =>
  value
    .toLowerCase()
    .replace(/\b([a-z])/g, (match) => match.toUpperCase())

const cleanPrompt = (prompt: string) =>
  prompt
    .replace(/[$€£₹]\s*\d+(?:[.,]\d+)?/g, '')
    .replace(/\b\d+(?:[.,]\d+)?\b/g, '')
    .replace(/\b(?:spent|spend|paid|pay|purchase|bought|earn|earned|income|salary|received|receive|from|at|for|on|to)\b/gi, '')
    .replace(/\s+/g, ' ')
    .trim()

const makeSmartTitle = () => {
  const vendor = result.value?.vendor?.trim()
  const source = result.value?.source?.trim()
  const category = categoryName.value

  if (result.value?.type === 'income') {
    if (source) return `Income from ${toTitleCase(source)}`
    if (category) return `${toTitleCase(category)} Income`
  }

  if (vendor) {
    if (category) return `${toTitleCase(category)} at ${toTitleCase(vendor)}`
    return `Purchase at ${toTitleCase(vendor)}`
  }

  if (category) {
    return result.value?.type === 'income'
      ? `${toTitleCase(category)} Income`
      : `${toTitleCase(category)} Expense`
  }

  const prompt = result.value?.raw_prompt?.trim() || ''
  const cleaned = cleanPrompt(prompt)
  if (cleaned) return toTitleCase(cleaned)

  return `${typeLabel.value} from Copilot`
}

const titleValue = computed(() => {
  const title = result.value?.title?.trim()
  if (title) return title
  return makeSmartTitle()
})

const descriptionValue = computed(() => {
  const description = result.value?.description?.trim()
  return description || null
})

const formattedAmount = computed(() => formatCurrencyFromCents(amountCents.value, authStore.currentUser?.currency))

const canCreate = computed(() => !!result.value && amountCents.value > 0 && !!categoryName.value && !!resolvedDate.value)

const analyzePrompt = async () => {
  if (copilotStore.isLoading) return
  if(result.value) {
    copilotStore.reset()
    await nextTick()
  }
  try {
    await copilotStore.copilot()
  } catch (_error) {
    // handled in store
  }
}

const createTransaction = async () => {
  if (!result.value) return

  let categoryId = categoryMatchId.value
  if (!categoryId && categoryName.value) {
    const res = await categoryStore.addCategory({
      name: categoryName.value,
      type: result.value.category_type,
      description: null,
    })
    categoryId = res?.data?.data?.id || null
  }

  if (!categoryId) {
    toast({
      title: 'Missing category',
      description: 'Please confirm or choose a category before creating.',
    })
    return
  }

  const payload = {
    title: titleValue.value,
    description: descriptionValue.value,
    amount: amountCents.value,
    date: resolvedDate.value,
    category_id: categoryId,
  }

  if (result.value.type === 'income') {
    await incomeStore.addIncome(payload)
  } else {
    await expenseStore.addExpense(payload)
  }

  toast({
    title: `${typeLabel.value} created`,
    description: `${typeLabel.value} has been added successfully.`,
  })

  closePalette()
}

const openInForm = () => {
  if (!result.value) return
  const payload = {
    title: titleValue.value,
    description: descriptionValue.value,
    amount: amountCents.value,
    date: resolvedDate.value,
    category_id: categoryMatchId.value || '',
  }

  if (result.value.type === 'income') {
    incomeStore.incomeData = { ...incomeStore.incomeData, ...payload }
    incomeStore.showIncomeModal = true
  } else {
    expenseStore.expenseData = { ...expenseStore.expenseData, ...payload }
    expenseStore.showExpenseModal = true
  }

  closePalette()
}

const openPalette = async () => {
  copilotStore.openPalette()
  await nextTick()
  inputRef.value?.focus()
}

const closePalette = () => {
  copilotStore.closePalette()
}

const handleShortcut = (event: KeyboardEvent) => {
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault()
    if (copilotStore.isPaletteOpen) {
      inputRef.value?.focus()
    } else {
      openPalette()
    }
  }
  if (event.key === 'Escape' && copilotStore.isPaletteOpen) {
    event.preventDefault()
    closePalette()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleShortcut)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleShortcut)
  if (progressTimer) {
    clearInterval(progressTimer)
    progressTimer = null
  }
})

const progressSteps = [
  'Parsing intent…',
  'Detecting amount & date…',
  'Matching category…',
  'Preparing preview…',
]


watch(
  () => copilotStore.isLoading,
  (isLoading) => {
    if (isLoading) {
      progressIndex.value = 0
      if (progressTimer) clearInterval(progressTimer)
      progressTimer = setInterval(() => {
        progressIndex.value = Math.min(progressIndex.value + 1, progressSteps.length - 1)
      }, 1400)
    } else if (progressTimer) {
      clearInterval(progressTimer)
      progressTimer = null
    }
  },
)


watch(
  () => copilotStore.result,
  (value) => {
    if (value) copilotStore.isPaletteOpen = true
  },
)
</script>

<template>
  <div>

    <div v-if="copilotStore.isPaletteOpen" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closePalette"></div>
      <div class="absolute left-1/2 top-20 w-[min(720px,92vw)] -translate-x-1/2">
        <div class="rounded-md border-0 bg-background shadow-2xl">
          <div class="flex items-center gap-3 border-b border-border/60 px-5 py-4">
            <Sparkles class="h-4 w-4 text-primary" />
            <input
              ref="inputRef"
              v-model="copilotStore.prompt"
              class="w-full bg-transparent text-base text-foreground placeholder:text-muted-foreground focus:outline-none"
              placeholder="Describe a transaction…"
              @keydown.enter.prevent="analyzePrompt"
            />
            <div class="flex items-center gap-2">
              <div v-if="copilotStore.isLoading" class="flex items-center gap-2 text-xs text-muted-foreground">
                <Loader2 class="h-4 w-4 animate-spin" />
                <span>Analyzing</span>
              </div>
              <Button
                v-if="!copilotStore.isLoading"
                variant="ghost"
                size="sm"
                class="rounded-none px-3"
                @click="analyzePrompt"
              >
                Analyze
              </Button>
              <!-- <Button
                v-else
                variant="ghost"
                size="sm"
                class="rounded-none px-3"
                @click="copilotStore.stop"
              >
                <Square class="h-4 w-4" />
                <span class="sr-only">Stop</span>
              </Button> -->
            </div>
          </div>

          <div class="px-5 py-4">
            <div v-if="copilotStore.error" class="rounded-none border-0 bg-red-50 px-3 py-2 text-sm text-red-700">
              {{ copilotStore.error }}
            </div>

            <div v-if="copilotStore.isLoading" class="mt-4 rounded-none border-0 bg-gradient-to-r from-muted/60 via-muted/30 to-transparent px-4 py-3 text-xs text-muted-foreground">
              <div class="flex items-center justify-between">
                <span class="uppercase tracking-[0.2em] text-[10px] text-muted-foreground/80">Processing</span>
                <span class="text-[10px] text-muted-foreground/80">{{ progressIndex + 1 }} / {{ progressSteps.length }}</span>
              </div>
              <div class="mt-2 h-1 w-full overflow-hidden rounded-none bg-muted/50">
                <div
                  class="h-full bg-primary transition-[width] duration-500"
                  :style="{ width: `${((progressIndex + 1) / progressSteps.length) * 100}%` }"
                ></div>
              </div>
              <div class="mt-2 flex flex-col gap-2">
                <span
                  v-for="(step, index) in progressSteps"
                  :key="step"
                  class="rounded-md border border-transparent px-2 py-1 text-[10px] font-bold uppercase tracking-[0.2em]"
                  :class="index <= progressIndex ? 'bg-primary/10 text-primary' : 'bg-muted/40 text-muted-foreground/70'"
                >
                  {{ step }}
                </span>
              </div>
            </div>

            <div v-if="result" class="mt-4 grid gap-3 text-sm">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <Badge :variant="isExpense ? 'destructive' : 'default'">{{ typeLabel }}</Badge>
                  <span class="text-xs text-muted-foreground">Preview</span>
                </div>
                <span class="text-xs text-muted-foreground">Date: {{ resolvedDate }}</span>
              </div>

              <div class="grid gap-2">
                <div class="flex items-center justify-between">
                  <span class="text-muted-foreground">Title</span>
                  <span class="font-medium">{{ titleValue }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-muted-foreground">Amount</span>
                  <span class="font-medium text-primary">{{ formattedAmount }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-muted-foreground">Category</span>
                  <span class="font-medium">{{ categoryName || '—' }}</span>
                </div>
              </div>

              <div v-if="descriptionValue" class="rounded-none bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                {{ descriptionValue }}
              </div>

              <div v-if="isNewCategory" class="rounded-none border-0 bg-primary/5 px-3 py-2 text-xs text-primary">
                New category detected. We’ll create “{{ categoryName }}”.
              </div>

              <div class="flex items-center justify-between gap-3 pt-2">
                <span class="text-xs text-muted-foreground">Press Enter to analyze again.</span>
                <div class="flex items-center gap-2">
                  <Button variant="outline" size="sm" class="gap-2" :disabled="!result" @click="openInForm">
                    Edit in form
                  </Button>
                  <Button size="sm" class="gap-2" :disabled="!canCreate" @click="createTransaction">
                    Create {{ typeLabel }}
                    <ArrowRight class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>

            <TypingTip
              v-else-if="!copilotStore.isLoading"
              class="mt-6"
              :tips="['Pay rent $1200', 'Earn 5000 from freelancing', 'Coffee at Starbucks 6.5']"
              prefix="Tip:"
              :is-active="copilotStore.isPaletteOpen && !copilotStore.isLoading && !result"
              :hold-ms="4000"
              :type-speed-ms="35"
            />
          </div>

          <div class="flex items-center justify-between border-t border-border/60 px-5 py-3 text-xs text-muted-foreground">
            <span></span>
            <!-- Cmd + K to open -->
            <button class="text-muted-foreground hover:text-foreground" type="button" @click="closePalette">
              Close
              <X class="ml-1 inline h-3.5 w-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

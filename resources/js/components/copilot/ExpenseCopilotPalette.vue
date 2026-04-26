<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useCopilotStore } from '@/js/stores/copilot'
import { useCategoryStore } from '@/js/stores/category'
import { useExpenseStore } from '@/js/stores/expense'
import { useIncomeStore } from '@/js/stores/income'
import { useAuthStore } from '@/js/stores/auth'
import { useToast } from '@/js/components/ui/toast/use-toast'
import { Button } from '@/js/components/ui/button'
import CopilotEmptyState from '@/js/components/copilot/CopilotEmptyState.vue'
import CopilotLoadingState from '@/js/components/copilot/CopilotLoadingState.vue'
import CopilotPreviewEditor from '@/js/components/copilot/CopilotPreviewEditor.vue'
import CopilotPromptInput from '@/js/components/copilot/CopilotPromptInput.vue'
import TypingTip from '@/js/components/copilot/TypingTip.vue'
import { Command, WandSparkles, X } from 'lucide-vue-next'

type PreviewSubmission = {
  transactionType: 'expense' | 'income'
  categoryName: string
  matchedCategoryId: number | null
  reuseMatchedCategory: boolean
  payload: {
    title: string
    description: string | null
    amount: number
    date: string
  }
}

const copilotStore = useCopilotStore()
const categoryStore = useCategoryStore()
const expenseStore = useExpenseStore()
const incomeStore = useIncomeStore()
const authStore = useAuthStore()
const { toast } = useToast()

const promptInputRef = ref<InstanceType<typeof CopilotPromptInput> | null>(null)
const progressIndex = ref(0)
let progressTimer: ReturnType<typeof setInterval> | null = null

const samplePrompts = [
  'Paid 450 for lunch at Barbeque Nation today',
  'Uber ride yesterday for 220',
  'Received salary from Acme for 65000 on April 1',
  'Bought groceries at DMart for 1800',
]

const progressSteps = [
  {
    title: 'Reading your note',
    detail: 'Looking for the single transaction hidden in the message.',
  },
  {
    title: 'Extracting details',
    detail: 'Detecting amount, type, date, and merchant details.',
  },
  {
    title: 'Matching category',
    detail: 'Comparing the result with your existing categories.',
  },
  {
    title: 'Building preview',
    detail: 'Preparing a clean draft before anything is saved.',
  },
]

const focusPromptInput = async () => {
  await nextTick()
  promptInputRef.value?.focus()
}

const analyzePrompt = async () => {
  if (copilotStore.isLoading) return

  if (copilotStore.result) {
    copilotStore.reset()
    await nextTick()
  }

  try {
    await copilotStore.copilot()
  } catch (_error) {
    // handled in store
  }
}

const closePalette = () => {
  copilotStore.closePalette()
}

const openPalette = async () => {
  copilotStore.openPalette()
  await focusPromptInput()
}

const createFromDraft = async (submission: PreviewSubmission) => {
  let categoryId = submission.reuseMatchedCategory ? submission.matchedCategoryId : null

  if (!categoryId && submission.categoryName) {
    const res = await categoryStore.addCategory({
      name: submission.categoryName,
      type: submission.transactionType === 'income' ? 'INCOME' : 'EXPENSE',
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
    ...submission.payload,
    category_id: categoryId,
  }

  if (submission.transactionType === 'income') {
    await incomeStore.addIncome(payload)
  } else {
    await expenseStore.addExpense(payload)
  }

  toast({
    title: `${submission.transactionType === 'income' ? 'Income' : 'Expense'} created`,
    description: 'Transaction has been added successfully.',
  })

  closePalette()
}

const openInForm = (submission: PreviewSubmission) => {
  const payload = {
    ...submission.payload,
    category_id: submission.reuseMatchedCategory ? submission.matchedCategoryId || '' : '',
  }

  if (submission.transactionType === 'income') {
    incomeStore.incomeData = { ...incomeStore.incomeData, ...payload }
    incomeStore.showIncomeModal = true
  } else {
    expenseStore.expenseData = { ...expenseStore.expenseData, ...payload }
    expenseStore.showExpenseModal = true
  }

  closePalette()
}

const handleShortcut = (event: KeyboardEvent) => {
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault()
    if (copilotStore.isPaletteOpen) {
      promptInputRef.value?.focus()
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

watch(
  () => copilotStore.isPaletteOpen,
  async (isOpen) => {
    if (!isOpen) return
    await focusPromptInput()
  },
)

watch(
  () => copilotStore.isLoading,
  (isLoading) => {
    if (isLoading) {
      progressIndex.value = 0
      if (progressTimer) clearInterval(progressTimer)
      progressTimer = setInterval(() => {
        progressIndex.value = Math.min(progressIndex.value + 1, progressSteps.length - 1)
      }, 1400)
      return
    }

    if (progressTimer) {
      clearInterval(progressTimer)
      progressTimer = null
    }
  },
)

watch(
  () => copilotStore.result,
  (value) => {
    if (value) {
      copilotStore.isPaletteOpen = true
    }
  },
)
</script>

<template>
  <div>
    <div v-if="copilotStore.isPaletteOpen" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closePalette"></div>
      <div class="absolute left-1/2 top-20 w-[min(720px,92vw)] -translate-x-1/2">
        <div class="overflow-hidden rounded-2xl border border-border/70 bg-background shadow-2xl">
          <div class="border-b border-border/60 bg-[radial-gradient(circle_at_top_left,_hsl(var(--primary)/0.16),_transparent_38%),linear-gradient(180deg,_hsl(var(--muted)/0.2),_transparent)] px-5 py-4">
            <div class="mb-3 flex items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary/10 text-primary">
                  <WandSparkles class="h-4 w-4" />
                </div>
                <div>
                  <div class="text-sm font-semibold text-foreground">Expense Copilot</div>
                  <div class="text-xs text-muted-foreground">Turn natural language into a ready-to-review transaction.</div>
                </div>
              </div>
              <button class="text-muted-foreground hover:text-foreground" type="button" @click="closePalette">
                <X class="h-4 w-4" />
              </button>
            </div>

            <CopilotPromptInput
              ref="promptInputRef"
              v-model="copilotStore.prompt"
              input-id="copilot-transaction-prompt"
              label="Describe your transaction"
              placeholder="Type the expense or income you want to add..."
              helper-text="Include amount, merchant, and date if you know them."
              example-text="Example: paid 450 for lunch today"
              :is-loading="copilotStore.isLoading"
              @analyze="analyzePrompt"
            />
          </div>

          <div class="px-5 py-4">
            <CopilotEmptyState
              v-if="!copilotStore.isLoading && !copilotStore.result && !copilotStore.error"
              :is-active="copilotStore.isPaletteOpen && !copilotStore.prompt.trim()"
            />

            <div v-if="copilotStore.error" class="rounded-none border-0 bg-red-50 px-3 py-2 text-sm text-red-700">
              {{ copilotStore.error }}
            </div>

            <CopilotLoadingState
              v-if="copilotStore.isLoading"
              :progress-index="progressIndex"
              :progress-steps="progressSteps"
            />

            <CopilotPreviewEditor
              v-if="copilotStore.result"
              :result="copilotStore.result"
              :currency="authStore.currentUser?.currency"
              :show-open-in-form="true"
              @create="createFromDraft"
              @open-form="openInForm"
            />
          </div>

          <div class="border-t border-border/60 bg-muted/20 px-5 py-3">
            <div class="flex items-center justify-between text-xs text-muted-foreground">
              <TypingTip
                v-if="!copilotStore.isLoading && !copilotStore.result && !copilotStore.error"
                :tips="samplePrompts"
                prefix="Quick start"
                :is-active="copilotStore.isPaletteOpen && !copilotStore.prompt.trim()"
                :hold-ms="2600"
                :type-speed-ms="24"
              />
              <div v-else></div>
              <div class="flex items-center gap-1">
                <Command class="h-3.5 w-3.5" />
                <span>K</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

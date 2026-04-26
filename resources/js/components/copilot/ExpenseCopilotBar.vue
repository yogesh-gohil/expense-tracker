<script setup lang="ts">
import { ref, watch } from 'vue'
import { useCopilotStore } from '@/js/stores/copilot'
import { useCategoryStore } from '@/js/stores/category'
import { useExpenseStore } from '@/js/stores/expense'
import { useIncomeStore } from '@/js/stores/income'
import { useAuthStore } from '@/js/stores/auth'
import { useToast } from '@/js/components/ui/toast/use-toast'
import CopilotLoadingState from '@/js/components/copilot/CopilotLoadingState.vue'
import CopilotPreviewEditor from '@/js/components/copilot/CopilotPreviewEditor.vue'
import CopilotPromptInput from '@/js/components/copilot/CopilotPromptInput.vue'
import { X } from 'lucide-vue-next'

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

const panelOpen = ref(false)
const progressIndex = ref(0)
let progressTimer: ReturnType<typeof setInterval> | null = null

const progressSteps = [
  { title: 'Reading your message', detail: 'Looking for the transaction in your note.' },
  { title: 'Extracting details', detail: 'Pulling amount, date, and merchant details.' },
  { title: 'Matching category', detail: 'Checking your current categories first.' },
  { title: 'Preparing preview', detail: 'Showing a clean draft before save.' },
]

const analyzePrompt = async () => {
  if (copilotStore.isLoading) return
  panelOpen.value = true

  try {
    await copilotStore.copilot()
  } catch (_error) {
    // errors handled in store + UI
  }
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

  copilotStore.reset()
  copilotStore.prompt = ''
  panelOpen.value = false
}

const closePanel = () => {
  panelOpen.value = false
}

watch(
  () => copilotStore.result,
  (value) => {
    if (value) panelOpen.value = true
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
      }, 1200)
      return
    }

    if (progressTimer) {
      clearInterval(progressTimer)
      progressTimer = null
    }
  },
)
</script>

<template>
  <div class="relative flex w-full max-w-[560px] items-center">
    <CopilotPromptInput
      v-model="copilotStore.prompt"
      input-id="copilot-bar-prompt"
      label="Describe the transaction you want to create"
      placeholder="Example: dinner at Domino's for 500 yesterday"
      :is-loading="copilotStore.isLoading"
      compact
      @analyze="analyzePrompt"
      @stop="copilotStore.stop"
    />

    <div
      v-if="panelOpen && (copilotStore.isLoading || copilotStore.result || copilotStore.error)"
      class="absolute left-0 top-full z-50 mt-2 w-full rounded-2xl border bg-background p-4 shadow-xl"
    >
      <div class="flex items-start justify-between gap-2">
        <div>
          <div class="text-xs uppercase tracking-[0.2em] text-muted-foreground">Expense Copilot</div>
          <div v-if="copilotStore.result" class="mt-1 text-sm text-muted-foreground">Ready to create</div>
        </div>
        <button class="text-muted-foreground hover:text-foreground" type="button" @click="closePanel">
          <X class="h-4 w-4" />
        </button>
      </div>

      <div v-if="copilotStore.error" class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
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
        compact
        @create="createFromDraft"
      />
    </div>
  </div>
</template>

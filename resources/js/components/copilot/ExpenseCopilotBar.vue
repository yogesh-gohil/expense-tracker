<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useCopilotStore } from '@/js/stores/copilot'
import { useCategoryStore } from '@/js/stores/category'
import { useExpenseStore } from '@/js/stores/expense'
import { useIncomeStore } from '@/js/stores/income'
import { useAuthStore } from '@/js/stores/auth'
import { useToast } from '@/js/components/ui/toast/use-toast'
import { Button } from '@/js/components/ui/button'
import { Badge } from '@/js/components/ui/badge'
import { Sparkles, Loader2, ArrowRight, X, Square } from 'lucide-vue-next'
import { formatCurrencyFromCents } from '@/js/lib/currency'

const copilotStore = useCopilotStore()
const categoryStore = useCategoryStore()
const expenseStore = useExpenseStore()
const incomeStore = useIncomeStore()
const authStore = useAuthStore()
const { toast } = useToast()

const panelOpen = ref(false)

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
  panelOpen.value = true
  try {
    await copilotStore.copilot()
  } catch (_error) {
    // errors handled in store + UI
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
</script>

<template>
  <div class="relative flex w-full max-w-[560px] items-center">
    <div class="flex w-full items-center gap-2 rounded-full border border-transparent bg-background/70 px-3 py-2 shadow-sm ring-1 ring-border/50 transition focus-within:ring-2 focus-within:ring-primary/30">
      <Sparkles class="h-4 w-4 text-primary" />
      <input
        v-model="copilotStore.prompt"
        class="w-full bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus:outline-none"
        placeholder="Try “Eat pizza at Domino’s for $50”"
        @keydown.enter.prevent="analyzePrompt"
      />
      <Button
        v-if="!copilotStore.isLoading"
        variant="ghost"
        size="sm"
        class="rounded-full px-3"
        @click="analyzePrompt"
      >
        Analyze
      </Button>
      <Button
        v-else
        variant="ghost"
        size="sm"
        class="rounded-full px-3"
        @click="copilotStore.stop"
      >
        <Square class="h-4 w-4" />
        <span class="sr-only">Stop</span>
      </Button>
    </div>

    <div
      v-if="panelOpen && (result || copilotStore.error)"
      class="absolute left-0 top-full z-50 mt-2 w-full rounded-2xl border bg-background p-4 shadow-xl"
    >
      <div class="flex items-start justify-between gap-2">
        <div>
          <div class="text-xs uppercase tracking-[0.2em] text-muted-foreground">Expense Copilot</div>
          <div v-if="result" class="mt-1 flex items-center gap-2">
            <Badge variant="secondary">{{ typeLabel }}</Badge>
            <span class="text-sm text-muted-foreground">Ready to create</span>
          </div>
        </div>
        <button class="text-muted-foreground hover:text-foreground" type="button" @click="closePanel">
          <X class="h-4 w-4" />
        </button>
      </div>

      <div v-if="copilotStore.error" class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        {{ copilotStore.error }}
      </div>

      <div v-if="result" class="mt-4 grid gap-3 text-sm">
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
        <div class="flex items-center justify-between">
          <span class="text-muted-foreground">Date</span>
          <span class="font-medium">{{ resolvedDate }}</span>
        </div>
        <div v-if="descriptionValue" class="rounded-lg bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
          {{ descriptionValue }}
        </div>

        <div v-if="isNewCategory" class="rounded-lg border border-dashed border-primary/40 bg-primary/5 px-3 py-2 text-xs text-primary">
          New category detected. We’ll create “{{ categoryName }}”.
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
          <span class="text-xs text-muted-foreground">Everything looks good?</span>
          <Button size="sm" class="gap-2" :disabled="!canCreate" @click="createTransaction">
            Create {{ typeLabel }}
            <ArrowRight class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

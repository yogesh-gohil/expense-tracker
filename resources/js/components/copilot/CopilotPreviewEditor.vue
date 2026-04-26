<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Badge } from '@/js/components/ui/badge'
import { Button } from '@/js/components/ui/button'
import BaseDatePicker from '@/js/components/base/BaseDatePicker.vue'
import Input from '@/js/components/ui/input/Input.vue'
import Textarea from '@/js/components/ui/textarea/Textarea.vue'
import { ArrowRight, CalendarDays, ReceiptText, Tags } from 'lucide-vue-next'
import { formatCurrencyFromCents } from '@/js/lib/currency'

type CopilotResult = {
  type: string
  amount: number | null
  currency: string
  category: string | null
  category_type: string
  category_match: { exists: boolean, id: number | null, name: string | null } | null
  date: string
  title: string | null
  description: string | null
  source: string | null
  vendor: string | null
  raw_prompt: string
}

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

const props = withDefaults(defineProps<{
  result: CopilotResult
  currency?: string | null
  showOpenInForm?: boolean
  compact?: boolean
}>(), {
  currency: 'USD',
  showOpenInForm: false,
  compact: false,
})

const emit = defineEmits<{
  (e: 'create', submission: PreviewSubmission): void
  (e: 'open-form', submission: PreviewSubmission): void
}>()

const activeEditor = ref<'type' | 'title' | 'amount' | 'category' | 'date' | 'description' | null>(null)
const draftType = ref<'expense' | 'income'>('expense')
const draftTitle = ref('')
const draftAmountCents = ref(0)
const draftCategory = ref('')
const draftDate = ref('')
const draftDescription = ref('')

const today = () => new Date().toISOString().slice(0, 10)
const isIsoDate = (value?: string | null) => !!value && /^\d{4}-\d{2}-\d{2}$/.test(value)

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

const isExpense = computed(() => draftType.value !== 'income')
const typeLabel = computed(() => (isExpense.value ? 'Expense' : 'Income'))
const resolvedDate = computed(() => (isIsoDate(draftDate.value) ? draftDate.value : today()))
const categoryName = computed(() => draftCategory.value.trim())
const extractedCategoryName = computed(() => props.result.category_match?.name?.trim() || props.result.category?.trim() || '')
const isSameCategoryAsExtracted = computed(() => categoryName.value === extractedCategoryName.value)
const isSameTypeAsExtracted = computed(() => draftType.value === (props.result.type === 'income' ? 'income' : 'expense'))
const isNewCategory = computed(() => !!categoryName.value && (!props.result.category_match?.exists || !isSameCategoryAsExtracted.value || !isSameTypeAsExtracted.value))

const makeSmartTitle = () => {
  const vendor = props.result.vendor?.trim()
  const source = props.result.source?.trim()
  const category = categoryName.value

  if (props.result.type === 'income') {
    if (source) return `Income from ${toTitleCase(source)}`
    if (category) return `${toTitleCase(category)} Income`
  }

  if (vendor) {
    if (category) return `${toTitleCase(category)} at ${toTitleCase(vendor)}`
    return `Purchase at ${toTitleCase(vendor)}`
  }

  if (category) {
    return props.result.type === 'income'
      ? `${toTitleCase(category)} Income`
      : `${toTitleCase(category)} Expense`
  }

  const cleaned = cleanPrompt(props.result.raw_prompt?.trim() || '')

  return cleaned ? toTitleCase(cleaned) : `${typeLabel.value} from Copilot`
}

const titleValue = computed(() => {
  const title = draftTitle.value.trim()
  if (title) return title
  return makeSmartTitle()
})

const descriptionValue = computed(() => {
  const description = draftDescription.value.trim()
  return description || null
})

const formattedAmount = computed(() => formatCurrencyFromCents(draftAmountCents.value, props.currency || 'USD'))
const canCreate = computed(() => draftAmountCents.value > 0 && !!categoryName.value && !!resolvedDate.value)
const sourceLabel = computed(() => props.result.vendor?.trim() || props.result.source?.trim() || 'your message')
const categoryStatus = computed(() => isNewCategory.value
  ? `New category will be created as ${categoryName.value}.`
  : `Matched existing category: ${categoryName.value}.`
)
const dateStatus = computed(() => !props.result?.date || !isIsoDate(props.result.date)
  ? `Date inferred as ${resolvedDate.value}.`
  : `Date resolved to ${resolvedDate.value}.`
)
const currencyStatus = computed(() => {
  const extractedCurrency = props.result.currency?.trim()?.toUpperCase()
  const userCurrency = props.currency?.toUpperCase() || 'USD'
  return extractedCurrency && extractedCurrency !== userCurrency
    ? `Currency detected as ${extractedCurrency}.`
    : `Using ${userCurrency} as the final currency.`
})
const understandingLine = computed(() => {
  const amount = draftAmountCents.value > 0 ? formattedAmount.value : 'an amount'
  const category = categoryName.value || 'an uncategorized transaction'
  return `${typeLabel.value} of ${amount} for ${category} from ${sourceLabel.value} on ${resolvedDate.value}.`
})

const resultHighlights = computed(() => [
  {
    label: 'Amount',
    value: formattedAmount.value,
    hint: currencyStatus.value,
    icon: ReceiptText,
  },
  {
    label: 'Category',
    value: categoryName.value || '—',
    hint: categoryStatus.value,
    icon: Tags,
  },
  {
    label: 'Date',
    value: resolvedDate.value,
    hint: dateStatus.value,
    icon: CalendarDays,
  },
])

const setActiveEditor = (field: typeof activeEditor.value) => {
  activeEditor.value = activeEditor.value === field ? null : field
}

const syncDraftFromResult = () => {
  const rawAmount = Number(props.result.amount)
  const resolvedCategory = extractedCategoryName.value
  const resolvedTitle = props.result.title?.trim()
    || (() => {
      const vendor = props.result.vendor?.trim()
      const source = props.result.source?.trim()

      if (props.result.type === 'income') {
        if (source) return `Income from ${toTitleCase(source)}`
        if (resolvedCategory) return `${toTitleCase(resolvedCategory)} Income`
      }

      if (vendor) {
        if (resolvedCategory) return `${toTitleCase(resolvedCategory)} at ${toTitleCase(vendor)}`
        return `Purchase at ${toTitleCase(vendor)}`
      }

      if (resolvedCategory) {
        return props.result.type === 'income'
          ? `${toTitleCase(resolvedCategory)} Income`
          : `${toTitleCase(resolvedCategory)} Expense`
      }

      const cleaned = cleanPrompt(props.result.raw_prompt?.trim() || '')

      return cleaned ? toTitleCase(cleaned) : `${props.result.type === 'income' ? 'Income' : 'Expense'} from Copilot`
    })()

  draftType.value = props.result.type === 'income' ? 'income' : 'expense'
  draftTitle.value = resolvedTitle
  draftAmountCents.value = Number.isFinite(rawAmount) ? Math.round(rawAmount * 100) : 0
  draftCategory.value = resolvedCategory
  draftDate.value = isIsoDate(props.result.date) ? props.result.date : today()
  draftDescription.value = props.result.description?.trim() || ''
  activeEditor.value = null
}

const buildSubmission = (): PreviewSubmission => ({
  transactionType: draftType.value,
  categoryName: categoryName.value,
  matchedCategoryId: props.result.category_match?.id || null,
  reuseMatchedCategory: isSameCategoryAsExtracted.value && isSameTypeAsExtracted.value,
  payload: {
    title: titleValue.value,
    description: descriptionValue.value,
    amount: draftAmountCents.value,
    date: resolvedDate.value,
  },
})

watch(
  () => props.result,
  () => {
    syncDraftFromResult()
  },
  { immediate: true },
)
</script>

<template>
  <div class="mt-4 grid gap-3 text-sm">
    <div class="rounded-2xl border bg-[linear-gradient(135deg,_hsl(var(--primary)/0.08),_transparent_60%)] px-4 py-4">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <button type="button" @click="setActiveEditor('type')">
            <Badge :variant="isExpense ? 'destructive' : 'default'">{{ typeLabel }}</Badge>
          </button>
          <span class="text-xs text-muted-foreground">Ready to review</span>
        </div>
        <span class="text-xs text-muted-foreground">Date: {{ resolvedDate }}</span>
      </div>
      <div v-if="activeEditor === 'type'" class="mt-3 flex gap-2">
        <Button size="sm" variant="outline" :class="draftType === 'expense' ? 'border-primary text-primary' : ''" @click="draftType = 'expense'">Expense</Button>
        <Button size="sm" variant="outline" :class="draftType === 'income' ? 'border-primary text-primary' : ''" @click="draftType = 'income'">Income</Button>
      </div>
      <div class="mt-3 w-full text-left">
        <div v-if="activeEditor === 'title'" class="space-y-2">
          <Input v-model="draftTitle" placeholder="Transaction title" />
          <div v-if="!compact" class="text-xs text-muted-foreground">Press the field again to collapse editing.</div>
        </div>
        <button v-else type="button" class="w-full text-left" @click="setActiveEditor('title')">
          <div :class="compact ? 'text-base font-semibold text-foreground' : 'text-lg font-semibold leading-tight text-foreground'">{{ titleValue }}</div>
        </button>
      </div>
      <div class="mt-2 text-sm text-muted-foreground">{{ understandingLine }}</div>
      <div class="mt-2 text-xs text-muted-foreground">Click any field below to edit before creating.</div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
      <div
        v-for="item in resultHighlights"
        :key="item.label"
        class="rounded-2xl border bg-background px-4 py-4"
      >
        <div class="w-full text-left">
          <div class="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-muted-foreground">
            <component :is="item.icon" class="h-3.5 w-3.5" />
            <span>{{ item.label }}</span>
          </div>
          <div v-if="activeEditor === item.label.toLowerCase()" class="mt-3">
            <Input v-if="item.label === 'Amount'" v-model="draftAmountCents" :is-money="true" />
            <Input v-else-if="item.label === 'Category'" v-model="draftCategory" placeholder="Category" />
            <BaseDatePicker v-else v-model="draftDate" />
          </div>
          <button
            v-else
            type="button"
            :class="compact ? 'mt-2 w-full text-left text-sm font-semibold text-foreground' : 'mt-3 w-full text-left text-base font-semibold text-foreground'"
            @click="setActiveEditor(item.label.toLowerCase() as 'amount' | 'category' | 'date')"
          >
            {{ item.value }}
          </button>
          <div v-if="item.hint" class="mt-2 text-xs text-muted-foreground">{{ item.hint }}</div>
        </div>
      </div>
    </div>

    <div :class="compact ? 'w-full rounded-lg bg-muted/40 px-3 py-2 text-xs text-muted-foreground' : 'w-full rounded-2xl border bg-muted/30 px-4 py-3 text-sm text-muted-foreground'">
      <div v-if="activeEditor === 'description'" class="space-y-2">
        <Textarea v-model="draftDescription" placeholder="Add a note" :class="compact ? 'min-h-[80px] bg-background' : 'min-h-[88px] bg-background'" />
        <div v-if="!compact" class="text-xs text-muted-foreground">Optional note shown before save.</div>
      </div>
      <button
        v-else
        type="button"
        class="w-full text-left"
        @click="setActiveEditor('description')"
      >
        {{ descriptionValue || 'Add a description before creating.' }}
      </button>
    </div>

    <div v-if="isNewCategory" class="rounded-2xl border border-dashed border-primary/40 bg-primary/5 px-4 py-3 text-sm text-primary">
      New category detected. We’ll create “{{ categoryName }}”.
    </div>

    <div :class="compact ? 'flex items-center justify-between gap-3 pt-2' : 'flex flex-wrap items-center justify-between gap-3 pt-2'">
      <div>
        <div class="text-sm font-medium text-foreground">Everything looks good?</div>
        <div class="text-xs text-muted-foreground">Create it now or open the full form for review.</div>
      </div>
      <div class="flex items-center gap-2">
        <Button v-if="showOpenInForm" variant="outline" size="sm" class="gap-2" @click="emit('open-form', buildSubmission())">
          Edit in form
        </Button>
        <Button size="sm" class="gap-2" :disabled="!canCreate" @click="emit('create', buildSubmission())">
          Create {{ typeLabel }}
          <ArrowRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useExpenseStore } from '@/js/stores/expense'
import { useIncomeStore } from '@/js/stores/income'
import { ArrowDownRight, ArrowUpRight, ChartPie, CircleDollarSign, Layers, ReceiptText } from 'lucide-vue-next'
import Card from '@/js/components/ui/card/Card.vue'
import CardHeader from '@/js/components/ui/card/CardHeader.vue'
import CardTitle from '@/js/components/ui/card/CardTitle.vue'
import CardDescription from '@/js/components/ui/card/CardDescription.vue'
import CardContent from '@/js/components/ui/card/CardContent.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/js/components/ui/select'
import ExpenseBreakdownChart from '@/js/components/charts/ExpenseBreakdownChart.vue'
import StatsCard from '@/js/components/dashboard/StatsCard.vue'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'
import Button from '@/js/components/ui/button/Button.vue'
import { useAuthStore } from '@/js/stores/auth'
import { formatCurrencyFromCents } from '@/js/lib/currency'

const expenseStore = useExpenseStore()
const incomeStore = useIncomeStore()
const authStore = useAuthStore()
const isLoading = ref(false)
const chartType = ref('pie')

const now = new Date()
const selectedMonth = ref(String(now.getMonth() + 1))
const selectedYear = ref(String(now.getFullYear()))

const monthOptions = [
  { label: 'January', value: 1 },
  { label: 'February', value: 2 },
  { label: 'March', value: 3 },
  { label: 'April', value: 4 },
  { label: 'May', value: 5 },
  { label: 'June', value: 6 },
  { label: 'July', value: 7 },
  { label: 'August', value: 8 },
  { label: 'September', value: 9 },
  { label: 'October', value: 10 },
  { label: 'November', value: 11 },
  { label: 'December', value: 12 },
]

const yearOptions = computed(() => {
  const currentYear = now.getFullYear()
  return Array.from({ length: 5 }, (_, index) => currentYear - index)
})

const summary = computed(() => expenseStore.monthlySummary || {
  total_spent: 0,
  transactions_count: 0,
  average_transaction: 0,
  top_category: null,
  highest_expense: null,
  recent_expenses: [],
  trend: {
    previous_total_spent: 0,
    change_amount: 0,
    change_percentage: null,
  },
  categories: [],
  month: Number(selectedMonth.value),
  year: Number(selectedYear.value),
})
const incomeSummary = computed(() => incomeStore.monthlySummary || {
  total_income: 0,
  transactions_count: 0,
  average_transaction: 0,
  trend: {
    previous_total_income: 0,
  },
})

const chartLabels = computed(() => summary.value.categories.map((item) => item.category_name))
const chartValues = computed(() => summary.value.categories.map((item) => item.total_spent))

const monthLabel = computed(() => new Date(summary.value.year, summary.value.month - 1, 1).toLocaleDateString('en-US', {
  month: 'long',
  year: 'numeric',
}))

const formatMoney = (amount) => formatCurrencyFromCents(amount, authStore.currentUser?.currency)
const formatDate = (value) => new Date(value).toLocaleDateString('en-US', {
  month: 'short',
  day: 'numeric',
  year: 'numeric',
})

const totalIncome = computed(() => Number(incomeSummary.value.total_income ?? 0))
const totalExpense = computed(() => Number(summary.value.total_spent ?? 0))
const netBalance = computed(() => totalIncome.value - totalExpense.value)
const previousNetBalance = computed(() => {
  const previousIncome = Number(incomeSummary.value.trend?.previous_total_income ?? 0)
  const previousExpense = Number(summary.value.trend?.previous_total_spent ?? 0)
  return previousIncome - previousExpense
})
const netChange = computed(() => netBalance.value - previousNetBalance.value)
const trendIsPositive = computed(() => netChange.value >= 0)
const savingsRate = computed(() => {
  if (totalIncome.value <= 0) {
    return '--'
  }

  return `${((netBalance.value / totalIncome.value) * 100).toFixed(1)}%`
})
const progressTones = [
  'bg-gradient-to-r from-cyan-500 to-blue-500 dark:from-cyan-400 dark:to-indigo-400',
  'bg-gradient-to-r from-violet-500 to-fuchsia-500 dark:from-violet-400 dark:to-pink-400',
  'bg-gradient-to-r from-emerald-500 to-teal-500 dark:from-emerald-400 dark:to-teal-300',
  'bg-gradient-to-r from-amber-500 to-orange-500 dark:from-amber-400 dark:to-orange-300',
  'bg-gradient-to-r from-rose-500 to-red-500 dark:from-rose-400 dark:to-red-300',
]

const progressTone = (index) => progressTones[index % progressTones.length]

const trendLabel = computed(() => {
  if (previousNetBalance.value === 0) {
    return 'No previous month baseline'
  }

  const percentage = (netChange.value / Math.abs(previousNetBalance.value)) * 100
  return `${Math.abs(Number(percentage)).toFixed(2)}% vs previous month`
})

const statsCards = computed(() => [
  {
    label: 'Total income',
    value: isLoading.value ? '...' : formatMoney(totalIncome.value),
    icon: CircleDollarSign,
    variant: 'emerald',
  },
  {
    label: 'Total spent',
    value: isLoading.value ? '...' : formatMoney(totalExpense.value),
    icon: ReceiptText,
    variant: 'sky',
  },
  {
    label: 'Net balance',
    value: isLoading.value ? '...' : formatMoney(netBalance.value),
    icon: Layers,
    variant: 'violet',
  },
  {
    label: 'Savings rate',
    value: isLoading.value ? '...' : savingsRate.value,
    icon: ChartPie,
    variant: 'rose',
  },
])

const fetchSummary = async () => {
  isLoading.value = true
  const params = {
    month: Number(selectedMonth.value),
    year: Number(selectedYear.value),
  }
  await Promise.all([
    expenseStore.fetchMonthlySummary(params),
    incomeStore.fetchMonthlySummary(params),
  ])
  isLoading.value = false
}

onMounted(fetchSummary)
</script>

<template>
  <div class="space-y-4 relative">
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 rounded-3xl opacity-0 dark:opacity-100 bg-[radial-gradient(ellipse_at_top,rgba(56,189,248,0.18),rgba(15,23,42,0)_60%),radial-gradient(ellipse_at_right,rgba(139,92,246,0.16),rgba(15,23,42,0)_55%)]" />
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <div>
        <h1 class="text-lg font-semibold md:text-2xl">Dashboard</h1>
        <p class="text-sm text-muted-foreground">Your money snapshot for {{ monthLabel }}</p>
      </div>
      <div class="flex items-center gap-2">
        <Select v-model="selectedMonth">
          <SelectTrigger class="w-[140px] dark:bg-slate-900/70 dark:border-slate-700 dark:text-slate-100">
            <SelectValue placeholder="Month" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="month in monthOptions" :key="month.value" :value="String(month.value)">
              {{ month.label }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Select v-model="selectedYear">
          <SelectTrigger class="w-[110px] dark:bg-slate-900/70 dark:border-slate-700 dark:text-slate-100">
            <SelectValue placeholder="Year" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="year in yearOptions" :key="year" :value="String(year)">
              {{ year }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Button size="sm" class="dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400" @click="fetchSummary">Apply</Button>
      </div>
    </div>

    <div v-if="isLoading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div v-for="i in 4" :key="`stat-skeleton-${i}`" class="rounded-lg border bg-card p-4 space-y-3 dark:bg-slate-900/60 dark:border-slate-800">
        <Skeleton class="h-4 w-28" />
        <Skeleton class="h-7 w-24" />
      </div>
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <StatsCard
        v-for="item in statsCards"
        :key="item.label"
        :label="item.label"
        :value="item.value"
        :icon="item.icon"
        :variant="item.variant"
      />
    </div>
        <Card class="overflow-hidden border border-cyan-100 text-slate-900 hover:shadow-md bg-gradient-to-r from-emerald-100 via-cyan-100 to-sky-100 dark:border-none dark:text-white dark:from-slate-950 dark:via-indigo-950 dark:to-cyan-950 dark:shadow-cyan-900/40">
      <CardContent class="p-6 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div>
            <p class="text-sm/6 text-slate-600 dark:text-cyan-100/90">Net balance in {{ monthLabel }}</p>
            <p v-if="!isLoading" class="text-3xl font-semibold md:text-4xl">{{ formatMoney(netBalance) }}</p>
            <Skeleton v-else class="h-10 w-44 bg-slate-200/80 dark:bg-white/35" />
            <p v-if="!isLoading" class="text-xs mt-2 text-slate-600 dark:text-cyan-100/85">
              Income {{ formatMoney(totalIncome) }} • Expense {{ formatMoney(totalExpense) }}
            </p>
          </div>
          <div class="rounded-lg px-4 py-2 text-sm min-w-[220px] bg-white/70 ring-1 ring-cyan-200/70 dark:bg-slate-900/60 dark:ring-cyan-300/20">
            <template v-if="!isLoading">
              <p class="text-slate-600 dark:text-cyan-100/80">{{ trendLabel }}</p>
              <p class="flex items-center gap-1 font-semibold text-slate-800 dark:text-cyan-100">
                <component :is="trendIsPositive ? ArrowUpRight : ArrowDownRight" class="h-4 w-4" />
                {{ formatMoney(netChange) }}
              </p>
            </template>
            <div v-else class="space-y-2">
              <Skeleton class="h-4 w-36 bg-slate-200/80 dark:bg-white/25" />
              <Skeleton class="h-5 w-24 bg-slate-300/70 dark:bg-white/35" />
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="grid gap-4 lg:grid-cols-5">
      <Card class="lg:col-span-3 dark:bg-slate-900/60 dark:border-slate-800">
        <CardHeader>
          <div class="flex items-center justify-between gap-2">
            <div>
              <CardTitle class="flex items-center gap-2">
                <ChartPie class="h-4 w-4" /> Spending Breakdown
              </CardTitle>
              <CardDescription>How your spend is split by category</CardDescription>
            </div>
            <div class="flex items-center gap-1 rounded-md border p-1 dark:border-slate-700 dark:bg-slate-950/40">
              <button
                class="rounded px-2 py-1 text-xs"
                :class="chartType === 'pie' ? 'bg-primary text-white dark:bg-cyan-500 dark:text-slate-950' : 'text-muted-foreground hover:bg-muted/70 dark:hover:bg-slate-800'"
                @click="chartType = 'pie'"
              >
                Pie
              </button>
              <button
                class="rounded px-2 py-1 text-xs"
                :class="chartType === 'bar' ? 'bg-primary text-white dark:bg-cyan-500 dark:text-slate-950' : 'text-muted-foreground hover:bg-muted/70 dark:hover:bg-slate-800'"
                @click="chartType = 'bar'"
              >
                Bar
              </button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="isLoading" class="space-y-3">
            <Skeleton class="h-60 w-full" />
          </div>
          <ExpenseBreakdownChart
            v-else
            :type="chartType"
            :labels="chartLabels"
            :values="chartValues"
            :height="280"
            :currency="authStore.currentUser?.currency"
          />
        </CardContent>
      </Card>

      <Card class="lg:col-span-2 dark:bg-slate-900/60 dark:border-slate-800">
        <CardHeader>
          <CardTitle>Top Categories</CardTitle>
          <CardDescription>Most spent categories in {{ monthLabel }}</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="isLoading" class="space-y-3">
            <div v-for="i in 4" :key="`cat-skeleton-${i}`" class="space-y-2">
              <div class="flex items-center justify-between">
                <Skeleton class="h-4 w-24" />
                <Skeleton class="h-4 w-16" />
              </div>
              <Skeleton class="h-2 w-full" />
            </div>
          </div>
          <div v-else-if="!summary.categories.length" class="text-sm text-muted-foreground">
            No category data available.
          </div>
          <div v-else class="space-y-3">
            <div v-for="(item, index) in summary.categories.slice(0, 5)" :key="item.category_id" class="space-y-1">
              <div class="flex items-center justify-between text-sm">
                <span class="font-medium">{{ item.category_name }}</span>
                <span>{{ formatMoney(item.total_spent) }}</span>
              </div>
              <div class="h-2 rounded-full bg-muted/90 dark:bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full"
                  :class="progressTone(index)"
                  :style="{ width: `${Math.min(Number(item.percentage), 100)}%` }"
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- <div class="grid gap-4 lg:grid-cols-2">
      <Card class="dark:bg-slate-900/60 dark:border-slate-800">
        <CardHeader>
          <CardTitle>Most Expensive Transaction</CardTitle>
          <CardDescription>Highest single spend in {{ monthLabel }}</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="!summary.highest_expense" class="text-sm text-muted-foreground">
            No expenses found this month.
          </div>
          <div v-else class="space-y-2">
            <p class="text-lg font-semibold">{{ summary.highest_expense.title }}</p>
            <p class="text-sm text-muted-foreground">
              {{ summary.highest_expense.category_name || 'Uncategorized' }} • {{ formatDate(summary.highest_expense.date) }}
            </p>
            <p class="text-2xl font-semibold text-primary">{{ formatMoney(summary.highest_expense.amount) }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="dark:bg-slate-900/60 dark:border-slate-800">
        <CardHeader>
          <CardTitle>Recent Expenses</CardTitle>
          <CardDescription>Last 5 transactions in {{ monthLabel }}</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="!summary.recent_expenses.length" class="text-sm text-muted-foreground">
            No recent expenses yet.
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="expense in summary.recent_expenses"
              :key="expense.id"
              class="flex items-center justify-between rounded-md border px-3 py-2 dark:border-slate-700 dark:bg-slate-950/35"
            >
              <div>
                <p class="text-sm font-medium">{{ expense.title }}</p>
                <p class="text-xs text-muted-foreground">
                  {{ expense.category_name || 'Uncategorized' }} • {{ formatDate(expense.date) }}
                </p>
              </div>
              <p class="font-semibold">{{ formatMoney(expense.amount) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div> -->
  </div>
</template>

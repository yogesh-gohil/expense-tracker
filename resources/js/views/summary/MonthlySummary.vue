<script setup>
import { computed, onMounted, ref } from 'vue'
import BaseBreadcrumb from '@/js/components/base/BaseBreadcrumb.vue'
import Button from '@/js/components/ui/button/Button.vue'
import Card from '@/js/components/ui/card/Card.vue'
import CardContent from '@/js/components/ui/card/CardContent.vue'
import CardDescription from '@/js/components/ui/card/CardDescription.vue'
import CardHeader from '@/js/components/ui/card/CardHeader.vue'
import CardTitle from '@/js/components/ui/card/CardTitle.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/js/components/ui/select'
import ExpenseBreakdownChart from '@/js/components/charts/ExpenseBreakdownChart.vue'
import { useExpenseStore } from '@/js/stores/expense'
import { useAuthStore } from '@/js/stores/auth'
import { formatCurrencyFromCents } from '@/js/lib/currency'

const expenseStore = useExpenseStore()
const authStore = useAuthStore()
const isLoading = ref(false)

const now = new Date()
const selectedMonth = ref(String(now.getMonth() + 1))
const selectedYear = ref(String(now.getFullYear()))

const breadcrumbData = [
  { title: 'Home', href: '/dashboard', active: false },
  { title: 'Monthly Summary', href: '', active: true },
]

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
  categories: [],
  month: Number(selectedMonth.value),
  year: Number(selectedYear.value),
})

const chartLabels = computed(() => summary.value.categories.map((item) => item.category_name))
const chartValues = computed(() => summary.value.categories.map((item) => item.total_spent))

const monthLabel = computed(() => new Date(summary.value.year, summary.value.month - 1, 1).toLocaleDateString('en-US', {
  month: 'long',
  year: 'numeric',
}))

const formatMoney = (amount) => formatCurrencyFromCents(amount, authStore.currentUser?.currency)

const fetchSummary = async () => {
  isLoading.value = true
  await expenseStore.fetchMonthlySummary({
    month: Number(selectedMonth.value),
    year: Number(selectedYear.value),
  })
  isLoading.value = false
}

onMounted(fetchSummary)
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between mb-2">
      <div class="flex flex-col space-y-2">
        <h1 class="text-lg font-semibold md:text-2xl">Monthly Summary</h1>
        <BaseBreadcrumb :data="breadcrumbData" />
      </div>
      <div class="flex items-center gap-2">
        <Select v-model="selectedMonth">
          <SelectTrigger class="w-[140px]">
            <SelectValue placeholder="Month" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="month in monthOptions" :key="month.value" :value="String(month.value)">
              {{ month.label }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Select v-model="selectedYear">
          <SelectTrigger class="w-[110px]">
            <SelectValue placeholder="Year" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="year in yearOptions" :key="year" :value="String(year)">
              {{ year }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Button size="sm" @click="fetchSummary">Apply</Button>
      </div>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Total Spent</CardTitle>
        <CardDescription>{{ monthLabel }}</CardDescription>
      </CardHeader>
      <CardContent>
        <p v-if="!isLoading" class="text-2xl font-semibold">{{ formatMoney(summary.total_spent) }}</p>
        <p v-else class="text-muted-foreground">Loading...</p>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Category Breakdown</CardTitle>
        <CardDescription>Total spent per category for {{ monthLabel }}</CardDescription>
      </CardHeader>
      <CardContent>
        <ExpenseBreakdownChart
          type="bar"
          :labels="chartLabels"
          :values="chartValues"
          :height="340"
          :currency="authStore.currentUser?.currency"
        />
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Summary Table</CardTitle>
      </CardHeader>
      <CardContent>
        <div v-if="isLoading" class="text-muted-foreground">Loading...</div>
        <div v-else-if="!summary.categories.length" class="text-muted-foreground">
          No expenses found for this month.
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="item in summary.categories"
            :key="item.category_id"
            class="flex items-center justify-between rounded-md border px-3 py-2"
          >
            <span class="font-medium">{{ item.category_name }}</span>
            <span class="text-muted-foreground">{{ item.percentage }}%</span>
            <span class="font-semibold">{{ formatMoney(item.total_spent) }}</span>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

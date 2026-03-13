<script setup>
import { ref, computed } from 'vue'
import { watchDebounced } from '@vueuse/core'
import { useExpenseStore } from '@/js/stores/expense'
import { useAuthStore } from '@/js/stores/auth'
import { useCopilotStore } from '@/js/stores/copilot'
import CardItem from '@/js/components/base/CardItem.vue'
import Button from '@/js/components/ui/button/Button.vue'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import { Badge } from '@/js/components/ui/badge'
import { Plus } from 'lucide-vue-next'
import Textarea from '../ui/textarea/Textarea.vue'
import { formatCurrencyFromCents } from '@/js/lib/currency'


const props = defineProps({
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const expenseStore = useExpenseStore()
const authStore = useAuthStore()
const copilotStore = useCopilotStore()

const isLoading = ref(false)
const params = computed(() => ({
  expand: 'category',
  ...props.filters,
}))
const hasActiveFilters = computed(() => Object.keys(props.filters || {}).length > 0)

const fetchExpenses = async () => {
  isLoading.value = true
  await expenseStore.fetchExpenses(params.value)
  isLoading.value = false
}
const editExpense = (expense) => {
  expenseStore.expenseData = {...expense}
  expenseStore.showExpenseModal = true
}

const formatMoney = (amount) => formatCurrencyFromCents(amount, authStore.currentUser?.currency)

watchDebounced(
  () => props.filters,
  () => {
    fetchExpenses()
  },
  { deep: true, debounce: 300, maxWait: 800, immediate: true },
)
</script>

<template>
  <!-- <div>
    <Textarea
      v-model="copilotStore.prompt"
      id="prompt"
    ></Textarea>
     <Button
        variant="outline"
        class="mt-4"
        size="sm"
        @click="copilotStore.copilot()"
      >Submit
      </Button>
  </div> -->
    <div v-if="isLoading" >
      <div v-for="i in 4" class="flex items-center border space-x-4 bg-gray-50 dark:bg-gray-900 p-4 mb-4 rounded-lg">
        <Skeleton class="h-12 w-12 rounded-full" />
        <div class="space-y-2">
          <Skeleton class="h-4 w-[250px]" />
          <Skeleton class="h-4 w-[200px]" />
        </div>
      </div>
    </div>
    <div v-else>
      <CardItem v-for="expense in expenseStore.expenses" :key="expense.id" @edit="editExpense(expense)">
        <template #title>
          <div class="flex items-center justify-between space-x-4">
            <span>{{ expense.title }}</span>
            <span class="text-primary">{{ formatMoney(expense.amount) }}</span>
            <Badge variant="outline">{{expense.category.name}}</Badge>
          </div>
        </template>
        <template #description>
            {{ expense.description }}
        </template>
      </CardItem>
    </div>


  <BaseEmptyPlaceholder
    v-if="!expenseStore.expenses.length && !isLoading && !hasActiveFilters"
    title="No Expenses"
    description="You have not created any expenses yet."
  >
    <template #default>
      <Button
        variant="outline"
        class="mt-4"
        size="sm"
        @click="expenseStore.showExpenseModal = true"
      >
        <Plus class="w-4 h-4" /> Add New Expense
      </Button>
    </template>
  </BaseEmptyPlaceholder>
  <BaseEmptyPlaceholder
    v-if="!expenseStore.expenses.length && !isLoading && hasActiveFilters"
    title="No Records Found"
    description="No expenses match the applied filters."
  />
</template>

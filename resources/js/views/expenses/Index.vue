<script setup>
import { computed, onMounted, reactive } from 'vue'
import ExpenseModal from '@/js/components/modal/ExpenseModal.vue'
import { Plus } from 'lucide-vue-next'
import Button from '@/js/components/ui/button/Button.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import BaseBreadcrumb from '@/js/components/base/BaseBreadcrumb.vue'
import { useExpenseStore } from '@/js/stores/expense'
import Expenses from '@/js/components/expenses/Expenses.vue'
import { useCategoryStore } from '@/js/stores/category'
import TransactionFilters from '@/js/components/filters/TransactionFilters.vue'

const expenseStore = useExpenseStore()
const categoryStore = useCategoryStore()

const breadcrumbData = [
  { title: 'Home', href:"/dashboard", active: false },
  { title: 'Expenses', href:"", active: true },
]

const filters = reactive({
  search: '',
  category_id: 'all',
  month: 'all',
  year: 'all',
})

const normalizedFilters = computed(() => {
  const payload = {
    search: filters.search?.trim(),
    category_id: filters.category_id,
    month: filters.month,
    year: filters.year,
  }

  return Object.fromEntries(
    Object.entries(payload).filter(([, value]) => value && value !== 'all'),
  )
})

const resetFilters = () => {
  filters.search = ''
  filters.category_id = 'all'
  filters.month = 'all'
  filters.year = 'all'
}

onMounted(() => {
  categoryStore.fetchCategories({ type: 'EXPENSE', limit: 'all' })
})
</script>

<template>
  <div class="flex items-center justify-between mb-4">
    <div class="flex flex-col space-y-2">
      <h1 class="text-lg font-semibold md:text-2xl">
        Expenses
      </h1>
      <BaseBreadcrumb :data="breadcrumbData" />
    </div>
    <Button @click="expenseStore.showExpenseModal = true">
      <Plus class="w-4 h-4" /> Add New Expense
    </Button>
  </div>

  <TransactionFilters
    :filters="filters"
    :categories="categoryStore.categories"
    id-prefix="expense"
    @reset="resetFilters"
  />
  
  <Expenses :filters="normalizedFilters" />

  <ExpenseModal />
</template>

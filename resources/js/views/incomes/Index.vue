<script setup>
import { computed, onMounted, reactive } from 'vue'
import { Plus } from 'lucide-vue-next'
import Button from '@/js/components/ui/button/Button.vue'
import BaseBreadcrumb from '@/js/components/base/BaseBreadcrumb.vue'
import { useIncomeStore } from '@/js/stores/income'
import Incomes from '@/js/components/incomes/incomes.vue'
import { useCategoryStore } from '@/js/stores/category'
import TransactionFilters from '@/js/components/filters/TransactionFilters.vue'
import ViewToggle from '@/js/components/filters/ViewToggle.vue'
import { useViewPreferenceStore } from '@/js/stores/viewPreference'

const incomeStore = useIncomeStore()
const categoryStore = useCategoryStore()
const viewPreferenceStore = useViewPreferenceStore()
const isTableView = computed({
  get: () => viewPreferenceStore.incomeView === 'table',
  set: (value) => viewPreferenceStore.setIncomeView(value ? 'table' : 'card'),
})

const breadcrumbData = [
  { title: 'Home', href:"/dashboard", active: false },
  { title: 'Incomes', href:"", active: true },
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
  viewPreferenceStore.initIncomeView()
  categoryStore.fetchCategories({ type: 'INCOME', limit: 'all' })
})
</script>

<template>
  <div class="flex items-center justify-between mb-4">
    <div class="flex flex-col space-y-2">
      <h1 class="text-lg font-semibold md:text-2xl">
        Incomes
      </h1>
      <BaseBreadcrumb :data="breadcrumbData" />
    </div>
    <Button @click="incomeStore.showIncomeModal = true">
      <Plus class="w-4 h-4" /> Add New Income
    </Button>
  </div>
  <TransactionFilters
    :filters="filters"
    :categories="categoryStore.categories"
    id-prefix="income"
    @reset="resetFilters"
  />
  <div class="mb-4 flex items-center justify-end">
    <ViewToggle
      v-model:isTableView="isTableView"
      active-class="bg-primary/80 text-white"
      hover-class="hover:bg-primary hover:text-white"
      wrapper-class="inline-flex items-center rounded-md border bg-card p-1"
      gap-class="inline-flex items-center gap-1"
    />
  </div>
<!--
  <BaseEmptyPlaceholder  title="No Incomes" description="You have not created any Incomes yet.">
    <template #default>
      <Button
        variant="outline"
        class="mt-4"
        size="sm"
      >
        <Plus class="w-4 h-4" /> Add New Income
      </Button>
    </template>
  </BaseEmptyPlaceholder> -->
  <Incomes :filters="normalizedFilters" :view="isTableView ? 'table' : 'card'" />
</template>

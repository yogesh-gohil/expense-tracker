<script setup>
import { ref, computed } from 'vue'
import { watchDebounced } from '@vueuse/core'
import { useIncomeStore } from '@/js/stores/income'
import { useAuthStore } from '@/js/stores/auth'
import CardItem from '@/js/components/base/CardItem.vue'
import Button from '@/js/components/ui/button/Button.vue'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import { Badge } from '@/js/components/ui/badge'
import { ArrowDown, ArrowUp, ArrowUpDown, Plus, Trash2 } from 'lucide-vue-next'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/js/components/ui/table'
import ConfirmDialog from '@/js/components/base/ConfirmDialog.vue'
import { useToast } from '@/js/components/ui/toast/use-toast'
import { formatCurrencyFromCents } from '@/js/lib/currency'


const props = defineProps({
  filters: {
    type: Object,
    default: () => ({}),
  },
  view: {
    type: String,
    default: 'card',
  },
})

const incomeStore = useIncomeStore()
const authStore = useAuthStore()
const { toast } = useToast()
const isLoading = ref(false)
const sortBy = ref(null)
const sortDir = ref('desc')
const isDeleteDialogOpen = ref(false)
const incomeToDelete = ref(null)
const params = computed(() => ({
  expand: 'category',
  ...props.filters,
  ...(sortBy.value ? { sort_by: sortBy.value, sort_dir: sortDir.value } : {}),
}))
const hasActiveFilters = computed(() => Object.keys(props.filters || {}).length > 0)
const isTableView = computed(() => props.view === 'table')

const fetchIncomes = async () => {
  isLoading.value = true
  await incomeStore.fetchIncomes(params.value)
  isLoading.value = false
}
const editIncome = (income) => {
  incomeStore.incomeData = {...income}
  incomeStore.showIncomeModal = true
}

const formatMoney = (amount) => formatCurrencyFromCents(amount, authStore.currentUser?.currency)

const requestDelete = (income) => {
  incomeToDelete.value = income
  isDeleteDialogOpen.value = true
}

const confirmDelete = async () => {
  if (!incomeToDelete.value) return
  try {
    await incomeStore.removeIncome(incomeToDelete.value.id)
    toast({
      title: 'Deleted',
      description: 'Income deleted successfully.',
    })
  } catch (error) {
    const message = error?.response?.data?.message || 'Unable to delete this income.'
    toast({
      title: 'Delete failed',
      description: message,
      variant: 'destructive',
    })
  } finally {
    isDeleteDialogOpen.value = false
    incomeToDelete.value = null
  }
}

const toggleSort = (key) => {
  if (sortBy.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    return
  }

  sortBy.value = key
  sortDir.value = 'asc'
}

const sortIcon = (key) => {
  if (sortBy.value !== key) return ArrowUpDown
  return sortDir.value === 'asc' ? ArrowUp : ArrowDown
}

watchDebounced(
  () => [props.filters, sortBy.value, sortDir.value],
  () => {
    fetchIncomes()
  },
  { deep: true, debounce: 300, maxWait: 800, immediate: true },
)
</script>

<template>
    <div v-if="isLoading" >
      <div v-for="i in 4" class="flex items-center border space-x-4 bg-gray-50 dark:bg-gray-900 p-4 mb-4 rounded-lg">
        <Skeleton class="h-12 w-12 rounded-full" />
        <div class="space-y-2">
          <Skeleton class="h-4 w-[250px]" />
          <Skeleton class="h-4 w-[200px]" />
        </div>
      </div>
    </div>
    <div v-else-if="!isTableView">
      <CardItem
        v-for="income in incomeStore.incomes"
        :key="income.id"
        @edit="editIncome(income)"
        @delete="requestDelete(income)"
      >
        <template #title>
          <div class="flex items-center justify-between space-x-4">
            <span>{{ income.title }}</span>
            <span class="text-primary">{{ formatMoney(income.amount) }}</span>
            <Badge variant="outline">{{income.category.name}}</Badge>
          </div>
        </template>
        <template #description>
            {{ income.description }}
        </template>
      </CardItem>
    </div>
    <div v-else>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('title')">
                Name
                <component :is="sortIcon('title')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead>
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('category_name')">
                Category
                <component :is="sortIcon('category_name')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead>
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('date')">
                Date
                <component :is="sortIcon('date')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead class="text-right">
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('amount')">
                Amount
                <component :is="sortIcon('amount')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead class="text-right">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="income in incomeStore.incomes" :key="income.id">
            <TableCell class="font-medium">
              <div>{{ income.title }}</div>
              <div v-if="income.description" class="text-xs text-muted-foreground">
                {{ income.description }}
              </div>
            </TableCell>
            <TableCell>{{ income.category?.name ?? '—' }}</TableCell>
            <TableCell>{{ income.date }}</TableCell>
            <TableCell class="text-right text-primary">{{ formatMoney(income.amount) }}</TableCell>
            <TableCell class="text-right">
              <div class="flex items-center justify-end gap-2">
                <Button variant="ghost" size="xs" @click="editIncome(income)">Edit</Button>
                <Button
                  variant="ghost"
                  size="xs"
                  class="text-red-500 hover:text-red-600"
                  @click="requestDelete(income)"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>


  <BaseEmptyPlaceholder
    v-if="!incomeStore.incomes.length && !isLoading && !hasActiveFilters"
    title="No Incomes"
    description="You have not created any incomes record yet."
  >
    <template #default>
      <Button
        variant="outline"
        class="mt-4"
        size="sm"
        @click="incomeStore.showIncomeModal = true"
      >
        <Plus class="w-4 h-4" /> Add New Income
      </Button>
    </template>
  </BaseEmptyPlaceholder>
  <BaseEmptyPlaceholder
    v-if="!incomeStore.incomes.length && !isLoading && hasActiveFilters"
    title="No Records Found"
    description="No incomes match the applied filters."
  />

  <ConfirmDialog
    v-model:open="isDeleteDialogOpen"
    title="Delete income?"
    description="This will permanently delete the income."
    confirm-text="Delete"
    @confirm="confirmDelete"
  />
</template>

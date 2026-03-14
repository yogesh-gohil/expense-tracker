<script setup>
import { ref, computed, watch } from 'vue'
import { watchDebounced } from '@vueuse/core'
import { useExpenseStore } from '@/js/stores/expense'
import { useAuthStore } from '@/js/stores/auth'
import { useCopilotStore } from '@/js/stores/copilot'
import CardItem from '@/js/components/base/CardItem.vue'
import Button from '@/js/components/ui/button/Button.vue'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import { Badge } from '@/js/components/ui/badge'
import { ArrowDown, ArrowUp, ArrowUpDown, Plus, Trash2 } from 'lucide-vue-next'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/js/components/ui/table'
import ConfirmDialog from '@/js/components/base/ConfirmDialog.vue'
import Paginator from '@/js/components/base/Paginator.vue'
import { useToast } from '@/js/components/ui/toast/use-toast'
import TableLoading from '@/js/components/base/TableLoading.vue'
import Textarea from '../ui/textarea/Textarea.vue'
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

const expenseStore = useExpenseStore()
const authStore = useAuthStore()
const copilotStore = useCopilotStore()
const { toast } = useToast()

const isLoading = ref(true)
const sortBy = ref(null)
const sortDir = ref('desc')
const page = ref(1)
const limit = ref(10)
const isDeleteDialogOpen = ref(false)
const expenseToDelete = ref(null)
const params = computed(() => ({
  expand: 'category',
  ...props.filters,
  ...(sortBy.value ? { sort_by: sortBy.value, sort_dir: sortDir.value } : {}),
  page: page.value,
  limit: limit.value,
}))
const hasActiveFilters = computed(() => Object.keys(props.filters || {}).length > 0)
const isTableView = computed(() => props.view === 'table')

const fetchExpenses = async () => {
  isLoading.value = true
  try {
    await expenseStore.fetchExpenses(params.value)
  } finally {
    isLoading.value = false
  }
}
const editExpense = (expense) => {
  expenseStore.expenseData = {...expense}
  expenseStore.showExpenseModal = true
}

const formatMoney = (amount) => formatCurrencyFromCents(amount, authStore.currentUser?.currency)

const requestDelete = (expense) => {
  expenseToDelete.value = expense
  isDeleteDialogOpen.value = true
}

const confirmDelete = async () => {
  if (!expenseToDelete.value) return
  try {
    await expenseStore.removeExpense(expenseToDelete.value.id)
    toast({
      title: 'Deleted',
      description: 'Expense deleted successfully.',
    })
  } catch (error) {
    // Error toasts are handled globally by axios interceptor.
  } finally {
    isDeleteDialogOpen.value = false
    expenseToDelete.value = null
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
  () => [props.filters, sortBy.value, sortDir.value, page.value, limit.value],
  () => {
    fetchExpenses()
  },
  { deep: true, debounce: 300, maxWait: 800, immediate: true },
)

watch(
  () => props.filters,
  () => {
    page.value = 1
  },
  { deep: true },
)

watch(
  () => expenseStore.pagination?.last_page,
  (lastPage) => {
    if (!lastPage) return
    if (page.value > lastPage) {
      page.value = Math.max(1, lastPage)
    }
  },
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
    <div v-if="isLoading && !isTableView">
      <div v-for="i in 4" class="flex items-center border space-x-4 bg-gray-50 dark:bg-gray-900 p-4 mb-4 rounded-lg">
        <Skeleton class="h-12 w-12 rounded-full" />
        <div class="space-y-2">
          <Skeleton class="h-4 w-[250px]" />
          <Skeleton class="h-4 w-[200px]" />
        </div>
      </div>
    </div>
    <div v-else-if="isLoading && isTableView">
      <TableLoading />
    </div>
    <div v-else-if="!isTableView">
      <CardItem
        v-for="expense in expenseStore.expenses"
        :key="expense.id"
        @edit="editExpense(expense)"
        @delete="requestDelete(expense)"
      >
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
    <div v-else>
      <div class="rounded-lg border bg-card overflow-hidden">
        <Table unstyled>
        <TableHeader>
          <TableRow>
            <TableHead>
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('title')">
                Name <component :is="sortIcon('title')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead>
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('category_name')">
                Category <component :is="sortIcon('category_name')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead>
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('date')">
                Date <component :is="sortIcon('date')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead class="text-right">
              <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground" @click="toggleSort('amount')">
                Amount <component :is="sortIcon('amount')" class="h-3 w-3" />
              </button>
            </TableHead>
            <TableHead class="text-right">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="expense in expenseStore.expenses" :key="expense.id">
            <TableCell class="font-medium">
              <div>{{ expense.title }}</div>
              <div v-if="expense.description" class="text-xs text-muted-foreground">
                {{ expense.description }}
              </div>
            </TableCell>
            <TableCell>{{ expense.category?.name ?? '—' }}</TableCell>
            <TableCell>{{ expense.date }}</TableCell>
            <TableCell class="text-right text-primary">{{ formatMoney(expense.amount) }}</TableCell>
            <TableCell class="text-right">
              <div class="flex items-center justify-end gap-2">
                <Button variant="ghost" size="xs" @click="editExpense(expense)">Edit</Button>
                <Button
                  variant="ghost"
                  size="xs"
                  class="text-red-500 hover:text-red-600"
                  @click="requestDelete(expense)"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
        </Table>
        <div v-if="expenseStore.pagination?.total > limit" class="border-t bg-background px-4 py-3">
          <Paginator
            :meta="expenseStore.pagination"
            @page-change="page = $event"
          />
        </div>
      </div>
    </div>

  <div
    v-if="!isTableView && expenseStore.pagination?.total > limit"
    class="mt-4"
  >
    <Paginator :meta="expenseStore.pagination" @page-change="page = $event" />
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

  <ConfirmDialog
    v-model:open="isDeleteDialogOpen"
    title="Delete expense?"
    description="This will permanently delete the expense."
    confirm-text="Delete"
    @confirm="confirmDelete"
  />
</template>

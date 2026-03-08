<script setup>
import { ref, reactive } from 'vue'
import { useIncomeStore } from '@/js/stores/income'
import { useAuthStore } from '@/js/stores/auth'
import CardItem from '@/js/components/base/CardItem.vue'
import Button from '@/js/components/ui/button/Button.vue'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import { Badge } from '@/js/components/ui/badge'
import { Plus } from 'lucide-vue-next'
import { formatCurrencyFromCents } from '@/js/lib/currency'


const incomeStore = useIncomeStore()
const authStore = useAuthStore()
const isLoading = ref(false)
const params = reactive({
  expand: 'category',
})

const fetchIncomes = async () => {
  isLoading.value = true
  await incomeStore.fetchIncomes(params)
  isLoading.value = false
}
const editIncome = (income) => {
  incomeStore.incomeData = {...income}
  incomeStore.showIncomeModal = true
}

const formatMoney = (amount) => formatCurrencyFromCents(amount, authStore.currentUser?.currency)

fetchIncomes()
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
    <div v-else>
      <CardItem v-for="income in incomeStore.incomes" :key="income.id" @edit="editIncome(income)">
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


  <BaseEmptyPlaceholder v-if="!incomeStore.incomes.length && !isLoading" title="No Incomes" description="You have not created any incomes record yet.">
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
</template>

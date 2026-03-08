<script setup>
import ExpenseModal from '@/js/components/modal/ExpenseModal.vue'
import { Plus } from 'lucide-vue-next'
import Button from '@/js/components/ui/button/Button.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import BaseBreadcrumb from '@/js/components/base/BaseBreadCrumb.vue'
import { useExpenseStore } from '@/js/stores/expense'
import Expenses from '@/js/components/expenses/Expenses.vue'

const expenseStore = useExpenseStore()

const breadcrumbData = [
  { title: 'Home', href:"/dashboard", active: false },
  { title: 'Expenses', href:"", active: true },
]
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
  
  <Expenses />

  <ExpenseModal />
</template>

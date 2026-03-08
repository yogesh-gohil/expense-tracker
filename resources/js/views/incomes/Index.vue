<script setup>
import IncomeModal from '@/js/components/modal/IncomeModal.vue'
import { Plus } from 'lucide-vue-next'
import Button from '@/js/components/ui/button/Button.vue'
import BaseBreadcrumb from '@/js/components/base/BaseBreadcrumb.vue'
import { useIncomeStore } from '@/js/stores/income'
import Incomes from '@/js/components/incomes/incomes.vue'

const incomeStore = useIncomeStore()

const breadcrumbData = [
  { title: 'Home', href:"/dashboard", active: false },
  { title: 'Incomes', href:"", active: true },
]
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
  <Incomes />
  <IncomeModal />
</template>

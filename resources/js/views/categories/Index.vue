<script setup>
import CategoryModal from '@/js/components/modal/CategoryModal.vue'
import { Plus } from 'lucide-vue-next'
import Button from '@/js/components/ui/button/Button.vue'
import BaseBreadcrumb from '@/js/components/base/BaseBreadcrumb.vue'
import { useCategoryStore } from '@/js/stores/category'
import Categories from '@/js/components/categories/Categories.vue'

const categoryStore = useCategoryStore()

const breadcrumbData = [
  { title: 'Home', href:"/dashboard", active: false },
  { title: 'Categories', href:"", active: true },
]
</script>

<template>
  <div class="flex items-center justify-between mb-4">
    <div class="flex flex-col space-y-2">
      <h1 class="text-lg font-semibold md:text-2xl">
        Categories
      </h1>
      <BaseBreadcrumb :data="breadcrumbData" />
    </div>
    <Button @click="categoryStore.showCategoryModal = true">
      <Plus class="w-4 h-4" /> Add New Category
    </Button>
  </div>

  <Categories />

  <CategoryModal />
</template>

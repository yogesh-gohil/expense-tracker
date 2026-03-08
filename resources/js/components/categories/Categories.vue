<script setup>
import { ref, reactive } from 'vue'
import { useCategoryStore } from '@/js/stores/category'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/js/components/ui/tabs'
import Button from '@/js/components/ui/button/Button.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import { Plus } from 'lucide-vue-next'
import CategoryList from './partials/CategoryList.vue'

const categoryStore = useCategoryStore()
const isLoading = ref(false)
const params = reactive({
  type: 'expense'
})

const fetchCategories = async () => {

  isLoading.value = true
  await categoryStore.fetchCategories(params)
  isLoading.value = false
}
const editCategory = (category) => {
  console.log(category);

  categoryStore.categoryData = {...category}
  categoryStore.showCategoryModal = true
}

fetchCategories()
</script>

<template>
  <Tabs
    v-model="params.type"
    default-value="expense"
    @update:modelValue="fetchCategories"
  >
    <TabsList>
      <TabsTrigger value="expense">
        Expense
      </TabsTrigger>
      <TabsTrigger value="income">
        Income
      </TabsTrigger>
    </TabsList>
    <hr class="mt-1 mb-4">
    <TabsContent value="expense">
      <CategoryList @edit="editCategory" :is-loading="isLoading" />
    </TabsContent>

    <TabsContent value="income">
      <CategoryList @edit="editCategory" :is-loading="isLoading" />
    </TabsContent>
  </Tabs>

  <BaseEmptyPlaceholder v-if="!categoryStore.categories.length && !isLoading" title="No Categories" description="You have not created any categories yet.">
    <template #default>
      <Button
        variant="outline"
        class="mt-4"
        size="sm"
        @click="categoryStore.isCategoryModalOpen = true"
      >
        <Plus class="w-4 h-4" /> Add New Category
      </Button>
    </template>
  </BaseEmptyPlaceholder>
</template>

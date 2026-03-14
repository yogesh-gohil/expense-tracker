<script setup>
import { ref, reactive, watch } from 'vue'
import { useCategoryStore } from '@/js/stores/category'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/js/components/ui/tabs'
import Button from '@/js/components/ui/button/Button.vue'
import BaseEmptyPlaceholder from '@/js/components/base/BaseEmptyPlaceholder.vue'
import { Plus } from 'lucide-vue-next'
import CategoryList from './partials/CategoryList.vue'
import Paginator from '@/js/components/base/Paginator.vue'

const categoryStore = useCategoryStore()
const isLoading = ref(true)
const page = ref(1)
const limit = ref(10)
const params = reactive({
  type: 'expense',
  page: page.value,
  limit: limit.value,
})

const fetchCategories = async () => {
  isLoading.value = true
  try {
    params.page = page.value
    params.limit = limit.value
    await categoryStore.fetchCategories(params)
  } finally {
    isLoading.value = false
  }
}

const onTabChange = () => {
  page.value = 1
  fetchCategories()
}
const editCategory = (category) => {
  console.log(category);

  categoryStore.categoryData = {...category}
  categoryStore.showCategoryModal = true
}

fetchCategories()

watch(
  () => categoryStore.pagination?.last_page,
  (lastPage) => {
    if (!lastPage) return
    if (page.value > lastPage) {
      page.value = Math.max(1, lastPage)
      fetchCategories()
    }
  },
)
</script>

<template>
  <Tabs
    v-model="params.type"
    default-value="expense"
    @update:modelValue="onTabChange"
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
  <div v-if="categoryStore.pagination?.total > limit" class="mt-4">
    <Paginator
      :meta="categoryStore.pagination"
      @page-change="page = $event; fetchCategories()"
    />
  </div>

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

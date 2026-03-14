<script setup>
import { ref } from 'vue'
import { useCategoryStore } from '@/js/stores/category'
import CardItem from '@/js/components/base/CardItem.vue'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'
import ConfirmDialog from '@/js/components/base/ConfirmDialog.vue'
import { useToast } from '@/js/components/ui/toast/use-toast'

const categoryStore = useCategoryStore()
const { toast } = useToast()
const emit = defineEmits(['edit'])

const props = defineProps({
  isLoading: {
    type: Boolean,
    required: false,
  }
})

const isDeleteDialogOpen = ref(false)
const categoryToDelete = ref(null)

const requestDelete = (category) => {
  categoryToDelete.value = category
  isDeleteDialogOpen.value = true
}

const confirmDelete = async () => {
  if (!categoryToDelete.value) return
  try {
    await categoryStore.removeCategory(categoryToDelete.value.id)
    toast({
      title: 'Deleted',
      description: 'Category deleted successfully.',
    })
  } catch (error) {
    const message = error?.response?.data?.message
      || error?.response?.data?.errors?.category?.[0]
      || 'Unable to delete this category.'
    toast({
      title: 'Delete failed',
      description: message,
      variant: 'destructive',
    })
  } finally {
    isDeleteDialogOpen.value = false
    categoryToDelete.value = null
  }
}
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
    <template v-else>
      <CardItem
        v-for="category in categoryStore.categories"
        :key="category.id"
        @edit="emit('edit', category)"
        @delete="requestDelete(category)"
      >
        <template #title> {{ category.name }} </template>
        <template #description>
          {{ category.description }}
        </template>
      </CardItem>
    </template>

  <ConfirmDialog
    v-model:open="isDeleteDialogOpen"
    title="Delete category?"
    description="This will permanently delete the category."
    confirm-text="Delete"
    @confirm="confirmDelete"
  />
</template>

<script setup>
import { useCategoryStore } from '@/js/stores/category'
import CardItem from '@/js/components/base/CardItem.vue'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'

const categoryStore = useCategoryStore()
const emit = defineEmits(['edit'])

const props = defineProps({
  isLoading: {
    type: Boolean,
    required: false,
  }
})
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
      <CardItem v-for="category in categoryStore.categories" :key="category.id" @edit="emit('edit', category)">
        <template #title> {{ category.name }} </template>
        <template #description>
          {{ category.description }}
        </template>
      </CardItem>
    </template>
</template>

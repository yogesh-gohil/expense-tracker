<script setup>
import Card from '../ui/card/Card.vue'
import CardHeader from '../ui/card/CardHeader.vue'
import CardTitle from '../ui/card/CardTitle.vue'
import CardDescription from '../ui/card/CardDescription.vue'
import { Pencil, Trash2 } from 'lucide-vue-next'
import { useCategoryStore } from '@/js/stores/category'

const props = defineProps({
  category: {
    type: Object,
    required: true,
    default: null
  }
})
const categoryStore = useCategoryStore();

const editCategory = () => {
  categoryStore.categoryData = {...props.category}
  categoryStore.showCategoryModal = true
}
</script>

  <template>

    <Card class="my-2 hover:shadow-md hover:border hover:border-gray-400 dark:hover:border-gray-600 transition duration-300">
      <CardHeader>
        <div class="flex justify-between items-center">
          <div>
            <CardTitle>{{ category.name }} </CardTitle>
            <CardDescription>{{ category.description }}</CardDescription>
          </div>
          <div class="flex space-x-2">
            <Pencil class="h-4 w-4 cursor-pointer text-gray-500 dark:text-gray-400" @click="editCategory"/>
            <Trash2 class="h-4 w-4 cursor-pointer text-red-500"/>
          </div>
        </div>
      </CardHeader>
    </Card>
  </template>

<script setup>
import Card from '../ui/card/Card.vue'
import CardHeader from '../ui/card/CardHeader.vue'
import CardTitle from '../ui/card/CardTitle.vue'
import CardDescription from '../ui/card/CardDescription.vue'
import { Pencil, Trash2 } from 'lucide-vue-next'

const emits = defineEmits(['edit','delete'])
// const onEdit = () =>
// const onDelete = () => emits('delete')
</script>

  <template>
    <Card class="my-2 border border-border/80 bg-card  transition-colors duration-300 hover:border-primary/50">
      <CardHeader>
        <div class="flex justify-between items-center">
          <div>
            <CardTitle><slot name="title"></slot></CardTitle>
            <CardDescription><slot name="description"></slot></CardDescription>
          </div>
          <div class="flex space-x-2">
            <Pencil class="h-4 w-4 cursor-pointer text-gray-500 dark:text-gray-400" @click="emits('edit')"/>
            <Trash2 class="h-4 w-4 cursor-pointer text-red-500" @click="emits('delete')"/>
          </div>
        </div>
      </CardHeader>
    </Card>
  </template>

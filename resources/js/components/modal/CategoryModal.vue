<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/js/components/ui/dialog'
import { Button } from '@/js/components/ui/button'
import { Input } from '@/js/components/ui/input'
import { Label } from '@/js/components/ui/label'
import { useToast } from '@/js/components/ui/toast/use-toast'
import Textarea from '../ui/textarea/Textarea.vue'

import { useCategoryStore } from '@/js/stores/category'
import { useVuelidate } from '@vuelidate/core'
import { required, helpers } from '@vuelidate/validators'
import { computed, onBeforeMount } from 'vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/js/components/ui/select'
const categoryStore = useCategoryStore()
const { toast } = useToast()

const rules = {
  name: {
    required: helpers.withMessage('Field is required.', required),
  },
}

const v$ = useVuelidate(rules, computed(() => categoryStore.categoryData), { $scope: false })

onBeforeMount(() => {
  categoryStore.resetCategoryData()
})

const isEdit = computed(() => categoryStore.categoryData?.id)

const onSubmit = async () => {

  v$.value.$touch()

  if (v$.value.$invalid)
    return true

  const action = isEdit.value ? categoryStore.updateCategory : categoryStore.addCategory

  const res = await action(categoryStore.categoryData)

  if (res?.data) {
    toast({
      title: 'Success',
      description: `Category ${isEdit.value ? 'Updated': 'Added'} Successfully!`,
    })

    v$.value.$reset()
    categoryStore.resetCategoryData()
  }
}

</script>

<template>
  <Dialog :open="categoryStore.showCategoryModal" @update:open="categoryStore.resetCategoryData">
    <DialogTrigger>
      <slot name="trigger" />
    </DialogTrigger>

    <DialogContent>
      <DialogHeader>
        <DialogTitle>Add New Category</DialogTitle>
        <DialogDescription>
          <!-- write suetable description for category modal -->
            Fill out the form to add a new category. Ensure the name is unique.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">

        <div class="grid gap-4">
          <div class="grid gap-2">
            <Label required for="name">Name</Label>
            <Input
              v-model="categoryStore.categoryData.name"
              :error="v$.name.$error && v$.name.$errors[0].$message"
              id="name"
            />
          </div>
          <div class="grid gap-2">
            <Label required for="name">Type</Label>
            <Select v-model="categoryStore.categoryData.type">
              <SelectTrigger>
                <SelectValue placeholder="Select a fruit" />
              </SelectTrigger>
              <SelectContent>
                  <SelectItem value="EXPENSE">
                    Expense
                  </SelectItem>
                  <SelectItem value="INCOME">
                    Income
                  </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea
              v-model="categoryStore.categoryData.description"
              id="description"
            />
          </div>
        </div>

      <DialogFooter>
        <Button
          variant="outline"
          type="button"
          class="mt-4"
          @click="categoryStore.showCategoryModal = false"
        >
          Cancel
        </Button>

        <Button type="submit" class="mt-4">
          Save
        </Button>
      </DialogFooter>
    </form>
    </DialogContent>
  </Dialog>
</template>

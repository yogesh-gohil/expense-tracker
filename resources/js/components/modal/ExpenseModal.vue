<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/js/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/js/components/ui/select'
import { Button } from '@/js/components/ui/button'
import { Input } from '@/js/components/ui/input'
import { Label } from '@/js/components/ui/label'
import { useToast } from '@/js/components/ui/toast/use-toast'
import Textarea from '../ui/textarea/Textarea.vue'
import { useExpenseStore } from '@/js/stores/expense'
import { useVuelidate } from '@vuelidate/core'
import { minValue, required, helpers } from '@vuelidate/validators'
import { computed, onBeforeMount } from 'vue'
import BaseDatePicker from '../base/BaseDatePicker.vue'
import { useCategoryStore } from '@/js/stores/category'

const categoryStore = useCategoryStore()
const expenseStore = useExpenseStore()
const { toast } = useToast()

const rules = {
  title: {
    required: helpers.withMessage('Field is required.', required),
  },
  amount: {
    required: helpers.withMessage('Field is required.', required),
    minValue: helpers.withMessage('Amount must be greater than 0.', minValue(1)),
  },
  category_id: {
    required: helpers.withMessage('Field is required.', required),
  },
  date: {
    required: helpers.withMessage('Field is required.', required),
  },
}

const v$ = useVuelidate(rules, computed(() => expenseStore.expenseData), { $scope: false })

const isEdit = computed(() => expenseStore.expenseData?.id)

onBeforeMount(() => {
  categoryStore.fetchCategories({type: 'EXPENSE'})
})

const onSubmit = async () => {
  v$.value.$touch()

  if (v$.value.$invalid)
    return true

  const action = isEdit.value ? expenseStore.updateExpense : expenseStore.addExpense
  const payload = {
    ...expenseStore.expenseData,
    description: expenseStore.expenseData.description?.trim() || null,
  }
  const res = await action(payload)

  if (res?.data) {
    toast({
      title: 'Success',
      description: `Expense ${isEdit.value ? 'Updated': 'Added'} Successfully!`,
    })

    expenseStore.resetExpenseData()
  }
}
</script>

<template>
  <Dialog :open="expenseStore.showExpenseModal" @update:open="expenseStore.resetExpenseData">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{isEdit ? 'Edit Expense' : 'Add New Expense'}}</DialogTitle>
        <DialogDescription v-if="!isEdit">
            Fill out the form to add a new expense.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4">

          <div class="grid gap-2">
            <Label required for="name">Title</Label>
            <Input
              v-model.lazy="expenseStore.expenseData.title"
              :error="v$.title.$error && v$.title.$errors[0].$message"
              id="name"
            />
            </div>

          <div class="grid gap-2">
            <Label required for="name">Category</Label>
            <Select
              v-model="expenseStore.expenseData.category_id"
              :error="v$.category_id.$error && v$.category_id.$errors[0].$message"
            >
              <SelectTrigger>
                <SelectValue placeholder="Select a Category" />
              </SelectTrigger>
              <SelectContent>
                  <SelectItem v-for="category in categoryStore.categories" :value="category.id" :key="category.id">
                    {{ category.name }}
                  </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid gap-2">
              <Label required for="name">Amount</Label>
              <Input
                is-money
                v-model.number="expenseStore.expenseData.amount"
                :error="v$.amount.$error && v$.amount.$errors[0].$message"
                id="amount"
              />
          </div>

          <div class="grid gap-2">
            <Label required for="date">Date</Label>
            <BaseDatePicker v-model="expenseStore.expenseData.date"/>
            <span v-if="v$.date.$error" class="text-sm text-red-500">
              {{ v$.date.$errors[0].$message }}
            </span>
          </div>

          <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea
              v-model="expenseStore.expenseData.description"
              id="description"
            ></Textarea>
          </div>

        </div>
        <DialogFooter>
          <Button
            variant="outline"
            type="button"
            class="mt-4"
            @click="expenseStore.resetExpenseData"
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

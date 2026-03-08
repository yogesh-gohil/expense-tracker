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
import { useIncomeStore } from '@/js/stores/income'
import { useVuelidate } from '@vuelidate/core'
import { minValue, required, helpers } from '@vuelidate/validators'
import { computed, watch } from 'vue'
import BaseDatePicker from '../base/BaseDatePicker.vue'
import { useCategoryStore } from '@/js/stores/category'

const categoryStore = useCategoryStore()
const incomeStore = useIncomeStore()
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

const v$ = useVuelidate(rules, computed(() => incomeStore.incomeData), { $scope: false })

const isEdit = computed(() => incomeStore.incomeData?.id)

watch(
  () => incomeStore.showIncomeModal,
  (isOpen) => {
    v$.value.$reset()

    if (isOpen) {
      categoryStore.fetchCategories({ type: 'INCOME' })
    }
  },
  { immediate: true },
)

const onSubmit = async () => {
  v$.value.$touch()

  if (v$.value.$invalid)
    return true

  const action = isEdit.value ? incomeStore.updateIncome : incomeStore.addIncome
  const payload = {
    ...incomeStore.incomeData,
    description: incomeStore.incomeData.description?.trim() || null,
  }
  const res = await action(payload)

  if (res?.data) {
    toast({
      title: 'Success',
      description: `Income ${isEdit.value ? 'Updated' : 'Added'} Successfully!`,
    })

    v$.value.$reset()
    incomeStore.resetIncomeData()
  }
}
</script>

<template>
  <Dialog :open="incomeStore.showIncomeModal" @update:open="incomeStore.resetIncomeData">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ isEdit ? 'Edit Income' : 'Add New Income' }}</DialogTitle>
        <DialogDescription v-if="!isEdit">
          Fill out the form to add a new income.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <Label required for="income-title">Title</Label>
            <Input
              id="income-title"
              v-model.lazy="incomeStore.incomeData.title"
              :error="v$.title.$error && v$.title.$errors[0].$message"
            />
          </div>

          <div class="grid gap-2">
            <Label required for="income-category">Category</Label>
            <Select
              v-model="incomeStore.incomeData.category_id"
              :error="v$.category_id.$error && v$.category_id.$errors[0].$message"
            >
              <SelectTrigger id="income-category">
                <SelectValue placeholder="Select a Category" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="category in categoryStore.categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid gap-2">
            <Label required for="income-amount">Amount</Label>
            <Input
              id="income-amount"
              is-money
              v-model.number="incomeStore.incomeData.amount"
              :error="v$.amount.$error && v$.amount.$errors[0].$message"
            />
          </div>

          <div class="grid gap-2">
            <Label required for="income-date">Date</Label>
            <BaseDatePicker v-model="incomeStore.incomeData.date" />
            <span v-if="v$.date.$error" class="text-sm text-red-500">
              {{ v$.date.$errors[0].$message }}
            </span>
          </div>

          <div class="grid gap-2">
            <Label for="income-description">Description</Label>
            <Textarea id="income-description" v-model="incomeStore.incomeData.description" />
          </div>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            type="button"
            class="mt-4"
            @click="incomeStore.resetIncomeData"
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

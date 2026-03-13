<script setup>
import { computed, ref } from 'vue'
import { Input } from '@/js/components/ui/input'
import { Label } from '@/js/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/js/components/ui/select'
import Button from '@/js/components/ui/button/Button.vue'

const props = defineProps({
  filters: {
    type: Object,
    required: true,
  },
  categories: {
    type: Array,
    default: () => [],
  },
  idPrefix: {
    type: String,
    default: 'transaction',
  },
  yearsBack: {
    type: Number,
    default: 6,
  },
})

const emits = defineEmits(['reset'])

const showFilters = ref(false)

const monthOptions = [
  { value: 1, label: 'January' },
  { value: 2, label: 'February' },
  { value: 3, label: 'March' },
  { value: 4, label: 'April' },
  { value: 5, label: 'May' },
  { value: 6, label: 'June' },
  { value: 7, label: 'July' },
  { value: 8, label: 'August' },
  { value: 9, label: 'September' },
  { value: 10, label: 'October' },
  { value: 11, label: 'November' },
  { value: 12, label: 'December' },
]

const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let index = 0; index < props.yearsBack; index += 1) {
    years.push(currentYear - index)
  }
  return years
})
</script>

<template>
  <div class="mb-4 rounded-lg border p-4" :class="showFilters ? ' dark:border-primary/30' : ''">
    <div class="flex items-center justify-between">
      <Button variant="outline" size="sm" @click="showFilters = !showFilters">
        Filter
      </Button>
      <button class="text-sm text-muted-foreground hover:text-foreground" @click="emits('reset')">
        Clear
      </button>
    </div>
    <div v-if="showFilters" class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
      <div class="grid gap-1">
        <Label :for="`${idPrefix}-search`">Name</Label>
        <Input
          :id="`${idPrefix}-search`"
          v-model="filters.search"
          placeholder="Search by name"
          class="w-full"
        />
      </div>
      <div class="grid gap-1">
        <Label :for="`${idPrefix}-category`">Category</Label>
        <Select v-model="filters.category_id">
          <SelectTrigger :id="`${idPrefix}-category`" class="w-full">
            <SelectValue placeholder="All categories" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All categories</SelectItem>
            <SelectItem
              v-for="category in categories"
              :key="category.id"
              :value="String(category.id)"
            >
              {{ category.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div class="grid gap-1">
        <Label :for="`${idPrefix}-month`">Month</Label>
        <Select v-model="filters.month">
          <SelectTrigger :id="`${idPrefix}-month`" class="w-full">
            <SelectValue placeholder="All months" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All months</SelectItem>
            <SelectItem
              v-for="month in monthOptions"
              :key="month.value"
              :value="String(month.value)"
            >
              {{ month.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div class="grid gap-1">
        <Label :for="`${idPrefix}-year`">Year</Label>
        <Select v-model="filters.year">
          <SelectTrigger :id="`${idPrefix}-year`" class="w-full">
            <SelectValue placeholder="All years" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All years</SelectItem>
            <SelectItem
              v-for="year in yearOptions"
              :key="year"
              :value="String(year)"
            >
              {{ year }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>
  </div>
</template>

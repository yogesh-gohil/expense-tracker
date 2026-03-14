<script setup>
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  meta: {
    type: Object,
    required: true,
  },
  maxButtons: {
    type: Number,
    default: 8,
  },
})

const emit = defineEmits(['page-change'])

const totalPages = computed(() => Number(props.meta?.last_page || 1))
const currentPage = computed(() => Number(props.meta?.current_page || 1))
const shouldRender = computed(() => totalPages.value > 1)

const pageItems = computed(() => {
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 6) {
    return Array.from({ length: total }, (_, i) => ({ type: 'page', value: i + 1 }))
  }

  if (current <= 4) {
    return [
      { type: 'page', value: 1 },
      { type: 'page', value: 2 },
      { type: 'page', value: 3 },
      { type: 'page', value: 4 },
      { type: 'page', value: 5 },
      { type: 'ellipsis' },
      { type: 'page', value: total },
    ]
  }

  if (current >= total - 3) {
    return [
      { type: 'page', value: 1 },
      { type: 'ellipsis' },
      { type: 'page', value: total - 4 },
      { type: 'page', value: total - 3 },
      { type: 'page', value: total - 2 },
      { type: 'page', value: total - 1 },
      { type: 'page', value: total },
    ]
  }

  return [
    { type: 'page', value: 1 },
    { type: 'ellipsis' },
    { type: 'page', value: current - 1 },
    { type: 'page', value: current },
    { type: 'page', value: current + 1 },
    { type: 'ellipsis' },
    { type: 'page', value: total },
  ]
})

const from = computed(() => Number(props.meta?.from || 0))
const to = computed(() => Number(props.meta?.to || 0))
const total = computed(() => Number(props.meta?.total || 0))

const goTo = (page) => {
  if (page < 1 || page > totalPages.value || page === currentPage.value) return
  emit('page-change', page)
}
</script>

<template>
  <div v-if="shouldRender" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="text-sm text-muted-foreground">
      Showing {{ from }} to {{ to }} of {{ total }} results
    </div>
    <div class="inline-flex items-center overflow-hidden rounded-md border bg-card shadow-sm">
      <button
        class="inline-flex h-9 w-9 items-center justify-center text-muted-foreground transition-colors hover:bg-muted disabled:opacity-50"
        :disabled="currentPage <= 1"
        @click="goTo(currentPage - 1)"
      >
        <ChevronLeft class="h-4 w-4" />
      </button>
      <button
        v-for="item in pageItems"
        :key="item.type === 'page' ? item.value : `ellipsis-${item.value ?? ''}`"
        class="inline-flex h-9 w-9 items-center justify-center border-l text-sm transition-colors"
        :class="item.type === 'page'
          ? (item.value === currentPage ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted')
          : 'text-muted-foreground'"
        :disabled="item.type === 'ellipsis'"
        @click="item.type === 'page' && goTo(item.value)"
      >
        <span v-if="item.type === 'page'">{{ item.value }}</span>
        <span v-else>...</span>
      </button>
      <button
        class="inline-flex h-9 w-9 items-center justify-center border-l text-muted-foreground transition-colors hover:bg-muted disabled:opacity-50"
        :disabled="currentPage >= totalPages"
        @click="goTo(currentPage + 1)"
      >
        <ChevronRight class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

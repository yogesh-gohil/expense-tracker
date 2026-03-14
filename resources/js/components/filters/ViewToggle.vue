<script setup>
import { LayoutGrid, Table as TableIcon } from 'lucide-vue-next'

const props = defineProps({
  isTableView: {
    type: Boolean,
    required: true,
  },
  activeClass: {
    type: String,
    default: 'bg-accent text-accent-foreground',
  },
  hoverClass: {
    type: String,
    default: 'hover:bg-muted',
  },
  wrapperClass: {
    type: String,
    default: 'inline-flex items-center rounded-md border bg-card p-1',
  },
  gapClass: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:isTableView'])

const setTableView = (value) => {
  emit('update:isTableView', value)
}
</script>

<template>
  <div :class="wrapperClass">
    <div :class="gapClass">
      <button
        class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors"
        :class="`${hoverClass} ${isTableView ? activeClass : ''}`"
        @click="setTableView(true)"
      >
        <TableIcon class="h-4 w-4" />
      </button>
      <button
        class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors"
        :class="`${hoverClass} ${!isTableView ? activeClass : ''}`"
        @click="setTableView(false)"
      >
        <LayoutGrid class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

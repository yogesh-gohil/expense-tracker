<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/js/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <th :class="cn('h-12 px-4 text-left align-middle font-medium text-muted-foreground', props.class)">
    <slot />
  </th>
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/js/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    :class="
      cn(
        'rounded-xl border bg-card text-card-foreground shadow dark:bg-gray-900/50',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/js/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
  wrapperClass?: HTMLAttributes['class']
  unstyled?: boolean
}>()
</script>

<template>
  <div
    :class="
      cn(
        'relative w-full overflow-x-auto',
        !props.unstyled && 'rounded-lg border bg-card',
        props.wrapperClass,
      )
    "
  >
    <table :class="cn('w-full min-w-[720px] caption-bottom text-sm', props.class)">
      <slot />
    </table>
  </div>
</template>

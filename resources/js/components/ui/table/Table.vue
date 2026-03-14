<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/js/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div class="relative w-full overflow-x-auto rounded-lg border bg-card">
    <table :class="cn('w-full min-w-[720px] caption-bottom text-sm', props.class)">
      <slot />
    </table>
  </div>
</template>

<script setup lang="ts">
import type { SelectRootEmits, SelectRootProps } from 'radix-vue'
import { SelectRoot, useForwardPropsEmits } from 'radix-vue'

type SelectProps = SelectRootProps & {
  error?: string | boolean
}

const props = defineProps<SelectProps>()
const emits = defineEmits<SelectRootEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <SelectRoot v-bind="forwarded" :class="`${props.error ? 'border-red-500 focus-visible:ring-1 focus-visible:ring-red-300 ' : 'focus-visible:ring-1 focus-visible:ring-ring'}`">
    <slot />
  </SelectRoot>
  <span v-if="props.error" class="text-sm text-red-500">{{ props.error }}</span>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { HTMLAttributes } from 'vue'
import { useVModel } from '@vueuse/core'
import { Eye, EyeOff } from 'lucide-vue-next'
import { Input } from '@/js/components/ui/input'

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
  error?: string | boolean
  disabled?: boolean
  id?: string
  name?: string
  placeholder?: string
  autocomplete?: string
  required?: boolean
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const isVisible = ref(false)
</script>

<template>
  <div class="relative">
    <Input
      v-bind="$attrs"
      v-model="modelValue"
      :id="id"
      :name="name"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :required="required"
      :disabled="disabled"
      :error="error"
      :type="isVisible ? 'text' : 'password'"
      :class="['pr-10', props.class]"
    />
    <button
      type="button"
      class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground disabled:pointer-events-none disabled:opacity-60"
      :aria-label="isVisible ? 'Hide password' : 'Show password'"
      :disabled="disabled"
      @click="isVisible = !isVisible"
    >
      <EyeOff v-if="isVisible" class="h-4 w-4" />
      <Eye v-else class="h-4 w-4" />
    </button>
  </div>
</template>

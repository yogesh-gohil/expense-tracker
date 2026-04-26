<script setup lang="ts">
import { computed, ref } from 'vue'
import { Button } from '@/js/components/ui/button'
import { Loader2, Sparkles, Square } from 'lucide-vue-next'

const props = withDefaults(defineProps<{
  modelValue: string
  isLoading: boolean
  inputId: string
  label: string
  placeholder: string
  helperText?: string
  exampleText?: string
  compact?: boolean
}>(), {
  helperText: '',
  exampleText: '',
  compact: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'analyze'): void
  (e: 'stop'): void
}>()

const inputRef = ref<HTMLInputElement | null>(null)

const wrapperClass = computed(() => props.compact
  ? 'flex w-full items-center gap-2 rounded-full border border-transparent bg-background/70 px-3 py-2 shadow-sm ring-1 ring-border/50 transition focus-within:ring-2 focus-within:ring-primary/30'
  : 'flex items-center gap-3 rounded-xl border border-border/60 bg-background/75 px-3 py-3'
)

const labelClass = computed(() => props.compact
  ? 'mb-1.5 block text-xs font-medium text-muted-foreground'
  : 'flex items-center justify-between gap-3 text-xs text-muted-foreground'
)

const focus = () => {
  inputRef.value?.focus()
}

defineExpose({
  focus,
})
</script>

<template>
  <div class="w-full">
    <div :class="wrapperClass">
      <Sparkles class="h-4 w-4 text-primary" />
      <input
        :id="inputId"
        ref="inputRef"
        :value="modelValue"
        class="w-full bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus:outline-none"
        :placeholder="placeholder"
        :aria-label="label"
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @keydown.enter.prevent="emit('analyze')"
      />
      <div class="flex items-center gap-2">
        <div v-if="isLoading" class="flex items-center gap-2 text-xs text-muted-foreground">
          <Loader2 class="h-4 w-4 animate-spin" />
          <span>Analyzing</span>
        </div>
        <Button
          v-if="!isLoading"
          variant="ghost"
          size="sm"
          :class="compact ? 'rounded-full px-3' : 'rounded-none px-3'"
          @click="emit('analyze')"
        >
          Analyze
        </Button>
        <Button
          v-else-if="compact"
          variant="ghost"
          size="sm"
          class="rounded-full px-3"
          @click="emit('stop')"
        >
          <Square class="h-4 w-4" />
          <span class="sr-only">Stop</span>
        </Button>
      </div>
    </div>

    <div v-if="helperText" class="mt-2 text-xs text-muted-foreground">
      {{ helperText }}
    </div>
  </div>
</template>

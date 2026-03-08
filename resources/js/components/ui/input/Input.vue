<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/js/lib/utils'
import { useVModel } from '@vueuse/core'
import { Money3Component } from 'v-money3'
import { computed } from 'vue'
import { useAuthStore } from '@/js/stores/auth'
import { getCurrencySymbol } from '@/js/lib/currency'

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
  error?: string | boolean,
  isMoney?: boolean
}>()
const classes = [
  cn(`
    flex
    h-9
    w-full
    rounded-md
    border border-input
    bg-transparent
    px-3 py-1 text-sm
    shadow-sm
    transition-colors
    file:border-0 file:bg-transparent file:text-sm file:font-medium
    placeholder:text-muted-foreground
    ${props.error ? 'border-red-500 focus-visible:ring-1 focus-visible:ring-red-300 ' : 'focus-visible:ring-1 focus-visible:ring-ring'}
    focus-visible:outline-none
    disabled:cursor-not-allowed disabled:opacity-50`,
  props.class),
]
const authStore = useAuthStore()
const moneyConfig = {
  prefix: '',
  suffix: '',
  thousands: ',',
  decimal: '.',
  precision: 2,
  disableNegative: false,
  disabled: false,
  min: null,
  max: null,
  allowBlank: false,
  minimumNumberOfCharacters: 0,
  shouldRound: true,
  focusOnRight: false,
}
const moneyPrefix = computed(() => `${getCurrencySymbol(authStore.currentUser?.currency)} `)
const money = computed({
  get: () => {
    const { modelValue } = props
    return modelValue / 100
  },
  set: (value) => {

    const newValue = Math.round(value * 100)
    emits('update:modelValue', newValue)
  },
})

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})


</script>

<template>
  <input v-if="!isMoney" v-bind="$attrs" :class="classes" v-model="modelValue">
  <Money3Component
    v-if="isMoney"
    v-bind="$attrs"
    v-model="money"
    v-money="{ ...moneyConfig, prefix: moneyPrefix }"
    :class="classes"
  />
  <span v-if="error" class="text-sm text-red-500">{{ error }}</span>
</template>

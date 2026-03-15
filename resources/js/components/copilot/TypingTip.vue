<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = withDefaults(defineProps<{
  tips: string[]
  prefix?: string
  isActive: boolean
  holdMs?: number
  typeSpeedMs?: number
}>(), {
  prefix: 'Tip:',
  holdMs: 4000,
  typeSpeedMs: 35,
})

const tipIndex = ref(0)
const tipText = ref('')
const showCursor = ref(false)

let tipTimer: ReturnType<typeof setInterval> | null = null
let tipHoldTimer: ReturnType<typeof setTimeout> | null = null

const canRun = computed(() => props.isActive && props.tips.length > 0)

const clearTipTimers = () => {
  if (tipTimer) {
    clearInterval(tipTimer)
    tipTimer = null
  }
  if (tipHoldTimer) {
    clearTimeout(tipHoldTimer)
    tipHoldTimer = null
  }
}

const runTypingTip = () => {
  if (!canRun.value) return
  clearTipTimers()
  tipText.value = ''
  const fullText = `Try “${props.tips[tipIndex.value % props.tips.length]}”.`
  let charIndex = 0
  showCursor.value = true

  tipTimer = setInterval(() => {
    charIndex += 1
    tipText.value = fullText.slice(0, charIndex)
    if (charIndex >= fullText.length) {
      clearTipTimers()
      tipHoldTimer = setTimeout(() => {
        tipText.value = ''
        tipIndex.value += 1
        if (canRun.value) {
          runTypingTip()
        } else {
          showCursor.value = false
        }
      }, props.holdMs)
    }
  }, props.typeSpeedMs)
}

watch(
  () => props.isActive,
  (active) => {
    if (!active) {
      clearTipTimers()
      tipText.value = ''
      showCursor.value = false
      return
    }
    runTypingTip()
  },
)

onMounted(() => {
  if (canRun.value) runTypingTip()
})

onBeforeUnmount(() => {
  clearTipTimers()
})
</script>

<template>
  <div class="min-h-[18px] text-xs text-muted-foreground">
    <span class="uppercase tracking-[0.2em] text-[10px] text-muted-foreground/80">{{ prefix }}</span>
    <span class="ml-2">{{ tipText }}</span>
    <span
      v-if="showCursor"
      class="ml-0.5 inline-block h-3 w-[1px] animate-pulse bg-muted-foreground align-middle"
    ></span>
  </div>
</template>

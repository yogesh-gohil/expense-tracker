<script setup>
import { computed } from 'vue'
import Card from '@/js/components/ui/card/Card.vue'
import CardHeader from '@/js/components/ui/card/CardHeader.vue'
import CardTitle from '@/js/components/ui/card/CardTitle.vue'
import CardDescription from '@/js/components/ui/card/CardDescription.vue'

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  icon: {
    type: [Object, Function],
    required: true,
  },
  variant: {
    type: String,
    default: 'sky',
  },
})

const colorMap = {
  emerald: {
    card: 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900/70 dark:bg-emerald-950/40',
    description: 'text-emerald-700 dark:text-emerald-300',
    iconBadge: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/70 dark:text-emerald-300',
    title: 'text-emerald-900 dark:text-emerald-200',
  },
  sky: {
    card: 'border-sky-200 bg-sky-50/70 dark:border-cyan-900/70 dark:bg-cyan-950/40',
    description: 'text-sky-700 dark:text-cyan-300',
    iconBadge: 'bg-sky-100 text-sky-600 dark:bg-cyan-900/70 dark:text-cyan-300',
    title: 'text-sky-900 dark:text-cyan-200',
  },
  violet: {
    card: 'border-violet-200 bg-violet-50/70 dark:border-violet-900/70 dark:bg-violet-950/40',
    description: 'text-violet-700 dark:text-violet-300',
    iconBadge: 'bg-violet-100 text-violet-600 dark:bg-violet-900/70 dark:text-violet-300',
    title: 'text-violet-900 dark:text-violet-200',
  },
  rose: {
    card: 'border-rose-200 bg-rose-50/70 dark:border-fuchsia-900/70 dark:bg-fuchsia-950/40',
    description: 'text-rose-700 dark:text-fuchsia-300',
    iconBadge: 'bg-rose-100 text-rose-600 dark:bg-fuchsia-900/70 dark:text-fuchsia-300',
    title: 'text-rose-900 dark:text-fuchsia-200',
  },
}

const tone = computed(() => colorMap[props.variant] || colorMap.sky)
</script>

<template>
  <Card :class="tone.card">
    <CardHeader class="pb-2">
      <CardDescription class="flex items-center gap-2" :class="tone.description">
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-md" :class="tone.iconBadge">
          <component :is="icon" class="h-4 w-4" />
        </span>
        {{ label }}
      </CardDescription>
      <CardTitle class="text-xl" :class="tone.title">
        {{ value }}
      </CardTitle>
    </CardHeader>
  </Card>
</template>

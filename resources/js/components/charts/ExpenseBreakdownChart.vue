<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Bar, Pie } from 'vue-chartjs'
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js'
import { formatCurrencyFromCents, getCurrencySymbol } from '@/js/lib/currency'

ChartJS.register(ArcElement, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps({
  type: {
    type: String,
    default: 'pie',
  },
  labels: {
    type: Array,
    default: () => [],
  },
  values: {
    type: Array,
    default: () => [],
  },
  height: {
    type: Number,
    default: 280,
  },
  currency: {
    type: String,
    default: 'USD',
  },
})

const isDarkMode = ref(false)

const syncTheme = () => {
  isDarkMode.value = document.documentElement.classList.contains('dark')
}

onMounted(() => {
  syncTheme()
  window.addEventListener('theme-changed', syncTheme)
})

onBeforeUnmount(() => {
  window.removeEventListener('theme-changed', syncTheme)
})

const lightColors = [
  '#60A5FA',
  '#34D399',
  '#FBBF24',
  '#FCA5A5',
  '#A78BFA',
  '#22D3EE',
  '#FDBA74',
  '#86EFAC',
]

const darkColors = [
  '#93C5FD',
  '#6EE7B7',
  '#FCD34D',
  '#FCA5A5',
  '#C4B5FD',
  '#67E8F9',
  '#FDBA74',
  '#86EFAC',
]

const colors = computed(() => (isDarkMode.value ? darkColors : lightColors))

const chartComponent = computed(() => (props.type === 'bar' ? Bar : Pie))

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [
    {
      label: 'Total Spent',
      data: props.values,
      backgroundColor: props.labels.map((_, index) => colors.value[index % colors.value.length]),
      borderColor: props.labels.map(() => (isDarkMode.value ? '#0F172A' : '#FFFFFF')),
      borderWidth: 1,
    },
  ],
}))

const chartOptions = computed(() => {
  const textColor = isDarkMode.value ? '#CBD5E1' : '#334155'
  const gridColor = isDarkMode.value ? 'rgba(148, 163, 184, 0.24)' : 'rgba(148, 163, 184, 0.3)'
  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: textColor,
        },
      },
      tooltip: {
        callbacks: {
          label: (context) => {
            const value = Number(context.raw ?? 0)
            return `${context.label}: ${formatCurrencyFromCents(value, props.currency)}`
          },
        },
      },
    },
  }

  if (props.type === 'bar') {
    return {
      ...baseOptions,
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: gridColor,
          },
          ticks: {
            color: textColor,
            callback: (value) => `${getCurrencySymbol(props.currency)}${(Number(value) / 100).toFixed(0)}`,
          },
        },
        x: {
          grid: {
            display: false,
          },
          ticks: {
            color: textColor,
          },
        },
      },
    }
  }

  return baseOptions
})
</script>

<template>
  <div class="w-full" :style="{ height: `${height}px` }">
    <component
      :is="chartComponent"
      v-if="labels.length"
      :data="chartData"
      :options="chartOptions"
    />
    <div v-else class="h-full w-full grid place-items-center text-sm text-muted-foreground">
      No expense data available for this month.
    </div>
  </div>
</template>

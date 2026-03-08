<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/js/components/ui/button'
import { Calendar } from '@/js/components/ui/calendar'
import { CalendarRangeIcon } from 'lucide-vue-next'
import { Popover, PopoverContent, PopoverTrigger } from '@/js/components/ui/popover'
import { cn } from '@/js/lib/utils'
import {CalendarDate, DateFormatter, getLocalTimeZone} from '@internationalized/date'
import dayjs from 'dayjs'


const df = new DateFormatter('en-US', {
  dateStyle: 'long',
})
const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
})
const emits = defineEmits(['update:modelValue'])
const dateValue = computed({
  get: () => {
    if (props.modelValue) {
      const [year, month, day] = props.modelValue.split('-').map(Number)
      return new CalendarDate(year, month, day)
      // dateValue.value = new CalendarDate(expense.date)
    }
  },
  set: (value) => {
    const date = dayjs(value).format('YYYY-MM-DD')
    emits('update:modelValue', date)
  },
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn(
          'justify-start text-left font-normal',
          !dateValue && 'text-muted-foreground',
        )"
      >
        <CalendarRangeIcon class="mr-2 h-4 w-4" />
        {{ dateValue ? df.format(dateValue.toDate(getLocalTimeZone())) : "" }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0">
      <Calendar v-model="dateValue" />
    </PopoverContent>
  </Popover>
</template>

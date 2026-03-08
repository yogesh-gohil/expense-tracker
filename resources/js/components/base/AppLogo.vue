<script setup>
defineProps({
  withText: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <div class="flex items-center gap-2">
    <div class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-sky-500 via-cyan-500 to-emerald-500 text-white font-extrabold shadow-sm shadow-cyan-500/30">
      E
    </div>
    <span v-if="withText" class="text-base font-semibold tracking-tight">Expense Tracker</span>
  </div>
</template>

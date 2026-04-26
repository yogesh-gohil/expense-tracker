<script setup lang="ts">
defineProps<{
  isActive: boolean
}>()
</script>

<template>
  <div class="grid gap-4">
    <div class="grid gap-3 sm:grid-cols-3">
      <div class="rounded-2xl border bg-background px-4 py-3">
        <div class="text-xs uppercase tracking-[0.2em] text-muted-foreground">Reads</div>
        <div class="mt-2 text-sm font-medium">Amount, date, vendor, and intent</div>
      </div>
      <div class="rounded-2xl border bg-background px-4 py-3">
        <div class="text-xs uppercase tracking-[0.2em] text-muted-foreground">Matches</div>
        <div class="mt-2 text-sm font-medium">Existing categories before creating new ones</div>
      </div>
      <div class="rounded-2xl border bg-background px-4 py-3">
        <div class="text-xs uppercase tracking-[0.2em] text-muted-foreground">Previews</div>
        <div class="mt-2 text-sm font-medium">A clean draft before anything is saved</div>
      </div>
    </div>
  </div>
</template>

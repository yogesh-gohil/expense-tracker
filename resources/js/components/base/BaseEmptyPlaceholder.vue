<script setup lang="ts">
defineProps({
  title: String,
  description: String,
})

</script>

<template>
  <!-- <div class="flex items-center">
    <h1 class="text-lg font-semibold md:text-2xl">
      {{ title }}
    </h1>
  </div> -->
  <div class="flex flex-1 items-center justify-center rounded-lg border border-dashed shadow-sm">
    <div class="flex flex-col items-center gap-1 text-center">
      <h3 class="text-2xl font-bold tracking-tight">
        {{title}}
      </h3>
      <p class="text-sm text-muted-foreground">
        {{ description }}
      </p>
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  progressIndex: number
  progressSteps: Array<{ title: string, detail: string }>
}>()
</script>

<template>
  <div class="mt-4 rounded-2xl border border-primary/10 bg-[linear-gradient(135deg,_hsl(var(--primary)/0.08),_transparent_55%),linear-gradient(180deg,_hsl(var(--muted)/0.55),_transparent)] px-4 py-4 text-xs text-muted-foreground">
    <div class="flex items-center justify-between gap-3">
      <div>
        <div class="uppercase tracking-[0.2em] text-[10px] text-muted-foreground/80">Processing</div>
        <div class="mt-1 text-sm font-medium text-foreground">{{ progressSteps[progressIndex].title }}</div>
        <div class="mt-1 text-xs text-muted-foreground">{{ progressSteps[progressIndex].detail }}</div>
      </div>
      <span class="rounded-full bg-background/80 px-2 py-1 text-[10px] text-muted-foreground/80">{{ progressIndex + 1 }} / {{ progressSteps.length }}</span>
    </div>

    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-background/60">
      <div
        class="h-full rounded-full bg-primary transition-[width] duration-500"
        :style="{ width: `${((progressIndex + 1) / progressSteps.length) * 100}%` }"
      ></div>
    </div>

    <div class="mt-4 grid gap-2 sm:grid-cols-2">
      <span
        v-for="(step, index) in progressSteps"
        :key="step.title"
        class="rounded-xl border px-3 py-2 text-[11px]"
        :class="index <= progressIndex ? 'border-primary/20 bg-primary/10 text-primary' : 'border-border/60 bg-background/60 text-muted-foreground/70'"
      >
        <span class="block font-semibold text-current">{{ step.title }}</span>
        <span class="mt-1 block text-[10px] text-current/80">{{ step.detail }}</span>
      </span>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
      <div class="rounded-xl border border-border/60 bg-background/70 px-3 py-3">
        <div class="h-2 w-12 rounded-full bg-muted"></div>
        <div class="mt-3 h-6 w-24 rounded-full bg-muted/80"></div>
        <div class="mt-2 h-2 w-20 rounded-full bg-muted"></div>
      </div>
      <div class="rounded-xl border border-border/60 bg-background/70 px-3 py-3">
        <div class="h-2 w-14 rounded-full bg-muted"></div>
        <div class="mt-3 h-6 w-28 rounded-full bg-muted/80"></div>
        <div class="mt-2 h-2 w-24 rounded-full bg-muted"></div>
      </div>
      <div class="rounded-xl border border-border/60 bg-background/70 px-3 py-3">
        <div class="h-2 w-10 rounded-full bg-muted"></div>
        <div class="mt-3 h-6 w-32 rounded-full bg-muted/80"></div>
        <div class="mt-2 h-2 w-16 rounded-full bg-muted"></div>
      </div>
    </div>
  </div>
</template>

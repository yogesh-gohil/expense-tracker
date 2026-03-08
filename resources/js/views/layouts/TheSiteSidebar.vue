<script setup>
import Sidebar from './partials/Sidebar.vue'
import AppLogo from '@/js/components/base/AppLogo.vue'
</script>

<template>
  <div class="hidden border-r bg-muted/40 md:block">
    <div class="flex h-full max-h-screen flex-col gap-2">
      <div class="flex h-14 items-center border-b px-4 lg:h-[60px] lg:px-6">
        <a href="/dashboard" class="font-semibold">
          <AppLogo />
        </a>
      </div>
      <div class="flex-1">
        <Sidebar />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/js/stores/auth'
import TheSiteHeader from './TheSiteHeader.vue'
import TheSiteSidebar from './TheSiteSidebar.vue'
import ExpenseCopilotPalette from '@/js/components/copilot/ExpenseCopilotPalette.vue'
import ExpenseModal from '@/js/components/modal/ExpenseModal.vue'
import IncomeModal from '@/js/components/modal/IncomeModal.vue'

const authStore = useAuthStore()

onMounted(() => {
  authStore.getAuthUser().catch(() => null)
})
</script>

<template>
  <div class="grid min-h-screen w-full md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]">
    <TheSiteSidebar />
    <div class="flex flex-col">
      <TheSiteHeader />
      <ExpenseCopilotPalette />
      <ExpenseModal />
      <IncomeModal />
      <main class="flex flex-1 flex-col p-4 lg:p-6 bg-background">
        <router-view />
      </main>
    </div>
  </div>
</template>

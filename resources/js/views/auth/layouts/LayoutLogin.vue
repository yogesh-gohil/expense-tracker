<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next'
import Button from '@/js/components/ui/button/Button.vue'
import { useTheme } from '@/js/lib/theme'

const { isDark, toggleTheme } = useTheme()
</script>

<template>
    <div class="relative flex h-screen bg-gray-50 dark:bg-gray-900 min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
      <Button
        variant="outline"
        size="icon"
        class="absolute right-6 top-6 rounded-full"
        @click="toggleTheme"
      >
        <Sun v-if="isDark" class="h-5 w-5" />
        <Moon v-else class="h-5 w-5" />
        <span class="sr-only">Toggle dark mode</span>
      </Button>
      <router-view />
    </div>
</template>

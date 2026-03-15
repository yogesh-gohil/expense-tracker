<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Sidebar from './partials/Sidebar.vue';
import AppLogo from '@/js/components/base/AppLogo.vue'
import { Button } from '@/js/components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from '@/js/components/ui/sheet'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/js/components/ui/dropdown-menu'
import { Menu, CircleUser, Moon, Sun, Command } from 'lucide-vue-next'
import { useAuthStore } from '@/js/stores/auth'
import { useCopilotStore } from '@/js/stores/copilot'
import { useRoute, useRouter } from 'vue-router'
import { useTheme } from '@/js/lib/theme'
const authStore = useAuthStore()
const copilotStore = useCopilotStore()
const router = useRouter()
const route = useRoute()
const { isDark, toggleTheme } = useTheme()

const isMobileNavOpen = ref(false)

const closeMobileNav = () => {
  isMobileNavOpen.value = false
}

watch(
  () => route.fullPath,
  () => {
    closeMobileNav()
  },
)

const onViewportChange = (event) => {
  if (event.matches) {
    closeMobileNav()
  }
}

let mediaQuery
onMounted(() => {
  if (typeof window === 'undefined') return
  mediaQuery = window.matchMedia('(min-width: 768px)')
  mediaQuery.addEventListener?.('change', onViewportChange)
})

onBeforeUnmount(() => {
  if (!mediaQuery) return
  mediaQuery.removeEventListener?.('change', onViewportChange)
})
</script>
<template>
    <header class="flex h-14 w-full items-center gap-4 rounded-none border-b bg-muted/40 px-4 lg:h-[60px] lg:px-6">
      <div class="flex min-w-0 flex-1 items-center gap-3">
        <!-- Mobile Sidebar -->

        <a href="/dashboard" class="md:hidden">
          <AppLogo />
        </a>

        <Sheet v-model:open="isMobileNavOpen">
          <SheetTrigger as-child>
            <Button
              variant="outline"
              size="icon"
              class="shrink-0 md:hidden"
            >
              <Menu class="h-5 w-5" />
              <span class="sr-only">Toggle navigation menu</span>
            </Button>
          </SheetTrigger>
        <SheetContent side="left" class="flex flex-col p-0">
            <div class="flex h-14 items-center border-b px-4">
              <a href="/dashboard" class="font-semibold">
                <AppLogo />
              </a>
            </div>
            <div class="flex-1 px-2">
              <Sidebar @navigate="closeMobileNav" />
            </div>
        </SheetContent>
      </Sheet>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-none border-0 bg-transparent px-2 py-1 text-xs text-muted-foreground hover:text-foreground"
        @click="copilotStore.openPalette"
      >
        <Command class="h-3.5 w-3.5" />
        Copilot
        <span class="rounded-sm border border-border px-1.5 py-0.5 text-[10px] uppercase tracking-[0.18em]">K</span>
      </button>
      </div>

      <div class="flex items-center gap-3">
        <Button
          variant="outline"
          size="icon"
          class="rounded-full"
          @click="toggleTheme"
        >
          <Sun v-if="isDark" class="h-5 w-5" />
          <Moon v-else class="h-5 w-5" />
          <span class="sr-only">Toggle dark mode</span>
        </Button>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="secondary" size="icon" class="rounded-full">
              <CircleUser class="h-5 w-5" />
              <span class="sr-only">Toggle user menu</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuLabel>My Account</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="router.push('/profile')">Profile</DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="authStore.logout">Logout</DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </header>
</template>

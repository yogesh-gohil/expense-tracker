<script setup>
import Sidebar from './partials/Sidebar.vue';
import { Button } from '@/js/components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from '@/js/components/ui/sheet'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/js/components/ui/dropdown-menu'
import { Menu, CircleUser, Moon, Sun } from 'lucide-vue-next'
import { useAuthStore } from '@/js/stores/auth'
import { useRouter } from 'vue-router'
import { useTheme } from '@/js/lib/theme'
const authStore = useAuthStore()
const router = useRouter()
const { isDark, toggleTheme } = useTheme()
</script>
<template>
    <header class="w-full justify-end flex h-14 items-center gap-4 border-b bg-muted/40 px-4 lg:h-[60px] lg:px-6">
      <!-- Mobile Sidebar -->
      <Sheet>
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
        <SheetContent side="left" class="flex flex-col">
          <Sidebar />
        </SheetContent>
      </Sheet>

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
    </header>
</template>

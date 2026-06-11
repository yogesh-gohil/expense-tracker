<script setup>
import { useRoute } from 'vue-router'
import { BarChart3, CircleDollarSign, HandCoins, Home, Package, UserRound } from 'lucide-vue-next'
const route = useRoute()
const emit = defineEmits(['navigate'])

const menus= [
  {
    title: 'Dashboard',
    icon: Home,
    name: 'dashboard',
  },
  {
    title: 'Categories',
    icon: Package,
    name: 'categories',
  },
  {
    title: 'Expenses',
    icon: HandCoins,
    name: 'expenses',
  },
  {
    title: 'Incomes',
    icon: CircleDollarSign,
    name: 'incomes',
  },
  {
    title: 'Monthly Summary',
    icon: BarChart3,
    name: 'monthly-summary',
  },
  {
    title: 'Profile',
    icon: UserRound,
    name: 'profile',
  },
]

const activeRoute = (name) => {
  return route.name === name
}
</script>

<template>
    <nav class="grid items-start px-2 text-sm font-medium lg:px-4">
      <router-link
        v-for="menu in menus"
        :to="{ name: menu.name }"
        class="flex items-center gap-3 rounded-lg px-3 py-2 mt-2 text-muted-foreground transition-all duration-200 hover:text-foreground hover:bg-accent/70"
        :class="{ 'bg-muted text-primary': activeRoute(menu.name) }"
        @click="emit('navigate')"
      >
        <component :is="menu.icon" class="h-4 w-4" />
        {{ menu.title }}
      </router-link>
    </nav>
</template>

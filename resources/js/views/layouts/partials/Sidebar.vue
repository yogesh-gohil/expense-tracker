<script setup>
import { useRoute } from 'vue-router'
import { BarChart3, CircleDollarSign, HandCoins, Home, Package, UserRound } from 'lucide-vue-next'
const route = useRoute()

const menus= [
  {
    title: 'Dashboard',
    icon: Home,
    link: '/dashboard'
  },
  {
    title: 'Categories',
    icon: Package,
    link: '/categories'
  },
  {
    title: 'Expenses',
    icon: HandCoins,
    link: '/expenses'
  },
  {
    title: 'Incomes',
    icon: CircleDollarSign,
    link: '/incomes'
  },
  {
    title: 'Monthly Summary',
    icon: BarChart3,
    link: '/monthly-summary'
  },
  {
    title: 'Profile',
    icon: UserRound,
    link: '/profile'
  },
]

const activeRoute = (path) => {
  return route.path.includes(path)
}
</script>

<template>
    <nav class="grid items-start px-2 text-sm font-medium lg:px-4">
      <router-link
        v-for="menu in menus"
        :to="menu.link"
        class="flex items-center gap-3 rounded-lg px-3 py-2 mt-2 text-muted-foreground transition-all duration-200 hover:text-foreground hover:bg-accent/70"
        :class="{'bg-muted text-primary': activeRoute(menu.link)}"
      >
        <component :is="menu.icon" class="h-4 w-4" />
        {{ menu.title }}
      </router-link>
    </nav>
</template>

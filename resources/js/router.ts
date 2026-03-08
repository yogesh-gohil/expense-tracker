import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './stores/auth'

const routes = [
  {
    path: '/',
    component: () => import('@/js/views/auth/layouts/LayoutLogin.vue'),
    meta: {
      requiresAuth: false,
      redirectIfAuthenticated: true,
    },
    children: [
      {
        path: '',
        component: () => import('@/js/views/auth/Login.vue'),
      },
      {
        path: 'login',
        name: 'login',
        component: () => import('@/js/views/auth/Login.vue'),
      },
      {
        path: 'register',
        name: 'register',
        component: () => import('@/js/views/auth/Register.vue'),
      },

      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: () => import('@/js/views/auth/ForgotPassword.vue'),
      },
      {
        path: '/reset-password/:token',
        name: 'reset-password',
        component: () => import('@/js/views/auth/ResetPassword.vue'),
      },
    ],
  },

  // Dashboard routes
  {
    path: '/',
    name: 'home',
    component: () => import('@/js/views/layouts/LayoutDashboard.vue'),
    meta: {
      requiresAuth: true,
      redirectIfAuthenticated: true,
    },
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/js/views/dashboard/Dashboard.vue'),
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('@/js/views/categories/Index.vue'),
      },
      {
        path: 'expenses',
        name: 'expenses',
        component: () => import('@/js/views/expenses/Index.vue'),
      },
      {
        path: 'incomes',
        name: 'incomes',
        component: () => import('@/js/views/incomes/Index.vue'),
      },
      {
        path: 'monthly-summary',
        name: 'monthly-summary',
        component: () => import('@/js/views/summary/MonthlySummary.vue'),
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/js/views/profile/Index.vue'),
      },
    ],
  },
  {
    path: '/403',
    name: 'forbidden',
    component: () => import('@/js/views/errors/403.vue'),
  },
  {
    path: '/:catchAll(.*)',
    name: 'not-found',
    component: () => import('@/js/views/errors/404.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  linkActiveClass: 'active',
  routes,
})


export default router

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/js/components/ui/button'
import { Moon, Sun, ArrowRight, TrendingUp, PieChart, Shield, Sparkles, Wallet, CreditCard, BarChart3 } from 'lucide-vue-next'

const router = useRouter()
const isDark = ref(false)

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  } else if (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
})

const toggleDarkMode = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const features = [
  {
    icon: TrendingUp,
    title: 'Track Expenses',
    description: 'Easily log and categorize your spending with intuitive interfaces.',
  },
  {
    icon: PieChart,
    title: 'Visual Reports',
    description: 'Beautiful charts and summaries help you understand your finances.',
  },
  {
    icon: Shield,
    title: 'Secure & Private',
    description: 'Your data is protected with industry-standard authentication.',
  },
]

const copilotFeatures = [
  'Natural language expense entry',
  'AI-powered categorization',
  'Smart spending insights',
  'Automated financial advice',
]

const stats = [
  { label: 'Active Users', value: '10K+' },
  { label: 'Expenses Tracked', value: '1M+' },
  { label: 'Time Saved', value: '50hrs/mo' },
]
</script>

<template>
  <div class="min-h-screen bg-background text-foreground transition-colors duration-300 overflow-x-hidden">
    <!-- Animated Background Orbs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-gradient-to-br from-sky-400/20 via-cyan-400/15 to-emerald-400/10 rounded-full blur-[120px] animate-pulse-slow"></div>
      <div class="absolute top-1/3 right-0 w-[500px] h-[500px] bg-gradient-to-bl from-cyan-400/15 via-sky-400/10 to-transparent rounded-full blur-[100px] animate-pulse-slow animation-delay-2000"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-gradient-to-tr from-emerald-400/15 via-cyan-400/10 to-transparent rounded-full blur-[80px] animate-pulse-slow animation-delay-4000"></div>

      <!-- Grid pattern overlay -->
      <div class="absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>
    </div>

    <!-- Header -->
    <header class="border-b border-border/40 bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50">
      <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <div class="flex items-center gap-2 group cursor-pointer" @click="router.push('/')">
          <div class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-sky-500 via-cyan-500 to-emerald-500 text-white font-extrabold shadow-sm shadow-cyan-500/30 group-hover:scale-110 transition-transform duration-300">
            E
          </div>
          <span class="text-base font-semibold tracking-tight">Expense Tracker</span>
        </div>
        <div class="flex items-center gap-4">
          <button
            @click="toggleDarkMode"
            class="p-2 rounded-lg hover:bg-muted transition-all duration-300 hover:scale-110"
            :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          >
            <Sun v-if="isDark" class="h-5 w-5 animate-spin-slow" />
            <Moon v-else class="h-5 w-5" />
          </button>
          <div class="flex items-center gap-2">
            <Button variant="ghost" @click="router.push('/auth/login')" class="hover:translate-x-1 transition-transform">Login</Button>
            <Button @click="router.push('/auth/register')" class="hover:scale-105 transition-transform">Get Started</Button>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="py-16 lg:py-28 relative">
      <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Left Content -->
          <div class="text-center lg:text-left">
            <!-- Animated Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-sky-500/10 via-cyan-500/10 to-emerald-500/10 border border-sky-500/20 mb-6 animate-fade-in-up">
              <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
              <span class="text-sm text-muted-foreground">Now with AI-powered insights</span>
            </div>

            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl mb-6 animate-fade-in-up animation-delay-200">
              Smart Expense Tracking for
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-500 animate-gradient">
                Modern Finances
              </span>
            </h1>
            <p class="text-lg text-muted-foreground mb-8 max-w-xl mx-auto lg:mx-0 animate-fade-in-up animation-delay-400">
              Track your expenses, analyze spending patterns, and get AI-powered insights to take control of your financial life.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-10 animate-fade-in-up animation-delay-600">
              <Button size="lg" class="gap-2 group hover:scale-105 transition-all duration-300 shadow-lg shadow-sky-500/25" @click="router.push('/auth/register')">
                Start Free
                <ArrowRight class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
              </Button>
              <Button size="lg" variant="outline" @click="router.push('/auth/login')" class="border-sky-500/50 text-sky-600 dark:text-sky-400 hover:bg-sky-500/10 hover:border-sky-500 hover:text-sky-600 dark:hover:text-sky-300 transition-all duration-300">
                Sign In
              </Button>
            </div>

            <!-- Stats -->
            <div class="flex flex-wrap justify-center lg:justify-start gap-8 animate-fade-in-up animation-delay-800">
              <div v-for="(stat, index) in stats" :key="stat.label" class="text-center lg:text-left">
                <div class="text-2xl lg:text-3xl font-bold text-foreground">{{ stat.value }}</div>
                <div class="text-sm text-muted-foreground">{{ stat.label }}</div>
              </div>
            </div>
          </div>

          <!-- Right Content - Floating Cards -->
          <div class="relative hidden lg:block h-[400px]">
            <!-- Main Card -->
            <div class="absolute top-1/2 left-1/2  w-72 p-6 rounded-2xl bg-background/90 backdrop-blur-xl border border-border/50 shadow-2xl">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center">
                    <Wallet class="h-5 w-5 text-white" />
                  </div>
                  <div>
                    <div class="text-sm font-medium">Total Balance</div>
                    <div class="text-xs text-muted-foreground">This month</div>
                  </div>
                </div>
              </div>
              <div class="text-3xl font-bold text-foreground mb-2">$12,450.00</div>
              <div class="flex items-center gap-2 text-sm text-emerald-500">
                <TrendingUp class="h-4 w-4" />
                <span>+12.5% from last month</span>
              </div>
            </div>

            <!-- Floating Icon Cards -->
            <div class="absolute top-10 right-10 w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 backdrop-blur-xl border border-amber-500/30 flex items-center justify-center animate-float animation-delay-1000">
              <CreditCard class="h-7 w-7 text-amber-500" />
            </div>

            <div class="absolute bottom-20 left-5 w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 backdrop-blur-xl border border-purple-500/30 flex items-center justify-center animate-float animation-delay-2000">
              <BarChart3 class="h-7 w-7 text-purple-500" />
            </div>

            <div class="absolute top-32 right-0 w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500/20 to-cyan-500/20 backdrop-blur-xl border border-emerald-500/30 flex items-center justify-center animate-float animation-delay-3000">
              <Sparkles class="h-6 w-6 text-emerald-500" />
            </div>

            <!-- Decorative circles -->
            <div class="absolute top-0 right-20 w-4 h-4 rounded-full bg-sky-500/50 animate-pulse"></div>
            <div class="absolute bottom-10 right-0 w-3 h-3 rounded-full bg-cyan-500/50 animate-pulse animation-delay-1000"></div>
            <div class="absolute bottom-0 left-10 w-5 h-5 rounded-full bg-emerald-500/30 animate-pulse animation-delay-2000"></div>
          </div>
        </div>
      </div>

      <!-- Gradient bottom fade -->
      <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-background to-transparent"></div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-muted/30 relative">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4 animate-fade-in-up">
            Everything you need to manage money
          </h2>
          <p class="text-muted-foreground text-lg max-w-2xl mx-auto animate-fade-in-up animation-delay-200">
            Powerful features designed to make expense tracking effortless and insightful.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
          <div
            v-for="(feature, index) in features"
            :key="feature.title"
            class="group p-6 rounded-xl bg-background/80 border border-border/50 shadow-sm hover:shadow-lg hover:border-sky-500/30 hover:-translate-y-2 transition-all duration-500 animate-fade-in-up"
            :style="{ animationDelay: `${(index + 1) * 200}ms` }"
          >
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg shadow-cyan-500/20">
              <component :is="feature.icon" class="h-7 w-7 text-white" />
            </div>
            <h3 class="text-xl font-semibold mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ feature.title }}</h3>
            <p class="text-muted-foreground">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Copilot Highlight Section -->
    <section class="py-20 relative overflow-hidden">
      <!-- Glow Effect -->
      <div class="absolute inset-0 bg-gradient-to-r from-sky-500/5 via-cyan-500/5 to-emerald-500/5"></div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="mx-auto max-w-4xl">
          <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-8 lg:p-12 border border-slate-700/50">
            <!-- Animated background pattern -->
            <div class="absolute inset-0 opacity-20">
              <div class="absolute inset-0 animate-pulse-slow" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
            </div>

            <!-- Glowing orb -->
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-cyan-500/20 rounded-full blur-3xl animate-pulse"></div>

            <div class="relative z-10 grid lg:grid-cols-2 gap-8 items-center">
              <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/20 text-cyan-400 text-sm font-medium mb-4 animate-pulse">
                  <Sparkles class="h-4 w-4 animate-spin-slow" />
                  AI Copilot
                </div>
                <h3 class="text-2xl lg:text-3xl font-bold text-white mb-4">
                  Your AI-powered financial assistant
                </h3>
                <p class="text-slate-300 mb-6">
                  Simply describe your expense in plain language and let our AI handle the rest—from categorization to insights.
                </p>
                <ul class="space-y-4">
                  <li
                    v-for="(feature, index) in copilotFeatures"
                    :key="feature"
                    class="flex items-center gap-3 text-slate-300 animate-fade-in-left"
                    :style="{ animationDelay: `${index * 150}ms` }"
                  >
                    <div class="w-6 h-6 rounded-full bg-cyan-500/20 flex items-center justify-center ring-1 ring-cyan-500/30">
                      <div class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></div>
                    </div>
                    <span class="hover:text-white transition-colors">{{ feature }}</span>
                  </li>
                </ul>
              </div>
              <div class="relative">
                <!-- Typing demo card -->
                <div class="rounded-xl bg-slate-800/70 border border-slate-600/50 p-4 backdrop-blur shadow-2xl shadow-cyan-500/10 animate-fade-in-up animation-delay-400">
                  <div class="flex items-center gap-2 mb-4">
                    <div class="w-3 h-3 rounded-full bg-red-500/80 animate-pulse"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500/80 animate-pulse animation-delay-200"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500/80 animate-pulse animation-delay-400"></div>
                    <span class="ml-2 text-xs text-slate-400">Expense Copilot</span>
                  </div>
                  <div class="space-y-4">
                    <div class="bg-slate-900/80 rounded-lg p-4 text-sm text-slate-300 border border-slate-700/50">
                      <span class="typing-effect">"Spent $45 on groceries at Whole Foods yesterday"</span>
                      <span class="animate-pulse">|</span>
                    </div>
                    <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-lg p-4 text-sm animate-fade-in-up">
                      <div class="text-cyan-400 font-medium mb-2 flex items-center gap-2">
                        <Sparkles class="h-4 w-4" />
                        AI Response:
                      </div>
                      <div class="text-slate-300">
                        Added expense: <span class="text-white font-bold">$45.00</span> to <span class="text-emerald-400 font-bold">Groceries</span>
                        <span class="inline-flex ml-1 animate-bounce">✓</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-muted/30 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-b from-transparent to-sky-500/5"></div>
      <div class="container mx-auto px-4 relative z-10">
        <div class="mx-auto max-w-2xl text-center">
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4 animate-fade-in-up">
            Ready to take control?
          </h2>
          <p class="text-muted-foreground text-lg mb-8 animate-fade-in-up animation-delay-200">
            Join thousands of users who track their expenses smarter with AI-powered insights.
          </p>
          <Button size="lg" class="gap-2 group hover:scale-105 transition-all duration-300 animate-fade-in-up animation-delay-400" @click="router.push('/auth/register')">
            Create Free Account
            <ArrowRight class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
          </Button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 border-t border-border/40 bg-background/80 backdrop-blur">
      <div class="container mx-auto px-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors cursor-pointer" @click="router.push('/')">
            <div class="grid h-6 w-6 place-items-center rounded-md bg-gradient-to-br from-sky-500 via-cyan-500 to-emerald-500 text-white font-bold text-xs">
              E
            </div>
            <span>Expense Tracker</span>
          </div>
          <p class="text-sm text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(5deg);
  }
}

@keyframes gradient {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

@keyframes pulse-slow {
  0%, 100% {
    opacity: 0.5;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.1);
  }
}

@keyframes spin-slow {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out forwards;
  opacity: 0;
}

.animate-fade-in-left {
  animation: fadeInLeft 0.6s ease-out forwards;
  opacity: 0;
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-gradient {
  background-size: 200% 200%;
  animation: gradient 4s ease infinite;
}

.animate-pulse-slow {
  animation: pulse-slow 8s ease-in-out infinite;
}

.animate-spin-slow {
  animation: spin-slow 12s linear infinite;
}

.animation-delay-200 {
  animation-delay: 200ms;
}

.animation-delay-400 {
  animation-delay: 400ms;
}

.animation-delay-600 {
  animation-delay: 600ms;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

/* Typing effect */
.typing-effect {
  display: inline;
}
</style>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/js/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/js/components/ui/card'
import { Input } from '@/js/components/ui/input'
import { Label } from '@/js/components/ui/label'
import BasePasswordInput from '@/js/components/base/BasePasswordInput.vue'
import { useAuthStore } from '@/js/stores/auth'
import { useVuelidate } from '@vuelidate/core'
import { required, email, helpers } from '@vuelidate/validators'
import { useToast } from '@/js/components/ui/toast/use-toast'
import { Loader2 } from 'lucide-vue-next'


const authStore = useAuthStore()
const router = useRouter()
const { toast } = useToast()
const isSubmitting = ref(false)

onMounted(() => {
  if (!authStore.loginData.email)
    authStore.loginData.email = 'yogesh@gmail.com'
  if (!authStore.loginData.password)
    authStore.loginData.password = 'Admin@123'
})

const rules = {
  email: {
    required: helpers.withMessage('Field is required.', required),
    email: helpers.withMessage('Invalid Email.', email),
  },
  password: {
    required: helpers.withMessage('Field is required.', required),
  },
}

const v$ = useVuelidate(rules, authStore.loginData)


const onSubmit = () => {
  if (isSubmitting.value)
    return true

  v$.value.$touch()

  if (v$.value.$invalid)
    return true

  isSubmitting.value = true
  authStore.login(authStore.loginData)
    .then(() => {
      toast({
        title: 'Success',
        description: 'Logged in successfully.',
      })
      router.replace({ name: 'dashboard' })
    })
    .finally(() => {
      isSubmitting.value = false
    })
}
</script>

<template>
  <Card class="sm:mx-auto sm:w-full sm:max-w-md">
    <CardHeader>
      <CardTitle class="text-2xl">
        Login
      </CardTitle>
      <CardDescription>
        Enter your email below to login to your account
      </CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input
              v-model="authStore.loginData.email"
              :error="v$.email.$error && v$.email.$errors[0].$message"
              id="email"
              type="email"
              :disabled="isSubmitting"
            />
          </div>
          <div class="grid gap-2">
            <Label for="password">Password</Label>
            <BasePasswordInput
              v-model="authStore.loginData.password"
              :error="v$.password.$error && v$.password.$errors[0].$message"
              id="password"
              required
              :disabled="isSubmitting"
            />
            <div class="flex items-center">
              <a href="#" class="ml-auto inline-block text-sm underline">
                Forgot your password?
              </a>
            </div>
          </div>
          <Button type="submit" class="w-full" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="inline-flex items-center gap-2">
              <Loader2 class="h-4 w-4 animate-spin" />
              Logging in...
            </span>
            <span v-else>Login</span>
          </Button>
          <!-- <Button variant="outline" class="w-full">
            Login with Google
          </Button> -->
        </div>
      </form>
      <div class="mt-4 text-center text-sm">
        Don't have an account?
        <router-link to="/register" class="underline">
          Sign up
        </router-link>
      </div>
    </CardContent>
  </Card>
</template>

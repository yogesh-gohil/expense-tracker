<script setup lang="ts">
import { useRouter } from 'vue-router'
import { Button } from '@/js/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/js/components/ui/card'
import { Input } from '@/js/components/ui/input'
import { Label } from '@/js/components/ui/label'
import { useAuthStore } from '@/js/stores/auth'
import { useVuelidate } from '@vuelidate/core'
import { required, email, helpers } from '@vuelidate/validators'
import { useToast } from '@/js/components/ui/toast/use-toast'


const authStore = useAuthStore()
const router = useRouter()
const { toast } = useToast()

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
  v$.value.$touch()

  if (v$.value.$invalid)
    return true

  authStore.login(authStore.loginData).then(() => {
    toast({
      title: 'Success',
      description: 'Logged in successfully.',
    })
    router.push({ name: 'dashboard' })
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
            />
          </div>
          <div class="grid gap-2">
            <Label for="password">Password</Label>
            <Input
              v-model="authStore.loginData.password"
              :error="v$.password.$error && v$.password.$errors[0].$message"
              id="password"
              type="password"
              required
            />
            <div class="flex items-center">
              <a href="#" class="ml-auto inline-block text-sm underline">
                Forgot your password?
              </a>
            </div>
          </div>
          <Button type="submit" class="w-full">
            Login
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

<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/js/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/js/components/ui/card'
import { Input } from '@/js/components/ui/input'
import { Label } from '@/js/components/ui/label'
import BasePasswordInput from '@/js/components/base/BasePasswordInput.vue'
import { useAuthStore } from '@/js/stores/auth'
import { useRouter } from 'vue-router'
import { useVuelidate } from '@vuelidate/core'
import { required, email, helpers, sameAs } from '@vuelidate/validators'
import { useToast } from '@/js/components/ui/toast/use-toast'

const authStore = useAuthStore()
const router = useRouter()
const { toast } = useToast()

const confirmPassword = computed(() => authStore.registerData.password)
const rules = computed(() => {
  return {
    name: {
      required: helpers.withMessage('Field is required.', required),
    },
    email: {
      required: helpers.withMessage('Field is required.', required),
      email: helpers.withMessage('Invalid Email.', email),
    },
    password: {
      required: helpers.withMessage('Field is required.', required),
    },
    password_confirmation: {
      sameAsPassword: helpers.withMessage('This value must be same as password',
        sameAs(confirmPassword.value),
      ),
    },
  }
})


const v$ = useVuelidate(rules, computed(() => authStore.registerData))

const onSubmit = async () => {
  v$.value.$touch()

  if (v$.value.$invalid)
    return true

  const res = await authStore.register(authStore.registerData)

  if (res?.data) {
    toast({
      title: 'Success',
      description: `Register Successfully!`,
    })

    router.push({ name: 'login' })
  }
}
</script>

<template>
  <Card class="sm:mx-auto sm:w-full sm:max-w-md">
    <CardHeader>
      <CardTitle class="text-2xl">
        Create Account
      </CardTitle>
      <CardDescription>
        Enter details below to register
      </CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="onSubmit">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input
              v-model="authStore.registerData.name"
              :error="v$.name.$error && v$.name.$errors[0].$message"
              id="name"
            />

          </div>
          <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input
              v-model="authStore.registerData.email"
              id="email"
              :error="v$.email.$error && v$.email.$errors[0].$message"
              type="email"
            />
          </div>
          <div class="grid gap-2">
            <Label for="password">Password</Label>
            <BasePasswordInput
              v-model="authStore.registerData.password"
              :error="v$.password.$error && v$.password.$errors[0].$message"
              id="password"
            />
          </div>
          <div class="grid gap-2">
            <Label for="c_password">Confirm Password</Label>
            <BasePasswordInput
              v-model="authStore.registerData.password_confirmation"
              :error="v$.password_confirmation.$error && v$.password_confirmation.$errors[0].$message"
              id="c_password"
            />
          </div>
          <Button type="submit" class="w-full">
            Register
          </Button>
        </div>
      </form>
      <div class="mt-4 text-center text-sm">
        Already have an account?
        <router-link to="/login" class="underline">
          Login
        </router-link>
      </div>
    </CardContent>
  </Card>
</template>

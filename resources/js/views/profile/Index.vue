<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useVuelidate } from '@vuelidate/core'
import { email, helpers, minLength, required, sameAs } from '@vuelidate/validators'
import BaseBreadcrumb from '@/js/components/base/BaseBreadcrumb.vue'
import Card from '@/js/components/ui/card/Card.vue'
import CardContent from '@/js/components/ui/card/CardContent.vue'
import CardDescription from '@/js/components/ui/card/CardDescription.vue'
import CardHeader from '@/js/components/ui/card/CardHeader.vue'
import CardTitle from '@/js/components/ui/card/CardTitle.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/js/components/ui/select'
import { Input } from '@/js/components/ui/input'
import { Label } from '@/js/components/ui/label'
import { Button } from '@/js/components/ui/button'
import Textarea from '@/js/components/ui/textarea/Textarea.vue'
import { useToast } from '@/js/components/ui/toast/use-toast'
import { SUPPORTED_CURRENCIES, normalizeCurrency } from '@/js/lib/currency'
import { useAuthStore } from '@/js/stores/auth'
import BasePasswordInput from '@/js/components/base/BasePasswordInput.vue'

const { toast } = useToast()
const authStore = useAuthStore()
const isLoading = ref(false)
const isSaving = ref(false)
const isUpdatingPassword = ref(false)

const breadcrumbData = [
  { title: 'Home', href: '/dashboard', active: false },
  { title: 'Profile', href: '', active: true },
]

const profileForm = reactive({
  name: '',
  email: '',
  phone: '',
  currency: 'USD',
  bio: '',
})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const profileErrors = reactive({
  name: '',
  email: '',
  phone: '',
  currency: '',
  bio: '',
})

const passwordErrors = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const profileRules = {
  name: {
    required: helpers.withMessage('Field is required.', required),
  },
  email: {
    required: helpers.withMessage('Field is required.', required),
    email: helpers.withMessage('Please enter a valid email address.', email),
  },
}

const passwordRules = {
  current_password: {
    required: helpers.withMessage('Field is required.', required),
  },
  password: {
    required: helpers.withMessage('Field is required.', required),
    minLength: helpers.withMessage('Password must be at least 8 characters.', minLength(8)),
  },
  password_confirmation: {
    required: helpers.withMessage('Field is required.', required),
    sameAsPassword: helpers.withMessage(
      'Password confirmation must match the new password.',
      sameAs(computed(() => passwordForm.password)),
    ),
  },
}

const profileV$ = useVuelidate(profileRules, computed(() => profileForm), { $scope: false })
const passwordV$ = useVuelidate(passwordRules, computed(() => passwordForm), { $scope: false })

const resetProfileErrors = () => {
  profileErrors.name = ''
  profileErrors.email = ''
  profileErrors.phone = ''
  profileErrors.currency = ''
  profileErrors.bio = ''
}

const resetPasswordErrors = () => {
  passwordErrors.current_password = ''
  passwordErrors.password = ''
  passwordErrors.password_confirmation = ''
}

const mapErrors = (source, target) => {
  Object.keys(target).forEach((key) => {
    target[key] = source?.[key]?.[0] || ''
  })
}

const fetchProfile = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/api/profile')
    const user = response.data.data
    authStore.currentUser = user

    profileForm.name = user?.name || ''
    profileForm.email = user?.email || ''
    profileForm.phone = user?.phone || ''
    profileForm.currency = normalizeCurrency(user?.currency)
    profileForm.bio = user?.bio || ''
  } finally {
    isLoading.value = false
  }
}

const saveProfile = async () => {
  profileV$.value.$touch()

  if (profileV$.value.$invalid)
    return

  resetProfileErrors()
  isSaving.value = true

  try {
    profileForm.currency = normalizeCurrency(profileForm.currency)
    const response = await axios.put('/api/profile', profileForm)
    authStore.currentUser = response.data.data
    toast({
      title: 'Success',
      description: 'Profile updated successfully.',
    })
  } catch (error) {
    mapErrors(error?.response?.data?.errors, profileErrors)
  } finally {
    isSaving.value = false
  }
}

const updatePassword = async () => {
  passwordV$.value.$touch()

  if (passwordV$.value.$invalid)
    return

  resetPasswordErrors()
  isUpdatingPassword.value = true

  try {
    await axios.put('/api/profile/password', passwordForm)
    toast({
      title: 'Success',
      description: 'Password updated successfully.',
    })
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
    passwordV$.value.$reset()
  } catch (error) {
    mapErrors(error?.response?.data?.errors, passwordErrors)
  } finally {
    isUpdatingPassword.value = false
  }
}

onMounted(fetchProfile)

const getValidationError = (validation, serverError) => {
  if (validation?.$error)
    return validation.$errors[0].$message

  return serverError
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="flex flex-col space-y-2">
        <h1 class="text-lg font-semibold md:text-2xl">Profile Settings</h1>
        <BaseBreadcrumb :data="breadcrumbData" />
      </div>
    </div>

    <div v-if="isLoading" class="text-sm text-muted-foreground">Loading profile...</div>

    <div v-else class="grid gap-4 lg:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Account Details</CardTitle>
          <CardDescription>Update your basic personal information.</CardDescription>
        </CardHeader>
        <CardContent>
          <form class="space-y-4" @submit.prevent="saveProfile">
            <div class="grid gap-2">
              <Label required for="name">Name</Label>
              <Input
                id="name"
                v-model="profileForm.name"
                :error="getValidationError(profileV$.name, profileErrors.name)"
              />
            </div>

            <div class="grid gap-2">
              <Label required for="email">Email</Label>
              <Input
                id="email"
                type="email"
                v-model="profileForm.email"
                :error="getValidationError(profileV$.email, profileErrors.email)"
              />
            </div>

            <div class="grid gap-2">
              <Label for="phone">Phone</Label>
              <Input id="phone" v-model="profileForm.phone" :error="profileErrors.phone" />
            </div>

            <div class="grid gap-2">
              <Label for="currency">Preferred Currency</Label>
              <Select v-model="profileForm.currency">
                <SelectTrigger>
                  <SelectValue placeholder="Select currency" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="currency in SUPPORTED_CURRENCIES"
                    :key="currency.code"
                    :value="currency.code"
                  >
                    {{ currency.code }} - {{ currency.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <span v-if="profileErrors.currency" class="text-sm text-red-500">{{ profileErrors.currency }}</span>
            </div>

            <div class="grid gap-2">
              <Label for="bio">Bio</Label>
              <Textarea id="bio" v-model="profileForm.bio" />
              <span v-if="profileErrors.bio" class="text-sm text-red-500">{{ profileErrors.bio }}</span>
            </div>

            <Button type="submit" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : 'Save Profile' }}
            </Button>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Password</CardTitle>
          <CardDescription>Use a strong password you do not use elsewhere.</CardDescription>
        </CardHeader>
        <CardContent>
          <form class="space-y-4" @submit.prevent="updatePassword">
            <div class="grid gap-2">
              <Label required for="current_password">Current Password</Label>
              <BasePasswordInput
                id="current_password"
                v-model="passwordForm.current_password"
                :error="getValidationError(passwordV$.current_password, passwordErrors.current_password)"
              />
            </div>

            <div class="grid gap-2">
              <Label required for="new_password">New Password</Label>
              <BasePasswordInput
                id="new_password"
                v-model="passwordForm.password"
                :error="getValidationError(passwordV$.password, passwordErrors.password)"
              />
            </div>

            <div class="grid gap-2">
              <Label required for="password_confirmation">Confirm Password</Label>
              <BasePasswordInput
                id="password_confirmation"
                v-model="passwordForm.password_confirmation"
                :error="getValidationError(passwordV$.password_confirmation, passwordErrors.password_confirmation)"
              />
            </div>

            <Button type="submit" :disabled="isUpdatingPassword">
              {{ isUpdatingPassword ? 'Updating...' : 'Update Password' }}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

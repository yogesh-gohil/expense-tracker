<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/js/components/ui/card'

const route = useRoute()
const token = computed(() => String(route.params.token || ''))
</script>

<template>
  <Card class="sm:mx-auto sm:w-full sm:max-w-md">
    <CardHeader>
      <CardTitle class="text-2xl">
        Reset Password
      </CardTitle>
      <CardDescription>
        Password reset flow is not configured yet.
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-2 text-sm">
      <p v-if="token" class="break-all">
        Token: {{ token }}
      </p>
      <router-link to="/login" class="underline">
        Back to login
      </router-link>
    </CardContent>
  </Card>
</template>

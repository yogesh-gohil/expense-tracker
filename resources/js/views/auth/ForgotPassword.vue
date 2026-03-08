<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/js/components/ui/card'
</script>

<template>
  <Card class="sm:mx-auto sm:w-full sm:max-w-md">
    <CardHeader>
      <CardTitle class="text-2xl">
        Forgot Password
      </CardTitle>
      <CardDescription>
        Password reset flow is not configured yet.
      </CardDescription>
    </CardHeader>
    <CardContent class="text-sm">
      <router-link to="/login" class="underline">
        Back to login
      </router-link>
    </CardContent>
  </Card>
</template>

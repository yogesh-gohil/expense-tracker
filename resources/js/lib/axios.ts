import axios from 'axios'
import { useAuthStore } from '@/js/stores/auth'
import { useToast } from '@/js/components/ui/toast/use-toast'

axios.defaults.withCredentials = true

axios.defaults.headers.common = {
  'X-Requested-With': 'XMLHttpRequest',
  'Accept': 'application/json',
  'Content-Type': 'application/json',
}

const DEFAULT_ERROR_MESSAGE = 'Something went wrong. Please try again.'

const extractErrorMessages = (error: any): string[] => {
  const responseData = error?.response?.data
  const messages: string[] = []

  if (typeof responseData?.message === 'string' && responseData.message.trim()) {
    messages.push(responseData.message.trim())
  }

  if (responseData?.errors && typeof responseData.errors === 'object') {
    for (const value of Object.values(responseData.errors)) {
      if (Array.isArray(value)) {
        for (const item of value) {
          if (typeof item === 'string' && item.trim()) {
            messages.push(item.trim())
          }
        }
      } else if (typeof value === 'string' && value.trim()) {
        messages.push(value.trim())
      }
    }
  }

  if (typeof error?.message === 'string' && error.message.trim() && messages.length === 0) {
    messages.push(error.message.trim())
  }

  if (messages.length === 0) {
    messages.push(DEFAULT_ERROR_MESSAGE)
  }

  return [...new Set(messages)]
}

/*
 * Interceptors
 */

axios.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    const authStore = useAuthStore()
    const { toast } = useToast()
    const errors = extractErrorMessages(error)

    for (const message of errors) {
      toast({
        title: 'Error!',
        description: message,
        variant: 'destructive',
      })
    }
    const status = error?.response?.status

    switch (status) {
      case 401: // Not logged in
        authStore.logout()
        return Promise.reject(error)
      default:
        // Allow individual requests to handle other errors
        return Promise.reject(error)
    }
  },
)

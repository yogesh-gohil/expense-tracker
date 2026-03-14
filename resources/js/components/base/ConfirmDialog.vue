<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/js/components/ui/dialog'
import { Button } from '@/js/components/ui/button'

const props = withDefaults(defineProps<{
  open: boolean
  title?: string
  description?: string
  confirmText?: string
  cancelText?: string
  confirmVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  loading?: boolean
}>(), {
  title: 'Are you sure?',
  description: 'This action cannot be undone.',
  confirmText: 'Delete',
  cancelText: 'Cancel',
  confirmVariant: 'destructive',
  loading: false,
})

const emit = defineEmits(['update:open', 'confirm'])

const close = () => emit('update:open', false)
const confirm = () => emit('confirm')
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>{{ description }}</DialogDescription>
      </DialogHeader>
      <DialogFooter class="gap-2 sm:gap-2">
        <Button variant="outline" :disabled="loading" @click="close">
          {{ cancelText }}
        </Button>
        <Button :variant="confirmVariant" :disabled="loading" @click="confirm">
          {{ confirmText }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

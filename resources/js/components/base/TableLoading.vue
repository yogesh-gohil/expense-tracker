<script setup>
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/js/components/ui/table'
import Skeleton from '@/js/components/ui/skeleton/Skeleton.vue'

const props = defineProps({
  rows: {
    type: Number,
    default: 6,
  },
})
</script>

<template>
  <div class="rounded-lg border bg-card overflow-hidden">
    <Table unstyled>
      <TableHeader>
        <TableRow>
          <TableHead>
            <div class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground">
              Name <span class="h-3 w-3" />
            </div>
          </TableHead>
          <TableHead>
            <div class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground">
              Category <span class="h-3 w-3" />
            </div>
          </TableHead>
          <TableHead>
            <div class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground">
              Date <span class="h-3 w-3" />
            </div>
          </TableHead>
          <TableHead class="text-right">
            <div class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground">
              Amount <span class="h-3 w-3" />
            </div>
          </TableHead>
          <TableHead class="text-right">
            <div class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground">
              Action <span class="h-3 w-3" />
            </div>
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="i in rows" :key="`table-skel-${i}`">
          <TableCell><Skeleton class="h-4 w-40" /></TableCell>
          <TableCell><Skeleton class="h-4 w-24" /></TableCell>
          <TableCell><Skeleton class="h-4 w-24" /></TableCell>
          <TableCell class="text-right"><Skeleton class="h-4 w-20 ml-auto" /></TableCell>
          <TableCell class="text-right"><Skeleton class="h-4 w-16 ml-auto" /></TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>

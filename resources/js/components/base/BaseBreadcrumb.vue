<script setup lang="ts">
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/js/components/ui/breadcrumb'

defineProps({
  data: {
    type: Array,
    required: true,
  }
})
</script>

<template>
  <Breadcrumb>
    <BreadcrumbList>
      <BreadcrumbItem v-for="(item,index) in data">
        <BreadcrumbLink v-if="!item?.active" :href="item.href">
          {{ item?.title }}
        </BreadcrumbLink>
        <BreadcrumbPage v-else>
          {{item.title}}
        </BreadcrumbPage>
        <BreadcrumbSeparator v-if="!item.active" />
      </BreadcrumbItem>
    </BreadcrumbList>
  </Breadcrumb>
</template>

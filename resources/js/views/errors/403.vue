<template>
  <div class="w-full h-screen">
    <div class="flex items-center justify-center w-full h-full">
      <div class="flex flex-col items-center justify-center">
        <h1 class="text-primary text-[10rem] font-bold">
          403
        </h1>
        <h5 class="mb-10 text-3xl text-muted-foreground">
          This request is unauthorized.
        </h5>
        <router-link
          class="
            flex
            items-center
            w-32
            h-12
            px-3
            py-1
            text-base
            font-medium
            leading-none
            text-center text-white
            rounded
            whitespace-nowrap
            bg-primary
            text-primary-foreground
            btn-lg
            hover:opacity-90
          "
          :to="{ name: 'dashboard' }"
        >
          GO HOME
        </router-link>
      </div>
    </div>
  </div>
</template>

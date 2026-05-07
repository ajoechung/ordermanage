<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { usePermissionStore } from '@/store/modules/permission'
import { useUserStore } from '@/store/modules/user'

const permissionStore = usePermissionStore()
const userStore = useUserStore()

onMounted(async () => {
  if (userStore.token) {
    await permissionStore.generateRoutes()
  }
})
</script>

<style>
html, body, #app {
  height: 100%;
  margin: 0;
  padding: 0;
}
</style>

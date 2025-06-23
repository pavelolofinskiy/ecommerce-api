<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <header class="bg-white shadow p-4 flex justify-between items-center">
      <h1 class="text-xl font-semibold">Панель администратора</h1>
      <div v-if="auth.token">
        <span class="mr-4">👤 {{ auth.user?.email }}</span>
        <button @click="logout" class="bg-red-600 text-white px-4 py-1 rounded">Выйти</button>
      </div>
    </header>

    <nav v-if="auth.token" class="bg-gray-100 p-3 flex gap-4">
      <RouterLink to="/orders" class="hover:underline">Заказы</RouterLink>
      <RouterLink to="/products" class="hover:underline">Товары</RouterLink>
    </nav>

    <main class="p-4">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'

const auth = useAuthStore()
const router = useRouter()

onMounted(() => {
  if (!auth.token && router.currentRoute.value.path !== '/login') {
    router.push('/login')
  }
})

const logout = () => {
  auth.logout()
  router.push('/login')
}
</script>
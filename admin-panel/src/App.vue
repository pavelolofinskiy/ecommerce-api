<template>
  <div class="app-container">
    <header class="app-header">
      <h1 class="app-title">Панель администратора</h1>
      <div v-if="auth.token" class="user-info">
        <span class="user-email">👤 {{ auth.user?.email }}</span>
        <button @click="logout" class="logout-btn">Выйти</button>
      </div>
    </header>

    <nav v-if="auth.token" class="app-nav">
      <RouterLink to="/orders" class="nav-link">Заказы</RouterLink>
      <RouterLink to="/products" class="nav-link">Товары</RouterLink>
      <RouterLink to="/categories" class="nav-link">Категории</RouterLink>
    </nav>

    <main class="app-main">
      <RouterView />
    </main>
  </div>
</template>

<style>
.app-container {
  min-height: 100vh;
  background: #f7f7fa;
  color: #222;
  font-family: 'Segoe UI', Arial, sans-serif;
}

.app-header {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  padding: 20px 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.app-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-email {
  margin-right: 18px;
  font-size: 1rem;
  color: #555;
}

.logout-btn {
  background: #e53935;
  color: #fff;
  border: none;
  padding: 8px 20px;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}
.logout-btn:hover {
  background: #b71c1c;
}

.app-nav {
  background: #f0f1f5;
  padding: 14px 32px;
  display: flex;
  gap: 24px;
  border-bottom: 1px solid #e0e0e0;
}

.nav-link {
  color: #333;
  text-decoration: none;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 4px;
  transition: background 0.2s, color 0.2s;
}
.nav-link:hover, .nav-link.router-link-exact-active {
  background: #e3e7fa;
  color: #1a237e;
}

.app-main {
  padding: 32px;
}
</style>

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
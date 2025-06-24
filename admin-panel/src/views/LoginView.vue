<template>
  <div class="login-container">
    <h2 class="login-title">Админ вход</h2>
    <input v-model="email" placeholder="Email" class="login-input" />
    <input v-model="password" type="password" placeholder="Пароль" class="login-input" />
    <button @click="login" class="login-btn">Войти</button>
  </div>
</template>

<style scoped>
.login-container {
  max-width: 350px;
  margin: 60px auto;
  padding: 32px 24px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
  display: flex;
  flex-direction: column;
  align-items: stretch;
}

.login-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 24px;
  text-align: center;
  color: #222;
}

.login-input {
  padding: 12px 14px;
  margin-bottom: 16px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
  outline: none;
  transition: border 0.2s;
}

.login-input:focus {
  border-color: #2563eb;
}

.login-btn {
  padding: 12px;
  background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s;
}

.login-btn:hover {
  background: linear-gradient(90deg, #1e40af 0%, #2563eb 100%);
}
</style>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const router = useRouter()

const login = async () => {
  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: email.value,
      password: password.value,
    })

    // допустим, токен приходит в response.data.token
    localStorage.setItem('token', response.data.token)

    router.push('/orders')
  } catch (err) {
    alert('Ошибка входа')
  }
}
</script>
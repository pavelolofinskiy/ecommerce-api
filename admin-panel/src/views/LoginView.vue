<template>
  <div class="p-4 max-w-md mx-auto">
    <h2 class="text-xl font-bold mb-4">Админ вход</h2>
    <input v-model="email" placeholder="Email" class="input mb-2 w-full" />
    <input v-model="password" type="password" placeholder="Пароль" class="input mb-2 w-full" />
    <button @click="login" class="btn w-full">Войти</button>
  </div>
</template>

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
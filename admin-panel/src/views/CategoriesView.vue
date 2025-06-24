<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Категории</h2>

    <form @submit.prevent="saveCategory" class="mb-4 space-x-2">
      <input v-model="form.name" placeholder="Название" class="input" required />
      <button class="btn bg-green-600 text-white">{{ form.id ? 'Обновить' : 'Создать' }}</button>
    </form>

    <ul class="space-y-2">
      <li v-for="cat in categories" :key="cat.id" class="flex justify-between border-b pb-2">
        <div>{{ cat.name }}</div>
        <div class="space-x-2">
          <button @click="editCategory(cat)" class="text-blue-600">Редактировать</button>
          <button @click="deleteCategory(cat.id)" class="text-red-600">Удалить</button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const categories = ref([])
const form = ref({ id: null, name: '' })

const loadCategories = async () => {
  const res = await api.get('/admin/categories')
  categories.value = res.data
}

const saveCategory = async () => {
  if (form.value.id) {
    await api.put(`/admin/categories/${form.value.id}`, { name: form.value.name })
  } else {
    await api.post('/admin/categories', { name: form.value.name })
  }
  resetForm()
  await loadCategories()
}

const editCategory = (cat) => {
  form.value = { id: cat.id, name: cat.name }
}

const deleteCategory = async (id) => {
  if (confirm('Удалить категорию?')) {
    await api.delete(`/admin/categories/${id}`)
    await loadCategories()
  }
}

const resetForm = () => {
  form.value = { id: null, name: '' }
}

onMounted(loadCategories)
</script>
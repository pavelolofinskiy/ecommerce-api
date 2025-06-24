<template>
  <div class="categories-container">
    <h2 class="categories-title">Категории</h2>

    <form @submit.prevent="saveCategory" class="category-form">
      <input v-model="form.name" placeholder="Название" class="category-input" required />
      <button class="category-btn">{{ form.id ? 'Обновить' : 'Создать' }}</button>
    </form>

    <ul class="categories-list">
      <li v-for="cat in categories" :key="cat.id" class="category-item">
        <div class="category-name">{{ cat.name }}</div>
        <div class="category-actions">
          <button @click="editCategory(cat)" class="edit-btn">Редактировать</button>
          <button @click="deleteCategory(cat.id)" class="delete-btn">Удалить</button>
        </div>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.categories-container {
  max-width: 500px;
  margin: 40px auto;
  padding: 32px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
}

.categories-title {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 24px;
  color: #222;
  text-align: center;
}

.category-form {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
}

.category-input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  outline: none;
  transition: border 0.2s;
}
.category-input:focus {
  border-color: #4f8cff;
}

.category-btn {
  background: #4f8cff;
  color: #fff;
  border: none;
  padding: 10px 22px;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
}
.category-btn:hover {
  background: #2563eb;
}

.categories-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #eee;
}

.category-name {
  font-size: 1.1rem;
  color: #333;
}

.category-actions {
  display: flex;
  gap: 10px;
}

.edit-btn,
.delete-btn {
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  padding: 4px 10px;
  border-radius: 4px;
  transition: background 0.2s, color 0.2s;
}

.edit-btn {
  color: #2563eb;
}
.edit-btn:hover {
  background: #e6f0ff;
}

.delete-btn {
  color: #e11d48;
}
.delete-btn:hover {
  background: #ffe6ea;
}
</style>

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
<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Товары</h2>

    <!-- Форма создания / редактирования -->
    <form @submit.prevent="saveProduct" class="mb-6 space-y-3">
      <input v-model="form.name" placeholder="Название товара" class="input w-full" required />
      <textarea v-model="form.description" placeholder="Описание" class="input w-full"></textarea>

      <select v-model="form.category_id" class="input w-full" required>
        <option disabled value="">Выберите категорию</option>
        <option v-for="cat in categories" :value="cat.id" :key="cat.id">{{ cat.name }}</option>
      </select>

      <input v-model="form.price" type="number" step="0.01" placeholder="Цена" class="input w-full" required />

      <button class="btn bg-green-600 text-white">{{ form.id ? 'Обновить' : 'Создать' }}</button>
    </form>

    <!-- Таблица товаров -->
    <table class="w-full border text-left">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 border">ID</th>
          <th class="p-2 border">Название</th>
          <th class="p-2 border">Цена</th>
          <th class="p-2 border">Категория</th>
          <th class="p-2 border">Действия</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id" class="border-t">
          <td class="p-2 border">{{ product.id }}</td>
          <td class="p-2 border">{{ product.name }}</td>
          <td class="p-2 border">{{ product.price?.amount ?? '—' }}</td>
          <td class="p-2 border">{{ product.category?.name }}</td>
          <td class="p-2 border space-x-2">
            <button @click="editProduct(product)" class="text-blue-600 hover:underline">Редактировать</button>
            <button @click="deleteProduct(product.id)" class="text-red-600 hover:underline">Удалить</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const products = ref([])
const categories = ref([])
const form = ref({
  id: null,
  name: '',
  description: '',
  category_id: '',
  price: ''
})

const loadProducts = async () => {
  const res = await api.get('/admin/products')
  products.value = res.data.data
}

const loadCategories = async () => {
  const res = await api.get('/admin/categories')
  categories.value = res.data
}

const saveProduct = async () => {
  try {
    if (form.value.id) {
      await api.put(`/admin/products/${form.value.id}`, form.value)
    } else {
      await api.post('/admin/products', form.value)
    }
    await loadProducts()
    resetForm()
  } catch (e) {
    alert('Ошибка при сохранении')
  }
}

const editProduct = (product) => {
  form.value = {
    id: product.id,
    name: product.name,
    description: product.description,
    category_id: product.category?.id || '',
    price: product.price?.amount || '',
  }
}

const deleteProduct = async (id) => {
  if (confirm('Удалить товар?')) {
    await api.delete(`/admin/products/${id}`)
    await loadProducts()
  }
}

const resetForm = () => {
  form.value = {
    id: null,
    name: '',
    description: '',
    category_id: '',
    price: ''
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
})
</script>
<template>
  <div class="products-container">
    <h2 class="products-title">Товары</h2>

    <!-- Фильтры -->
    <div class="filters">
      <div class="filter-item">
        <label>Поиск</label>
        <input v-model="filters.search" placeholder="Поиск по названию" />
      </div>

      <div class="filter-item">
        <label>Категория</label>
        <select v-model="filters.category_id">
          <option value="">Все категории</option>
          <option v-for="cat in categories" :value="cat.id" :key="cat.id">{{ cat.name }}</option>
        </select>
      </div>

      <div class="filter-item">
        <label>Тип товара</label>
        <select v-model="filters.type">
          <option value="">Все типы</option>
          <option v-for="type in types" :value="type" :key="type">{{ type }}</option>
        </select>
      </div>

      <div class="filter-item">
        <label>Мин. цена</label>
        <input v-model.number="filters.price_min" type="number" placeholder="Мин. цена" />
      </div>

      <div class="filter-item">
        <label>Макс. цена</label>
        <input v-model.number="filters.price_max" type="number" placeholder="Макс. цена" />
      </div>

      <div class="filter-actions">
        <button @click="loadProducts" class="btn btn-primary">Фильтровать</button>
        <button @click="resetFilters" class="btn btn-secondary">Сбросить</button>
      </div>
    </div>

    <!-- Массовое изменение типов -->
    <div class="bulk-type-update">
      <h4>Массовое изменение типа товаров</h4>
      <select v-model="bulkType">
        <option disabled value="">Выберите тип</option>
        <option v-for="type in types" :value="type" :key="type">{{ type }}</option>
      </select>
      <button @click="applyBulkType" class="btn btn-success">Применить к выбранным</button>
    </div>

    <!-- Форма создания / редактирования -->
    <form @submit.prevent="saveProduct" class="product-form">
      <div class="form-row">
        <div class="form-group">
          <label>Название товара</label>
          <input v-model="form.name" placeholder="Название товара" required />
        </div>

        <div class="form-group">
          <label>Категория</label>
          <select v-model="form.category_id" required>
            <option disabled value="">Выберите категорию</option>
            <option v-for="cat in categories" :value="cat.id" :key="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>Тип товара</label>
          <select v-model="form.type" required>
            <option disabled value="">Выберите тип</option>
            <option v-for="type in types" :value="type" :key="type">{{ type }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>Цена</label>
          <input v-model="form.price" type="number" step="0.01" placeholder="Цена" required />
        </div>

        <div class="form-group">
          <label>Изображение</label>
          <input type="file" @change="onFileChange" />
        </div>
      </div>

      <div class="form-group">
        <label>Описание</label>
        <textarea v-model="form.description" placeholder="Описание"></textarea>
      </div>

      <!-- Новое: теги -->
      <div class="form-group">
        <label>Теги</label>
        <div class="tags-checkboxes">
          <label v-for="(label, tag) in allowedTags" :key="tag" class="tag-checkbox">
            <input
              type="checkbox"
              :value="tag"
              v-model="form.tags"
              :disabled="!form.tags.includes(tag) && form.tags.length >= 3"
            />
            {{ label }}
          </label>
          <small class="tags-help">Можно выбрать от 1 до 3 тегов</small>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn btn-success">
          {{ form.id ? 'Обновить' : 'Создать' }}
        </button>
      </div>
    </form>

    <!-- Таблица товаров -->
    <div class="table-wrapper">
      <table class="products-table">
        <thead>
          <tr>
            <th><input type="checkbox" @change="toggleSelectAll" :checked="allSelected" /></th>
            <th>ID</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Категория</th>
            <th>Тип</th>
            <th>Теги</th>
            <th>Действия</th>
            <th>Изображение</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td><input type="checkbox" :value="product.id" v-model="selectedProducts" /></td>
            <td>{{ product.id }}</td>
            <td>{{ product.name }}</td>
            <td>{{ product.price ?? '—' }}</td>
            <td>{{ product.category?.name }}</td>
            <td>{{ product.type ?? '—' }}</td>
            <td>
              <span
                v-for="tag in product.tags || []"
                :key="tag"
                class="tag-label"
              >
                {{ allowedTags[tag] ?? tag }}
              </span>
            </td>
            <td>
              <button @click="editProduct(product)" class="btn-link edit">Редактировать</button>
              <button @click="deleteProduct(product.id)" class="btn-link delete">Удалить</button>
            </td>
            <td>
              <img
                v-if="product.image"
                :src="getImageUrl(product.image)"
                class="product-image"
                alt="Product image"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../api'

const products = ref([])
const categories = ref([])
const types = ['new', 'checked', 'popular']
const allowedTags = {
  new: 'Новинка',
  top: 'Топ',
  hit: 'Хит'
}
const selectedProducts = ref([])
const bulkType = ref('')

const form = ref({
  id: null,
  name: '',
  description: '',
  category_id: '',
  price: '',
  type: '',
  tags: []  // <-- добавлено
})

const filters = ref({
  search: '',
  category_id: '',
  price_min: null,
  price_max: null,
  type: ''
})

const loadProducts = async () => {
  const params = { ...filters.value }
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key]
    }
  })
  const res = await api.get('/admin/products', { params })
  products.value = res.data.data
}

const resetFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    price_min: null,
    price_max: null,
    type: ''
  }
  loadProducts()
}

const imageFile = ref(null)
const onFileChange = (e) => { imageFile.value = e.target.files[0] }

const loadCategories = async () => {
  const res = await api.get('/admin/categories')
  categories.value = res.data
}

const getImageUrl = (path) => {
  if (!path.startsWith('/')) path = '/' + path
  return 'http://localhost:8000' + path
}

const saveProduct = async () => {
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('category_id', form.value.category_id)
    formData.append('price', form.value.price)
    formData.append('type', form.value.type)
    form.value.tags.forEach(tag => {
      formData.append('tags[]', tag)
    })
    console.log('formDataformData',formData);
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }
    if (form.value.id) {
      await api.post(`/admin/products/${form.value.id}?_method=PUT`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/admin/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
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
    type: product.type || '',
    tags: product.tags || [] // <-- подгружаем теги в форму
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
    price: '',
    type: '',
    tags: []
  }
  imageFile.value = null
}

const applyBulkType = async () => {
  if (!bulkType.value || selectedProducts.value.length === 0) {
    alert('Выберите тип и хотя бы один товар')
    return
  }
  try {
    await api.post('/admin/products/bulk-update-type', {
      product_ids: selectedProducts.value,
      type: bulkType.value
    })
    alert('Тип успешно обновлен')
    selectedProducts.value = []
    bulkType.value = ''
    await loadProducts()
  } catch (e) {
    alert('Ошибка при обновлении типов')
  }
}

const allSelected = computed(() => {
  return selectedProducts.value.length === products.value.length && products.value.length > 0
})

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedProducts.value = []
  } else {
    selectedProducts.value = products.value.map(p => p.id)
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
})
</script>

<style scoped>
/* Добавил стили для тегов */
.tag-label {
  display: inline-block;
  background-color: #2563eb;
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
  margin-right: 6px;
  user-select: none;
}

.tags-checkboxes {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.tag-checkbox input[type="checkbox"] {
  margin-right: 5px;
}

.tags-help {
  font-size: 0.75rem;
  color: #718096;
  margin-top: 4px;
  display: block;
}
.products-container {
  max-width: 900px;
  margin: 40px auto;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
  padding: 36px 32px 32px 32px;
}
.products-title {
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 32px;
  color: #2d3748;
  letter-spacing: 0.02em;
}
.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  align-items: flex-end;
  background: #f7fafc;
  padding: 18px 18px 10px 18px;
  border-radius: 12px;
  margin-bottom: 32px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.filter-item {
  display: flex;
  flex-direction: column;
  min-width: 160px;
  flex: 1 1 160px;
}
.filter-item label {
  font-size: 0.97rem;
  font-weight: 500;
  color: #4a5568;
  margin-bottom: 4px;
}
.filter-item input,
.filter-item select {
  padding: 7px 10px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 1rem;
  background: #fff;
  outline: none;
  transition: border 0.2s;
}
.filter-item input:focus,
.filter-item select:focus {
  border-color: #3182ce;
}
.filter-actions {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}
.btn {
  padding: 7px 18px;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background 0.18s;
}
.btn-primary {
  background: #2563eb;
  color: #fff;
}
.btn-primary:hover {
  background: #1d4ed8;
}
.btn-secondary {
  background: #e2e8f0;
  color: #2d3748;
}
.btn-secondary:hover {
  background: #cbd5e1;
}
.btn-success {
  background: #22c55e;
  color: #fff;
  padding: 8px 28px;
}
.btn-success:hover {
  background: #16a34a;
}
.product-form {
  background: #f7fafc;
  padding: 22px 18px 18px 18px;
  border-radius: 12px;
  margin-bottom: 32px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.form-row {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
}
.form-group {
  display: flex;
  flex-direction: column;
  flex: 1 1 180px;
  min-width: 180px;
  margin-bottom: 12px;
}
.form-group label {
  font-size: 0.97rem;
  font-weight: 500;
  color: #4a5568;
  margin-bottom: 4px;
}
.form-group input,
.form-group select,
.form-group textarea {
  padding: 7px 10px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 1rem;
  background: #fff;
  outline: none;
  transition: border 0.2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #3182ce;
}
.form-group textarea {
  min-height: 60px;
  resize: vertical;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 8px;
}
.table-wrapper {
  overflow-x: auto;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
  background: #fff;
}
.products-table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
}
.products-table th,
.products-table td {
  padding: 12px 10px;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
}
.products-table th {
  background: #e0e7ef;
  color: #374151;
  font-weight: 600;
  font-size: 1.01rem;
}
.products-table tr:hover {
  background: #f1f5f9;
  transition: background 0.18s;
}
.btn-link {
  background: none;
  border: none;
  color: #2563eb;
  cursor: pointer;
  font-weight: 500;
  text-decoration: underline;
  margin-right: 8px;
  padding: 0;
  font-size: 1rem;
  transition: color 0.18s;
}
.btn-link.edit:hover {
  color: #1d4ed8;
}
.btn-link.delete {
  color: #ef4444;
}
.btn-link.delete:hover {
  color: #b91c1c;
}
.product-image {
  width: 48px;
  height: 48px;
  object-fit: contain;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
}
@media (max-width: 800px) {
  .products-container {
    padding: 16px 4px;
  }
  .filters, .product-form {
    padding: 10px 4px;
  }
  .form-row {
    flex-direction: column;
    gap: 8px;
  }
}
</style>
<template>
  <div class="orders-container">
    <h2 class="orders-title">Заказы</h2>

    <table class="orders-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Пользователь</th>
          <th>Статус</th>
          <th>Сумма</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id">
          <td>{{ order.id }}</td>
          <td>{{ order.user?.email }}</td>
          <td>
            <select v-model="order.status" @change="updateStatus(order)">
              <option value="new">новый</option>
              <option value="processing">в обработке</option>
              <option value="completed">завершён</option>
              <option value="canceled">отменён</option>
            </select>
          </td>
          <td>{{ order.total }} грн</td>
          <td>
            <button @click="viewDetails(order)" class="details-btn">Подробнее</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.orders-container {
  padding: 32px;
  background: #f8f9fb;
  min-height: 100vh;
}

.orders-title {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 24px;
  color: #222;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border-radius: 8px;
  overflow: hidden;
}

.orders-table th,
.orders-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  text-align: left;
}

.orders-table th {
  background: #f1f3f7;
  font-weight: 600;
  color: #444;
}

.orders-table tr:last-child td {
  border-bottom: none;
}

.orders-table tr:hover {
  background: #f6f8fa;
  transition: background 0.2s;
}

select {
  padding: 6px 10px;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  background: #f9fafb;
  font-size: 1rem;
  outline: none;
  transition: border 0.2s;
}

select:focus {
  border-color: #2563eb;
}

.details-btn {
  background: none;
  color: #2563eb;
  border: none;
  cursor: pointer;
  font-weight: 500;
  text-decoration: underline;
  padding: 0;
  transition: color 0.2s;
}

.details-btn:hover {
  color: #1d4ed8;
}
</style>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../api'

const orders = ref([])

const loadOrders = async () => {
  const res = await api.get('/admin/orders')
  orders.value = res.data.data
}

const updateStatus = async (order) => {
  try {
    await api.patch(`/admin/orders/${order.id}/status`, { status: order.status })
  } catch (err) {
    alert('Не удалось обновить статус')
  }
}

const viewDetails = (order) => {
  alert(`Заказ #${order.id} содержит ${order.items.length} товаров`)
}

onMounted(loadOrders)
</script>
<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Заказы</h2>

    <table class="w-full text-left border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 border">ID</th>
          <th class="p-2 border">Пользователь</th>
          <th class="p-2 border">Статус</th>
          <th class="p-2 border">Сумма</th>
          <th class="p-2 border">Действия</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id" class="border-t">
          <td class="p-2 border">{{ order.id }}</td>
          <td class="p-2 border">{{ order.user?.email }}</td>
          <td class="p-2 border">
            <select v-model="order.status" @change="updateStatus(order)">
              <option value="new">новый</option>
              <option value="processing">в обработке</option>
              <option value="completed">завершён</option>
              <option value="canceled">отменён</option>
            </select>
          </td>
          <td class="p-2 border">{{ order.total }} грн</td>
          <td class="p-2 border">
            <button @click="viewDetails(order)" class="text-blue-600 hover:underline">Подробнее</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

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
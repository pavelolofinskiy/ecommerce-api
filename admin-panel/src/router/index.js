import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import OrdersView from '../views/OrdersView.vue'
import ProductsView from '../views/ProductsView.vue'

const routes = [
  { path: '/', redirect: '/orders' },
  { path: '/login', component: LoginView },
  { path: '/orders', component: OrdersView },
  { path: '/products', component: ProductsView },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import TransactionsView from '../views/TransactionsView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/transactions',
    },
    {
      path: '/login',
      component: LoginView,
    },
    {
      path: '/transactions',
      component: TransactionsView,
      meta: {
        requiresAuth: true,
      },
    },
  ],
})

router.beforeEach((to) => {
  const hasToken = Boolean(localStorage.getItem('token'))

  if (to.meta.requiresAuth && !hasToken) {
    return '/login'
  }

  if (to.path === '/login' && hasToken) {
    return '/transactions'
  }

  return true
})

export default router
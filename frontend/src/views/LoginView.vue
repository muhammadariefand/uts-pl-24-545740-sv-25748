<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const form = reactive({
  email: 'admin@kasmuda.test',
  password: 'password',
})

const loading = ref(false)
const errorMessage = ref('')

const login = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.post('/login', form)

    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))

    router.push('/transactions')
  } catch (error) {
    if (error.response?.status === 422) {
      errorMessage.value =
        error.response.data.message || 'Email atau password salah.'
    } else {
      errorMessage.value = 'Login gagal. Pastikan backend sedang berjalan.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-card">
      <h1>KasMuda Desa</h1>
      <p class="subtitle">Login Pengelola Kas</p>

      <form @submit.prevent="login">
        <label for="email">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          autocomplete="email"
          required
        />

        <label for="password">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          required
        />

        <p v-if="errorMessage" class="error">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Login' }}
        </button>
      </form>
    </section>
  </main>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: #f1f5f9;
  padding: 1rem;
}

.auth-card {
  width: min(100%, 400px);
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgb(15 23 42 / 12%);
}

h1 {
  margin: 0;
  color: #0f766e;
  text-align: center;
}

.subtitle {
  text-align: center;
  color: #64748b;
  margin-bottom: 1.5rem;
}

form {
  display: grid;
  gap: 0.6rem;
}

input {
  padding: 0.7rem;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  margin-bottom: 0.6rem;
}

button {
  border: 0;
  border-radius: 6px;
  background: #0f766e;
  color: white;
  padding: 0.75rem;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #b91c1c;
  background: #fee2e2;
  padding: 0.75rem;
  border-radius: 6px;
}
</style>
<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <div style="font-size: 2.5rem;">📚</div>
        <h1>Perpustakaan Digital</h1>
        <p>Sistem Informasi Manajemen Buku - UTS KEPL</p>
      </div>

      <!-- Alert Error -->
      <div v-if="errorMessage" class="alert alert-danger">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label class="form-label" for="email">Alamat Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-input"
            :class="{ 'is-invalid': errors.email }"
            placeholder="admin@example.com"
            required
            autocomplete="email"
          />
          <div v-if="errors.email" class="form-error">{{ errors.email[0] }}</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Kata Sandi</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-input"
            :class="{ 'is-invalid': errors.password }"
            placeholder="••••••••"
            required
            autocomplete="current-password"
          />
          <div v-if="errors.password" class="form-error">{{ errors.password[0] }}</div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;" :disabled="loading">
          <span v-if="loading">Memproses...</span>
          <span v-else>Masuk ke Sistem</span>
        </button>

        <button
          type="button"
          class="btn btn-secondary btn-sm"
          style="width: 100%; margin-top: 0.75rem;"
          @click="fillDemo"
        >
          Isi Akun Demo (admin@example.com)
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '../api';

const router = useRouter();

const form = reactive({
  email: '',
  password: '',
});

const errors = ref({});
const errorMessage = ref('');
const loading = ref(false);

const fillDemo = () => {
  form.email = 'admin@example.com';
  form.password = 'password123';
};

const handleLogin = async () => {
  loading.value = true;
  errors.value = {};
  errorMessage.value = '';

  try {
    const response = await apiClient.post('/login', form);
    const { token, user } = response.data;

    // Store token and user in localStorage
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));

    // Redirect to books list
    router.push({ name: 'books' });
  } catch (error) {
    if (error.response) {
      if (error.response.status === 401) {
        errorMessage.value = error.response.data.message || 'Kredensial tidak valid.';
      } else if (error.response.status === 422) {
        errors.value = error.response.data.errors || {};
        errorMessage.value = 'Periksa kembali data yang dimasukkan.';
      } else {
        errorMessage.value = error.response.data.message || 'Terjadi kesalahan pada server.';
      }
    } else {
      errorMessage.value = 'Tidak dapat terhubung ke server backend.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

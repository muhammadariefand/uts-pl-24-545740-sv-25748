<template>
  <div>
    <!-- Navbar -->
    <header class="navbar">
      <div class="nav-brand">
        <span>📚</span>
        <span>Perpustakaan Digital UTS KEPL</span>
      </div>
      <div class="nav-user">
        <span class="user-badge">👤 {{ currentUser?.name || 'Administrator' }} ({{ currentUser?.email }})</span>
        <button class="btn btn-secondary btn-sm" @click="handleLogout" :disabled="loggingOut">
          {{ loggingOut ? 'Keluar...' : 'Keluar' }}
        </button>
      </div>
    </header>

    <main class="container">
      <!-- Success Message Banner -->
      <div v-if="successMessage" class="alert alert-success">
        <span>✅</span>
        <span>{{ successMessage }}</span>
      </div>

      <!-- Error Message Banner -->
      <div v-if="errorMessage" class="alert alert-danger">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Stats Summary -->
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">Total Judul Buku</div>
          <div class="stat-value">{{ books.length }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Eksemplar Stok</div>
          <div class="stat-value">{{ totalStock }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Jumlah Kategori</div>
          <div class="stat-value">{{ totalCategories }}</div>
        </div>
      </section>

      <!-- Main Content Card -->
      <div class="card">
        <div class="card-header">
          <div>
            <h2 class="card-title">Daftar Koleksi Buku</h2>
            <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.25rem;">
              Kelola data katalog buku perpustakaan secara real-time via RESTful API
            </p>
          </div>
          <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <input
              v-model="searchQuery"
              type="text"
              class="form-input"
              style="width: 240px;"
              placeholder="Cari judul, penulis, ISBN..."
            />
            <button class="btn btn-primary" @click="openAddModal">
              <span>+</span>
              <span>Tambah Buku</span>
            </button>
          </div>
        </div>

        <div class="card-body" style="padding: 0;">
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th style="width: 60px;">No</th>
                  <th>Informasi Buku</th>
                  <th>ISBN</th>
                  <th>Kategori</th>
                  <th>Tahun</th>
                  <th>Stok</th>
                  <th style="text-align: right; width: 160px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                    Memuat data buku dari server...
                  </td>
                </tr>
                <tr v-else-if="filteredBooks.length === 0">
                  <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                    {{ searchQuery ? 'Tidak ada buku yang sesuai dengan pencarian.' : 'Belum ada data buku.' }}
                  </td>
                </tr>
                <tr v-for="(book, index) in filteredBooks" :key="book.id">
                  <td>{{ index + 1 }}</td>
                  <td>
                    <div style="font-weight: 600; color: var(--gray-900);">{{ book.title }}</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500);">Penulis: {{ book.author }}</div>
                  </td>
                  <td>
                    <code style="background: var(--gray-100); padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.8rem;">
                      {{ book.isbn }}
                    </code>
                  </td>
                  <td>
                    <span class="badge badge-primary">{{ book.category }}</span>
                  </td>
                  <td>{{ book.published_year }}</td>
                  <td>
                    <span :class="['badge', book.stock > 0 ? 'badge-success' : 'badge-primary']">
                      {{ book.stock }} pcs
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <div style="display: inline-flex; gap: 0.5rem;">
                      <button class="btn btn-secondary btn-sm" @click="openEditModal(book)">
                        Edit
                      </button>
                      <button class="btn btn-danger btn-sm" @click="confirmDelete(book)">
                        Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Form (Tambah / Edit) -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditing ? 'Edit Informasi Buku' : 'Tambah Buku Baru' }}</h3>
          <button style="background: transparent; border: none; font-size: 1.25rem; cursor: pointer;" @click="closeModal">
            &times;
          </button>
        </div>

        <form @submit.prevent="saveBook">
          <div class="modal-body">
            <div class="form-group">
              <label class="form-label" for="book-title">Judul Buku *</label>
              <input
                id="book-title"
                v-model="form.title"
                type="text"
                class="form-input"
                :class="{ 'is-invalid': formErrors.title }"
                placeholder="Contoh: Clean Code"
                required
              />
              <div v-if="formErrors.title" class="form-error">{{ formErrors.title[0] }}</div>
            </div>

            <div class="form-group">
              <label class="form-label" for="book-author">Penulis *</label>
              <input
                id="book-author"
                v-model="form.author"
                type="text"
                class="form-input"
                :class="{ 'is-invalid': formErrors.author }"
                placeholder="Contoh: Robert C. Martin"
                required
              />
              <div v-if="formErrors.author" class="form-error">{{ formErrors.author[0] }}</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label" for="book-isbn">ISBN *</label>
                <input
                  id="book-isbn"
                  v-model="form.isbn"
                  type="text"
                  class="form-input"
                  :class="{ 'is-invalid': formErrors.isbn }"
                  placeholder="978-0132350884"
                  required
                />
                <div v-if="formErrors.isbn" class="form-error">{{ formErrors.isbn[0] }}</div>
              </div>

              <div class="form-group">
                <label class="form-label" for="book-category">Kategori *</label>
                <input
                  id="book-category"
                  v-model="form.category"
                  type="text"
                  class="form-input"
                  :class="{ 'is-invalid': formErrors.category }"
                  placeholder="Software Engineering"
                  required
                />
                <div v-if="formErrors.category" class="form-error">{{ formErrors.category[0] }}</div>
              </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label" for="book-year">Tahun Terbit *</label>
                <input
                  id="book-year"
                  v-model.number="form.published_year"
                  type="number"
                  class="form-input"
                  :class="{ 'is-invalid': formErrors.published_year }"
                  min="1000"
                  max="2030"
                  required
                />
                <div v-if="formErrors.published_year" class="form-error">{{ formErrors.published_year[0] }}</div>
              </div>

              <div class="form-group">
                <label class="form-label" for="book-stock">Stok *</label>
                <input
                  id="book-stock"
                  v-model.number="form.stock"
                  type="number"
                  class="form-input"
                  :class="{ 'is-invalid': formErrors.stock }"
                  min="0"
                  required
                />
                <div v-if="formErrors.stock" class="form-error">{{ formErrors.stock[0] }}</div>
              </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
              <label class="form-label" for="book-desc">Deskripsi</label>
              <textarea
                id="book-desc"
                v-model="form.description"
                rows="3"
                class="form-textarea"
                placeholder="Catatan ringkas mengenai buku..."
              ></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="saving">
              Batal
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Buku') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header">
          <h3 class="modal-title">Konfirmasi Penghapusan</h3>
          <button style="background: transparent; border: none; font-size: 1.25rem; cursor: pointer;" @click="showDeleteModal = false">
            &times;
          </button>
        </div>
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus buku <strong>"{{ bookToDelete?.title }}"</strong>?</p>
          <p style="color: var(--gray-500); font-size: 0.8rem; margin-top: 0.5rem;">
            Tindakan ini permanen dan akan menghapus data dari basis data.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showDeleteModal = false" :disabled="deleting">
            Batal
          </button>
          <button class="btn btn-danger" @click="deleteBook" :disabled="deleting">
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus Data' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '../api';

const router = useRouter();

// State
const books = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const loggingOut = ref(false);

const searchQuery = ref('');
const successMessage = ref('');
const errorMessage = ref('');

const showModal = ref(false);
const isEditing = ref(false);
const currentBookId = ref(null);

const showDeleteModal = ref(false);
const bookToDelete = ref(null);

const currentUser = ref(null);

const form = reactive({
  title: '',
  author: '',
  isbn: '',
  category: '',
  published_year: new Date().getFullYear(),
  stock: 1,
  description: '',
});

const formErrors = ref({});

// Computed stats & filtered list
const totalStock = computed(() => {
  return books.value.reduce((acc, b) => acc + (Number(b.stock) || 0), 0);
});

const totalCategories = computed(() => {
  const cats = new Set(books.value.map(b => b.category).filter(Boolean));
  return cats.size;
});

const filteredBooks = computed(() => {
  if (!searchQuery.value.trim()) return books.value;
  const q = searchQuery.value.toLowerCase();
  return books.value.filter(b =>
    (b.title && b.title.toLowerCase().includes(q)) ||
    (b.author && b.author.toLowerCase().includes(q)) ||
    (b.isbn && b.isbn.toLowerCase().includes(q)) ||
    (b.category && b.category.toLowerCase().includes(q))
  );
});

// Flash messages helper
const flashSuccess = (msg) => {
  successMessage.value = msg;
  setTimeout(() => {
    successMessage.value = '';
  }, 4000);
};

// Fetch books from API
const fetchBooks = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const response = await apiClient.get('/books');
    books.value = response.data.data || [];
  } catch (err) {
    errorMessage.value = 'Gagal memuat data buku dari server.';
  } finally {
    loading.value = false;
  }
};

// Modal controls
const openAddModal = () => {
  isEditing.value = false;
  currentBookId.value = null;
  formErrors.value = {};
  Object.assign(form, {
    title: '',
    author: '',
    isbn: '',
    category: '',
    published_year: new Date().getFullYear(),
    stock: 1,
    description: '',
  });
  showModal.value = true;
};

const openEditModal = (book) => {
  isEditing.value = true;
  currentBookId.value = book.id;
  formErrors.value = {};
  Object.assign(form, {
    title: book.title,
    author: book.author,
    isbn: book.isbn,
    category: book.category,
    published_year: book.published_year,
    stock: book.stock,
    description: book.description || '',
  });
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  formErrors.value = {};
};

// Create or Update Book
const saveBook = async () => {
  saving.value = true;
  formErrors.value = {};
  errorMessage.value = '';

  try {
    if (isEditing.value) {
      // PUT /api/books/{id}
      await apiClient.put(`/books/${currentBookId.value}`, form);
      flashSuccess('Data buku berhasil diperbarui!');
    } else {
      // POST /api/books
      await apiClient.post('/books', form);
      flashSuccess('Buku baru berhasil ditambahkan!');
    }
    closeModal();
    await fetchBooks();
  } catch (err) {
    if (err.response && err.response.status === 422) {
      formErrors.value = err.response.data.errors || {};
    } else {
      errorMessage.value = err.response?.data?.message || 'Gagal menyimpan data buku.';
    }
  } finally {
    saving.value = false;
  }
};

// Delete Book
const confirmDelete = (book) => {
  bookToDelete.value = book;
  showDeleteModal.value = true;
};

const deleteBook = async () => {
  if (!bookToDelete.value) return;
  deleting.value = true;
  try {
    await apiClient.delete(`/books/${bookToDelete.value.id}`);
    flashSuccess(`Buku "${bookToDelete.value.title}" berhasil dihapus.`);
    showDeleteModal.value = false;
    bookToDelete.value = null;
    await fetchBooks();
  } catch (err) {
    errorMessage.value = 'Gagal menghapus data buku.';
  } finally {
    deleting.value = false;
  }
};

// Logout
const handleLogout = async () => {
  loggingOut.value = true;
  try {
    await apiClient.post('/logout');
  } catch (e) {
    // ignore
  } finally {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    loggingOut.value = false;
    router.push({ name: 'login' });
  }
};

onMounted(() => {
  const storedUser = localStorage.getItem('user');
  if (storedUser) {
    try {
      currentUser.value = JSON.parse(storedUser);
    } catch (e) {
      currentUser.value = null;
    }
  }
  fetchBooks();
});
</script>

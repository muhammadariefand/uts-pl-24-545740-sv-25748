<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const transactions = ref([])
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const editingId = ref(null)

const form = reactive({
  type: 'income',
  title: '',
  description: '',
  amount: '',
  transaction_date: new Date().toISOString().slice(0, 10),
})

const user = computed(() => {
  const savedUser = localStorage.getItem('user')
  return savedUser ? JSON.parse(savedUser) : null
})

const totalIncome = computed(() =>
  transactions.value
    .filter((transaction) => transaction.type === 'income')
    .reduce((total, transaction) => total + Number(transaction.amount), 0),
)

const totalExpense = computed(() =>
  transactions.value
    .filter((transaction) => transaction.type === 'expense')
    .reduce((total, transaction) => total + Number(transaction.amount), 0),
)

const balance = computed(() => totalIncome.value - totalExpense.value)

const formatRupiah = (amount) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount)

const resetForm = () => {
  editingId.value = null
  form.type = 'income'
  form.title = ''
  form.description = ''
  form.amount = ''
  form.transaction_date = new Date().toISOString().slice(0, 10)
}

const clearMessages = () => {
  errorMessage.value = ''
  successMessage.value = ''
}

const loadTransactions = async () => {
  loading.value = true
  clearMessages()

  try {
    const response = await api.get('/transactions')
    transactions.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal mengambil data transaksi.'
  } finally {
    loading.value = false
  }
}

const submitForm = async () => {
  saving.value = true
  clearMessages()

  const payload = {
    type: form.type,
    title: form.title,
    description: form.description || null,
    amount: Number(form.amount),
    transaction_date: form.transaction_date,
  }

  try {
    if (editingId.value) {
      await api.put(`/transactions/${editingId.value}`, payload)
      successMessage.value = 'Transaksi berhasil diubah.'
    } else {
      await api.post('/transactions', payload)
      successMessage.value = 'Transaksi berhasil dibuat.'
    }

    resetForm()
    await loadTransactions()
  } catch (error) {
    if (error.response?.status === 422) {
      errorMessage.value = Object.values(
        error.response.data.errors || {},
      )
        .flat()
        .join(' ')
    } else {
      errorMessage.value =
        error.response?.data?.message || 'Gagal menyimpan transaksi.'
    }
  } finally {
    saving.value = false
  }
}

const startEdit = (transaction) => {
  clearMessages()
  editingId.value = transaction.id
  form.type = transaction.type
  form.title = transaction.title
  form.description = transaction.description || ''
  form.amount = transaction.amount
  form.transaction_date = transaction.transaction_date
}

const deleteTransaction = async (transaction) => {
  const confirmed = window.confirm(
    `Hapus transaksi "${transaction.title}"?`,
  )

  if (!confirmed) {
    return
  }

  clearMessages()

  try {
    await api.delete(`/transactions/${transaction.id}`)
    successMessage.value = 'Transaksi berhasil dihapus.'
    await loadTransactions()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal menghapus transaksi.'
  }
}

const logout = async () => {
  try {
    await api.post('/logout')
  } catch {
    // Token tetap dihapus agar user tidak terjebak di halaman ini.
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}

onMounted(loadTransactions)
</script>

<template>
  <main class="page">
    <header class="topbar">
      <div>
        <h1>KasMuda Desa</h1>
        <p v-if="user">Login sebagai {{ user.name }}</p>
      </div>

      <button class="logout-button" type="button" @click="logout">
        Logout
      </button>
    </header>

    <section class="summary-grid">
      <article class="summary-card">
        <span>Total pemasukan</span>
        <strong class="income">{{ formatRupiah(totalIncome) }}</strong>
      </article>

      <article class="summary-card">
        <span>Total pengeluaran</span>
        <strong class="expense">{{ formatRupiah(totalExpense) }}</strong>
      </article>

      <article class="summary-card">
        <span>Saldo</span>
        <strong>{{ formatRupiah(balance) }}</strong>
      </article>
    </section>

    <p v-if="successMessage" class="success">
      {{ successMessage }}
    </p>

    <p v-if="errorMessage" class="error">
      {{ errorMessage }}
    </p>

    <section class="content-grid">
      <article class="panel">
        <h2>{{ editingId ? 'Ubah Transaksi' : 'Tambah Transaksi' }}</h2>

        <form class="transaction-form" @submit.prevent="submitForm">
          <label for="type">Tipe transaksi</label>
          <select id="type" v-model="form.type" required>
            <option value="income">Pemasukan</option>
            <option value="expense">Pengeluaran</option>
          </select>

          <label for="title">Judul</label>
          <input id="title" v-model="form.title" required maxlength="255" />

          <label for="description">Deskripsi</label>
          <textarea id="description" v-model="form.description" />

          <label for="amount">Jumlah</label>
          <input
            id="amount"
            v-model="form.amount"
            type="number"
            min="1"
            required
          />

          <label for="transaction-date">Tanggal</label>
          <input
            id="transaction-date"
            v-model="form.transaction_date"
            type="date"
            required
          />

          <div class="form-actions">
            <button type="submit" :disabled="saving">
              {{ saving ? 'Menyimpan...' : editingId ? 'Simpan Perubahan' : 'Tambah' }}
            </button>

            <button
              v-if="editingId"
              class="secondary-button"
              type="button"
              @click="resetForm"
            >
              Batal
            </button>
          </div>
        </form>
      </article>

      <article class="panel">
        <div class="panel-heading">
          <h2>Daftar Transaksi</h2>
          <button type="button" @click="loadTransactions">Muat ulang</button>
        </div>

        <p v-if="loading">Memuat transaksi...</p>

        <div v-else-if="transactions.length === 0" class="empty">
          Belum ada transaksi.
        </div>

        <div v-else class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Transaksi</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Aksi</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="transaction in transactions" :key="transaction.id">
                <td>{{ transaction.transaction_date }}</td>
                <td>
                  <strong>{{ transaction.title }}</strong>
                  <small>{{ transaction.description }}</small>
                </td>
                <td>
                  <span
                    :class="[
                      'badge',
                      transaction.type === 'income'
                        ? 'badge-income'
                        : 'badge-expense',
                    ]"
                  >
                    {{
                      transaction.type === 'income'
                        ? 'Pemasukan'
                        : 'Pengeluaran'
                    }}
                  </span>
                </td>
                <td>{{ formatRupiah(transaction.amount) }}</td>
                <td class="actions">
                  <button type="button" @click="startEdit(transaction)">
                    Edit
                  </button>
                  <button
                    class="danger-button"
                    type="button"
                    @click="deleteTransaction(transaction)"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </main>
</template>

<style scoped>
.page {
  min-height: 100vh;
  background: #f8fafc;
  padding: 1.5rem;
  color: #1e293b;
}

.topbar,
.panel-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.topbar {
  max-width: 1200px;
  margin: 0 auto 1.5rem;
}

.topbar h1 {
  margin: 0;
  color: #0f766e;
}

.topbar p {
  margin: 0.25rem 0 0;
  color: #64748b;
}

button {
  border: 0;
  border-radius: 6px;
  background: #0f766e;
  color: white;
  padding: 0.6rem 0.85rem;
  cursor: pointer;
}

.logout-button,
.danger-button {
  background: #b91c1c;
}

.secondary-button {
  background: #64748b;
}

.summary-grid,
.content-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  gap: 1rem;
}

.summary-grid {
  grid-template-columns: repeat(3, 1fr);
  margin-bottom: 1rem;
}

.summary-card,
.panel {
  background: white;
  border-radius: 10px;
  padding: 1rem;
  box-shadow: 0 2px 10px rgb(15 23 42 / 8%);
}

.summary-card {
  display: grid;
  gap: 0.5rem;
}

.summary-card span {
  color: #64748b;
}

.summary-card strong {
  font-size: 1.25rem;
}

.income {
  color: #15803d;
}

.expense {
  color: #b91c1c;
}

.content-grid {
  grid-template-columns: minmax(260px, 340px) 1fr;
}

.transaction-form {
  display: grid;
  gap: 0.5rem;
}

.transaction-form input,
.transaction-form select,
.transaction-form textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 0.65rem;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.transaction-form textarea {
  min-height: 80px;
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
  padding: 0.7rem;
  white-space: nowrap;
}

td small {
  display: block;
  color: #64748b;
  white-space: normal;
}

.actions {
  display: flex;
  gap: 0.4rem;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 999px;
  font-size: 0.8rem;
}

.badge-income {
  color: #166534;
  background: #dcfce7;
}

.badge-expense {
  color: #991b1b;
  background: #fee2e2;
}

.success,
.error {
  max-width: 1200px;
  margin: 0 auto 1rem;
  padding: 0.75rem;
  border-radius: 6px;
}

.success {
  color: #166534;
  background: #dcfce7;
}

.error {
  color: #991b1b;
  background: #fee2e2;
}

.empty {
  padding: 2rem;
  text-align: center;
  color: #64748b;
}

@media (max-width: 800px) {
  .summary-grid,
  .content-grid {
    grid-template-columns: 1fr;
  }

  .page {
    padding: 1rem;
  }
}
</style>
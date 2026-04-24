<template>
  <AppLayout title="Transaksi">
    <Head title="Transaksi" />

    <div class="trx-page">
      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="sum-card">
          <div class="sum-icon purple">📦</div>
          <div class="sum-info">
            <span class="sum-label">Total Transaksi</span>
            <span class="sum-value">{{ summary.totalTransactions }}</span>
          </div>
        </div>
        <div class="sum-card">
          <div class="sum-icon green">💰</div>
          <div class="sum-info">
            <span class="sum-label">Total Pendapatan</span>
            <span class="sum-value">{{ fmt(summary.totalRevenue) }}</span>
          </div>
        </div>
        <div class="sum-card">
          <div class="sum-icon blue">📊</div>
          <div class="sum-info">
            <span class="sum-label">Transaksi Hari Ini</span>
            <span class="sum-value">{{ summary.todayTransactions }}</span>
          </div>
        </div>
        <div class="sum-card">
          <div class="sum-icon amber">🔥</div>
          <div class="sum-info">
            <span class="sum-label">Pendapatan Hari Ini</span>
            <span class="sum-value">{{ fmt(summary.todayRevenue) }}</span>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-bar">
        <div class="search-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="search-icon"><path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" /></svg>
          <input v-model="filterForm.search" type="text" placeholder="Cari no. transaksi..." class="search-input" @input="debouncedFilter" />
        </div>
        <select v-model="filterForm.channel" class="filter-select" @change="applyFilter">
          <option value="">Semua Channel</option>
          <option value="Offline">Offline</option>
          <option value="Shopee">Shopee</option>
        </select>
        <select v-model="filterForm.payment_method" class="filter-select" @change="applyFilter">
          <option value="">Semua Pembayaran</option>
          <option value="Cash">Cash</option>
          <option value="QRIS">QRIS</option>
          <option value="Transfer">Transfer</option>
        </select>
        <input v-model="filterForm.date_from" type="date" class="filter-input" @change="applyFilter" />
        <input v-model="filterForm.date_to" type="date" class="filter-input" @change="applyFilter" />
        <button v-if="hasActiveFilters" class="btn-clear" @click="clearFilters">✕ Reset</button>
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Channel</th>
                <th>Items</th>
                <th class="text-right">Total</th>
                <th>Pembayaran</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="trx in transactions.data" :key="trx.id" class="table-row">
                <td>
                  <span class="trx-number">{{ trx.transaction_number }}</span>
                </td>
                <td>
                  <span class="trx-date">{{ formatDate(trx.date) }}</span>
                </td>
                <td>
                  <span :class="['ch-badge', trx.channel.toLowerCase()]">{{ trx.channel }}</span>
                </td>
                <td>
                  <span class="item-count">{{ trx.items.length }} produk</span>
                </td>
                <td class="text-right">
                  <span class="trx-total">{{ fmt(trx.total) }}</span>
                </td>
                <td>
                  <span :class="['pay-badge', trx.payment_method.toLowerCase()]">{{ trx.payment_method }}</span>
                </td>
                <td class="text-center">
                  <div class="action-buttons">
                    <Link :href="`/transactions/${trx.id}`" class="btn-icon view" title="Lihat Detail">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z" /><path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" /></svg>
                    </Link>
                    <Link :href="`/pos/receipt/${trx.id}`" class="btn-icon print" title="Cetak Struk">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.875 1.5C6.839 1.5 6 2.34 6 3.375v2.99c-.426.053-.851.11-1.274.174-1.454.218-2.476 1.483-2.476 2.917v6.294a3 3 0 003 3h.27l-.155 1.705A1.875 1.875 0 007.232 22.5h9.536a1.875 1.875 0 001.867-2.045l-.155-1.705h.27a3 3 0 003-3V9.456c0-1.434-1.022-2.7-2.476-2.917A48.716 48.716 0 0018 6.366V3.375c0-1.036-.84-1.875-1.875-1.875h-8.25zM16.5 6.205v-2.83A.375.375 0 0016.125 3h-8.25a.375.375 0 00-.375.375v2.83a49.353 49.353 0 019 0zm-6.822 12.17l-.156 1.705a.375.375 0 00.373.41h9.536a.375.375 0 00.374-.41l-.156-1.705H9.678z" clip-rule="evenodd" /></svg>
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="transactions.data.length === 0">
                <td colspan="7" class="empty-state">
                  <div class="empty-content">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40"><path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625z" clip-rule="evenodd" /></svg>
                    <p>Belum ada transaksi</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="transactions.last_page > 1" class="pagination">
          <Link v-for="link in transactions.links" :key="link.label" :href="link.url || '#'" :class="['page-link', { active: link.active, disabled: !link.url }]" v-html="link.label" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  transactions: Object,
  filters: Object,
  summary: Object,
});

const filterForm = ref({
  search: props.filters.search || '',
  channel: props.filters.channel || '',
  payment_method: props.filters.payment_method || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
});

let debounceTimer = null;

const hasActiveFilters = computed(() =>
  Object.values(filterForm.value).some(v => v !== '')
);

function debouncedFilter() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(applyFilter, 400);
}

function applyFilter() {
  const params = {};
  Object.entries(filterForm.value).forEach(([k, v]) => { if (v) params[k] = v; });
  router.get('/transactions', params, { preserveState: true, replace: true });
}

function clearFilters() {
  filterForm.value = { search: '', channel: '', payment_method: '', date_from: '', date_to: '' };
  router.get('/transactions', {}, { preserveState: true, replace: true });
}

function fmt(v) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v);
}

function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<style scoped>
.trx-page { max-width: 1400px; }

/* Summary */
.summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem; }
.sum-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; padding: 1rem; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; }
.sum-card:hover { border-color: var(--c-border-hover); transform: translateY(-1px); }
.sum-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.sum-icon.purple { background: rgba(168,85,247,0.12); }
.sum-icon.green { background: rgba(34,197,94,0.12); }
.sum-icon.blue { background: rgba(59,130,246,0.12); }
.sum-icon.amber { background: rgba(245,158,11,0.12); }
.sum-info { display: flex; flex-direction: column; }
.sum-label { font-size: 0.7rem; color: var(--c-text-dim); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 500; }
.sum-value { font-size: 1.15rem; font-weight: 700; color: var(--c-text); }

/* Filters */
.filters-bar { display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; }
.search-wrapper { flex: 1; min-width: 200px; position: relative; }
.search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--c-text-faint); }
.search-input { width: 100%; background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.55rem 0.75rem 0.55rem 2.2rem; color: var(--c-text); font-size: 0.8rem; outline: none; transition: border-color 0.2s; }
.search-input:focus { border-color: rgba(168,85,247,0.4); }
.search-input::placeholder { color: var(--c-text-faint); }
.filter-select, .filter-input { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.55rem 0.65rem; color: var(--c-text); font-size: 0.8rem; outline: none; cursor: pointer; }
.filter-select:focus, .filter-input:focus { border-color: rgba(168,85,247,0.4); }
.filter-input { min-width: 130px; }
.btn-clear { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 10px; padding: 0.55rem 0.75rem; color: #f87171; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.btn-clear:hover { background: rgba(239,68,68,0.2); }

/* Table */
.table-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--c-surface); padding: 0.7rem 1rem; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); text-align: left; white-space: nowrap; }
.data-table td { padding: 0.75rem 1rem; font-size: 0.85rem; border-top: 1px solid var(--c-border); white-space: nowrap; color: var(--c-text); }
.table-row { transition: background 0.15s; }
.table-row:hover { background: var(--c-surface); }

.trx-number { font-family: 'SF Mono','Fira Code',monospace; font-size: 0.78rem; font-weight: 600; color: var(--c-accent); }
.trx-date { font-size: 0.8rem; color: var(--c-text-muted); }
.trx-total { font-weight: 700; color: var(--c-text); }
.item-count { font-size: 0.8rem; color: var(--c-text-muted); }

.ch-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.72rem; font-weight: 600; }
.ch-badge.offline { background: rgba(34,197,94,0.12); color: #4ade80; }
.ch-badge.shopee { background: rgba(249,115,22,0.12); color: #fb923c; }

.pay-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.72rem; font-weight: 500; background: var(--c-accent-bg); color: var(--c-accent); border: 1px solid var(--c-accent-border); }
.pay-badge.cash { background: rgba(34,197,94,0.08); color: var(--c-success); border-color: rgba(34,197,94,0.15); }
.pay-badge.qris { background: rgba(59,130,246,0.08); color: #60a5fa; border-color: rgba(59,130,246,0.15); }
.pay-badge.transfer { background: rgba(168,85,247,0.08); color: var(--c-accent); border-color: var(--c-accent-border); }

.text-right { text-align: right; }
.text-center { text-align: center; }

.action-buttons { display: flex; gap: 0.3rem; justify-content: center; }
.btn-icon { width: 30px; height: 30px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.btn-icon svg { width: 15px; height: 15px; }
.btn-icon.view { background: rgba(59,130,246,0.1); color: #60a5fa; }
.btn-icon.view:hover { background: rgba(59,130,246,0.2); }
.btn-icon.print { background: rgba(34,197,94,0.1); color: #4ade80; }
.btn-icon.print:hover { background: rgba(34,197,94,0.2); }

.empty-state { text-align: center; padding: 3rem !important; }
.empty-content { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; color: var(--c-text-faint); }

/* Pagination */
.pagination { display: flex; justify-content: center; align-items: center; gap: 0.25rem; padding: 0.75rem; border-top: 1px solid var(--c-border); }
.page-link { padding: 0.35rem 0.65rem; border-radius: 8px; font-size: 0.75rem; color: var(--c-text-muted); text-decoration: none; transition: all 0.2s; }
.page-link:hover:not(.disabled):not(.active) { background: var(--c-surface); color: var(--c-text); }
.page-link.active { background: var(--c-accent-bg); color: var(--c-accent); border: 1px solid var(--c-accent-border); }
.page-link.disabled { opacity: 0.3; cursor: not-allowed; }

@media (max-width: 768px) {
  .summary-grid { grid-template-columns: repeat(2, 1fr); }
  .data-table th:nth-child(4), .data-table td:nth-child(4) { display: none; }
}
</style>

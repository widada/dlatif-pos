<template>
  <AppLayout :title="`Riwayat Stok — ${product.name}`">
    <Head :title="`Riwayat Stok — ${product.name}`" />
    <div class="sm-page">
      <div class="sm-header">
        <Link href="/products" class="back-link">← Kembali ke Produk</Link>
        <h2>{{ product.name }}</h2>
        <div class="sm-stock">Stok: <strong :class="product.stock <= 0 ? 'out' : product.stock <= product.min_stock ? 'low' : 'ok'">{{ product.stock }}</strong></div>
      </div>

      <!-- Filter -->
      <div class="filters-bar">
        <select v-model="selectedType" class="filter-select" @change="applyFilter">
          <option value="">Semua Tipe</option>
          <option value="sale">Penjualan</option>
          <option value="purchase">Pembelian</option>
          <option value="adjustment_in">Adjustment Masuk</option>
          <option value="adjustment_out">Adjustment Keluar</option>
        </select>
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Stok Sebelum</th>
                <th class="text-center">Stok Sesudah</th>
                <th>Referensi</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="movements.data.length === 0"><td colspan="7" class="empty-state">Belum ada riwayat</td></tr>
              <tr v-for="m in movements.data" :key="m.id" class="table-row">
                <td class="text-nowrap">{{ fmtDate(m.created_at) }}</td>
                <td><span :class="['type-badge', m.type]">{{ typeLabel(m.type) }}</span></td>
                <td class="text-center" :class="m.quantity > 0 ? 'text-green' : 'text-red'">{{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}</td>
                <td class="text-center">{{ m.stock_before }}</td>
                <td class="text-center">{{ m.stock_after }}</td>
                <td class="text-muted">{{ m.reference || '—' }}</td>
                <td class="text-muted notes-col">{{ m.notes || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div v-if="movements.links && movements.last_page > 1" class="pagination">
          <Link v-for="link in movements.links" :key="link.label" :href="link.url || '#'" :class="['pg-btn', { active: link.active, disabled: !link.url }]" v-html="link.label" preserve-scroll />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ product: Object, movements: Object, filters: Object });

const selectedType = ref(props.filters?.type || '');

function applyFilter() {
  router.get(`/products/${props.product.id}/stock-movements`, {
    type: selectedType.value || undefined,
  }, { preserveState: true, replace: true });
}

function fmtDate(d) { return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }

function typeLabel(type) {
  const map = { sale: 'Penjualan', purchase: 'Pembelian', adjustment_in: 'Masuk', adjustment_out: 'Keluar' };
  return map[type] || type;
}
</script>

<style scoped>
.sm-page { max-width: 1100px; }
.sm-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
.back-link { color: var(--c-text-muted); font-size: 0.85rem; text-decoration: none; }
.back-link:hover { color: var(--c-accent); }
.sm-header h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--c-text); }
.sm-stock { margin-left: auto; font-size: 0.85rem; color: var(--c-text-muted); }
.sm-stock strong { font-size: 1rem; }
.sm-stock strong.ok { color: #22c55e; }
.sm-stock strong.low { color: #f59e0b; }
.sm-stock strong.out { color: #ef4444; }
.filters-bar { margin-bottom: 0.75rem; }
.filter-select { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.55rem 0.75rem; color: var(--c-text); font-size: 0.82rem; outline: none; min-width: 180px; }
.table-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--c-surface); padding: 0.65rem 0.85rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); text-align: left; white-space: nowrap; }
.data-table td { padding: 0.6rem 0.85rem; font-size: 0.82rem; border-top: 1px solid var(--c-border); color: var(--c-text); }
.table-row:hover { background: var(--c-surface); }
.text-center { text-align: center; }
.text-muted { color: var(--c-text-muted); }
.text-nowrap { white-space: nowrap; }
.text-green { color: #22c55e; font-weight: 600; }
.text-red { color: #ef4444; font-weight: 600; }
.notes-col { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.type-badge { padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.72rem; font-weight: 600; white-space: nowrap; }
.type-badge.sale { background: rgba(59,130,246,0.1); color: #60a5fa; }
.type-badge.purchase { background: rgba(34,197,94,0.1); color: #22c55e; }
.type-badge.adjustment_in { background: rgba(20,184,166,0.1); color: #14b8a6; }
.type-badge.adjustment_out { background: rgba(245,158,11,0.1); color: #f59e0b; }
.empty-state { text-align: center; padding: 3rem !important; color: var(--c-text-faint); }
.pagination { display: flex; justify-content: center; padding: 0.75rem; gap: 0.25rem; border-top: 1px solid var(--c-border); }
.pg-btn { padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem; color: var(--c-text-muted); background: transparent; text-decoration: none; border: 1px solid transparent; }
.pg-btn.active { background: var(--c-accent-bg); color: var(--c-accent); border-color: var(--c-accent-border); }
.pg-btn.disabled { opacity: 0.3; pointer-events: none; }
</style>

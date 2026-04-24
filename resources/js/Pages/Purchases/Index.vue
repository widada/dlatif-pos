<template>
  <AppLayout title="Pembelian Stok">
    <Head title="Pembelian Stok" />
    <div class="pur-page">
      <div class="pur-header">
        <div class="summary-cards">
          <div class="sm-card"><span class="sm-label">Bulan Ini</span><span class="sm-value">{{ fmt(summary.totalThisMonth) }}</span><span class="sm-sub">{{ summary.countThisMonth }} pembelian</span></div>
        </div>
        <Link href="/purchases/create" class="btn-create">+ Buat Pembelian</Link>
      </div>

      <!-- Filters -->
      <div class="filters-bar">
        <div class="search-wrapper">
          <input v-model="search" type="text" placeholder="Cari no. PO / invoice..." class="search-input" @input="debounceSearch" />
        </div>
        <select v-model="supplierId" class="filter-select" @change="applyFilters">
          <option value="">Semua Supplier</option>
          <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
        <input v-model="startDate" type="date" class="date-input" @change="applyFilters" />
        <span class="date-sep">—</span>
        <input v-model="endDate" type="date" class="date-input" @change="applyFilters" />
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>No. PO</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Invoice</th>
                <th class="text-right">Total</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="purchases.data.length === 0"><td colspan="6" class="empty-state">Belum ada pembelian</td></tr>
              <tr v-for="po in purchases.data" :key="po.id" class="table-row">
                <td><span class="po-num">{{ po.purchase_number }}</span></td>
                <td>{{ fmtDate(po.date) }}</td>
                <td>{{ po.supplier?.name || '—' }}</td>
                <td class="text-muted">{{ po.invoice_number || '—' }}</td>
                <td class="text-right fw-600">{{ fmt(po.total) }}</td>
                <td class="text-center">
                  <Link :href="`/purchases/${po.id}`" class="btn-icon view" title="Detail">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z" /><path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" /></svg>
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div v-if="purchases.links && purchases.last_page > 1" class="pagination">
          <Link v-for="link in purchases.links" :key="link.label" :href="link.url || '#'" :class="['pg-btn', { active: link.active, disabled: !link.url }]" v-html="link.label" preserve-scroll />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ purchases: Object, suppliers: Array, summary: Object, filters: Object });

const search = ref(props.filters?.search || '');
const supplierId = ref(props.filters?.supplier_id || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

let timeout;
function debounceSearch() { clearTimeout(timeout); timeout = setTimeout(applyFilters, 300); }

function applyFilters() {
  router.get('/purchases', {
    search: search.value || undefined,
    supplier_id: supplierId.value || undefined,
    start_date: startDate.value || undefined,
    end_date: endDate.value || undefined,
  }, { preserveState: true, replace: true });
}

function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v); }
function fmtDate(d) { return new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' }); }
</script>

<style scoped>
.pur-page { max-width: 1200px; }
.pur-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem; }
.summary-cards { display: flex; gap: 0.75rem; }
.sm-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; padding: 0.75rem 1.25rem; display: flex; flex-direction: column; }
.sm-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); font-weight: 500; }
.sm-value { font-size: 1.15rem; font-weight: 700; color: var(--c-text); }
.sm-sub { font-size: 0.72rem; color: var(--c-text-faint); }
.btn-create { padding: 0.6rem 1.2rem; background: linear-gradient(135deg, #a855f7, #ec4899); color: white; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; }
.filters-bar { display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; }
.search-wrapper { flex: 1; min-width: 200px; }
.search-input { width: 100%; background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.55rem 0.85rem; color: var(--c-text); font-size: 0.85rem; outline: none; }
.search-input::placeholder { color: var(--c-text-faint); }
.filter-select { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.55rem 0.75rem; color: var(--c-text); font-size: 0.82rem; outline: none; min-width: 160px; }
.date-input { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.55rem 0.6rem; color: var(--c-text); font-size: 0.82rem; outline: none; }
.date-sep { color: var(--c-text-faint); }
.table-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--c-surface); padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); text-align: left; white-space: nowrap; }
.data-table td { padding: 0.75rem 1rem; font-size: 0.85rem; border-top: 1px solid var(--c-border); color: var(--c-text); white-space: nowrap; }
.table-row:hover { background: var(--c-surface); }
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-muted { color: var(--c-text-muted); }
.fw-600 { font-weight: 600; }
.po-num { font-weight: 600; color: var(--c-accent); font-family: 'SF Mono','Fira Code',monospace; font-size: 0.82rem; }
.empty-state { text-align: center; padding: 3rem !important; color: var(--c-text-faint); }
.btn-icon { width: 30px; height: 30px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.btn-icon svg { width: 16px; height: 16px; }
.btn-icon.view { background: rgba(59,130,246,0.1); color: #60a5fa; }
.btn-icon.view:hover { background: rgba(59,130,246,0.2); }
.pagination { display: flex; justify-content: center; padding: 0.75rem; gap: 0.25rem; border-top: 1px solid var(--c-border); }
.pg-btn { padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem; color: var(--c-text-muted); background: transparent; text-decoration: none; border: 1px solid transparent; }
.pg-btn.active { background: var(--c-accent-bg); color: var(--c-accent); border-color: var(--c-accent-border); }
.pg-btn.disabled { opacity: 0.3; pointer-events: none; }
</style>

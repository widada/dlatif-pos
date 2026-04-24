<template>
  <AppLayout title="Produk">
    <Head title="Produk" />

    <template #actions>
      <Link href="/products/create" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H5.25a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd" /></svg>
        Tambah Produk
      </Link>
    </template>

    <div class="products-page">
      <!-- Filters -->
      <div class="filters-bar">
        <div class="search-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="search-icon"><path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" /></svg>
          <input
            id="search-input"
            v-model="searchQuery"
            type="text"
            placeholder="Cari produk atau barcode..."
            class="search-input"
            @input="debouncedSearch"
          />
        </div>
        <select
          id="category-filter"
          v-model="selectedCategory"
          class="filter-select"
          @change="applyFilters"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>

      <!-- Products Table -->
      <div class="table-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th class="sortable" @click="toggleSort('name')">Produk <span class="sort-arrow">{{ sortIcon('name') }}</span></th>
                <th class="sortable" @click="toggleSort('category')">Kategori <span class="sort-arrow">{{ sortIcon('category') }}</span></th>
                <th>Barcode</th>
                <th class="text-right sortable" @click="toggleSort('price_offline')">Harga Offline <span class="sort-arrow">{{ sortIcon('price_offline') }}</span></th>
                <th class="text-right">Harga Shopee</th>
                <th class="text-right">HPP</th>
                <th class="text-center sortable" @click="toggleSort('stock')">Stok <span class="sort-arrow">{{ sortIcon('stock') }}</span></th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="products.data.length === 0">
                <td colspan="8" class="empty-state">
                  <div class="empty-content">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="48" height="48"><path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v.75a.75.75 0 01-1.5 0v-.75a1.5 1.5 0 00-3 0v.75a.75.75 0 01-1.5 0v-.75z" clip-rule="evenodd" /></svg>
                    <p>Belum ada produk</p>
                  </div>
                </td>
              </tr>
              <tr v-for="product in products.data" :key="product.id" class="table-row">
                <td>
                  <div class="product-cell">
                    <div class="product-thumb">
                      <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
                      <div v-else class="thumb-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" /></svg>
                      </div>
                    </div>
                    <div class="product-name">{{ product.name }}</div>
                  </div>
                </td>
                <td>
                  <span class="category-badge">{{ product.category }}</span>
                </td>
                <td>
                  <span class="barcode-text">{{ product.barcode || '—' }}</span>
                </td>
                <td class="text-right">{{ formatCurrency(product.price_offline) }}</td>
                <td class="text-right">{{ formatCurrency(product.price_shopee) }}</td>
                <td class="text-right text-muted">{{ formatCurrency(product.cost_price) }}</td>
                <td class="text-center">
                  <span :class="['stock-badge', stockClass(product)]">
                    {{ product.stock }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="action-buttons">
                    <Link :href="`/products/${product.id}/edit`" class="btn-icon edit" title="Edit">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" /><path d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z" /></svg>
                    </Link>
                    <button class="btn-icon delete" title="Hapus" @click="confirmDelete(product)">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="products.links && products.links.length > 3" class="pagination">
          <template v-for="link in products.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="['page-link', { active: link.active }]"
              v-html="link.label"
              preserve-scroll
            />
            <span v-else class="page-link disabled" v-html="link.label" />
          </template>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
          <div class="modal-content">
            <div class="modal-icon danger">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
            </div>
            <h3>Hapus Produk</h3>
            <p>Yakin ingin menghapus <strong>{{ deletingProduct?.name }}</strong>? Aksi ini tidak dapat dibatalkan.</p>
            <div class="modal-actions">
              <button class="btn-secondary" @click="showDeleteModal = false">Batal</button>
              <button class="btn-danger" @click="deleteProduct" :disabled="deleteForm.processing">Hapus</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');
const sortBy = ref(props.filters?.sort_by || 'name');
const sortDir = ref(props.filters?.sort_dir || 'asc');
const showDeleteModal = ref(false);
const deletingProduct = ref(null);

const deleteForm = useForm({});

let searchTimeout;
function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 300);
}

function applyFilters() {
  router.get('/products', {
    search: searchQuery.value || undefined,
    category: selectedCategory.value || undefined,
    sort_by: sortBy.value !== 'name' ? sortBy.value : undefined,
    sort_dir: sortDir.value !== 'asc' ? sortDir.value : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}

function toggleSort(col) {
  if (sortBy.value === col) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = col;
    sortDir.value = col === 'stock' ? 'asc' : 'asc';
  }
  applyFilters();
}

function sortIcon(col) {
  if (sortBy.value !== col) return '⇅';
  return sortDir.value === 'asc' ? '↑' : '↓';
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

function stockClass(product) {
  if (product.stock <= 0) return 'out';
  if (product.stock <= product.min_stock) return 'low';
  return 'ok';
}

function confirmDelete(product) {
  deletingProduct.value = product;
  showDeleteModal.value = true;
}

function deleteProduct() {
  deleteForm.delete(`/products/${deletingProduct.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      deletingProduct.value = null;
    },
  });
}
</script>

<style scoped>
.products-page { max-width: 1400px; }
.filters-bar { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.search-wrapper { flex: 1; min-width: 250px; position: relative; }
.search-icon { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--c-text-faint); }
.search-input { width: 100%; background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.625rem 0.875rem 0.625rem 2.5rem; color: var(--c-text); font-size: 0.875rem; outline: none; transition: all 0.2s; }
.search-input::placeholder { color: var(--c-text-faint); }
.search-input:focus { border-color: rgba(168,85,247,0.4); box-shadow: 0 0 0 3px rgba(168,85,247,0.1); }
.filter-select { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.625rem 0.875rem; color: var(--c-text); font-size: 0.875rem; outline: none; cursor: pointer; min-width: 160px; }
.filter-select:focus { border-color: rgba(168,85,247,0.4); }
.table-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--c-surface); padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--c-text-dim); text-align: left; white-space: nowrap; }
.data-table th.sortable { cursor: pointer; user-select: none; transition: color 0.2s; }
.data-table th.sortable:hover { color: var(--c-accent); }
.sort-arrow { font-size: 0.65rem; margin-left: 0.2rem; opacity: 0.5; }
.data-table th.sortable:hover .sort-arrow { opacity: 1; }
.data-table td { padding: 0.875rem 1rem; font-size: 0.875rem; border-top: 1px solid var(--c-border); white-space: nowrap; color: var(--c-text); }
.table-row { transition: background 0.15s; }
.table-row:hover { background: var(--c-surface); }
.product-cell { display: flex; align-items: center; gap: 0.75rem; }
.product-thumb { width: 40px; height: 40px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: var(--c-surface); }
.product-thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--c-text-faint); }
.product-name { font-weight: 500; color: var(--c-text); }
.category-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; background: rgba(99,102,241,0.12); color: #818cf8; border: 1px solid rgba(99,102,241,0.15); }
.barcode-text { font-family: 'SF Mono','Fira Code',monospace; font-size: 0.8rem; color: var(--c-text-dim); }
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-muted { color: var(--c-text-dim); }
.stock-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; min-width: 36px; }
.stock-badge.ok { background: rgba(34,197,94,0.12); color: var(--c-success); }
.stock-badge.low { background: rgba(245,158,11,0.12); color: var(--c-warning); }
.stock-badge.out { background: rgba(239,68,68,0.12); color: var(--c-danger); }
.action-buttons { display: flex; gap: 0.35rem; justify-content: center; }
.btn-icon { width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.btn-icon svg { width: 16px; height: 16px; }
.btn-icon.edit { background: rgba(59,130,246,0.1); color: #60a5fa; }
.btn-icon.edit:hover { background: rgba(59,130,246,0.2); }
.btn-icon.delete { background: rgba(239,68,68,0.1); color: #f87171; }
.btn-icon.delete:hover { background: rgba(239,68,68,0.2); }
.empty-state { text-align: center; padding: 3rem !important; }
.empty-content { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; color: var(--c-text-faint); }
.pagination { display: flex; justify-content: center; align-items: center; gap: 0.25rem; padding: 1rem; border-top: 1px solid var(--c-border); }
.page-link { padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; color: var(--c-text-muted); text-decoration: none; transition: all 0.2s; }
.page-link:hover:not(.disabled):not(.active) { background: var(--c-surface); color: var(--c-text); }
.page-link.active { background: var(--c-accent-bg); color: var(--c-accent); border: 1px solid var(--c-accent-border); }
.page-link.disabled { opacity: 0.3; cursor: not-allowed; }
.btn-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; background: linear-gradient(135deg,#a855f7,#ec4899); color: white; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.2s; }
.btn-primary:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 4px 20px rgba(168,85,247,0.3); }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 50; }
.modal-content { background: var(--c-card); border: 1px solid var(--c-border-hover); border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; text-align: center; }
.modal-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
.modal-icon.danger { background: rgba(239,68,68,0.12); color: #f87171; }
.modal-content h3 { font-size: 1.1rem; font-weight: 600; color: var(--c-text-white); margin: 0 0 0.5rem; }
.modal-content p { font-size: 0.875rem; color: var(--c-text-muted); margin: 0 0 1.5rem; line-height: 1.5; }
.modal-actions { display: flex; gap: 0.75rem; justify-content: center; }
.btn-secondary { padding: 0.6rem 1.25rem; background: var(--c-surface); border: 1px solid var(--c-border-hover); border-radius: 10px; color: var(--c-text-muted); font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.btn-secondary:hover { background: var(--c-card-hover); color: var(--c-text); }
.btn-danger { padding: 0.6rem 1.25rem; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); border-radius: 10px; color: #f87171; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-danger:hover { background: rgba(239,68,68,0.25); }
.btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }
.modal-enter-active { transition: all 0.2s ease; }
.modal-leave-active { transition: all 0.15s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-content { transform: scale(0.95); }
@media (max-width: 768px) { .data-table th:nth-child(5), .data-table td:nth-child(5), .data-table th:nth-child(6), .data-table td:nth-child(6) { display: none; } }
</style>


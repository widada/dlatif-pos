<template>
  <AppLayout title="Buat Pembelian">
    <Head title="Buat Pembelian" />
    <div class="create-page">
      <form @submit.prevent="submit">
        <!-- Header -->
        <div class="form-section">
          <h3 class="section-label">Informasi Pembelian</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Tanggal <span class="req">*</span></label>
              <input v-model="form.date" type="date" :max="today" :class="{ error: form.errors.date }" />
              <span v-if="form.errors.date" class="error-text">{{ form.errors.date }}</span>
            </div>
            <div class="form-group">
              <label>Supplier <span class="req">*</span></label>
              <select v-model="form.supplier_id" :class="{ error: form.errors.supplier_id }">
                <option value="">Pilih supplier...</option>
                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <span v-if="form.errors.supplier_id" class="error-text">{{ form.errors.supplier_id }}</span>
            </div>
            <div class="form-group">
              <label>No. Invoice</label>
              <input v-model="form.invoice_number" type="text" placeholder="INV-xxx" />
            </div>
          </div>
          <div class="form-group">
            <label>Catatan</label>
            <textarea v-model="form.notes" rows="2" placeholder="Catatan tambahan..."></textarea>
          </div>
        </div>

        <!-- Items -->
        <div class="form-section">
          <h3 class="section-label">Produk</h3>
          <!-- Search product -->
          <div class="product-search">
            <input v-model="productQuery" type="text" placeholder="Cari produk / scan barcode..." class="search-input" @input="filterProducts" @keydown.enter.prevent="addFirstMatch" />
            <div v-if="filteredProducts.length && productQuery" class="product-dropdown">
              <div v-for="p in filteredProducts" :key="p.id" class="pd-item" @click="addProduct(p)">
                <span class="pd-name">{{ p.name }}</span>
                <span class="pd-info">Stok: {{ p.stock }} · HPP: {{ fmt(p.cost_price) }}</span>
              </div>
            </div>
          </div>

          <!-- Items table -->
          <div v-if="form.items.length" class="items-table">
            <div class="it-header">
              <span class="it-col name">Produk</span>
              <span class="it-col qty">Qty</span>
              <span class="it-col price">Harga Beli</span>
              <span class="it-col sub">Subtotal</span>
              <span class="it-col act"></span>
            </div>
            <div v-for="(item, i) in form.items" :key="i" class="it-row">
              <span class="it-col name">{{ item.product_name }}</span>
              <span class="it-col qty">
                <input v-model.number="item.quantity" type="number" min="1" class="num-input" />
              </span>
              <span class="it-col price">
                <div class="input-prefix"><span>Rp</span><input v-model.number="item.cost_price" type="number" min="1" class="num-input" /></div>
              </span>
              <span class="it-col sub fw">{{ fmt(item.quantity * item.cost_price) }}</span>
              <span class="it-col act">
                <button type="button" class="rm-btn" @click="form.items.splice(i, 1)">✕</button>
              </span>
            </div>
            <div class="it-footer">
              <span>Grand Total:</span>
              <span class="grand-total">{{ fmt(grandTotal) }}</span>
            </div>
          </div>
          <p v-else class="empty-items">Belum ada produk. Cari dan tambah produk di atas.</p>
          <span v-if="form.errors.items" class="error-text">{{ form.errors.items }}</span>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <Link href="/purchases" class="btn-cancel">Batal</Link>
          <button type="submit" class="btn-submit" :disabled="form.processing || form.items.length === 0">
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan Pembelian</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ suppliers: Array, products: Array });

const today = new Date().toISOString().split('T')[0];
const productQuery = ref('');
const filteredProducts = ref([]);

const form = useForm({
  supplier_id: '',
  date: today,
  invoice_number: '',
  notes: '',
  items: [],
});

const grandTotal = computed(() => form.items.reduce((s, i) => s + (i.quantity * i.cost_price), 0));

function filterProducts() {
  const q = productQuery.value.toLowerCase();
  if (!q) { filteredProducts.value = []; return; }
  const addedIds = new Set(form.items.map(i => i.product_id));
  filteredProducts.value = props.products
    .filter(p => !addedIds.has(p.id) && (p.name.toLowerCase().includes(q) || (p.barcode && p.barcode.toLowerCase().includes(q))))
    .slice(0, 8);
}

function addProduct(p) {
  const existing = form.items.find(i => i.product_id === p.id);
  if (existing) { existing.quantity++; } else {
    form.items.push({ product_id: p.id, product_name: p.name, quantity: 1, cost_price: Number(p.cost_price) || 0 });
  }
  productQuery.value = '';
  filteredProducts.value = [];
}

function addFirstMatch() {
  if (filteredProducts.value.length) addProduct(filteredProducts.value[0]);
}

function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v); }

function submit() { form.post('/purchases'); }
</script>

<style scoped>
.create-page { max-width: 900px; }
.form-section { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; }
.section-label { margin: 0 0 1rem; font-size: 0.9rem; font-weight: 700; color: var(--c-text); }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group label { font-size: 0.78rem; font-weight: 600; color: var(--c-text-muted); }
.req { color: var(--c-danger); }
.form-group input, .form-group select, .form-group textarea { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.6rem 0.85rem; color: var(--c-text); font-size: 0.85rem; outline: none; font-family: inherit; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: rgba(168,85,247,0.4); }
.form-group input.error, .form-group select.error { border-color: var(--c-danger); }
.error-text { color: var(--c-danger); font-size: 0.75rem; }

/* Product search */
.product-search { position: relative; margin-bottom: 0.75rem; }
.search-input { width: 100%; background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.6rem 0.85rem; color: var(--c-text); font-size: 0.85rem; outline: none; }
.search-input::placeholder { color: var(--c-text-faint); }
.product-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: var(--c-card); border: 1px solid var(--c-border); border-radius: 10px; margin-top: 0.25rem; max-height: 220px; overflow-y: auto; z-index: 50; }
.pd-item { padding: 0.55rem 0.85rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s; }
.pd-item:hover { background: var(--c-surface); }
.pd-name { font-size: 0.85rem; font-weight: 500; color: var(--c-text); }
.pd-info { font-size: 0.72rem; color: var(--c-text-faint); }

/* Items table */
.items-table { border: 1px solid var(--c-border); border-radius: 10px; overflow: hidden; }
.it-header { display: flex; padding: 0.5rem 0.75rem; background: var(--c-surface); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); }
.it-row { display: flex; padding: 0.6rem 0.75rem; border-top: 1px solid var(--c-border); align-items: center; }
.it-col.name { flex: 2; }
.it-col.qty { flex: 0.7; }
.it-col.price { flex: 1.2; }
.it-col.sub { flex: 1; text-align: right; }
.it-col.act { flex: 0.3; text-align: center; }
.it-col.fw { font-weight: 600; }
.num-input { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 6px; padding: 0.3rem 0.5rem; color: var(--c-text); font-size: 0.85rem; width: 70px; outline: none; }
.input-prefix { display: flex; align-items: center; gap: 0.3rem; }
.input-prefix span { font-size: 0.75rem; color: var(--c-text-faint); }
.rm-btn { width: 24px; height: 24px; border-radius: 6px; border: none; background: rgba(239,68,68,0.1); color: #f87171; cursor: pointer; font-size: 0.7rem; }
.it-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 0.75rem; border-top: 1px solid var(--c-border); font-size: 0.9rem; font-weight: 700; color: var(--c-text); }
.grand-total { color: var(--c-accent); }
.empty-items { text-align: center; padding: 2rem; color: var(--c-text-faint); font-size: 0.85rem; }

/* Actions */
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
.btn-cancel { padding: 0.6rem 1.2rem; border: 1px solid var(--c-border); border-radius: 10px; background: var(--c-surface); color: var(--c-text-muted); font-size: 0.85rem; text-decoration: none; }
.btn-submit { padding: 0.6rem 1.5rem; background: linear-gradient(135deg, #a855f7, #ec4899); color: white; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

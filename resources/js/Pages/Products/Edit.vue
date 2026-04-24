<template>
  <AppLayout title="Edit Produk">
    <Head :title="`Edit - ${product.name}`" />

    <div class="form-page">
      <div class="form-header">
        <Link href="/products" class="back-link">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M7.28 7.72a.75.75 0 010 1.06l-2.47 2.47H21a.75.75 0 010 1.5H4.81l2.47 2.47a.75.75 0 11-1.06 1.06l-3.75-3.75a.75.75 0 010-1.06l3.75-3.75a.75.75 0 011.06 0z" clip-rule="evenodd" /></svg>
          Kembali
        </Link>
        <h2>Edit Produk</h2>
        <p>{{ product.name }}</p>
      </div>

      <form @submit.prevent="submit" class="product-form">
        <!-- Basic Info -->
        <div class="form-section">
          <h3 class="section-label">Informasi Dasar</h3>
          <div class="form-grid">
            <div class="form-group full">
              <label for="name">Nama Produk <span class="required">*</span></label>
              <input id="name" v-model="form.name" type="text" :class="{ error: form.errors.name }" />
              <span v-if="form.errors.name" class="error-text">{{ form.errors.name }}</span>
            </div>
            <div class="form-group">
              <label for="category">Kategori <span class="required">*</span></label>
              <select id="category" v-model="form.category" :class="{ error: form.errors.category }">
                <option value="">Pilih Kategori</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
              <span v-if="form.errors.category" class="error-text">{{ form.errors.category }}</span>
            </div>
            <div class="form-group">
              <label for="barcode">Barcode</label>
              <input id="barcode" v-model="form.barcode" type="text" placeholder="Scan atau ketik barcode" :class="{ error: form.errors.barcode }" />
              <span v-if="form.errors.barcode" class="error-text">{{ form.errors.barcode }}</span>
            </div>
          </div>
        </div>

        <!-- Image Upload -->
        <div class="form-section">
          <h3 class="section-label">Gambar Produk</h3>
          <div class="image-upload-area" @click="$refs.imageInput.click()" @dragover.prevent @drop.prevent="onDrop">
            <input ref="imageInput" type="file" accept="image/jpg,image/jpeg,image/png,image/webp" class="hidden-input" @change="onFileChange" />
            <div v-if="imagePreview" class="image-preview">
              <img :src="imagePreview" alt="Preview" />
              <button type="button" class="remove-image" @click.stop="removeImage">&times;</button>
            </div>
            <div v-else class="upload-placeholder">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="36" height="36"><path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" /></svg>
              <p>Klik atau drag gambar ke sini</p>
              <span>JPG, PNG, WebP • Maks 2MB</span>
            </div>
          </div>
          <span v-if="form.errors.image" class="error-text">{{ form.errors.image }}</span>
        </div>
        <div class="form-section">
          <h3 class="section-label">Harga</h3>
          <div class="form-grid three">
            <div class="form-group">
              <label for="cost_price">HPP (Harga Pokok) <span class="required">*</span></label>
              <div class="input-prefix">
                <span>Rp</span>
                <input id="cost_price" v-model.number="form.cost_price" type="number" min="0" :class="{ error: form.errors.cost_price }" />
              </div>
              <span v-if="form.errors.cost_price" class="error-text">{{ form.errors.cost_price }}</span>
            </div>
            <div class="form-group">
              <label for="price_offline">Harga Offline <span class="required">*</span></label>
              <div class="input-prefix">
                <span>Rp</span>
                <input id="price_offline" v-model.number="form.price_offline" type="number" min="0" :class="{ error: form.errors.price_offline }" />
              </div>
              <span v-if="form.errors.price_offline" class="error-text">{{ form.errors.price_offline }}</span>
              <span v-if="marginOffline !== null" class="margin-info">Margin: {{ marginOffline }}%</span>
            </div>
            <div class="form-group">
              <label for="price_shopee">Harga Shopee <span class="required">*</span></label>
              <div class="input-prefix">
                <span>Rp</span>
                <input id="price_shopee" v-model.number="form.price_shopee" type="number" min="0" :class="{ error: form.errors.price_shopee }" />
              </div>
              <span v-if="form.errors.price_shopee" class="error-text">{{ form.errors.price_shopee }}</span>
              <span v-if="marginShopee !== null" class="margin-info">Margin: {{ marginShopee }}%</span>
            </div>
          </div>
        </div>

        <!-- Stock -->
        <div class="form-section">
          <h3 class="section-label">Stok</h3>
          <div class="form-grid">
            <div class="form-group">
              <label for="stock">Stok Saat Ini</label>
              <div class="stock-display">
                <span :class="['stock-badge', product.stock <= 0 ? 'out' : product.stock <= product.min_stock ? 'low' : 'ok']">{{ product.stock }}</span>
                <span class="stock-hint">Gunakan fitur "Adjust Stok" di halaman Produk untuk mengubah stok</span>
              </div>
            </div>
            <div class="form-group">
              <label for="min_stock">Minimum Stok <span class="required">*</span></label>
              <input id="min_stock" v-model.number="form.min_stock" type="number" min="0" :class="{ error: form.errors.min_stock }" />
              <span v-if="form.errors.min_stock" class="error-text">{{ form.errors.min_stock }}</span>
              <span class="help-text">Notifikasi saat stok dibawah angka ini</span>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="form-actions">
          <Link href="/products" class="btn-secondary">Batal</Link>
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Menyimpan...' : 'Perbarui Produk' }}
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

const props = defineProps({
  product: Object,
  categories: Array,
});

const imagePreview = ref(props.product.image_url || null);

const form = useForm({
  name: props.product.name,
  category: props.product.category,
  barcode: props.product.barcode || '',
  image: null,
  price_offline: parseFloat(props.product.price_offline),
  price_shopee: parseFloat(props.product.price_shopee),
  cost_price: parseFloat(props.product.cost_price),
  min_stock: props.product.min_stock,
});

const marginOffline = computed(() => {
  if (form.cost_price > 0 && form.price_offline > 0) {
    return Math.round(((form.price_offline - form.cost_price) / form.cost_price) * 100);
  }
  return null;
});

const marginShopee = computed(() => {
  if (form.cost_price > 0 && form.price_shopee > 0) {
    return Math.round(((form.price_shopee - form.cost_price) / form.cost_price) * 100);
  }
  return null;
});

function onFileChange(e) {
  const file = e.target.files[0];
  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
}

function onDrop(e) {
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
}

function removeImage() {
  form.image = null;
  imagePreview.value = null;
}

function submit() {
  form.post(`/products/${props.product.id}`, {
    forceFormData: true,
    headers: { 'X-HTTP-Method-Override': 'PUT' },
    _method: 'put',
  });
}
</script>

<style scoped>
.form-page {
  max-width: 720px;
  margin: 0 auto;
}

.form-header {
  margin-bottom: 1.5rem;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--c-text-muted);
  text-decoration: none;
  font-size: 0.85rem;
  margin-bottom: 1rem;
  transition: color 0.2s;
}

.back-link:hover {
  color: #c084fc;
}

.form-header h2 {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--c-text-white);
  margin: 0 0 0.3rem;
}

.form-header p {
  font-size: 0.85rem;
  color: var(--c-text-dim);
  margin: 0;
}

.form-section {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.section-label {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--c-text-muted);
  margin: 0 0 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-grid.three {
  grid-template-columns: repeat(3, 1fr);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-group.full {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--c-text-muted);
}

.required {
  color: #f87171;
}

.form-group input,
.form-group select {
  background: var(--c-surface);
  border: 1px solid var(--c-input-border);
  border-radius: 10px;
  padding: 0.625rem 0.875rem;
  color: var(--c-text);
  font-size: 0.875rem;
  outline: none;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  border-color: rgba(168, 85, 247, 0.4);
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
}

.form-group input.error,
.form-group select.error {
  border-color: rgba(239, 68, 68, 0.5);
}

.input-prefix {
  display: flex;
  align-items: center;
  background: var(--c-surface);
  border: 1px solid var(--c-input-border);
  border-radius: 10px;
  overflow: hidden;
  transition: all 0.2s;
}

.input-prefix:focus-within {
  border-color: rgba(168, 85, 247, 0.4);
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
}

.input-prefix span {
  padding: 0 0 0 0.875rem;
  color: var(--c-text-faint);
  font-size: 0.85rem;
  font-weight: 500;
}

.input-prefix input {
  background: transparent;
  border: none;
  border-radius: 0;
  flex: 1;
  padding-left: 0.4rem;
}

.input-prefix input:focus {
  box-shadow: none;
}

.error-text {
  font-size: 0.75rem;
  color: #f87171;
}

.help-text {
  font-size: 0.72rem;
  color: var(--c-text-faint);
}

.margin-info {
  font-size: 0.72rem;
  color: #4ade80;
  font-weight: 500;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 0.5rem;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1.5rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: var(--c-text-white);
  border: none;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(168, 85, 247, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  padding: 0.7rem 1.5rem;
  background: var(--c-surface);
  border: 1px solid var(--c-border-hover);
  border-radius: 10px;
  color: var(--c-text-muted);
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: var(--c-input-border);
  color: var(--c-text-white);
}

@media (max-width: 640px) {
  .form-grid,
  .form-grid.three {
    grid-template-columns: 1fr;
  }
}

/* Image Upload */
.hidden-input {
  display: none;
}

.image-upload-area {
  border: 2px dashed var(--c-border-hover);
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s;
  overflow: hidden;
}

.image-upload-area:hover {
  border-color: rgba(168, 85, 247, 0.3);
  background: rgba(168, 85, 247, 0.03);
}

.upload-placeholder {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: var(--c-text-faint);
}

.upload-placeholder p {
  font-size: 0.875rem;
  color: var(--c-text-muted);
  margin: 0;
}

.upload-placeholder span {
  font-size: 0.72rem;
}

.image-preview {
  position: relative;
  display: flex;
  justify-content: center;
  padding: 1rem;
}

.image-preview img {
  max-height: 200px;
  max-width: 100%;
  object-fit: contain;
  border-radius: 10px;
}

.remove-image {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: none;
  background: rgba(239, 68, 68, 0.8);
  color: var(--c-text-white);
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-image:hover {
  background: #ef4444;
}

.stock-display { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; }
.stock-badge { padding: 0.3rem 0.75rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; }
.stock-badge.ok { background: rgba(34,197,94,0.12); color: #22c55e; }
.stock-badge.low { background: rgba(245,158,11,0.12); color: #f59e0b; }
.stock-badge.out { background: rgba(239,68,68,0.12); color: #ef4444; }
.stock-hint { font-size: 0.72rem; color: var(--c-text-faint); font-style: italic; }
</style>

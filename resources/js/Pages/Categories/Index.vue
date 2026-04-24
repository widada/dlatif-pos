<template>
  <AppLayout title="Kategori">
    <Head title="Kategori" />

    <div class="cat-page">
      <!-- Add Category -->
      <div class="add-bar">
        <form @submit.prevent="addCategory" class="add-form">
          <input v-model="addForm.name" type="text" placeholder="Nama kategori baru..." class="add-input" :class="{ error: addForm.errors.name }" />
          <input v-model="addForm.description" type="text" placeholder="Deskripsi (opsional)" class="add-input desc" />
          <button type="submit" class="btn-add" :disabled="addForm.processing || !addForm.name.trim()">+ Tambah</button>
        </form>
        <span v-if="addForm.errors.name" class="error-text">{{ addForm.errors.name }}</span>
      </div>

      <!-- Categories List -->
      <div class="table-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th class="text-center">Jumlah Produk</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="categories.length === 0">
                <td colspan="4" class="empty-state">
                  <div class="empty-content">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40"><path fill-rule="evenodd" d="M5.25 2.25a3 3 0 00-3 3v4.318a3 3 0 00.879 2.121l9.58 9.581c.92.92 2.39.986 3.39.152l6.13-5.105c1.04-.867 1.12-2.418.18-3.39L12.83 3.349a3.001 3.001 0 00-2.121-.879H5.25zM6.375 7.5a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z" clip-rule="evenodd" /></svg>
                    <p>Belum ada kategori</p>
                  </div>
                </td>
              </tr>
              <tr v-for="cat in categories" :key="cat.id" class="table-row">
                <td>
                  <template v-if="editingId === cat.id">
                    <input v-model="editForm.name" type="text" class="inline-input" @keyup.enter="saveEdit" @keyup.escape="cancelEdit" ref="editInput" />
                  </template>
                  <template v-else>
                    <span class="cat-name">{{ cat.name }}</span>
                  </template>
                </td>
                <td>
                  <template v-if="editingId === cat.id">
                    <input v-model="editForm.description" type="text" class="inline-input" placeholder="Deskripsi..." @keyup.enter="saveEdit" @keyup.escape="cancelEdit" />
                  </template>
                  <template v-else>
                    <span class="cat-desc">{{ cat.description || '—' }}</span>
                  </template>
                </td>
                <td class="text-center">
                  <span class="product-count">{{ cat.products_count }}</span>
                </td>
                <td class="text-center">
                  <div class="action-buttons">
                    <template v-if="editingId === cat.id">
                      <button class="btn-icon save" title="Simpan" @click="saveEdit">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" /></svg>
                      </button>
                      <button class="btn-icon cancel" title="Batal" @click="cancelEdit">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" /></svg>
                      </button>
                    </template>
                    <template v-else>
                      <button class="btn-icon edit" title="Edit" @click="startEdit(cat)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" /><path d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z" /></svg>
                      </button>
                      <button class="btn-icon delete" title="Hapus" @click="confirmDelete(cat)" :disabled="cat.products_count > 0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" /></svg>
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Delete Modal -->
      <Teleport to="body">
        <Transition name="fade">
          <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
            <div class="modal-card">
              <div class="modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
              </div>
              <h3>Hapus Kategori</h3>
              <p>Yakin ingin menghapus <strong>{{ deletingCat?.name }}</strong>?</p>
              <div class="modal-actions">
                <button class="btn-secondary" @click="showDeleteModal = false">Batal</button>
                <button class="btn-danger" @click="deleteCategory" :disabled="deleteForm.processing">Hapus</button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ categories: Array });

const addForm = useForm({ name: '', description: '' });
const editForm = useForm({ name: '', description: '' });
const deleteForm = useForm({});
const editingId = ref(null);
const showDeleteModal = ref(false);
const deletingCat = ref(null);
const editInput = ref(null);

function addCategory() {
  addForm.post('/categories', {
    preserveScroll: true,
    onSuccess: () => addForm.reset(),
  });
}

function startEdit(cat) {
  editingId.value = cat.id;
  editForm.name = cat.name;
  editForm.description = cat.description || '';
  nextTick(() => { if (editInput.value?.[0]) editInput.value[0].focus(); });
}

function cancelEdit() { editingId.value = null; }

function saveEdit() {
  editForm.put(`/categories/${editingId.value}`, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null; },
  });
}

function confirmDelete(cat) {
  deletingCat.value = cat;
  showDeleteModal.value = true;
}

function deleteCategory() {
  deleteForm.delete(`/categories/${deletingCat.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { showDeleteModal.value = false; deletingCat.value = null; },
  });
}
</script>

<style scoped>
.cat-page { max-width: 900px; }

/* Add bar */
.add-bar { margin-bottom: 1rem; }
.add-form { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.add-input { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.6rem 0.85rem; color: var(--c-text); font-size: 0.85rem; outline: none; transition: border-color 0.2s; flex: 1; min-width: 180px; }
.add-input.desc { flex: 1.5; }
.add-input:focus { border-color: rgba(168,85,247,0.4); }
.add-input::placeholder { color: var(--c-text-faint); }
.add-input.error { border-color: var(--c-danger); }
.btn-add { padding: 0.6rem 1.2rem; background: linear-gradient(135deg, #a855f7, #ec4899); color: white; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
.btn-add:hover:not(:disabled) { opacity: 0.9; }
.btn-add:disabled { opacity: 0.5; cursor: not-allowed; }
.error-text { color: var(--c-danger); font-size: 0.75rem; margin-top: 0.25rem; }

/* Table */
.table-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--c-surface); padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); text-align: left; white-space: nowrap; }
.data-table td { padding: 0.75rem 1rem; font-size: 0.85rem; border-top: 1px solid var(--c-border); color: var(--c-text); }
.table-row { transition: background 0.15s; }
.table-row:hover { background: var(--c-surface); }
.text-center { text-align: center; }

.cat-name { font-weight: 600; color: var(--c-text); }
.cat-desc { font-size: 0.82rem; color: var(--c-text-muted); }
.product-count { font-weight: 600; color: var(--c-text-muted); font-size: 0.85rem; }

.inline-input { background: var(--c-input-bg); border: 1px solid rgba(168,85,247,0.3); border-radius: 8px; padding: 0.35rem 0.6rem; color: var(--c-text); font-size: 0.85rem; outline: none; width: 100%; }

/* Actions */
.action-buttons { display: flex; gap: 0.3rem; justify-content: center; }
.btn-icon { width: 30px; height: 30px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-icon svg { width: 15px; height: 15px; }
.btn-icon.edit { background: rgba(59,130,246,0.1); color: #60a5fa; }
.btn-icon.edit:hover { background: rgba(59,130,246,0.2); }
.btn-icon.delete { background: rgba(239,68,68,0.1); color: #f87171; }
.btn-icon.delete:hover { background: rgba(239,68,68,0.2); }
.btn-icon.delete:disabled { opacity: 0.3; cursor: not-allowed; }
.btn-icon.save { background: rgba(34,197,94,0.1); color: #4ade80; }
.btn-icon.save:hover { background: rgba(34,197,94,0.2); }
.btn-icon.cancel { background: rgba(161,161,170,0.1); color: var(--c-text-dim); }
.btn-icon.cancel:hover { background: rgba(161,161,170,0.2); }

.empty-state { text-align: center; padding: 3rem !important; }
.empty-content { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; color: var(--c-text-faint); }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; padding: 1.5rem; max-width: 380px; width: 90%; text-align: center; }
.modal-icon { color: var(--c-warning); margin-bottom: 0.5rem; }
.modal-card h3 { margin: 0 0 0.5rem; font-size: 1rem; color: var(--c-text); }
.modal-card p { margin: 0 0 1rem; font-size: 0.85rem; color: var(--c-text-muted); }
.modal-actions { display: flex; gap: 0.5rem; justify-content: center; }
.btn-secondary { padding: 0.5rem 1rem; border: 1px solid var(--c-border); border-radius: 10px; background: var(--c-surface); color: var(--c-text-muted); font-size: 0.85rem; cursor: pointer; }
.btn-danger { padding: 0.5rem 1rem; border: none; border-radius: 10px; background: #ef4444; color: white; font-size: 0.85rem; font-weight: 600; cursor: pointer; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

<template>
  <AppLayout title="Supplier">
    <Head title="Supplier" />
    <div class="sup-page">
      <!-- Add Form -->
      <div class="add-bar">
        <form @submit.prevent="addSupplier" class="add-form">
          <input v-model="addForm.name" type="text" placeholder="Nama supplier *" class="add-input" :class="{ error: addForm.errors.name }" />
          <input v-model="addForm.phone" type="text" placeholder="No. Telp/WA *" class="add-input sm" :class="{ error: addForm.errors.phone }" />
          <input v-model="addForm.email" type="email" placeholder="Email" class="add-input sm" />
          <input v-model="addForm.address" type="text" placeholder="Alamat" class="add-input" />
          <button type="submit" class="btn-add" :disabled="addForm.processing || !addForm.name.trim() || !addForm.phone.trim()">+ Tambah</button>
        </form>
        <div class="err-row" v-if="Object.keys(addForm.errors).length">
          <span v-for="(e, k) in addForm.errors" :key="k" class="error-text">{{ e }}</span>
        </div>
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Alamat</th>
                <th class="text-center">Pembelian</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="suppliers.length === 0">
                <td colspan="6" class="empty-state"><div class="empty-content"><p>Belum ada supplier</p></div></td>
              </tr>
              <tr v-for="sup in suppliers" :key="sup.id" class="table-row">
                <td>
                  <input v-if="editingId === sup.id" v-model="editForm.name" class="inline-input" @keyup.enter="saveEdit" @keyup.escape="cancelEdit" />
                  <span v-else class="sup-name">{{ sup.name }}</span>
                </td>
                <td>
                  <input v-if="editingId === sup.id" v-model="editForm.phone" class="inline-input sm" @keyup.enter="saveEdit" @keyup.escape="cancelEdit" />
                  <span v-else>{{ sup.phone }}</span>
                </td>
                <td>
                  <input v-if="editingId === sup.id" v-model="editForm.email" class="inline-input" @keyup.enter="saveEdit" @keyup.escape="cancelEdit" />
                  <span v-else class="text-muted">{{ sup.email || '—' }}</span>
                </td>
                <td>
                  <input v-if="editingId === sup.id" v-model="editForm.address" class="inline-input" @keyup.enter="saveEdit" @keyup.escape="cancelEdit" />
                  <span v-else class="text-muted addr">{{ sup.address || '—' }}</span>
                </td>
                <td class="text-center"><span class="count-badge">{{ sup.purchases_count }}</span></td>
                <td class="text-center">
                  <div class="action-buttons">
                    <template v-if="editingId === sup.id">
                      <button class="btn-icon save" @click="saveEdit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" /></svg></button>
                      <button class="btn-icon cancel" @click="cancelEdit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" /></svg></button>
                    </template>
                    <template v-else>
                      <button class="btn-icon edit" @click="startEdit(sup)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" /><path d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z" /></svg></button>
                      <button class="btn-icon delete" @click="confirmDelete(sup)" :disabled="sup.purchases_count > 0"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" /></svg></button>
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
              <h3>Hapus Supplier</h3>
              <p>Yakin hapus <strong>{{ deletingSup?.name }}</strong>?</p>
              <div class="modal-actions">
                <button class="btn-secondary" @click="showDeleteModal = false">Batal</button>
                <button class="btn-danger" @click="deleteSupplier" :disabled="deleteForm.processing">Hapus</button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ suppliers: Array });

const addForm = useForm({ name: '', phone: '', email: '', address: '' });
const editForm = useForm({ name: '', phone: '', email: '', address: '' });
const deleteForm = useForm({});
const editingId = ref(null);
const showDeleteModal = ref(false);
const deletingSup = ref(null);

function addSupplier() { addForm.post('/suppliers', { preserveScroll: true, onSuccess: () => addForm.reset() }); }
function startEdit(sup) { editingId.value = sup.id; editForm.name = sup.name; editForm.phone = sup.phone; editForm.email = sup.email || ''; editForm.address = sup.address || ''; }
function cancelEdit() { editingId.value = null; }
function saveEdit() { editForm.put(`/suppliers/${editingId.value}`, { preserveScroll: true, onSuccess: () => { editingId.value = null; } }); }
function confirmDelete(sup) { deletingSup.value = sup; showDeleteModal.value = true; }
function deleteSupplier() { deleteForm.delete(`/suppliers/${deletingSup.value.id}`, { preserveScroll: true, onSuccess: () => { showDeleteModal.value = false; } }); }
</script>

<style scoped>
.sup-page { max-width: 1100px; }
.add-bar { margin-bottom: 1rem; }
.add-form { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.add-input { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 10px; padding: 0.6rem 0.85rem; color: var(--c-text); font-size: 0.85rem; outline: none; transition: border-color 0.2s; flex: 1; min-width: 150px; }
.add-input.sm { max-width: 180px; }
.add-input:focus { border-color: rgba(168,85,247,0.4); }
.add-input::placeholder { color: var(--c-text-faint); }
.add-input.error { border-color: var(--c-danger); }
.btn-add { padding: 0.6rem 1.2rem; background: linear-gradient(135deg, #a855f7, #ec4899); color: white; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
.btn-add:disabled { opacity: 0.5; cursor: not-allowed; }
.err-row { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.3rem; }
.error-text { color: var(--c-danger); font-size: 0.75rem; }
.table-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--c-surface); padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); text-align: left; white-space: nowrap; }
.data-table td { padding: 0.75rem 1rem; font-size: 0.85rem; border-top: 1px solid var(--c-border); color: var(--c-text); }
.table-row:hover { background: var(--c-surface); }
.text-center { text-align: center; }
.text-muted { color: var(--c-text-muted); }
.addr { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block; }
.sup-name { font-weight: 600; }
.count-badge { font-weight: 600; color: var(--c-text-muted); }
.inline-input { background: var(--c-input-bg); border: 1px solid rgba(168,85,247,0.3); border-radius: 8px; padding: 0.35rem 0.6rem; color: var(--c-text); font-size: 0.85rem; outline: none; width: 100%; }
.inline-input.sm { max-width: 140px; }
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
.empty-content { color: var(--c-text-faint); }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; padding: 1.5rem; max-width: 380px; width: 90%; text-align: center; }
.modal-card h3 { margin: 0 0 0.5rem; font-size: 1rem; color: var(--c-text); }
.modal-card p { margin: 0 0 1rem; font-size: 0.85rem; color: var(--c-text-muted); }
.modal-actions { display: flex; gap: 0.5rem; justify-content: center; }
.btn-secondary { padding: 0.5rem 1rem; border: 1px solid var(--c-border); border-radius: 10px; background: var(--c-surface); color: var(--c-text-muted); font-size: 0.85rem; cursor: pointer; }
.btn-danger { padding: 0.5rem 1rem; border: none; border-radius: 10px; background: #ef4444; color: white; font-size: 0.85rem; font-weight: 600; cursor: pointer; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

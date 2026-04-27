<template>
<AppLayout title="User Management">
<Head title="User Management" />
<div class="page-header"><div class="header-left"><h1>👤 User Management</h1><span class="subtitle">Kelola admin & kasir</span></div>
<button class="btn-add" @click="showModal=true">+ Tambah User</button></div>

<div v-if="$page.props.flash?.generated_password" class="pw-alert">🔑 Password baru: <code>{{ $page.props.flash.generated_password }}</code> — Salin dan berikan ke user!</div>

<div class="filter-bar">
<input v-model="searchQuery" type="text" placeholder="🔍 Cari nama/username..." class="f-input" @input="debouncedSearch" />
<select v-model="filterRole" class="f-select" @change="applyFilter"><option value="">Semua Role</option><option value="admin">Admin</option><option value="kasir">Kasir</option></select>
<select v-model="filterStatus" class="f-select" @change="applyFilter"><option value="">Semua Status</option><option value="1">Aktif</option><option value="0">Non-aktif</option></select>
</div>

<div class="table-wrap"><table class="data-table">
<thead><tr><th>User</th><th>Username</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
<tbody>
<tr v-for="u in users.data" :key="u.id">
<td><span class="u-name">{{ u.name }}</span><br><span class="u-email">{{ u.email || '—' }}</span></td>
<td class="mono">{{ u.username }}</td>
<td><span :class="['role-badge', u.role]">{{ u.role }}</span></td>
<td><span :class="['status-dot', u.is_active ? 'active' : 'inactive']">{{ u.is_active ? 'Aktif' : 'Non-aktif' }}</span></td>
<td>{{ u.last_login_at ? fmtDate(u.last_login_at) : '—' }}</td>
<td class="acts">
<button class="ab edit" @click="openEdit(u)">Edit</button>
<button class="ab warn" @click="doReset(u)">Reset PW</button>
<button class="ab" :class="u.is_active ? 'del' : 'view'" @click="doToggle(u)">{{ u.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
<button v-if="u.id !== $page.props.auth.user.id" class="ab del" @click="confirmDel(u)">Hapus</button>
</td>
</tr>
<tr v-if="users.data.length===0"><td colspan="6" class="empty">Belum ada user</td></tr>
</tbody>
</table></div>

<Teleport to="body">
<div v-if="showModal" class="mo" @click.self="closeModal"><div class="mc">
<h3>{{ editing ? 'Edit User' : 'Tambah User' }}</h3>
<form @submit.prevent="submitForm">
<div class="fg"><label>Nama *</label><input v-model="form.name" :class="{'input-error':form.errors.name}" /><span v-if="form.errors.name" class="err">{{ form.errors.name }}</span></div>
<div v-if="!editing" class="fg"><label>Username *</label><input v-model="form.username" :class="{'input-error':form.errors.username}" /><span v-if="form.errors.username" class="err">{{ form.errors.username }}</span></div>
<div class="fg"><label>Email</label><input v-model="form.email" type="email" :class="{'input-error':form.errors.email}" /><span v-if="form.errors.email" class="err">{{ form.errors.email }}</span></div>
<div class="fg"><label>No. HP</label><input v-model="form.phone" /><span v-if="form.errors.phone" class="err">{{ form.errors.phone }}</span></div>
<div class="fg"><label>Role *</label><select v-model="form.role" :class="{'input-error':form.errors.role}"><option value="admin">Admin</option><option value="kasir">Kasir</option></select><span v-if="form.errors.role" class="err">{{ form.errors.role }}</span></div>
<div v-if="!editing" class="fg"><label>Password *</label><input v-model="form.password" type="password" placeholder="Min 8 char, huruf besar/kecil + angka" :class="{'input-error':form.errors.password}" /><span v-if="form.errors.password" class="err">{{ form.errors.password }}</span></div>
<div class="ma"><button type="button" class="btn-c" @click="closeModal">Batal</button><button type="submit" class="btn-s" :disabled="form.processing">{{ form.processing?'Menyimpan...':'Simpan' }}</button></div>
</form>
</div></div>
</Teleport>

<Teleport to="body">
<div v-if="showDel" class="mo" @click.self="showDel=false"><div class="mc cc">
<h3>Hapus User?</h3><p>User <strong>{{ delUser?.name }}</strong> akan dihapus.</p>
<div class="ma"><button class="btn-c" @click="showDel=false">Batal</button><button class="btn-d" @click="doDel" :disabled="delForm.processing">Hapus</button></div>
</div></div>
</Teleport>
</AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ users: Object, filters: Object });
const searchQuery = ref(props.filters?.search || '');
const filterRole = ref(props.filters?.role || '');
const filterStatus = ref(props.filters?.status ?? '');
const showModal = ref(false); const showDel = ref(false);
const editing = ref(null); const delUser = ref(null);
const form = useForm({ name:'', username:'', email:'', phone:'', role:'kasir', password:'' });
const delForm = useForm({});
let st = null;
function debouncedSearch() { clearTimeout(st); st = setTimeout(() => applyFilter(), 400); }
function applyFilter() { router.get('/users', { search: searchQuery.value||undefined, role: filterRole.value||undefined, status: filterStatus.value!==''?filterStatus.value:undefined }, { preserveState:true, replace:true }); }
function openEdit(u) { editing.value=u; form.name=u.name; form.email=u.email||''; form.phone=u.phone||''; form.role=u.role; showModal.value=true; }
function closeModal() { showModal.value=false; editing.value=null; form.reset(); form.clearErrors(); }
function submitForm() {
  const opts = { preserveScroll:true, onSuccess:()=>closeModal(), onError:()=>{showModal.value=true;} };
  if(editing.value) { form.put(`/users/${editing.value.id}`, opts); } else { form.post('/users', opts); }
}
function confirmDel(u) { delUser.value=u; showDel.value=true; }
function doDel() { delForm.delete(`/users/${delUser.value.id}`, { onSuccess:()=>{showDel.value=false;delUser.value=null;} }); }
function doReset(u) { if(confirm(`Reset password ${u.name}?`)) router.post(`/users/${u.id}/reset-password`); }
function doToggle(u) { if(confirm(`${u.is_active?'Nonaktifkan':'Aktifkan'} ${u.name}?`)) router.post(`/users/${u.id}/toggle-active`); }
function fmtDate(d) { return d ? new Date(d).toLocaleString('id-ID',{dateStyle:'short',timeStyle:'short'}) : '—'; }
</script>
<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.header-left h1{margin:0;font-size:1.35rem;font-weight:700;color:var(--c-text-white)}.subtitle{font-size:.78rem;color:var(--c-text-dim)}
.btn-add{padding:.55rem 1.2rem;background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s}
.btn-add:hover{opacity:.9;box-shadow:0 4px 20px rgba(168,85,247,.3)}
.pw-alert{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:.7rem 1rem;color:#4ade80;font-size:.85rem;margin-bottom:1rem}
.pw-alert code{background:rgba(34,197,94,.15);padding:.15rem .4rem;border-radius:4px;font-family:'SF Mono','Fira Code',monospace;font-weight:700}
.filter-bar{display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap}
.f-input{flex:1;min-width:200px;background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:10px;padding:.55rem .75rem;color:var(--c-text);font-size:.85rem;outline:none}
.f-input:focus{border-color:rgba(168,85,247,.4)}.f-input::placeholder{color:var(--c-text-faint)}
.f-select{background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:10px;padding:.55rem .75rem;color:var(--c-text);font-size:.82rem;outline:none;min-width:120px}
.table-wrap{background:var(--c-card);border:1px solid var(--c-border);border-radius:14px;overflow-x:auto}
.data-table{width:100%;border-collapse:collapse;font-size:.84rem}
.data-table th{text-align:left;padding:.75rem 1rem;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--c-text-dim);border-bottom:1px solid var(--c-border)}
.data-table td{padding:.7rem 1rem;border-bottom:1px solid var(--c-border);color:var(--c-text-muted)}
.data-table tr:last-child td{border-bottom:none}.data-table tr:hover td{background:rgba(168,85,247,.03)}
.u-name{font-weight:600;color:var(--c-text)}.u-email{font-size:.72rem;color:var(--c-text-faint)}
.mono{font-family:'SF Mono','Fira Code',monospace;font-size:.8rem;color:var(--c-text-dim)}
.role-badge{padding:.15rem .5rem;border-radius:6px;font-size:.72rem;font-weight:600;text-transform:uppercase}
.role-badge.admin{background:rgba(168,85,247,.12);color:#c084fc}.role-badge.kasir{background:rgba(59,130,246,.12);color:#60a5fa}
.status-dot{font-size:.78rem;font-weight:500}.status-dot.active{color:#4ade80}.status-dot.inactive{color:#f87171}
.empty{text-align:center;padding:2rem!important;color:var(--c-text-faint)}
.acts{white-space:nowrap}
.ab{padding:.3rem .6rem;border:1px solid var(--c-border);border-radius:6px;font-size:.72rem;font-weight:500;cursor:pointer;transition:all .15s;background:transparent;display:inline-block;margin-right:.25rem}
.ab.edit{color:#fbbf24;border-color:rgba(251,191,36,.2)}.ab.edit:hover{background:rgba(251,191,36,.1)}
.ab.warn{color:#fb923c;border-color:rgba(249,115,22,.2)}.ab.warn:hover{background:rgba(249,115,22,.1)}
.ab.view{color:#60a5fa;border-color:rgba(96,165,250,.2)}.ab.view:hover{background:rgba(96,165,250,.1)}
.ab.del{color:#f87171;border-color:rgba(248,113,113,.2)}.ab.del:hover{background:rgba(248,113,113,.1)}
.mo{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem}
.mc{background:var(--c-card);border:1px solid var(--c-border);border-radius:16px;padding:1.5rem;width:100%;max-width:480px;animation:mi .2s ease}
@keyframes mi{from{opacity:0;transform:scale(.95) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
.mc h3{margin:0 0 1rem;font-size:1.1rem;font-weight:700;color:var(--c-text-white)}
.cc p{color:var(--c-text-muted);font-size:.875rem;margin:0 0 1.25rem;line-height:1.5}
.fg{margin-bottom:.85rem}.fg label{display:block;font-size:.75rem;font-weight:600;color:var(--c-text-dim);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.35rem}
.fg input,.fg select{width:100%;background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:10px;padding:.55rem .75rem;color:var(--c-text);font-size:.85rem;outline:none;font-family:inherit}
.fg input:focus,.fg select:focus{border-color:rgba(168,85,247,.4)}
.input-error{border-color:#f87171!important;background:rgba(248,113,113,.05)!important}
.err{display:block;color:#f87171;font-size:.72rem;margin-top:.25rem}
.ma{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.25rem}
.btn-c{padding:.5rem 1rem;background:transparent;border:1px solid var(--c-border);border-radius:10px;color:var(--c-text-muted);font-size:.85rem;cursor:pointer;transition:all .15s}.btn-c:hover{border-color:var(--c-border-hover);color:var(--c-text)}
.btn-s{padding:.5rem 1.2rem;background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s}.btn-s:hover{opacity:.9}.btn-s:disabled{opacity:.4;cursor:not-allowed}
.btn-d{padding:.5rem 1.2rem;background:#ef4444;color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s}.btn-d:hover{opacity:.9}.btn-d:disabled{opacity:.4;cursor:not-allowed}
</style>

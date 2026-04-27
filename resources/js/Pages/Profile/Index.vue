<template>
<AppLayout title="Profil"><Head title="Profil" />
<div class="profile-page">
<div class="profile-card">
<h2>👤 Profil Saya</h2>
<form @submit.prevent="updateProfile">
<div class="fg"><label>Nama</label><input v-model="profileForm.name" /></div>
<div class="fg"><label>Username</label><input :value="user.username" disabled class="disabled" /></div>
<div class="fg"><label>Email</label><input v-model="profileForm.email" type="email" /></div>
<div class="fg"><label>No. HP</label><input v-model="profileForm.phone" /></div>
<div class="fg"><label>Role</label><input :value="user.role" disabled class="disabled" /></div>
<div class="fg"><label>Member Sejak</label><input :value="fmtDate(user.created_at)" disabled class="disabled" /></div>
<button type="submit" class="btn-s" :disabled="profileForm.processing">Simpan Profil</button>
</form>
</div>
<div class="profile-card">
<h2>🔒 Ganti Password</h2>
<form @submit.prevent="changePassword">
<div v-if="!mustChangePassword" class="fg"><label>Password Lama</label><input v-model="pwForm.current_password" type="password" :class="{'input-error':pwForm.errors.current_password}" /><span v-if="pwForm.errors.current_password" class="err">{{ pwForm.errors.current_password }}</span></div>
<div class="fg"><label>Password Baru</label><input v-model="pwForm.new_password" type="password" placeholder="Min 8 char, huruf besar/kecil + angka" :class="{'input-error':pwForm.errors.new_password}" /><span v-if="pwForm.errors.new_password" class="err">{{ pwForm.errors.new_password }}</span></div>
<div class="fg"><label>Konfirmasi Password</label><input v-model="pwForm.new_password_confirmation" type="password" /></div>
<button type="submit" class="btn-s" :disabled="pwForm.processing">Ganti Password</button>
</form>
</div>
</div>
</AppLayout>
</template>
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ user: Object, mustChangePassword: Boolean });
const profileForm = useForm({ name: props.user.name, email: props.user.email||'', phone: props.user.phone||'' });
const pwForm = useForm({ current_password:'', new_password:'', new_password_confirmation:'' });
function updateProfile() { profileForm.put('/profile', { preserveScroll:true }); }
function changePassword() { pwForm.post('/profile/change-password', { preserveScroll:true, onSuccess:()=>pwForm.reset() }); }
function fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID',{dateStyle:'long'}) : '—'; }
</script>
<style scoped>
.profile-page{max-width:560px;display:flex;flex-direction:column;gap:1rem}
.profile-card{background:var(--c-card);border:1px solid var(--c-border);border-radius:16px;padding:1.5rem}
.profile-card h2{margin:0 0 1rem;font-size:1.1rem;font-weight:700;color:var(--c-text-white)}
.fg{margin-bottom:.85rem}.fg label{display:block;font-size:.75rem;font-weight:600;color:var(--c-text-dim);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.35rem}
.fg input{width:100%;background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:10px;padding:.55rem .75rem;color:var(--c-text);font-size:.85rem;outline:none;font-family:inherit}
.fg input:focus{border-color:rgba(168,85,247,.4)}.fg input.disabled{opacity:.5;cursor:not-allowed}
.input-error{border-color:#f87171!important}.err{display:block;color:#f87171;font-size:.72rem;margin-top:.25rem}
.btn-s{padding:.55rem 1.2rem;background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s}.btn-s:hover{opacity:.9}.btn-s:disabled{opacity:.4;cursor:not-allowed}
</style>

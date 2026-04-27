<template>
<AppLayout title="Ganti Password"><Head title="Ganti Password" />
<div class="pw-page"><div class="pw-card">
<h2>🔒 Ganti Password</h2>
<p v-if="mustChange" class="warn-msg">⚠️ Anda wajib mengganti password sebelum melanjutkan.</p>
<form @submit.prevent="submit">
<div v-if="!mustChange" class="fg"><label>Password Lama</label><input v-model="form.current_password" type="password" :class="{'input-error':form.errors.current_password}" /><span v-if="form.errors.current_password" class="err">{{ form.errors.current_password }}</span></div>
<div class="fg"><label>Password Baru</label><input v-model="form.new_password" type="password" placeholder="Min 8 char, huruf besar/kecil + angka" :class="{'input-error':form.errors.new_password}" /><span v-if="form.errors.new_password" class="err">{{ form.errors.new_password }}</span></div>
<div class="fg"><label>Konfirmasi Password</label><input v-model="form.new_password_confirmation" type="password" /></div>
<button type="submit" class="btn-s" :disabled="form.processing">Ganti Password</button>
</form>
</div></div>
</AppLayout>
</template>
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ mustChange: Boolean });
const form = useForm({ current_password:'', new_password:'', new_password_confirmation:'' });
function submit() { form.post('/profile/change-password', { preserveScroll:true }); }
</script>
<style scoped>
.pw-page{max-width:460px}
.pw-card{background:var(--c-card);border:1px solid var(--c-border);border-radius:16px;padding:1.5rem}
.pw-card h2{margin:0 0 1rem;font-size:1.1rem;font-weight:700;color:var(--c-text-white)}
.warn-msg{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);border-radius:10px;padding:.6rem .8rem;color:#fbbf24;font-size:.82rem;margin-bottom:1rem}
.fg{margin-bottom:.85rem}.fg label{display:block;font-size:.75rem;font-weight:600;color:var(--c-text-dim);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.35rem}
.fg input{width:100%;background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:10px;padding:.55rem .75rem;color:var(--c-text);font-size:.85rem;outline:none;font-family:inherit}
.fg input:focus{border-color:rgba(168,85,247,.4)}
.input-error{border-color:#f87171!important}.err{display:block;color:#f87171;font-size:.72rem;margin-top:.25rem}
.btn-s{padding:.55rem 1.2rem;background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s}.btn-s:hover{opacity:.9}.btn-s:disabled{opacity:.4;cursor:not-allowed}
</style>

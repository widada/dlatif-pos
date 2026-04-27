<template>
<AppLayout title="Activity Log"><Head title="Activity Log" />
<div class="page-header"><h1>📋 Activity Log</h1><span class="subtitle">Audit trail aktivitas user</span></div>
<div class="filter-bar">
<input v-model="search" type="text" placeholder="🔍 Cari..." class="f-input" @input="debouncedSearch" />
<select v-model="fUser" class="f-select" @change="apply"><option value="">Semua User</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select>
<select v-model="fModule" class="f-select" @change="apply"><option value="">Semua Modul</option><option v-for="m in modules" :key="m" :value="m">{{ m }}</option></select>
<select v-model="fAction" class="f-select" @change="apply"><option value="">Semua Aksi</option><option v-for="a in actions" :key="a" :value="a">{{ a }}</option></select>
</div>
<div class="table-wrap"><table class="data-table">
<thead><tr><th>Waktu</th><th>User</th><th>Modul</th><th>Aksi</th><th>Deskripsi</th><th>IP</th></tr></thead>
<tbody>
<tr v-for="l in logs.data" :key="l.id" @click="showDetail(l)" class="clickable">
<td class="mono">{{ fmtDate(l.created_at) }}</td>
<td><span class="u-name">{{ l.user?.name || '—' }}</span><br><span :class="['role-badge', l.user?.role]">{{ l.user?.role }}</span></td>
<td><span class="mod-badge">{{ l.module }}</span></td>
<td>{{ l.action }}</td>
<td class="desc">{{ l.description || '—' }}</td>
<td class="mono sm">{{ l.ip_address }}</td>
</tr>
<tr v-if="logs.data.length===0"><td colspan="6" class="empty">Belum ada log</td></tr>
</tbody>
</table></div>
<div v-if="logs.last_page>1" class="pagi">
<Link v-for="lk in logs.links" :key="lk.label" :href="lk.url" :class="['pl',{active:lk.active,dis:!lk.url}]" v-html="lk.label" preserve-scroll />
</div>
<Teleport to="body">
<div v-if="detailLog" class="mo" @click.self="detailLog=null"><div class="mc">
<h3>Detail Log</h3>
<div class="dl"><div class="dl-row"><span>User</span><span>{{ detailLog.user?.name }}</span></div>
<div class="dl-row"><span>Modul</span><span>{{ detailLog.module }}</span></div>
<div class="dl-row"><span>Aksi</span><span>{{ detailLog.action }}</span></div>
<div class="dl-row"><span>Deskripsi</span><span>{{ detailLog.description }}</span></div>
<div class="dl-row"><span>IP</span><span>{{ detailLog.ip_address }}</span></div>
<div class="dl-row"><span>Waktu</span><span>{{ fmtDate(detailLog.created_at) }}</span></div>
<div v-if="detailLog.old_values" class="dl-json"><label>Old Values</label><pre>{{ JSON.stringify(detailLog.old_values,null,2) }}</pre></div>
<div v-if="detailLog.new_values" class="dl-json"><label>New Values</label><pre>{{ JSON.stringify(detailLog.new_values,null,2) }}</pre></div>
</div>
<div class="ma"><button class="btn-c" @click="detailLog=null">Tutup</button></div>
</div></div>
</Teleport>
</AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ logs: Object, users: Array, modules: Array, actions: Array, filters: Object });
const search = ref(props.filters?.search||''); const fUser = ref(props.filters?.user_id||'');
const fModule = ref(props.filters?.module||''); const fAction = ref(props.filters?.action||'');
const detailLog = ref(null); let st=null;
function debouncedSearch() { clearTimeout(st); st=setTimeout(()=>apply(),400); }
function apply() { router.get('/activity-logs',{search:search.value||undefined,user_id:fUser.value||undefined,module:fModule.value||undefined,action:fAction.value||undefined},{preserveState:true,replace:true}); }
function showDetail(l) { detailLog.value=l; }
function fmtDate(d) { return d ? new Date(d).toLocaleString('id-ID',{dateStyle:'short',timeStyle:'medium'}) : '—'; }
</script>
<style scoped>
.page-header{margin-bottom:1.25rem}.page-header h1{margin:0;font-size:1.35rem;font-weight:700;color:var(--c-text-white)}.subtitle{font-size:.78rem;color:var(--c-text-dim)}
.filter-bar{display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap}
.f-input{flex:1;min-width:180px;background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:10px;padding:.55rem .75rem;color:var(--c-text);font-size:.85rem;outline:none}
.f-input:focus{border-color:rgba(168,85,247,.4)}.f-input::placeholder{color:var(--c-text-faint)}
.f-select{background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:10px;padding:.55rem .75rem;color:var(--c-text);font-size:.82rem;outline:none;min-width:110px}
.table-wrap{background:var(--c-card);border:1px solid var(--c-border);border-radius:14px;overflow-x:auto}
.data-table{width:100%;border-collapse:collapse;font-size:.82rem}
.data-table th{text-align:left;padding:.7rem .8rem;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--c-text-dim);border-bottom:1px solid var(--c-border)}
.data-table td{padding:.6rem .8rem;border-bottom:1px solid var(--c-border);color:var(--c-text-muted)}
.data-table tr:last-child td{border-bottom:none}.clickable{cursor:pointer}.clickable:hover td{background:rgba(168,85,247,.03)}
.u-name{font-weight:600;color:var(--c-text);font-size:.8rem}
.mono{font-family:'SF Mono','Fira Code',monospace;font-size:.75rem;color:var(--c-text-dim)}.sm{font-size:.68rem}
.role-badge{padding:.1rem .4rem;border-radius:4px;font-size:.65rem;font-weight:600;text-transform:uppercase}
.role-badge.admin{background:rgba(168,85,247,.12);color:#c084fc}.role-badge.kasir{background:rgba(59,130,246,.12);color:#60a5fa}
.mod-badge{background:rgba(20,184,166,.1);color:#2dd4bf;padding:.15rem .4rem;border-radius:5px;font-size:.72rem;font-weight:500}
.desc{max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.empty{text-align:center;padding:2rem!important;color:var(--c-text-faint)}
.pagi{display:flex;gap:.25rem;justify-content:center;margin-top:1rem;flex-wrap:wrap}
.pl{padding:.4rem .7rem;background:var(--c-card);border:1px solid var(--c-border);border-radius:8px;color:var(--c-text-muted);font-size:.78rem;text-decoration:none;transition:all .15s}
.pl:hover:not(.dis){border-color:rgba(168,85,247,.3);color:#c084fc}.pl.active{background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;border-color:transparent}.pl.dis{opacity:.3;pointer-events:none}
.mo{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem}
.mc{background:var(--c-card);border:1px solid var(--c-border);border-radius:16px;padding:1.5rem;width:100%;max-width:560px;max-height:80vh;overflow-y:auto;animation:mi .2s ease}
@keyframes mi{from{opacity:0;transform:scale(.95) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
.mc h3{margin:0 0 1rem;font-size:1.1rem;font-weight:700;color:var(--c-text-white)}
.dl-row{display:flex;justify-content:space-between;padding:.35rem 0;font-size:.82rem;border-bottom:1px solid var(--c-border)}
.dl-row span:first-child{color:var(--c-text-dim);font-weight:500}.dl-row span:last-child{color:var(--c-text);font-weight:600}
.dl-json{margin-top:.75rem}.dl-json label{display:block;font-size:.72rem;font-weight:600;color:var(--c-text-dim);text-transform:uppercase;margin-bottom:.3rem}
.dl-json pre{background:var(--c-surface);border-radius:8px;padding:.6rem;font-size:.72rem;color:var(--c-text-muted);overflow-x:auto;max-height:200px;font-family:'SF Mono','Fira Code',monospace}
.ma{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.25rem}
.btn-c{padding:.5rem 1rem;background:transparent;border:1px solid var(--c-border);border-radius:10px;color:var(--c-text-muted);font-size:.85rem;cursor:pointer;transition:all .15s}.btn-c:hover{border-color:var(--c-border-hover);color:var(--c-text)}
</style>

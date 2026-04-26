<template>
  <AppLayout title="Settings">
    <Head title="Settings" />
    <div class="page-header">
      <div class="header-left"><h1>⚙️ Settings</h1><span class="subtitle">Konfigurasi sistem membership &amp; struk</span></div>
      <div class="header-actions">
        <button class="btn-reset" @click="confirmReset=true">🔄 Reset Default</button>
        <button class="btn-save" @click="saveSettings" :disabled="saving">{{ saving ? 'Menyimpan...' : '💾 Simpan' }}</button>
      </div>
    </div>
    <!-- Tabs -->
    <div class="tabs">
      <button v-for="t in tabs" :key="t.key" :class="['tab',{active:activeTab===t.key}]" @click="activeTab=t.key">{{ t.icon }} {{ t.label }}</button>
    </div>
    <!-- Member System -->
    <div v-if="activeTab==='member'" class="section">
      <h2>👤 Sistem Member</h2>
      <div class="settings-grid">
        <div v-for="s in groupSettings('member')" :key="s.key" class="setting-item">
          <div class="si-info"><span class="si-label">{{ s.label }}</span><span class="si-desc">{{ s.description }}</span></div>
          <div class="si-ctrl">
            <label v-if="s.type==='boolean'" class="toggle"><input type="checkbox" v-model="form[s.key]" /><span class="slider"></span></label>
            <input v-else-if="s.type==='integer'" type="number" v-model.number="form[s.key]" class="num-input" />
            <input v-else type="text" v-model="form[s.key]" class="txt-input" />
          </div>
        </div>
      </div>
    </div>
    <!-- Point -->
    <div v-if="activeTab==='point'" class="section">
      <h2>🎯 Poin Reward</h2>
      <div class="preview-box" v-if="form.point_earn_amount>0">
        <span>Preview: Belanja <strong>{{ fmt(form.point_earn_amount * 5) }}</strong> = <strong>{{ form.point_earn_value * 5 }} poin</strong></span>
        <span> | 1 poin = <strong>{{ fmt(form.point_redeem_value) }}</strong></span>
        <span> | Min redeem: <strong>{{ form.point_min_redeem }} poin</strong></span>
      </div>
      <div class="settings-grid">
        <div v-for="s in groupSettings('point')" :key="s.key" class="setting-item">
          <div class="si-info"><span class="si-label">{{ s.label }}</span><span class="si-desc">{{ s.description }}</span></div>
          <div class="si-ctrl">
            <label v-if="s.type==='boolean'" class="toggle"><input type="checkbox" v-model="form[s.key]" /><span class="slider"></span></label>
            <input v-else-if="s.type==='integer'" type="number" v-model.number="form[s.key]" class="num-input" />
            <input v-else type="text" v-model="form[s.key]" class="txt-input" />
          </div>
        </div>
      </div>
    </div>
    <!-- Discount -->
    <div v-if="activeTab==='discount'" class="section">
      <h2>🏷️ Diskon Member</h2>
      <div class="preview-box" v-if="form.member_discount_enabled">
        <span>Preview: Belanja <strong>{{ fmt(100000) }}</strong> → diskon <strong>{{ fmt(100000 * form.member_discount_percent / 100) }}</strong> ({{ form.member_discount_percent }}%)</span>
      </div>
      <div class="settings-grid">
        <div v-for="s in groupSettings('discount')" :key="s.key" class="setting-item">
          <div class="si-info"><span class="si-label">{{ s.label }}</span><span class="si-desc">{{ s.description }}</span></div>
          <div class="si-ctrl">
            <label v-if="s.type==='boolean'" class="toggle"><input type="checkbox" v-model="form[s.key]" /><span class="slider"></span></label>
            <input v-else-if="s.type==='integer'" type="number" v-model.number="form[s.key]" class="num-input" />
            <input v-else type="text" v-model="form[s.key]" class="txt-input" />
          </div>
        </div>
      </div>
    </div>
    <!-- Birthday -->
    <div v-if="activeTab==='birthday'" class="section">
      <h2>🎂 Birthday Treat</h2>
      <div class="settings-grid">
        <div v-for="s in groupSettings('birthday')" :key="s.key" class="setting-item">
          <div class="si-info"><span class="si-label">{{ s.label }}</span><span class="si-desc">{{ s.description }}</span></div>
          <div class="si-ctrl">
            <label v-if="s.type==='boolean'" class="toggle"><input type="checkbox" v-model="form[s.key]" /><span class="slider"></span></label>
            <input v-else-if="s.type==='integer'" type="number" v-model.number="form[s.key]" class="num-input" />
            <input v-else type="text" v-model="form[s.key]" class="txt-input" />
          </div>
        </div>
      </div>
    </div>
    <!-- Receipt -->
    <div v-if="activeTab==='receipt'" class="section">
      <h2>🧾 Struk / Receipt</h2>
      <div class="settings-grid">
        <div v-for="s in groupSettings('receipt')" :key="s.key" class="setting-item">
          <div class="si-info"><span class="si-label">{{ s.label }}</span><span class="si-desc">{{ s.description }}</span></div>
          <div class="si-ctrl">
            <label v-if="s.type==='boolean'" class="toggle"><input type="checkbox" v-model="form[s.key]" /><span class="slider"></span></label>
            <input v-else type="text" v-model="form[s.key]" class="txt-input" />
          </div>
        </div>
      </div>
    </div>
    <!-- Reset Confirm -->
    <Teleport to="body">
      <div v-if="confirmReset" class="mo" @click.self="confirmReset=false">
        <div class="mc">
          <h3>Reset Settings?</h3>
          <p>Semua pengaturan akan kembali ke nilai default.</p>
          <div class="ma">
            <button class="btn-c" @click="confirmReset=false">Batal</button>
            <button class="btn-d" @click="doReset">Reset</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ settings: Object });
const tabs = [
  { key:'member', label:'Member', icon:'👤' },
  { key:'point', label:'Poin', icon:'🎯' },
  { key:'discount', label:'Diskon', icon:'🏷️' },
  { key:'birthday', label:'Birthday', icon:'🎂' },
  { key:'receipt', label:'Struk', icon:'🧾' },
];
const activeTab = ref('member');
const saving = ref(false);
const confirmReset = ref(false);
// Build form from all settings
const form = reactive({});
for (const [group, items] of Object.entries(props.settings || {})) {
  for (const s of items) { form[s.key] = s.value; }
}
function groupSettings(group) { return props.settings[group] || []; }
function saveSettings() {
  saving.value = true;
  router.put('/settings', { settings: { ...form } }, {
    onFinish: () => { saving.value = false; },
  });
}
function doReset() {
  confirmReset.value = false;
  router.post('/settings/reset');
}
function fmt(v) { return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0,maximumFractionDigits:0}).format(v); }
</script>
<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.header-left h1{margin:0;font-size:1.35rem;font-weight:700;color:var(--c-text-white)}.subtitle{font-size:.78rem;color:var(--c-text-dim)}
.header-actions{display:flex;gap:.5rem}
.btn-reset{padding:.5rem 1rem;background:transparent;border:1px solid var(--c-border);border-radius:10px;color:var(--c-text-muted);font-size:.82rem;cursor:pointer;transition:all .15s}
.btn-reset:hover{border-color:var(--c-border-hover);color:var(--c-text)}
.btn-save{padding:.5rem 1.2rem;background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s}
.btn-save:hover{opacity:.9}.btn-save:disabled{opacity:.4;cursor:not-allowed}
.tabs{display:flex;gap:.25rem;margin-bottom:1.25rem;background:var(--c-surface);border-radius:10px;padding:3px;flex-wrap:wrap;width:fit-content}
.tab{padding:.45rem .8rem;border:none;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;background:transparent;color:var(--c-text-dim);transition:all .2s;white-space:nowrap}
.tab.active{background:linear-gradient(135deg,#a855f7,#ec4899);color:#fff}
.section{animation:fadeIn .2s ease}@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.section h2{font-size:1.05rem;font-weight:700;color:var(--c-text-white);margin:0 0 1rem}
.preview-box{background:rgba(168,85,247,.06);border:1px solid rgba(168,85,247,.15);border-radius:10px;padding:.65rem .9rem;margin-bottom:1rem;font-size:.82rem;color:var(--c-text-muted);display:flex;flex-wrap:wrap;gap:.5rem}
.preview-box strong{color:#c084fc}
.settings-grid{display:flex;flex-direction:column;gap:.5rem}
.setting-item{display:flex;justify-content:space-between;align-items:center;background:var(--c-card);border:1px solid var(--c-border);border-radius:12px;padding:.75rem 1rem;gap:1rem}
.si-info{flex:1}.si-label{display:block;font-size:.85rem;font-weight:600;color:var(--c-text)}.si-desc{display:block;font-size:.72rem;color:var(--c-text-faint);margin-top:.15rem}
.si-ctrl{flex-shrink:0}
.num-input{width:100px;background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:8px;padding:.4rem .6rem;color:var(--c-text);font-size:.85rem;outline:none;text-align:right}
.num-input:focus{border-color:rgba(168,85,247,.4)}
.txt-input{width:220px;background:var(--c-input-bg);border:1px solid var(--c-input-border);border-radius:8px;padding:.4rem .6rem;color:var(--c-text);font-size:.85rem;outline:none}
.txt-input:focus{border-color:rgba(168,85,247,.4)}
/* Toggle switch */
.toggle{position:relative;display:inline-block;width:44px;height:24px;cursor:pointer}
.toggle input{opacity:0;width:0;height:0}
.slider{position:absolute;inset:0;background:var(--c-input-border);border-radius:24px;transition:.3s}
.slider::before{content:'';position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;left:3px;bottom:3px;transition:.3s}
.toggle input:checked+.slider{background:linear-gradient(135deg,#a855f7,#ec4899)}
.toggle input:checked+.slider::before{transform:translateX(20px)}
/* Modals */
.mo{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem}
.mc{background:var(--c-card);border:1px solid var(--c-border);border-radius:16px;padding:1.5rem;width:100%;max-width:400px}
.mc h3{margin:0 0 .75rem;font-size:1.1rem;font-weight:700;color:var(--c-text-white)}.mc p{color:var(--c-text-muted);font-size:.875rem;margin:0 0 1.25rem}
.ma{display:flex;gap:.5rem;justify-content:flex-end}
.btn-c{padding:.5rem 1rem;background:transparent;border:1px solid var(--c-border);border-radius:10px;color:var(--c-text-muted);font-size:.85rem;cursor:pointer}
.btn-d{padding:.5rem 1.2rem;background:#ef4444;color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:600;cursor:pointer}
@media(max-width:768px){.setting-item{flex-direction:column;align-items:stretch;gap:.5rem}.txt-input,.num-input{width:100%}}
</style>

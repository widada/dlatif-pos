<template>
  <AppLayout title="Customers">

    <Head title="Customers" />
    <div class="page-header">
      <div class="header-left">
        <h1>👥 Customers</h1>
        <span class="subtitle">Kelola data member &amp; customer</span>
      </div>
      <button class="btn-add" @click="showModal = true">+ Tambah Customer</button>
    </div>
    <div class="search-bar">
      <input v-model="searchQuery" type="text" placeholder="🔍 Cari nama atau no. HP..." @input="debouncedSearch" />
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>No. HP</th>
            <th class="num">Total Belanja</th>
            <th class="num">Poin</th>
            <th>Terakhir Belanja</th>
            <th>Member Sejak</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in customers.data" :key="c.id">
            <td><span class="cust-name">{{ c.name }}</span> <span v-if="c.birth_date && isBdayNear(c.birth_date)"
                class="bday-badge">🎂</span></td>
            <td class="mono">{{ c.phone }}</td>
            <td class="num">{{ fmt(c.total_spent) }}</td>
            <td class="num"><span class="pts">{{ c.points }} pts</span></td>
            <td>{{ c.last_purchase_at ? fmtDate(c.last_purchase_at) : '—' }}</td>
            <td>{{ fmtDate(c.created_at) }}</td>
            <td class="acts">
              <Link :href="`/customers/${c.id}`" class="ab view">Detail</Link>
              <button class="ab edit" @click="openEdit(c)">Edit</button>
              <button class="ab del" @click="confirmDel(c)">Hapus</button>
            </td>
          </tr>
          <tr v-if="customers.data.length === 0">
            <td colspan="7" class="empty">Belum ada customer</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="customers.last_page > 1" class="pagi">
      <Link v-for="l in customers.links" :key="l.label" :href="l.url" :class="['pl', { active: l.active, dis: !l.url }]"
        v-html="l.label" preserve-scroll />
    </div>
    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="mo" @click.self="closeModal">
        <div class="mc">
          <h3>{{ editing ? 'Edit Customer' : 'Tambah Customer' }}</h3>
          <form @submit.prevent="submitForm">
            <div class="fg"><label>Nama *</label><input v-model="form.name" :class="{ 'input-error': form.errors.name }" /><span v-if="form.errors.name"
                class="err">{{ form.errors.name }}</span></div>
            <div class="fg"><label>No. HP *</label><input v-model="form.phone" placeholder="08xx" :class="{ 'input-error': form.errors.phone }" /><span
                v-if="form.errors.phone" class="err">{{ form.errors.phone }}</span></div>
            <div class="fg"><label>Tanggal Lahir</label><input v-model="form.birth_date" type="date" :class="{ 'input-error': form.errors.birth_date }" /><span v-if="form.errors.birth_date" class="err">{{ form.errors.birth_date }}</span></div>
            <div class="fg"><label>Catatan</label><textarea v-model="form.notes" rows="2"></textarea></div>
            <div class="ma"><button type="button" class="btn-c" @click="closeModal">Batal</button><button type="submit"
                class="btn-s" :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button></div>
          </form>
        </div>
      </div>
    </Teleport>
    <!-- Delete Confirm -->
    <Teleport to="body">
      <div v-if="showDel" class="mo" @click.self="showDel = false">
        <div class="mc cc">
          <h3>Hapus Customer?</h3>
          <p>Customer <strong>{{ delCust?.name }}</strong> akan dihapus.</p>
          <div class="ma"><button class="btn-c" @click="showDel = false">Batal</button><button class="btn-d"
              @click="doDel" :disabled="delForm.processing">Hapus</button></div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ customers: Object, filters: Object });
const searchQuery = ref(props.filters?.search || '');
const showModal = ref(false); const showDel = ref(false);
const editing = ref(null); const delCust = ref(null);
const form = useForm({ name: '', phone: '', birth_date: '', notes: '' });
const delForm = useForm({});
let st = null;
function debouncedSearch() { clearTimeout(st); st = setTimeout(() => { router.get('/customers', { search: searchQuery.value || undefined }, { preserveState: true, replace: true }); }, 400); }
function openEdit(c) { editing.value = c; form.name = c.name; form.phone = c.phone; form.birth_date = c.birth_date ? c.birth_date.split('T')[0] : ''; form.notes = c.notes || ''; showModal.value = true; }
function closeModal() { showModal.value = false; editing.value = null; form.reset(); form.clearErrors(); }
function submitForm() {
  const opts = {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => { showModal.value = true; },
  };
  if (editing.value) {
    form.put(`/customers/${editing.value.id}`, opts);
  } else {
    form.post('/customers', opts);
  }
}
function confirmDel(c) { delCust.value = c; showDel.value = true; }
function doDel() { delForm.delete(`/customers/${delCust.value.id}`, { onSuccess: () => { showDel.value = false; delCust.value = null; } }); }
function isBdayNear(bd) { if (!bd) return false; const d = new Date(bd), t = new Date(); d.setFullYear(t.getFullYear()); return Math.abs((d - t) / 864e5) <= 7; }
function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }); }
</script>
<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  gap: .75rem
}

.header-left h1 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--c-text-white)
}

.subtitle {
  font-size: .78rem;
  color: var(--c-text-dim)
}

.btn-add {
  padding: .55rem 1.2rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: .85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s
}

.btn-add:hover {
  opacity: .9;
  box-shadow: 0 4px 20px rgba(168, 85, 247, .3)
}

.search-bar {
  margin-bottom: 1rem;
  max-width: 400px
}

.search-bar input {
  width: 100%;
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 10px;
  padding: .6rem .8rem;
  color: var(--c-text);
  font-size: .85rem;
  outline: none
}

.search-bar input:focus {
  border-color: rgba(168, 85, 247, .4)
}

.search-bar input::placeholder {
  color: var(--c-text-faint)
}

.table-wrap {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 14px;
  overflow-x: auto
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .84rem
}

.data-table th {
  text-align: left;
  padding: .75rem 1rem;
  font-size: .7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--c-text-dim);
  border-bottom: 1px solid var(--c-border)
}

.data-table td {
  padding: .7rem 1rem;
  border-bottom: 1px solid var(--c-border);
  color: var(--c-text-muted)
}

.data-table tr:last-child td {
  border-bottom: none
}

.data-table tr:hover td {
  background: rgba(168, 85, 247, .03)
}

.num {
  text-align: right
}

.cust-name {
  font-weight: 600;
  color: var(--c-text)
}

.bday-badge {
  font-size: .9rem
}

.mono {
  font-family: 'SF Mono', 'Fira Code', monospace;
  font-size: .8rem;
  color: var(--c-text-dim)
}

.pts {
  background: rgba(168, 85, 247, .12);
  color: #c084fc;
  padding: .15rem .5rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: .78rem
}

.empty {
  text-align: center;
  padding: 2rem !important;
  color: var(--c-text-faint)
}

.acts {
  white-space: nowrap
}

.ab {
  padding: .3rem .6rem;
  border: 1px solid var(--c-border);
  border-radius: 6px;
  font-size: .72rem;
  font-weight: 500;
  cursor: pointer;
  transition: all .15s;
  background: transparent;
  text-decoration: none;
  display: inline-block;
  margin-right: .25rem
}

.ab.view {
  color: #60a5fa;
  border-color: rgba(96, 165, 250, .2)
}

.ab.view:hover {
  background: rgba(96, 165, 250, .1)
}

.ab.edit {
  color: #fbbf24;
  border-color: rgba(251, 191, 36, .2)
}

.ab.edit:hover {
  background: rgba(251, 191, 36, .1)
}

.ab.del {
  color: #f87171;
  border-color: rgba(248, 113, 113, .2)
}

.ab.del:hover {
  background: rgba(248, 113, 113, .1)
}

.pagi {
  display: flex;
  gap: .25rem;
  justify-content: center;
  margin-top: 1rem;
  flex-wrap: wrap
}

.pl {
  padding: .4rem .7rem;
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 8px;
  color: var(--c-text-muted);
  font-size: .78rem;
  text-decoration: none;
  transition: all .15s
}

.pl:hover:not(.dis) {
  border-color: rgba(168, 85, 247, .3);
  color: #c084fc
}

.pl.active {
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: #fff;
  border-color: transparent
}

.pl.dis {
  opacity: .3;
  pointer-events: none
}

.mo {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, .6);
  backdrop-filter: blur(4px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem
}

.mc {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 16px;
  padding: 1.5rem;
  width: 100%;
  max-width: 440px;
  animation: mi .2s ease
}

@keyframes mi {
  from {
    opacity: 0;
    transform: scale(.95) translateY(10px)
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0)
  }
}

.mc h3 {
  margin: 0 0 1rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--c-text-white)
}

.cc p {
  color: var(--c-text-muted);
  font-size: .875rem;
  margin: 0 0 1.25rem;
  line-height: 1.5
}

.fg {
  margin-bottom: .85rem
}

.fg label {
  display: block;
  font-size: .75rem;
  font-weight: 600;
  color: var(--c-text-dim);
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: .35rem
}

.fg input,
.fg textarea {
  width: 100%;
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 10px;
  padding: .55rem .75rem;
  color: var(--c-text);
  font-size: .85rem;
  outline: none;
  font-family: inherit
}

.fg input:focus,
.fg textarea:focus {
  border-color: rgba(168, 85, 247, .4)
}

.err {
  display: block;
  color: #f87171;
  font-size: .72rem;
  margin-top: .25rem
}

.input-error {
  border-color: #f87171 !important;
  background: rgba(248, 113, 113, .05) !important;
}

.ma {
  display: flex;
  gap: .5rem;
  justify-content: flex-end;
  margin-top: 1.25rem
}

.btn-c {
  padding: .5rem 1rem;
  background: transparent;
  border: 1px solid var(--c-border);
  border-radius: 10px;
  color: var(--c-text-muted);
  font-size: .85rem;
  cursor: pointer;
  transition: all .15s
}

.btn-c:hover {
  border-color: var(--c-border-hover);
  color: var(--c-text)
}

.btn-s {
  padding: .5rem 1.2rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: .85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s
}

.btn-s:hover {
  opacity: .9
}

.btn-s:disabled {
  opacity: .4;
  cursor: not-allowed
}

.btn-d {
  padding: .5rem 1.2rem;
  background: #ef4444;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: .85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s
}

.btn-d:hover {
  opacity: .9
}

.btn-d:disabled {
  opacity: .4;
  cursor: not-allowed
}

@media(max-width:768px) {
  .data-table {
    font-size: .78rem
  }

  .data-table th,
  .data-table td {
    padding: .5rem .6rem
  }
}
</style>

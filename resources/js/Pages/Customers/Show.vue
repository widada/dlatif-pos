<template>
  <AppLayout title="Detail Customer">

    <Head :title="`Customer - ${customer.name}`" />
    <div class="page-header">
      <Link href="/customers" class="back-link">← Kembali</Link>
      <h1>{{ customer.name }} <span v-if="isBdayNear" class="bday-tag">🎂 Ulang Tahun!</span></h1>
    </div>
    <!-- Info & Stats -->
    <div class="info-grid">
      <div class="info-card">
        <div class="ic-label">No. HP</div>
        <div class="ic-value mono">{{ maskedPhone }}</div>
      </div>
      <div class="info-card">
        <div class="ic-label">Tanggal Lahir</div>
        <div class="ic-value">{{ customer.birth_date ? fmtDate(customer.birth_date) : 'Belum diisi' }}</div>
      </div>
      <div class="info-card">
        <div class="ic-label">Member Sejak</div>
        <div class="ic-value">{{ fmtDate(customer.created_at) }}</div>
      </div>
    </div>
    <div class="stats-grid">
      <div class="stat-card purple">
        <div class="sc-val">{{ fmt(customer.total_spent) }}</div>
        <div class="sc-lbl">Total Belanja</div>
      </div>
      <div class="stat-card pink">
        <div class="sc-val">{{ customer.total_transactions }}</div>
        <div class="sc-lbl">Total Transaksi</div>
      </div>
      <div class="stat-card gold">
        <div class="sc-val">{{ customer.points }} pts</div>
        <div class="sc-lbl">Saldo Poin</div>
      </div>
    </div>
    <!-- Tabs -->
    <div class="tabs">
      <button :class="['tab', { active: tab === 'trx' }]" @click="tab = 'trx'">Riwayat Transaksi</button>
      <button :class="['tab', { active: tab === 'pts' }]" @click="tab = 'pts'">Riwayat Poin</button>
    </div>
    <!-- Transaction History -->
    <div v-if="tab === 'trx'" class="tab-content">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>No. Transaksi</th>
              <th>Tanggal</th>
              <th>Channel</th>
              <th class="num">Total</th>
              <th class="num">Poin</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in customer.transactions" :key="t.id">
              <td>
                <Link :href="`/transactions/${t.id}`" class="trx-link">{{ t.transaction_number }}</Link>
              </td>
              <td>{{ fmtDateTime(t.date) }}</td>
              <td><span :class="['ch-badge', t.channel.toLowerCase()]">{{ t.channel }}</span></td>
              <td class="num fw">{{ fmt(t.total) }}</td>
              <td class="num"><span v-if="t.points_earned > 0" class="earn">+{{ t.points_earned }}</span></td>
            </tr>
            <tr v-if="!customer.transactions?.length">
              <td colspan="5" class="empty">Belum ada transaksi</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Point History -->
    <div v-if="tab === 'pts'" class="tab-content">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Tipe</th>
              <th class="num">Poin</th>
              <th class="num">Saldo</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in customer.point_logs" :key="p.id">
              <td>{{ fmtDateTime(p.created_at) }}</td>
              <td><span :class="['type-badge', p.type]">{{ typeLabel(p.type) }}</span></td>
              <td class="num"><span :class="p.points > 0 ? 'earn' : 'redm'">{{ p.points > 0 ? '+' : '' }}{{ p.points }}</span></td>
              <td class="num fw">{{ p.balance_after }}</td>
              <td class="note">{{ p.notes || '—' }}</td>
            </tr>
            <tr v-if="!customer.point_logs?.length">
              <td colspan="5" class="empty">Belum ada riwayat poin</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ customer: Object, maskedPhone: String });
const tab = ref('trx');
const isBdayNear = computed(() => {
  if (!props.customer.birth_date) return false;
  const d = new Date(props.customer.birth_date), t = new Date();
  d.setFullYear(t.getFullYear());
  return Math.abs((d - t) / 864e5) <= 7;
});
function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); }
function fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—'; }
function fmtDateTime(d) { return d ? new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—'; }
function typeLabel(t) { return { earn: 'Dapat', redeem: 'Pakai', expired: 'Expired', adjustment: 'Adjustment' }[t] || t; }
</script>
<style scoped>
.page-header {
  margin-bottom: 1.25rem
}

.back-link {
  color: var(--c-text-dim);
  text-decoration: none;
  font-size: .8rem;
  display: inline-block;
  margin-bottom: .5rem;
  transition: color .15s
}

.back-link:hover {
  color: #c084fc
}

.page-header h1 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--c-text-white);
  display: flex;
  align-items: center;
  gap: .5rem
}

.bday-tag {
  font-size: .75rem;
  background: rgba(251, 191, 36, .12);
  color: #fbbf24;
  padding: .2rem .6rem;
  border-radius: 8px;
  font-weight: 600
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: .75rem;
  margin-bottom: 1rem
}

.info-card {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 12px;
  padding: .85rem 1rem
}

.ic-label {
  font-size: .68rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--c-text-dim);
  margin-bottom: .3rem
}

.ic-value {
  font-size: .9rem;
  font-weight: 600;
  color: var(--c-text)
}

.mono {
  font-family: 'SF Mono', 'Fira Code', monospace
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: .75rem;
  margin-bottom: 1.5rem
}

.stat-card {
  border-radius: 14px;
  padding: 1rem 1.1rem;
  border: 1px solid var(--c-border)
}

.stat-card.purple {
  background: linear-gradient(135deg, rgba(168, 85, 247, .08), rgba(168, 85, 247, .02));
  border-color: rgba(168, 85, 247, .15)
}

.stat-card.pink {
  background: linear-gradient(135deg, rgba(236, 72, 153, .08), rgba(236, 72, 153, .02));
  border-color: rgba(236, 72, 153, .15)
}

.stat-card.gold {
  background: linear-gradient(135deg, rgba(251, 191, 36, .08), rgba(251, 191, 36, .02));
  border-color: rgba(251, 191, 36, .15)
}

.sc-val {
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--c-text-white)
}

.sc-lbl {
  font-size: .72rem;
  color: var(--c-text-dim);
  margin-top: .2rem
}

.tabs {
  display: flex;
  gap: .25rem;
  margin-bottom: 1rem;
  background: var(--c-surface);
  border-radius: 10px;
  padding: 3px;
  width: fit-content
}

.tab {
  padding: .45rem .9rem;
  border: none;
  border-radius: 8px;
  font-size: .8rem;
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  color: var(--c-text-dim);
  transition: all .2s
}

.tab.active {
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: #fff
}

.tab-content {
  animation: fadeIn .2s ease
}

@keyframes fadeIn {
  from {
    opacity: 0
  }

  to {
    opacity: 1
  }
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
  padding: .65rem 1rem;
  border-bottom: 1px solid var(--c-border);
  color: var(--c-text-muted)
}

.data-table tr:last-child td {
  border-bottom: none
}

.num {
  text-align: right
}

.fw {
  font-weight: 600;
  color: var(--c-text)
}

.empty {
  text-align: center;
  padding: 2rem !important;
  color: var(--c-text-faint)
}

.trx-link {
  color: #60a5fa;
  text-decoration: none;
  font-weight: 500
}

.trx-link:hover {
  text-decoration: underline
}

.ch-badge {
  padding: .15rem .45rem;
  border-radius: 6px;
  font-size: .72rem;
  font-weight: 600
}

.ch-badge.offline {
  background: rgba(34, 197, 94, .1);
  color: #4ade80
}

.ch-badge.shopee {
  background: rgba(249, 115, 22, .1);
  color: #fb923c
}

.earn {
  color: #4ade80;
  font-weight: 600
}

.redm {
  color: #f87171;
  font-weight: 600
}

.type-badge {
  padding: .15rem .45rem;
  border-radius: 6px;
  font-size: .72rem;
  font-weight: 600
}

.type-badge.earn {
  background: rgba(34, 197, 94, .1);
  color: #4ade80
}

.type-badge.redeem {
  background: rgba(248, 113, 113, .1);
  color: #f87171
}

.type-badge.expired {
  background: rgba(161, 161, 170, .1);
  color: #a1a1aa
}

.type-badge.adjustment {
  background: rgba(96, 165, 250, .1);
  color: #60a5fa
}

.note {
  font-size: .78rem;
  color: var(--c-text-faint);
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap
}

@media(max-width:768px) {

  .info-grid,
  .stats-grid {
    grid-template-columns: 1fr
  }
}
</style>

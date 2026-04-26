<template>
  <AppLayout title="Laporan">
    <Head title="Laporan" />

    <div class="report-page">
      <!-- Period Selector -->
      <div class="period-bar">
        <div class="period-btns">
          <button v-for="p in periods" :key="p.value" :class="['period-btn', { active: currentPeriod === p.value }]" @click="setPeriod(p.value)">{{ p.label }}</button>
        </div>
        <div v-if="currentPeriod === 'custom'" class="custom-dates">
          <input v-model="customStart" type="date" class="date-input" @change="applyCustom" />
          <span class="date-sep">—</span>
          <input v-model="customEnd" type="date" class="date-input" @change="applyCustom" />
        </div>
        <div class="period-label">{{ dateRange.start }} s/d {{ dateRange.end }}</div>
      </div>

      <!-- Cash Flow Banner -->
      <div class="cashflow-card" :class="overview.grossProfit >= 0 ? 'positive' : 'negative-cf'">
        <div class="cf-left">
          <span class="cf-icon">💸</span>
          <div class="cf-info">
            <span class="cf-label">Cash Flow</span>
            <span class="cf-sub">Penjualan − Pembelian</span>
          </div>
        </div>
        <span class="cf-value" :class="{ negative: overview.grossProfit < 0 }">{{ fmt(overview.grossProfit) }}</span>
      </div>

      <!-- Overview Cards -->
      <div class="overview-grid">
        <div class="ov-card">
          <div class="ov-icon green">💰</div>
          <div class="ov-info">
            <span class="ov-label">Total Penjualan</span>
            <span class="ov-value">{{ fmt(overview.totalRevenue) }}</span>
          </div>
        </div>
        <div class="ov-card">
          <div class="ov-icon orange">📦</div>
          <div class="ov-info">
            <span class="ov-label">Total Pembelian</span>
            <span class="ov-value">{{ fmt(overview.totalPurchases) }}</span>
          </div>
        </div>
        <div class="ov-card">
          <div class="ov-icon" :class="overview.netProfit >= 0 ? 'teal' : 'red'">💹</div>
          <div class="ov-info">
            <span class="ov-label">Laba Bersih Penjualan</span>
            <span class="ov-value" :class="{ negative: overview.netProfit < 0 }">{{ fmt(overview.netProfit) }}</span>
            <span class="ov-sub">HPP: {{ fmt(overview.totalCogs) }}</span>
          </div>
        </div>
        <div class="ov-card">
          <div class="ov-icon purple">🧾</div>
          <div class="ov-info">
            <span class="ov-label">Total Transaksi</span>
            <span class="ov-value">{{ overview.totalTransactions }}</span>
          </div>
        </div>
        <div class="ov-card">
          <div class="ov-icon amber">🏷️</div>
          <div class="ov-info">
            <span class="ov-label">Total Diskon</span>
            <span class="ov-value">{{ fmt(overview.totalDiscount) }}</span>
          </div>
        </div>
        <div class="ov-card">
          <div class="ov-icon blue">📊</div>
          <div class="ov-info">
            <span class="ov-label">Rata-rata / Transaksi</span>
            <span class="ov-value">{{ fmt(overview.avgTransaction) }}</span>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="charts-grid">
        <!-- Revenue Chart -->
        <div class="chart-card wide">
          <h3>📈 Pendapatan Harian</h3>
          <div class="bar-chart">
            <div v-for="day in dailyRevenue" :key="day.day" class="bar-col" :title="`${formatDay(day.day)}: ${fmt(day.revenue)} (${day.count} trx)`">
              <div class="bar-value">{{ fmtK(day.revenue) }}</div>
              <div class="bar" :style="{ height: barHeight(day.revenue) + '%' }"></div>
              <div class="bar-label">{{ shortDay(day.day) }}</div>
            </div>
            <div v-if="dailyRevenue.length === 0" class="chart-empty">Belum ada data</div>
          </div>
        </div>

        <!-- By Channel -->
        <div class="chart-card">
          <h3>🏪 Per Channel</h3>
          <div v-if="byChannel.length" class="breakdown-list">
            <div v-for="ch in byChannel" :key="ch.channel" class="bd-item">
              <div class="bd-header">
                <span :class="['ch-dot', ch.channel.toLowerCase()]"></span>
                <span class="bd-name">{{ ch.channel }}</span>
                <span class="bd-count">{{ ch.count }} trx</span>
              </div>
              <div class="bd-bar-wrap">
                <div class="bd-bar" :class="ch.channel.toLowerCase()" :style="{ width: channelPct(ch.revenue) + '%' }"></div>
              </div>
              <div class="bd-value">{{ fmt(ch.revenue) }}</div>
            </div>
          </div>
          <div v-else class="chart-empty">Belum ada data</div>
        </div>

        <!-- By Payment -->
        <div class="chart-card">
          <h3>💳 Per Pembayaran</h3>
          <div v-if="byPayment.length" class="breakdown-list">
            <div v-for="pm in byPayment" :key="pm.payment_method" class="bd-item">
              <div class="bd-header">
                <span :class="['pay-dot', pm.payment_method.toLowerCase()]"></span>
                <span class="bd-name">{{ pm.payment_method }}</span>
                <span class="bd-count">{{ pm.count }} trx</span>
              </div>
              <div class="bd-bar-wrap">
                <div class="bd-bar" :class="pm.payment_method.toLowerCase()" :style="{ width: paymentPct(pm.revenue) + '%' }"></div>
              </div>
              <div class="bd-value">{{ fmt(pm.revenue) }}</div>
            </div>
          </div>
          <div v-else class="chart-empty">Belum ada data</div>
        </div>
      </div>

      <!-- Bottom Row -->
      <div class="bottom-grid">
        <!-- Top Products -->
        <div class="chart-card">
          <h3>🏆 Produk Terlaris</h3>
          <div v-if="topProducts.length" class="top-list">
            <div v-for="(prod, i) in topProducts" :key="prod.product_name" class="top-item">
              <span class="top-rank" :class="{ gold: i===0, silver: i===1, bronze: i===2 }">{{ i + 1 }}</span>
              <div class="top-info">
                <span class="top-name">{{ prod.product_name }}</span>
                <span class="top-qty">{{ prod.total_qty }} terjual</span>
              </div>
              <span class="top-rev">{{ fmt(prod.total_revenue) }}</span>
            </div>
          </div>
          <div v-else class="chart-empty">Belum ada data</div>
        </div>

        <!-- Recent Transactions -->
        <div class="chart-card">
          <h3>🕐 Transaksi Terakhir</h3>
          <div v-if="recentTransactions.length" class="recent-list">
            <div v-for="trx in recentTransactions" :key="trx.id" class="recent-item">
              <div class="recent-info">
                <span class="recent-num">{{ trx.transaction_number }}</span>
                <span class="recent-date">{{ formatDateTime(trx.date) }}</span>
              </div>
              <div class="recent-right">
                <span :class="['ch-badge', trx.channel.toLowerCase()]">{{ trx.channel }}</span>
                <span class="recent-total">{{ fmt(trx.total) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="chart-empty">Belum ada data</div>
        </div>

        <!-- Top Suppliers -->
        <div class="chart-card">
          <h3>🏭 Top Supplier</h3>
          <div v-if="topSuppliers.length" class="top-list">
            <div v-for="(sup, i) in topSuppliers" :key="sup.supplier_id" class="top-item">
              <span class="top-rank" :class="{ gold: i===0, silver: i===1, bronze: i===2 }">{{ i + 1 }}</span>
              <div class="top-info">
                <span class="top-name">{{ sup.supplier?.name || 'Unknown' }}</span>
                <span class="top-qty">{{ sup.count }} pembelian</span>
              </div>
              <span class="top-rev">{{ fmt(sup.total_spend) }}</span>
            </div>
          </div>
          <div v-else class="chart-empty">Belum ada data</div>
        </div>
      </div>

      <!-- Member Report -->
      <div class="member-report" v-if="memberReport">
        <h2>👥 Member & Poin</h2>
        <div class="member-stats">
          <div class="ms-card"><div class="ms-val">{{ memberReport.newMembers }}</div><div class="ms-lbl">Member Baru</div></div>
          <div class="ms-card"><div class="ms-val">{{ memberReport.totalActiveMembers }}</div><div class="ms-lbl">Member Aktif</div></div>
          <div class="ms-card"><div class="ms-val">{{ memberReport.pointsEarned }}</div><div class="ms-lbl">Poin Diberikan</div></div>
          <div class="ms-card"><div class="ms-val">{{ memberReport.pointsRedeemed }}</div><div class="ms-lbl">Poin Dipakai</div></div>
          <div class="ms-card"><div class="ms-val">{{ memberReport.outstandingPoints }}</div><div class="ms-lbl">Outstanding Poin</div></div>
          <div class="ms-card"><div class="ms-val">{{ fmt(memberReport.totalMemberDiscount) }}</div><div class="ms-lbl">Total Diskon Member</div></div>
        </div>
        <div class="chart-card" v-if="memberReport.topCustomers?.length">
          <h3>🏆 Top Customer</h3>
          <div class="top-list">
            <div v-for="(c,i) in memberReport.topCustomers" :key="c.id" class="top-item">
              <span class="top-rank" :class="{gold:i===0,silver:i===1,bronze:i===2}">{{ i+1 }}</span>
              <div class="top-info"><span class="top-name">{{ c.name }}</span><span class="top-qty">{{ c.total_transactions }} trx · {{ c.points }} pts</span></div>
              <span class="top-rev">{{ fmt(c.total_spent) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  overview: Object,
  dailyRevenue: Array,
  byChannel: Array,
  byPayment: Array,
  topProducts: Array,
  topSuppliers: Array,
  recentTransactions: Array,
  memberReport: Object,
  period: String,
  dateRange: Object,
});

const currentPeriod = ref(props.period);
const customStart = ref(props.dateRange.start);
const customEnd = ref(props.dateRange.end);

const periods = [
  { value: '7d', label: '7 Hari' },
  { value: '30d', label: '30 Hari' },
  { value: 'this_month', label: 'Bulan Ini' },
  { value: 'last_month', label: 'Bulan Lalu' },
  { value: 'custom', label: 'Custom' },
];

function setPeriod(p) {
  currentPeriod.value = p;
  const params = { period: p };
  if (p === 'custom') {
    params.start_date = customStart.value;
    params.end_date = customEnd.value;
  }
  router.get('/reports', params, { preserveState: true, replace: true });
}

function applyCustom() {
  if (customStart.value && customEnd.value) {
    router.get('/reports', { period: 'custom', start_date: customStart.value, end_date: customEnd.value }, { preserveState: true, replace: true });
  }
}

function fmt(v) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v);
}

function fmtK(v) {
  if (v >= 1000000) return (v / 1000000).toFixed(1) + 'jt';
  if (v >= 1000) return (v / 1000).toFixed(0) + 'rb';
  return v;
}

function formatDay(d) {
  return new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' });
}

function shortDay(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

function formatDateTime(d) {
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
}

const maxRevenue = computed(() => Math.max(...props.dailyRevenue.map(d => Number(d.revenue)), 1));

function barHeight(revenue) {
  return Math.max((Number(revenue) / maxRevenue.value) * 100, 4);
}

const totalChannelRevenue = computed(() => props.byChannel.reduce((s, c) => s + Number(c.revenue), 0) || 1);
const totalPaymentRevenue = computed(() => props.byPayment.reduce((s, p) => s + Number(p.revenue), 0) || 1);

function channelPct(rev) { return (Number(rev) / totalChannelRevenue.value * 100).toFixed(1); }
function paymentPct(rev) { return (Number(rev) / totalPaymentRevenue.value * 100).toFixed(1); }
</script>

<style scoped>
.report-page { max-width: 1400px; }

/* Period */
.period-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.period-btns { display: flex; gap: 0.25rem; background: var(--c-surface); border-radius: 10px; padding: 3px; }
.period-btn { padding: 0.4rem 0.75rem; border: none; border-radius: 8px; font-size: 0.78rem; font-weight: 500; cursor: pointer; background: transparent; color: var(--c-text-dim); transition: all 0.2s; }
.period-btn.active { background: var(--c-accent-bg); color: var(--c-accent); border: 1px solid var(--c-accent-border); }
.period-btn:hover:not(.active) { color: var(--c-text); }
.custom-dates { display: flex; align-items: center; gap: 0.4rem; }
.date-input { background: var(--c-input-bg); border: 1px solid var(--c-input-border); border-radius: 8px; padding: 0.35rem 0.5rem; color: var(--c-text); font-size: 0.78rem; outline: none; }
.date-sep { color: var(--c-text-faint); font-size: 0.8rem; }
.period-label { margin-left: auto; font-size: 0.75rem; color: var(--c-text-faint); }

/* Cash Flow Banner */
.cashflow-card { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-radius: 14px; margin-bottom: 0.75rem; transition: all 0.2s; }
.cashflow-card.positive { background: linear-gradient(135deg, rgba(34,197,94,0.08), rgba(20,184,166,0.08)); border: 1px solid rgba(34,197,94,0.2); }
.cashflow-card.negative-cf { background: linear-gradient(135deg, rgba(239,68,68,0.08), rgba(245,158,11,0.08)); border: 1px solid rgba(239,68,68,0.2); }
.cf-left { display: flex; align-items: center; gap: 0.75rem; }
.cf-icon { font-size: 1.5rem; }
.cf-info { display: flex; flex-direction: column; }
.cf-label { font-size: 0.85rem; font-weight: 700; color: var(--c-text); }
.cf-sub { font-size: 0.7rem; color: var(--c-text-faint); }
.cf-value { font-size: 1.4rem; font-weight: 800; color: var(--c-text); }
.cf-value.negative { color: #ef4444; }

/* Overview */
.overview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
.ov-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; padding: 1rem; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; }
.ov-card:hover { border-color: var(--c-border-hover); transform: translateY(-1px); }
.ov-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
.ov-icon.green { background: rgba(34,197,94,0.12); }
.ov-icon.orange { background: rgba(249,115,22,0.12); }
.ov-icon.teal { background: rgba(20,184,166,0.12); }
.ov-icon.red { background: rgba(239,68,68,0.12); }
.ov-icon.purple { background: rgba(168,85,247,0.12); }
.ov-icon.blue { background: rgba(59,130,246,0.12); }
.ov-icon.amber { background: rgba(245,158,11,0.12); }
.ov-info { display: flex; flex-direction: column; min-width: 0; }
.ov-label { font-size: 0.7rem; color: var(--c-text-dim); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 500; }
.ov-value { font-size: 1.1rem; font-weight: 700; color: var(--c-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ov-value.negative { color: #ef4444; }
.ov-sub { font-size: 0.68rem; color: var(--c-text-faint); }

/* Charts */
.charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; }
.chart-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; padding: 1rem 1.25rem; }
.chart-card.wide { grid-column: 1 / -1; }
.chart-card h3 { margin: 0 0 0.75rem; font-size: 0.85rem; font-weight: 600; color: var(--c-text); }
.chart-empty { text-align: center; padding: 2rem; color: var(--c-text-faint); font-size: 0.85rem; }

/* Bar Chart */
.bar-chart { display: flex; align-items: flex-end; gap: 2px; height: 180px; padding-top: 1rem; }
.bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.2rem; height: 100%; justify-content: flex-end; min-width: 0; }
.bar { width: 100%; max-width: 40px; background: linear-gradient(180deg, #a855f7, #7c3aed); border-radius: 4px 4px 0 0; transition: height 0.4s ease; min-height: 3px; }
.bar-value { font-size: 0.6rem; color: var(--c-text-faint); white-space: nowrap; }
.bar-label { font-size: 0.6rem; color: var(--c-text-dim); white-space: nowrap; margin-top: 0.25rem; }

/* Breakdown */
.breakdown-list { display: flex; flex-direction: column; gap: 0.75rem; }
.bd-item {}
.bd-header { display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem; }
.ch-dot, .pay-dot { width: 8px; height: 8px; border-radius: 50%; }
.ch-dot.offline { background: #4ade80; }
.ch-dot.shopee { background: #fb923c; }
.pay-dot.cash { background: #4ade80; }
.pay-dot.qris { background: #60a5fa; }
.pay-dot.transfer { background: #c084fc; }
.bd-name { font-size: 0.8rem; font-weight: 600; color: var(--c-text); }
.bd-count { margin-left: auto; font-size: 0.7rem; color: var(--c-text-faint); }
.bd-bar-wrap { height: 6px; background: var(--c-surface); border-radius: 3px; overflow: hidden; margin-bottom: 0.2rem; }
.bd-bar { height: 100%; border-radius: 3px; transition: width 0.4s ease; }
.bd-bar.offline { background: #4ade80; }
.bd-bar.shopee { background: #fb923c; }
.bd-bar.cash { background: #4ade80; }
.bd-bar.qris { background: #60a5fa; }
.bd-bar.transfer { background: #c084fc; }
.bd-value { font-size: 0.85rem; font-weight: 600; color: var(--c-text); }

/* Bottom Grid */
.bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

/* Top Products */
.top-list { display: flex; flex-direction: column; gap: 0.5rem; }
.top-item { display: flex; align-items: center; gap: 0.6rem; padding: 0.4rem 0; }
.top-rank { width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; background: var(--c-surface); color: var(--c-text-dim); flex-shrink: 0; }
.top-rank.gold { background: rgba(245,158,11,0.15); color: #fbbf24; }
.top-rank.silver { background: rgba(148,163,184,0.15); color: #94a3b8; }
.top-rank.bronze { background: rgba(180,83,9,0.15); color: #d97706; }
.top-info { flex: 1; min-width: 0; }
.top-name { display: block; font-size: 0.82rem; font-weight: 500; color: var(--c-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.top-qty { font-size: 0.7rem; color: var(--c-text-faint); }
.top-rev { font-size: 0.82rem; font-weight: 600; color: var(--c-text); white-space: nowrap; }

/* Recent */
.recent-list { display: flex; flex-direction: column; gap: 0.5rem; }
.recent-item { display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px solid var(--c-border); }
.recent-item:last-child { border-bottom: none; }
.recent-info { display: flex; flex-direction: column; }
.recent-num { font-size: 0.78rem; font-weight: 600; color: var(--c-accent); font-family: 'SF Mono','Fira Code',monospace; }
.recent-date { font-size: 0.7rem; color: var(--c-text-faint); }
.recent-right { display: flex; align-items: center; gap: 0.6rem; }
.ch-badge { padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.65rem; font-weight: 600; }
.ch-badge.offline { background: rgba(34,197,94,0.12); color: #4ade80; }
.ch-badge.shopee { background: rgba(249,115,22,0.12); color: #fb923c; }
.recent-total { font-size: 0.85rem; font-weight: 700; color: var(--c-text); }

/* Member Report */
.member-report { margin-top: 1rem; }
.member-report h2 { font-size: 1.05rem; font-weight: 700; color: var(--c-text-white); margin: 0 0 .75rem; }
.member-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; margin-bottom: .75rem; }
.ms-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 12px; padding: .75rem .9rem; text-align: center; }
.ms-val { font-size: 1.15rem; font-weight: 800; color: var(--c-text-white); }
.ms-lbl { font-size: .68rem; color: var(--c-text-dim); margin-top: .15rem; text-transform: uppercase; letter-spacing: .03em; }

@media (max-width: 768px) {
  .charts-grid, .bottom-grid { grid-template-columns: 1fr; }
  .chart-card.wide { grid-column: 1; }
  .overview-grid, .member-stats { grid-template-columns: repeat(2, 1fr); }
  .period-btns { flex-wrap: wrap; }
}
</style>

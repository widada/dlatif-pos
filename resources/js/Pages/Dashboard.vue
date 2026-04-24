<template>
  <AppLayout title="Dashboard">
    <Head title="Dashboard" />

    <div class="dashboard">
      <!-- Greeting -->
      <div class="greeting">
        <h2>{{ greetingText }}, {{ userName.split(' ')[0] }} 👋</h2>
        <p class="greeting-date">{{ todayFormatted }}</p>
      </div>

      <!-- Today Stats -->
      <div class="stats-grid">
        <div class="stat-card highlight">
          <div class="stat-icon green">💰</div>
          <div class="stat-content">
            <span class="stat-label">Penjualan Hari Ini</span>
            <span class="stat-value">{{ fmt(stats.salesToday) }}</span>
            <span class="stat-compare" :class="compareClass">{{ compareText }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple">🧾</div>
          <div class="stat-content">
            <span class="stat-label">Transaksi Hari Ini</span>
            <span class="stat-value">{{ stats.trxCountToday }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" :class="stats.profitToday >= 0 ? 'teal' : 'red'">💹</div>
          <div class="stat-content">
            <span class="stat-label">Laba Hari Ini</span>
            <span class="stat-value" :class="{ negative: stats.profitToday < 0 }">{{ fmt(stats.profitToday) }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue">📦</div>
          <div class="stat-content">
            <span class="stat-label">Total Produk</span>
            <span class="stat-value">{{ stats.totalProducts }}</span>
            <span class="stat-sub">Nilai: {{ fmt(stats.totalStockValue) }}</span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="section">
        <h3 class="section-title">Aksi Cepat</h3>
        <div class="quick-actions">
          <Link href="/pos" class="action-card primary">
            <div class="action-icon pink">🛒</div>
            <span>Kasir (POS)</span>
          </Link>
          <Link href="/purchases/create" class="action-card">
            <div class="action-icon orange">📥</div>
            <span>Buat Pembelian</span>
          </Link>
          <Link href="/products/create" class="action-card">
            <div class="action-icon purple">➕</div>
            <span>Tambah Produk</span>
          </Link>
          <Link href="/reports" class="action-card">
            <div class="action-icon blue">📊</div>
            <span>Lihat Laporan</span>
          </Link>
        </div>
      </div>

      <!-- Bottom Grid -->
      <div class="bottom-grid">
        <!-- Low Stock Alert -->
        <div class="panel">
          <div class="panel-header">
            <h3>⚠️ Stok Rendah</h3>
            <Link href="/products?sort_by=stock&sort_dir=asc" class="panel-link">Lihat Semua</Link>
          </div>
          <div v-if="lowStockProducts.length" class="low-stock-list">
            <div v-for="p in lowStockProducts" :key="p.id" class="ls-item">
              <div class="ls-info">
                <span class="ls-name">{{ p.name }}</span>
                <span class="ls-cat">{{ p.category }}</span>
              </div>
              <div class="ls-stock">
                <span class="ls-current" :class="p.stock <= 0 ? 'out' : 'low'">{{ p.stock }}</span>
                <span class="ls-min">/ {{ p.min_stock }}</span>
              </div>
            </div>
          </div>
          <div v-else class="panel-empty">
            <span>✅</span>
            <p>Semua stok aman!</p>
          </div>
        </div>

        <!-- Activity Feed -->
        <div class="panel">
          <div class="panel-header">
            <h3>📋 Aktivitas Terbaru</h3>
          </div>
          <div v-if="activityFeed.length" class="activity-list">
            <div v-for="a in activityFeed" :key="a.id" class="act-item">
              <span :class="['act-dot', a.type]"></span>
              <div class="act-info">
                <span class="act-text">
                  <strong>{{ a.product?.name || 'Produk' }}</strong>
                  <span :class="['act-badge', a.type]">{{ typeLabel(a.type) }}</span>
                  <span :class="a.quantity > 0 ? 'act-plus' : 'act-minus'">{{ a.quantity > 0 ? '+' : '' }}{{ a.quantity }}</span>
                </span>
                <span class="act-meta">{{ a.reference || '—' }} · {{ fmtTime(a.created_at) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="panel-empty">
            <span>📭</span>
            <p>Belum ada aktivitas</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  userName: String,
  stats: Object,
  lowStockProducts: Array,
  activityFeed: Array,
});

const greetingText = computed(() => {
  const h = new Date().getHours();
  if (h < 11) return 'Selamat Pagi';
  if (h < 15) return 'Selamat Siang';
  if (h < 18) return 'Selamat Sore';
  return 'Selamat Malam';
});

const todayFormatted = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

const compareClass = computed(() => {
  if (props.stats.salesYesterday === 0 && props.stats.salesToday === 0) return '';
  return props.stats.salesToday >= props.stats.salesYesterday ? 'up' : 'down';
});

const compareText = computed(() => {
  const y = props.stats.salesYesterday;
  const t = props.stats.salesToday;
  if (y === 0 && t === 0) return 'Belum ada penjualan';
  if (y === 0) return '↑ Baru mulai hari ini';
  const pct = Math.round(((t - y) / y) * 100);
  return pct >= 0 ? `↑ ${pct}% dari kemarin` : `↓ ${Math.abs(pct)}% dari kemarin`;
});

function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); }

function typeLabel(type) {
  const map = { sale: 'Jual', purchase: 'Beli', adjustment_in: 'Masuk', adjustment_out: 'Keluar' };
  return map[type] || type;
}

function fmtTime(d) { return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }); }
</script>

<style scoped>
.dashboard { max-width: 1200px; }

/* Greeting */
.greeting { margin-bottom: 1.25rem; }
.greeting h2 { margin: 0; font-size: 1.35rem; font-weight: 700; color: var(--c-text-white); }
.greeting-date { margin: 0.2rem 0 0; font-size: 0.82rem; color: var(--c-text-faint); }

/* Stats */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 0.85rem; transition: all 0.3s ease; }
.stat-card:hover { border-color: var(--c-border-hover); transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.12); }
.stat-card.highlight { border-color: rgba(34,197,94,0.2); background: linear-gradient(135deg, var(--c-card), rgba(34,197,94,0.04)); }
.stat-icon { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.stat-icon.green { background: rgba(34,197,94,0.12); }
.stat-icon.purple { background: rgba(168,85,247,0.12); }
.stat-icon.teal { background: rgba(20,184,166,0.12); }
.stat-icon.red { background: rgba(239,68,68,0.12); }
.stat-icon.blue { background: rgba(59,130,246,0.12); }
.stat-content { display: flex; flex-direction: column; min-width: 0; }
.stat-label { font-size: 0.7rem; color: var(--c-text-dim); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 500; }
.stat-value { font-size: 1.25rem; font-weight: 700; color: var(--c-text-white); line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stat-value.negative { color: #ef4444; }
.stat-compare { font-size: 0.72rem; font-weight: 500; margin-top: 0.15rem; }
.stat-compare.up { color: #22c55e; }
.stat-compare.down { color: #ef4444; }
.stat-sub { font-size: 0.68rem; color: var(--c-text-faint); margin-top: 0.1rem; }

/* Quick Actions */
.section { margin-bottom: 1.5rem; }
.section-title { font-size: 0.8rem; font-weight: 600; color: var(--c-text-muted); margin: 0 0 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
.quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.6rem; }
.action-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; text-decoration: none; color: var(--c-text-muted); font-size: 0.82rem; font-weight: 500; transition: all 0.2s; cursor: pointer; }
.action-card:hover { background: var(--c-card-hover); border-color: var(--c-border-hover); color: var(--c-text); transform: translateY(-2px); }
.action-card.primary { border-color: rgba(168,85,247,0.2); background: linear-gradient(135deg, var(--c-card), rgba(168,85,247,0.06)); }
.action-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
.action-icon.pink { background: rgba(236,72,153,0.12); }
.action-icon.orange { background: rgba(249,115,22,0.12); }
.action-icon.purple { background: rgba(168,85,247,0.12); }
.action-icon.blue { background: rgba(59,130,246,0.12); }

/* Bottom Grid */
.bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.panel { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; padding: 1.25rem; }
.panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; }
.panel-header h3 { margin: 0; font-size: 0.88rem; font-weight: 600; color: var(--c-text); }
.panel-link { font-size: 0.75rem; color: var(--c-accent); text-decoration: none; font-weight: 500; }
.panel-link:hover { text-decoration: underline; }
.panel-empty { text-align: center; padding: 1.5rem 0; color: var(--c-text-faint); }
.panel-empty span { font-size: 1.5rem; display: block; margin-bottom: 0.4rem; }
.panel-empty p { margin: 0; font-size: 0.82rem; }

/* Low Stock */
.low-stock-list { display: flex; flex-direction: column; gap: 0.4rem; }
.ls-item { display: flex; align-items: center; justify-content: space-between; padding: 0.45rem 0.5rem; border-radius: 8px; transition: background 0.15s; }
.ls-item:hover { background: var(--c-surface); }
.ls-info { display: flex; flex-direction: column; min-width: 0; }
.ls-name { font-size: 0.82rem; font-weight: 500; color: var(--c-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ls-cat { font-size: 0.68rem; color: var(--c-text-faint); }
.ls-stock { display: flex; align-items: baseline; gap: 0.2rem; flex-shrink: 0; }
.ls-current { font-weight: 700; font-size: 0.9rem; padding: 0.1rem 0.4rem; border-radius: 4px; }
.ls-current.low { color: #f59e0b; background: rgba(245,158,11,0.1); }
.ls-current.out { color: #ef4444; background: rgba(239,68,68,0.1); }
.ls-min { font-size: 0.7rem; color: var(--c-text-faint); }

/* Activity Feed */
.activity-list { display: flex; flex-direction: column; gap: 0.35rem; }
.act-item { display: flex; align-items: flex-start; gap: 0.6rem; padding: 0.4rem 0; }
.act-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 0.35rem; flex-shrink: 0; }
.act-dot.sale { background: #60a5fa; }
.act-dot.purchase { background: #4ade80; }
.act-dot.adjustment_in { background: #14b8a6; }
.act-dot.adjustment_out { background: #f59e0b; }
.act-info { min-width: 0; }
.act-text { display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; font-size: 0.8rem; color: var(--c-text); }
.act-text strong { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; display: inline-block; vertical-align: middle; }
.act-badge { padding: 0.08rem 0.3rem; border-radius: 3px; font-size: 0.65rem; font-weight: 600; }
.act-badge.sale { background: rgba(59,130,246,0.1); color: #60a5fa; }
.act-badge.purchase { background: rgba(34,197,94,0.1); color: #22c55e; }
.act-badge.adjustment_in { background: rgba(20,184,166,0.1); color: #14b8a6; }
.act-badge.adjustment_out { background: rgba(245,158,11,0.1); color: #f59e0b; }
.act-plus { color: #22c55e; font-weight: 600; font-size: 0.78rem; }
.act-minus { color: #ef4444; font-weight: 600; font-size: 0.78rem; }
.act-meta { font-size: 0.68rem; color: var(--c-text-faint); }

@media (max-width: 900px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .quick-actions { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .bottom-grid { grid-template-columns: 1fr; }
}
</style>

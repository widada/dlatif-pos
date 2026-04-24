<template>
  <AppLayout title="Dashboard">
    <Head title="Dashboard" />

    <div class="dashboard">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon purple">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v.75a.75.75 0 01-1.5 0v-.75a1.5 1.5 0 00-3 0v.75a.75.75 0 01-1.5 0v-.75z" clip-rule="evenodd" /></svg>
          </div>
          <div class="stat-content">
            <span class="stat-label">Total Produk</span>
            <span class="stat-value">{{ stats.totalProducts }}</span>
          </div>
        </div>

        <div class="stat-card warning">
          <div class="stat-icon amber">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
          </div>
          <div class="stat-content">
            <span class="stat-label">Stok Rendah</span>
            <span class="stat-value">{{ stats.lowStockProducts }}</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon green">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z" /><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 14.625v-9.75zM8.25 9.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM18.75 9a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75V9.75a.75.75 0 00-.75-.75h-.008zM4.5 9.75A.75.75 0 015.25 9h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V9.75z" clip-rule="evenodd" /></svg>
          </div>
          <div class="stat-content">
            <span class="stat-label">Nilai Stok (HPP)</span>
            <span class="stat-value">{{ formatCurrency(stats.totalStockValue) }}</span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="section">
        <h3 class="section-title">Aksi Cepat</h3>
        <div class="quick-actions">
          <Link href="/products/create" class="action-card">
            <div class="action-icon purple">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H5.25a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd" /></svg>
            </div>
            <span>Tambah Produk</span>
          </Link>
          <Link href="/products" class="action-card">
            <div class="action-icon blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v.75a.75.75 0 01-1.5 0v-.75a1.5 1.5 0 00-3 0v.75a.75.75 0 01-1.5 0v-.75z" clip-rule="evenodd" /></svg>
            </div>
            <span>Kelola Produk</span>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
});

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}
</script>

<style scoped>
.dashboard { max-width: 1200px; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 16px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.3s ease; }
.stat-card:hover { border-color: var(--c-border-hover); transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.15); }
.stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 24px; height: 24px; }
.stat-icon.purple { background: rgba(168,85,247,0.15); color: #c084fc; }
.stat-icon.amber { background: rgba(245,158,11,0.15); color: #fbbf24; }
.stat-icon.green { background: rgba(34,197,94,0.15); color: #4ade80; }
.stat-content { display: flex; flex-direction: column; }
.stat-label { font-size: 0.75rem; color: var(--c-text-dim); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 500; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: var(--c-text-white); line-height: 1.2; }
.section { margin-bottom: 2rem; }
.section-title { font-size: 0.875rem; font-weight: 600; color: var(--c-text-muted); margin: 0 0 1rem; text-transform: uppercase; letter-spacing: 0.05em; }
.quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; }
.action-card { background: var(--c-input-bg); border: 1px solid var(--c-border); border-radius: 14px; padding: 1.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--c-text-muted); font-size: 0.875rem; font-weight: 500; transition: all 0.2s ease; cursor: pointer; }
.action-card:hover { background: var(--c-card-hover); border-color: var(--c-border-hover); color: var(--c-text); transform: translateY(-2px); }
.action-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.action-icon svg { width: 22px; height: 22px; }
.action-icon.purple { background: rgba(168,85,247,0.15); color: #c084fc; }
.action-icon.blue { background: rgba(59,130,246,0.15); color: #60a5fa; }
</style>

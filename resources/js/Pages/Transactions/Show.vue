<template>
  <AppLayout title="Detail Transaksi">
    <Head title="Detail Transaksi" />

    <div class="detail-page">
      <div class="detail-header">
        <Link href="/transactions" class="back-link">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M7.72 12.53a.75.75 0 010-1.06l7.5-7.5a.75.75 0 111.06 1.06L9.31 12l6.97 6.97a.75.75 0 11-1.06 1.06l-7.5-7.5z" clip-rule="evenodd" /></svg>
          Kembali
        </Link>
        <h2>{{ transaction.transaction_number }}</h2>
      </div>

      <div class="detail-grid">
        <!-- Info Card -->
        <div class="info-card">
          <h3>Informasi Transaksi</h3>
          <div class="info-rows">
            <div class="info-row"><span class="info-label">No. Transaksi</span><span class="info-val mono">{{ transaction.transaction_number }}</span></div>
            <div class="info-row"><span class="info-label">Tanggal</span><span class="info-val">{{ formatDate(transaction.date) }}</span></div>
            <div class="info-row"><span class="info-label">Channel</span><span :class="['ch-badge', transaction.channel.toLowerCase()]">{{ transaction.channel }}</span></div>
            <div class="info-row"><span class="info-label">Pembayaran</span><span class="info-val">{{ transaction.payment_method }}</span></div>
            <div v-if="transaction.notes" class="info-row"><span class="info-label">Catatan</span><span class="info-val">{{ transaction.notes }}</span></div>
          </div>
        </div>

        <!-- Payment Card -->
        <div class="info-card">
          <h3>Ringkasan Pembayaran</h3>
          <div class="info-rows">
            <div class="info-row"><span class="info-label">Subtotal</span><span class="info-val">{{ fmt(transaction.subtotal) }}</span></div>
            <div v-if="transaction.discount > 0" class="info-row disc"><span class="info-label">Diskon</span><span class="info-val">-{{ fmt(transaction.discount) }}</span></div>
            <div class="info-row total"><span class="info-label">Total</span><span class="info-val">{{ fmt(transaction.total) }}</span></div>
            <div class="info-row"><span class="info-label">Bayar</span><span class="info-val">{{ fmt(transaction.payment_amount) }}</span></div>
            <div v-if="transaction.change_amount > 0" class="info-row"><span class="info-label">Kembalian</span><span class="info-val">{{ fmt(transaction.change_amount) }}</span></div>
          </div>
          <Link :href="`/pos/receipt/${transaction.id}`" class="btn-receipt">🖨️ Cetak Struk</Link>
        </div>
      </div>

      <!-- Items -->
      <div class="items-card">
        <h3>Detail Item ({{ transaction.items.length }} produk)</h3>
        <table class="items-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Produk</th>
              <th class="text-center">Qty</th>
              <th class="text-right">Harga Satuan</th>
              <th class="text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in transaction.items" :key="item.id">
              <td class="text-muted">{{ i + 1 }}</td>
              <td class="item-name">{{ item.product_name }}</td>
              <td class="text-center">{{ item.quantity }}</td>
              <td class="text-right">{{ fmt(item.price) }}</td>
              <td class="text-right item-sub">{{ fmt(item.subtotal) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ transaction: Object });

function fmt(v) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v);
}

function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
}
</script>

<style scoped>
.detail-page { max-width: 900px; }

.detail-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; }
.back-link { display: flex; align-items: center; gap: 0.3rem; color: var(--c-text-muted); text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: color 0.2s; }
.back-link:hover { color: var(--c-accent); }
.detail-header h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--c-text); font-family: 'SF Mono','Fira Code',monospace; }

.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin-bottom: 1rem; }

.info-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; padding: 1.25rem; }
.info-card h3 { margin: 0 0 0.75rem; font-size: 0.8rem; font-weight: 600; color: var(--c-text-dim); text-transform: uppercase; letter-spacing: 0.04em; }
.info-rows { display: flex; flex-direction: column; gap: 0.5rem; }
.info-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; }
.info-label { color: var(--c-text-muted); }
.info-val { color: var(--c-text); font-weight: 500; }
.info-val.mono { font-family: 'SF Mono','Fira Code',monospace; font-weight: 600; color: var(--c-accent); }
.info-row.disc .info-val { color: var(--c-danger); }
.info-row.total { padding-top: 0.5rem; border-top: 1px solid var(--c-border); margin-top: 0.25rem; }
.info-row.total .info-label, .info-row.total .info-val { font-size: 1.05rem; font-weight: 700; color: var(--c-text); }

.ch-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.72rem; font-weight: 600; }
.ch-badge.offline { background: rgba(34,197,94,0.12); color: #4ade80; }
.ch-badge.shopee { background: rgba(249,115,22,0.12); color: #fb923c; }

.btn-receipt { display: block; width: 100%; margin-top: 1rem; padding: 0.6rem; background: var(--c-surface); border: 1px solid var(--c-border-hover); border-radius: 10px; color: var(--c-text-muted); font-size: 0.85rem; font-weight: 500; text-align: center; text-decoration: none; transition: all 0.2s; }
.btn-receipt:hover { border-color: var(--c-accent-border); color: var(--c-accent); }

.items-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; overflow: hidden; }
.items-card h3 { margin: 0; padding: 1rem 1.25rem; font-size: 0.8rem; font-weight: 600; color: var(--c-text-dim); text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid var(--c-border); }

.items-table { width: 100%; border-collapse: collapse; }
.items-table th { background: var(--c-surface); padding: 0.6rem 1rem; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); text-align: left; }
.items-table td { padding: 0.7rem 1rem; font-size: 0.85rem; border-top: 1px solid var(--c-border); color: var(--c-text); }
.item-name { font-weight: 500; }
.item-sub { font-weight: 600; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-muted { color: var(--c-text-faint); }
</style>

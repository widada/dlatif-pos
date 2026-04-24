<template>
  <AppLayout title="Detail Pembelian">
    <Head :title="`Detail ${purchase.purchase_number}`" />
    <div class="show-page">
      <div class="show-header">
        <Link href="/purchases" class="back-link">← Kembali</Link>
        <h2>{{ purchase.purchase_number }}</h2>
      </div>

      <div class="detail-grid">
        <div class="detail-card">
          <h3>Informasi</h3>
          <div class="info-row"><span class="lbl">Tanggal</span><span class="val">{{ fmtDate(purchase.date) }}</span></div>
          <div class="info-row"><span class="lbl">Supplier</span><span class="val">{{ purchase.supplier?.name || '—' }}</span></div>
          <div class="info-row"><span class="lbl">No. Invoice</span><span class="val">{{ purchase.invoice_number || '—' }}</span></div>
          <div class="info-row" v-if="purchase.notes"><span class="lbl">Catatan</span><span class="val">{{ purchase.notes }}</span></div>
        </div>

        <div class="detail-card total-card">
          <span class="total-label">Total Pembelian</span>
          <span class="total-value">{{ fmt(purchase.total) }}</span>
          <span class="total-items">{{ purchase.items?.length || 0 }} produk</span>
        </div>
      </div>

      <div class="items-card">
        <h3>Detail Produk</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Produk</th>
              <th class="text-center">Qty</th>
              <th class="text-right">Harga Beli</th>
              <th class="text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in purchase.items" :key="item.id">
              <td><span class="prod-name">{{ item.product_name }}</span></td>
              <td class="text-center">{{ item.quantity }}</td>
              <td class="text-right">{{ fmt(item.cost_price) }}</td>
              <td class="text-right fw-600">{{ fmt(item.subtotal) }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-right fw-600">Grand Total</td>
              <td class="text-right fw-600 accent">{{ fmt(purchase.total) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({ purchase: Object });

function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v); }
function fmtDate(d) { return new Date(d).toLocaleDateString('id-ID', { dateStyle: 'long' }); }
</script>

<style scoped>
.show-page { max-width: 900px; }
.show-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; }
.back-link { color: var(--c-text-muted); font-size: 0.85rem; text-decoration: none; }
.back-link:hover { color: var(--c-accent); }
.show-header h2 { margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--c-accent); font-family: 'SF Mono','Fira Code',monospace; }

.detail-grid { display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; margin-bottom: 1rem; }
.detail-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; padding: 1.25rem; }
.detail-card h3 { margin: 0 0 0.75rem; font-size: 0.85rem; font-weight: 600; color: var(--c-text-dim); text-transform: uppercase; letter-spacing: 0.04em; }
.info-row { display: flex; justify-content: space-between; padding: 0.35rem 0; border-bottom: 1px solid var(--c-border); }
.info-row:last-child { border-bottom: none; }
.lbl { font-size: 0.82rem; color: var(--c-text-muted); }
.val { font-size: 0.82rem; font-weight: 500; color: var(--c-text); }
.total-card { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; min-width: 180px; }
.total-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); }
.total-value { font-size: 1.4rem; font-weight: 800; color: var(--c-accent); margin: 0.25rem 0; }
.total-items { font-size: 0.75rem; color: var(--c-text-faint); }

.items-card { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 14px; overflow: hidden; }
.items-card h3 { padding: 1rem 1.25rem 0; margin: 0 0 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--c-text-dim); text-transform: uppercase; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--c-surface); padding: 0.6rem 1rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--c-text-dim); text-align: left; }
.data-table td { padding: 0.65rem 1rem; font-size: 0.85rem; border-top: 1px solid var(--c-border); color: var(--c-text); }
.data-table tfoot td { border-top: 2px solid var(--c-border); font-size: 0.9rem; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.fw-600 { font-weight: 600; }
.accent { color: var(--c-accent); }
.prod-name { font-weight: 500; }

@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
</style>

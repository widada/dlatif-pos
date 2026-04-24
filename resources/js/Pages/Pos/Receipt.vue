<template>
  <AppLayout title="Struk Transaksi">
    <Head title="Struk Transaksi" />

    <div class="receipt-page">
      <div class="receipt-card">
        <div class="receipt-header">
          <h2>Dlatif POS</h2>
          <p>Kosmetik & Aksesoris</p>
        </div>

        <div class="receipt-meta">
          <div class="meta-row"><span>No. Transaksi</span><strong>{{ transaction.transaction_number }}</strong></div>
          <div class="meta-row"><span>Tanggal</span><span>{{ formatDate(transaction.date) }}</span></div>
          <div class="meta-row"><span>Channel</span><span :class="['ch-tag', transaction.channel.toLowerCase()]">{{ transaction.channel }}</span></div>
          <div class="meta-row"><span>Pembayaran</span><span>{{ transaction.payment_method }}</span></div>
        </div>

        <div class="receipt-divider"></div>

        <table class="receipt-items">
          <thead><tr><th>Produk</th><th class="r">Qty</th><th class="r">Harga</th><th class="r">Sub</th></tr></thead>
          <tbody>
            <tr v-for="item in transaction.items" :key="item.id">
              <td>{{ item.product_name }}</td>
              <td class="r">{{ item.quantity }}</td>
              <td class="r">{{ fmt(item.price) }}</td>
              <td class="r">{{ fmt(item.subtotal) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="receipt-divider"></div>

        <div class="receipt-totals">
          <div class="tot-row"><span>Subtotal</span><span>{{ fmt(transaction.subtotal) }}</span></div>
          <div v-if="transaction.discount > 0" class="tot-row disc"><span>Diskon</span><span>-{{ fmt(transaction.discount) }}</span></div>
          <div class="tot-row total"><span>TOTAL</span><span>{{ fmt(transaction.total) }}</span></div>
          <div v-if="transaction.payment_amount" class="tot-row"><span>Bayar</span><span>{{ fmt(transaction.payment_amount) }}</span></div>
          <div v-if="transaction.change_amount" class="tot-row"><span>Kembalian</span><span>{{ fmt(transaction.change_amount) }}</span></div>
        </div>

        <div class="receipt-footer">
          <p>Terima kasih atas pembelian Anda!</p>
          <p class="small">{{ formatDate(transaction.date) }}</p>
        </div>

        <div class="receipt-actions">
          <button class="btn-print" @click="printReceipt">🖨️ Cetak Struk</button>
          <Link href="/pos" class="btn-back">Transaksi Baru</Link>
        </div>
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
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}

function printReceipt() { window.print(); }
</script>

<style scoped>
.receipt-page { display: flex; justify-content: center; padding-top: 1rem; }
.receipt-card { background: linear-gradient(135deg, rgba(24,24,27,0.9), rgba(39,39,42,0.4)); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 2rem; max-width: 440px; width: 100%; }
.receipt-header { text-align: center; margin-bottom: 1.25rem; }
.receipt-header h2 { margin: 0; font-size: 1.3rem; font-weight: 700; color: white; }
.receipt-header p { margin: 0.15rem 0 0; font-size: 0.75rem; color: #71717a; text-transform: uppercase; letter-spacing: 0.05em; }
.receipt-meta { display: flex; flex-direction: column; gap: 0.35rem; }
.meta-row { display: flex; justify-content: space-between; font-size: 0.8rem; color: #a1a1aa; }
.meta-row strong { color: #e4e4e7; }
.ch-tag { padding: 0.1rem 0.45rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600; }
.ch-tag.offline { background: rgba(34,197,94,0.12); color: #4ade80; }
.ch-tag.shopee { background: rgba(249,115,22,0.12); color: #fb923c; }
.receipt-divider { border-top: 1px dashed rgba(255,255,255,0.1); margin: 1rem 0; }
.receipt-items { width: 100%; border-collapse: collapse; }
.receipt-items th { font-size: 0.7rem; font-weight: 600; color: #71717a; text-transform: uppercase; letter-spacing: 0.04em; padding-bottom: 0.5rem; text-align: left; }
.receipt-items td { font-size: 0.8rem; color: #d4d4d8; padding: 0.3rem 0; }
.r { text-align: right; }
.receipt-totals { display: flex; flex-direction: column; gap: 0.3rem; }
.tot-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: #a1a1aa; }
.tot-row.disc { color: #f87171; }
.tot-row.total { font-size: 1.1rem; font-weight: 700; color: white; padding-top: 0.4rem; border-top: 1px solid rgba(255,255,255,0.08); margin-top: 0.2rem; }
.receipt-footer { text-align: center; margin-top: 1.25rem; }
.receipt-footer p { margin: 0; font-size: 0.8rem; color: #71717a; }
.receipt-footer .small { font-size: 0.7rem; color: #3f3f46; margin-top: 0.2rem; }
.receipt-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
.btn-print { flex: 1; padding: 0.65rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #a1a1aa; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.btn-print:hover { background: rgba(255,255,255,0.08); color: white; }
.btn-back { flex: 1; padding: 0.65rem; background: linear-gradient(135deg, #a855f7, #ec4899); border: none; border-radius: 10px; color: white; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; transition: all 0.2s; }
.btn-back:hover { opacity: 0.9; }

@media print {
  .receipt-actions { display: none; }
}
</style>

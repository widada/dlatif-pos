<template>
  <AppLayout title="Struk Transaksi">
    <Head title="Struk Transaksi" />

    <div class="receipt-page">
      <div class="receipt-wrapper">
        <!-- Receipt -->
        <div class="receipt" id="printable-receipt">
          <div class="rc-header">
            <h2>Dlatif Store</h2>
            <p>Skincare, Aksesoris, & ATK</p>
            <p class="rc-address">Buntit RT 27, Pare, Mondokan, Sragen</p>
            <p class="rc-phone">Hp: 0822-3439-2553</p>
          </div>

          <div class="rc-divider double"></div>

          <table class="rc-meta">
            <tr><td class="lbl">No. Transaksi</td><td class="val">{{ transaction.transaction_number }}</td></tr>
            <tr><td class="lbl">Tanggal</td><td class="val">{{ formatDate(transaction.date) }}</td></tr>
            <tr><td class="lbl">Channel</td><td class="val">{{ transaction.channel }}</td></tr>
            <tr><td class="lbl">Pembayaran</td><td class="val">{{ transaction.payment_method }}</td></tr>
          </table>

          <div class="rc-divider"></div>

          <table class="rc-items">
            <thead>
              <tr>
                <th class="col-name">Produk</th>
                <th class="col-qty">Qty</th>
                <th class="col-price">Harga</th>
                <th class="col-sub">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in transaction.items" :key="item.id">
                <td class="col-name">{{ item.product_name }}</td>
                <td class="col-qty">{{ item.quantity }}</td>
                <td class="col-price">{{ fmtShort(item.price) }}</td>
                <td class="col-sub">{{ fmtShort(item.subtotal) }}</td>
              </tr>
            </tbody>
          </table>

          <div class="rc-divider"></div>

          <div class="rc-summary">
            <div class="sum-line"><span>Subtotal</span><span>{{ fmt(transaction.subtotal) }}</span></div>
            <div v-if="transaction.discount > 0" class="sum-line disc"><span>Diskon</span><span>-{{ fmt(transaction.discount) }}</span></div>
          </div>

          <div class="rc-divider double"></div>

          <div class="rc-total">
            <span>TOTAL</span>
            <span>{{ fmt(transaction.total) }}</span>
          </div>

          <div class="rc-divider double"></div>

          <div class="rc-payment">
            <div class="sum-line"><span>Bayar</span><span>{{ fmt(transaction.payment_amount) }}</span></div>
            <div v-if="transaction.change_amount > 0" class="sum-line"><span>Kembali</span><span>{{ fmt(transaction.change_amount) }}</span></div>
          </div>

          <div class="rc-footer">
            <p>Terima kasih atas pembelian Anda!</p>
            <p class="rc-date">{{ formatDate(transaction.date) }}</p>
          </div>
        </div>

        <!-- Actions (outside receipt for print) -->
        <div class="rc-actions">
          <button class="btn-print" @click="printReceipt">🖨️ Cetak Struk</button>
          <Link href="/pos" class="btn-new">➕ Transaksi Baru</Link>
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

function fmtShort(v) {
  return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v);
}

function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}

function printReceipt() {
  const receiptEl = document.getElementById('printable-receipt');
  if (!receiptEl) return;

  // Create hidden iframe
  const iframe = document.createElement('iframe');
  iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none;';
  document.body.appendChild(iframe);

  const doc = iframe.contentDocument || iframe.contentWindow.document;
  doc.open();
  doc.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Struk</title>
<style>
  @page { size: 80mm auto; margin: 3mm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: black; background: white; width: 80mm; padding: 2mm; }
  .rc-header { text-align: center; padding-bottom: 3mm; }
  .rc-header h2 { font-size: 16px; font-weight: 800; margin: 0; }
  .rc-header p { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #333; margin-top: 1px; }
  .rc-address { text-transform: none; letter-spacing: normal; font-size: 9px; color: #333; margin-top: 2mm; }
  .rc-phone { text-transform: none; letter-spacing: normal; font-size: 9px; color: #333; }
  .rc-divider { border-top: 1px dashed #000; margin: 2mm 0; }
  .rc-divider.double { border-top: 3px double #000; }
  table { width: 100%; border-collapse: collapse; }
  .rc-meta td { padding: 0.5mm 0; font-size: 10px; }
  .rc-meta .lbl { color: #333; width: 85px; }
  .rc-meta .val { text-align: right; font-weight: 600; }
  .rc-items th { text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #333; padding-bottom: 1.5mm; border-bottom: 1px solid #000; }
  .rc-items td { padding: 1mm 0; font-size: 10px; vertical-align: top; }
  .col-qty { width: 24px; text-align: center; }
  .col-price { width: 55px; text-align: right; }
  .col-sub { width: 60px; text-align: right; font-weight: 600; }
  .rc-items th.col-qty { text-align: center; }
  .rc-items th.col-price, .rc-items th.col-sub { text-align: right; }
  .sum-line { display: flex; justify-content: space-between; font-size: 10px; padding: 0.3mm 0; }
  .rc-total { display: flex; justify-content: space-between; font-size: 14px; font-weight: 900; padding: 0.5mm 0; }
  .rc-payment { font-size: 10px; }
  .rc-footer { text-align: center; margin-top: 3mm; font-size: 10px; color: #333; }
  .rc-date { font-size: 9px; color: #666; margin-top: 1px; }
</style>
</head>
<body>${receiptEl.innerHTML}</body>
</html>`);
  doc.close();

  iframe.contentWindow.focus();
  setTimeout(() => {
    iframe.contentWindow.print();
    // Clean up after print dialog closes
    setTimeout(() => document.body.removeChild(iframe), 500);
  }, 200);
}
</script>

<style scoped>
/* ===== PAGE ===== */
.receipt-page {
  display: flex;
  justify-content: center;
  padding: 1.5rem 1rem;
}

.receipt-wrapper {
  width: 360px;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ===== RECEIPT CARD ===== */
.receipt {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 14px;
  padding: 1.5rem 1.25rem;
  color: var(--c-text);
}

/* Header */
.rc-header {
  text-align: center;
  padding-bottom: 0.5rem;
}
.rc-header h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: 0.5px;
}
.rc-header p {
  margin: 0.15rem 0 0;
  font-size: 0.7rem;
  color: var(--c-text-dim);
  text-transform: uppercase;
  letter-spacing: 1.5px;
}
.rc-address {
  text-transform: none !important;
  letter-spacing: normal !important;
  font-size: 0.72rem !important;
  color: var(--c-text-muted) !important;
  margin-top: 0.35rem !important;
}
.rc-phone {
  text-transform: none !important;
  letter-spacing: normal !important;
  font-size: 0.72rem !important;
  color: var(--c-text-muted) !important;
}

/* Dividers */
.rc-divider {
  border: none;
  border-top: 1px dashed var(--c-text-faint);
  margin: 0.75rem 0;
}
.rc-divider.double {
  border-top-style: double;
  border-top-width: 3px;
}

/* Meta info */
.rc-meta {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
}
.rc-meta td {
  padding: 0.15rem 0;
}
.rc-meta .lbl {
  color: var(--c-text-muted);
  width: 110px;
}
.rc-meta .val {
  text-align: right;
  font-weight: 600;
  color: var(--c-text);
}

/* Items table */
.rc-items {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
}
.rc-items th {
  text-align: left;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--c-text-dim);
  padding-bottom: 0.4rem;
  border-bottom: 1px solid var(--c-border);
}
.rc-items td {
  padding: 0.35rem 0;
  vertical-align: top;
  color: var(--c-text);
}
.col-name { width: auto; }
.col-qty { width: 32px; text-align: center; }
.col-price { width: 70px; text-align: right; }
.col-sub { width: 80px; text-align: right; font-weight: 600; }
.rc-items th.col-qty { text-align: center; }
.rc-items th.col-price, .rc-items th.col-sub { text-align: right; }

/* Summary */
.rc-summary { font-size: 0.85rem; }
.sum-line {
  display: flex;
  justify-content: space-between;
  padding: 0.1rem 0;
  color: var(--c-text-muted);
}
.sum-line.disc { color: var(--c-danger); }

/* Total */
.rc-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--c-text);
  padding: 0.1rem 0;
}

/* Payment */
.rc-payment { font-size: 0.85rem; }

/* Footer */
.rc-footer {
  text-align: center;
  margin-top: 0.75rem;
  padding-top: 0.5rem;
}
.rc-footer p {
  margin: 0;
  font-size: 0.75rem;
  color: var(--c-text-dim);
}
.rc-date {
  font-size: 0.7rem;
  color: var(--c-text-faint);
  margin-top: 0.1rem;
}

/* ===== ACTION BUTTONS ===== */
.rc-actions {
  display: flex;
  gap: 0.75rem;
}
.btn-print {
  flex: 1;
  padding: 0.7rem;
  background: var(--c-card);
  border: 1px solid var(--c-border-hover);
  border-radius: 10px;
  color: var(--c-text-muted);
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}
.btn-print:hover { border-color: var(--c-accent-border); color: var(--c-text); }
.btn-new {
  flex: 1;
  padding: 0.7rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  border: none;
  border-radius: 10px;
  color: white;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  transition: all 0.2s;
}
.btn-new:hover { opacity: 0.9; box-shadow: 0 4px 16px rgba(168,85,247,0.3); }

/* ===== PRINT STYLES ===== */
@media print {
  @page {
    size: 80mm auto;
    margin: 3mm;
  }

  /* Hide buttons */
  .rc-actions { display: none !important; }

  /* Reset page layout */
  .receipt-page { padding: 0 !important; justify-content: flex-start !important; }
  .receipt-wrapper { width: 100% !important; }

  /* Clean receipt for thermal */
  .receipt {
    background: white !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    color: black !important;
    width: 100% !important;
  }

  /* All text black */
  .rc-header h2,
  .rc-header p,
  .rc-meta td,
  .rc-meta .lbl,
  .rc-meta .val,
  .rc-items th,
  .rc-items td,
  .sum-line,
  .sum-line span,
  .rc-total,
  .rc-total span,
  .rc-footer p,
  .rc-date { color: black !important; }

  /* Dividers solid black */
  .rc-divider { border-top-color: black !important; }

  .rc-items th { border-bottom-color: black !important; }
}
</style>

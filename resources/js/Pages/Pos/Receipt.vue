<template>
  <AppLayout title="Struk Transaksi">

    <Head title="Struk Transaksi" />
    <div class="receipt-page">
      <div class="receipt-wrapper">
        <div class="receipt" id="printable-receipt">
          <div class="rc-header">
            <h2>{{ rs.header_text }}</h2>
            <p v-if="rs.address" class="rc-address">{{ rs.address }}</p>
            <p v-if="rs.phone" class="rc-phone">Hp: {{ rs.phone }}</p>
            <p v-if="rs.social_media" class="rc-social">IG: {{ rs.social_media }}</p>
          </div>
          <div class="rc-divider double"></div>
          <table class="rc-meta">
            <tr>
              <td class="lbl">No. Transaksi</td>
              <td class="val">{{ transaction.transaction_number }}</td>
            </tr>
            <tr>
              <td class="lbl">Tanggal</td>
              <td class="val">{{ formatDate(transaction.date) }}</td>
            </tr>
            <tr>
              <td class="lbl">Channel</td>
              <td class="val">{{ transaction.channel }}</td>
            </tr>
            <tr>
              <td class="lbl">Pembayaran</td>
              <td class="val">{{ transaction.payment_method }}</td>
            </tr>
          </table>
          <!-- Member Info -->
          <template v-if="transaction.customer && rs.show_member_info">
            <div class="rc-divider"></div>
            <div class="rc-member">
              <div class="rm-title">🎉 MEMBER</div>
              <div class="rm-row"><span>Nama</span><span>{{ transaction.customer.name }}</span></div>
              <div class="rm-row"><span>No. HP</span><span>{{ maskedPhone }}</span></div>
              <div v-if="isBirthday" class="rm-bday">🎂 SELAMAT ULANG TAHUN!</div>
            </div>
          </template>
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
            <div v-if="transaction.discount > 0" class="sum-line disc"><span>Diskon</span><span>-{{
              fmt(transaction.discount)
                }}</span></div>
            <div v-if="transaction.member_discount > 0" class="sum-line disc"><span>🏷️ Diskon Member</span><span>-{{
              fmt(transaction.member_discount) }}</span></div>
            <div v-if="transaction.birthday_discount > 0" class="sum-line disc"><span>🎂 Diskon Birthday</span><span>-{{
              fmt(transaction.birthday_discount) }}</span></div>
            <div v-if="transaction.point_discount > 0" class="sum-line disc"><span>🎁 Diskon Poin ({{
              transaction.points_used }}
                pts)</span><span>-{{ fmt(transaction.point_discount) }}</span></div>
          </div>
          <div class="rc-divider double"></div>
          <div class="rc-total"><span>TOTAL</span><span>{{ fmt(transaction.total) }}</span></div>
          <div class="rc-divider double"></div>
          <div class="rc-payment">
            <div class="sum-line"><span>Bayar</span><span>{{ fmt(transaction.payment_amount) }}</span></div>
            <div v-if="transaction.change_amount > 0" class="sum-line"><span>Kembali</span><span>{{
              fmt(transaction.change_amount)
                }}</span></div>
          </div>
          <!-- Point Reward Info -->
          <template
            v-if="transaction.customer && rs.show_point_info && (transaction.points_earned > 0 || transaction.points_used > 0)">
            <div class="rc-divider"></div>
            <div class="rc-points">
              <div class="rp-title">🎁 POIN REWARD</div>
              <div class="rp-row"><span>Poin sebelumnya</span><span>{{ pointsBefore }}</span></div>
              <div v-if="transaction.points_used > 0" class="rp-row red"><span>Poin dipakai</span><span>-{{
                transaction.points_used }}</span></div>
              <div v-if="transaction.points_earned > 0" class="rp-row green"><span>Poin diperoleh</span><span>+{{
                transaction.points_earned }}</span></div>
              <div class="rc-divider"></div>
              <div class="rp-total"><span>SISA POIN</span><span>{{ transaction.customer.points }}</span></div>
            </div>
          </template>
          <!-- Savings -->
          <div v-if="rs.show_savings && totalSavings > 0" class="rc-savings">
            Anda hemat {{ fmt(totalSavings) }} hari ini! 🎉
          </div>
          <div class="rc-footer">
            <p>{{ rs.footer_text }}</p>
            <p v-if="rs.show_promo_footer && rs.promo_text" class="rc-promo">{{ rs.promo_text }}</p>
          </div>
        </div>
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
const props = defineProps({ transaction: Object, receiptSettings: Object, maskedPhone: String, isBirthday: Boolean, pointsBefore: Number, totalSavings: Number });
const rs = props.receiptSettings || {};
function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); }
function fmtShort(v) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); }
function formatDate(d) { return new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }); }
function printReceipt() {
  const el = document.getElementById('printable-receipt'); if (!el) return;
  const iframe = document.createElement('iframe');
  iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none;';
  document.body.appendChild(iframe);
  const doc = iframe.contentDocument || iframe.contentWindow.document;
  doc.open();
  doc.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>Struk</title>
<style>@page{size:80mm auto;margin:3mm}*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Segoe UI',Arial,sans-serif;font-size:11px;color:#000;background:#fff;width:80mm;padding:2mm}
.rc-header{text-align:center;padding-bottom:3mm}.rc-header h2{font-size:16px;font-weight:800}.rc-header p{font-size:9px;color:#333;margin-top:1px}
.rc-divider{border-top:1px dashed #000;margin:2mm 0}.rc-divider.double{border-top:3px double #000}
table{width:100%;border-collapse:collapse}.rc-meta td{padding:.5mm 0;font-size:10px}.rc-meta .lbl{color:#333;width:85px}.rc-meta .val{text-align:right;font-weight:600}
.rc-items th{text-align:left;font-size:9px;font-weight:700;text-transform:uppercase;padding-bottom:1.5mm;border-bottom:1px solid #000}
.rc-items td{padding:1mm 0;font-size:10px}.col-qty{width:24px;text-align:center}.col-price{width:55px;text-align:right}.col-sub{width:60px;text-align:right;font-weight:600}
.rc-items th.col-qty{text-align:center}.rc-items th.col-price,.rc-items th.col-sub{text-align:right}
.sum-line{display:flex;justify-content:space-between;font-size:10px;padding:.3mm 0}.disc{color:#333}
.rc-total{display:flex;justify-content:space-between;font-size:14px;font-weight:900;padding:.5mm 0}
.rc-member{font-size:10px}.rm-title{font-weight:700;margin-bottom:1mm}.rm-row{display:flex;justify-content:space-between;padding:.3mm 0}.rm-bday{text-align:center;font-weight:700;margin-top:1mm}
.rc-points{font-size:10px}.rp-title{font-weight:700;margin-bottom:1mm}.rp-row{display:flex;justify-content:space-between;padding:.3mm 0}.rp-total{display:flex;justify-content:space-between;font-weight:800;font-size:11px}
.rc-savings{text-align:center;font-weight:700;font-size:11px;margin-top:2mm;padding:1mm;border:1px solid #000;border-radius:2mm}
.rc-footer{text-align:center;margin-top:3mm;font-size:10px;color:#333}.rc-promo{font-style:italic;margin-top:1mm}
</style></head><body>${el.innerHTML}</body></html>`);
  doc.close();
  iframe.contentWindow.focus();
  setTimeout(() => { iframe.contentWindow.print(); setTimeout(() => document.body.removeChild(iframe), 500); }, 200);
}
</script>
<style scoped>
.receipt-page {
  display: flex;
  justify-content: center;
  padding: 1.5rem 1rem
}

.receipt-wrapper {
  width: 380px;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1rem
}

.receipt {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 14px;
  padding: 1.5rem 1.25rem;
  color: var(--c-text)
}

.rc-header {
  text-align: center;
  padding-bottom: .5rem
}

.rc-header h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: .5px
}

.rc-header p {
  margin: .15rem 0 0;
  font-size: .72rem;
  color: var(--c-text-muted)
}

.rc-divider {
  border: none;
  border-top: 1px dashed var(--c-text-faint);
  margin: .75rem 0
}

.rc-divider.double {
  border-top-style: double;
  border-top-width: 3px
}

.rc-meta {
  width: 100%;
  border-collapse: collapse;
  font-size: .8rem
}

.rc-meta td {
  padding: .15rem 0
}

.rc-meta .lbl {
  color: var(--c-text-muted);
  width: 110px
}

.rc-meta .val {
  text-align: right;
  font-weight: 600;
  color: var(--c-text)
}

.rc-member {
  font-size: .82rem
}

.rm-title {
  font-weight: 700;
  color: var(--c-text-white);
  margin-bottom: .3rem
}

.rm-row {
  display: flex;
  justify-content: space-between;
  padding: .1rem 0;
  color: var(--c-text-muted)
}

.rm-row span:last-child {
  font-weight: 600;
  color: var(--c-text)
}

.rm-bday {
  text-align: center;
  font-weight: 700;
  color: #fbbf24;
  margin-top: .3rem;
  font-size: .85rem
}

.rc-items {
  width: 100%;
  border-collapse: collapse;
  font-size: .8rem
}

.rc-items th {
  text-align: left;
  font-size: .65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: var(--c-text-dim);
  padding-bottom: .4rem;
  border-bottom: 1px solid var(--c-border)
}

.rc-items td {
  padding: .35rem 0;
  vertical-align: top;
  color: var(--c-text)
}

.col-name {
  width: auto
}

.col-qty {
  width: 32px;
  text-align: center
}

.col-price {
  width: 70px;
  text-align: right
}

.col-sub {
  width: 80px;
  text-align: right;
  font-weight: 600
}

.rc-items th.col-qty {
  text-align: center
}

.rc-items th.col-price,
.rc-items th.col-sub {
  text-align: right
}

.rc-summary {
  font-size: .85rem
}

.sum-line {
  display: flex;
  justify-content: space-between;
  padding: .1rem 0;
  color: var(--c-text-muted)
}

.sum-line.disc {
  color: #4ade80
}

.rc-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--c-text);
  padding: .1rem 0
}

.rc-payment {
  font-size: .85rem
}

.rc-points {
  font-size: .82rem
}

.rp-title {
  font-weight: 700;
  color: var(--c-text-white);
  margin-bottom: .3rem
}

.rp-row {
  display: flex;
  justify-content: space-between;
  padding: .1rem 0;
  color: var(--c-text-muted)
}

.rp-row.green span:last-child {
  color: #4ade80;
  font-weight: 600
}

.rp-row.red span:last-child {
  color: #f87171;
  font-weight: 600
}

.rp-total {
  display: flex;
  justify-content: space-between;
  font-weight: 800;
  color: var(--c-text-white);
  font-size: .9rem
}

.rc-savings {
  text-align: center;
  font-weight: 700;
  color: #4ade80;
  font-size: .85rem;
  margin-top: .5rem;
  padding: .4rem;
  background: rgba(34, 197, 94, .08);
  border-radius: 8px
}

.rc-footer {
  text-align: center;
  margin-top: .75rem;
  padding-top: .5rem
}

.rc-footer p {
  margin: 0;
  font-size: .75rem;
  color: var(--c-text-dim)
}

.rc-promo {
  font-style: italic;
  color: var(--c-text-faint) !important;
  margin-top: .2rem !important
}

.rc-actions {
  display: flex;
  gap: .75rem
}

.btn-print {
  flex: 1;
  padding: .7rem;
  background: var(--c-card);
  border: 1px solid var(--c-border-hover);
  border-radius: 10px;
  color: var(--c-text-muted);
  font-size: .85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all .2s;
  font-family: inherit
}

.btn-print:hover {
  border-color: rgba(168, 85, 247, .3);
  color: var(--c-text)
}

.btn-new {
  flex: 1;
  padding: .7rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  border: none;
  border-radius: 10px;
  color: #fff;
  font-size: .85rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  transition: all .2s
}

.btn-new:hover {
  opacity: .9;
  box-shadow: 0 4px 16px rgba(168, 85, 247, .3)
}

@media print {
  @page {
    size: 80mm auto;
    margin: 3mm
  }

  .rc-actions {
    display: none !important
  }

  .receipt-page {
    padding: 0 !important
  }

  .receipt-wrapper {
    width: 100% !important
  }

  .receipt {
    background: #fff !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    color: #000 !important
  }

  .rc-header h2,
  .rc-meta td,
  .rc-items th,
  .rc-items td,
  .sum-line,
  .rc-total,
  .rc-footer p,
  .rc-member *,
  .rc-points * {
    color: #000 !important
  }

  .rc-divider {
    border-top-color: #000 !important
  }

  .rc-items th {
    border-bottom-color: #000 !important
  }
}
</style>

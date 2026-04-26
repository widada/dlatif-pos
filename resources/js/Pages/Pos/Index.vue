<template>
  <AppLayout title="Kasir (POS)">

    <Head title="Kasir (POS)" />
    <div class="pos-layout">
      <div class="pos-products">
        <div class="pos-toolbar">
          <div class="channel-switch">
            <button :class="['ch-btn', { active: channel === 'Offline' }]" @click="channel = 'Offline'">🏪 Offline</button>
            <button :class="['ch-btn', { active: channel === 'Shopee' }]" @click="channel = 'Shopee'">🛒 Shopee</button>
          </div>
          <div class="search-box">
            <input ref="searchInput" v-model="search" type="text" placeholder="Cari produk atau scan barcode... (F2)"
              @input="onSearch" />
          </div>
          <select v-model="filterCat" class="cat-filter">
            <option value="">Semua</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
        <div class="product-grid">
          <div v-for="p in filteredProducts" :key="p.id" class="p-card" @click="addToCart(p)">
            <div class="p-img">
              <img v-if="p.image_url" :src="p.image_url" :alt="p.name" />
              <div v-else class="p-img-ph">📦</div>
            </div>
            <div class="p-info">
              <span class="p-name">{{ p.name }}</span>
              <span class="p-price">{{ fmt(getPrice(p)) }}</span>
              <span class="p-stock">Stok: {{ p.stock }}</span>
            </div>
          </div>
          <div v-if="filteredProducts.length === 0" class="empty-grid">
            <p>Produk tidak ditemukan</p>
          </div>
        </div>
      </div>
      <div class="pos-cart">
        <div class="cart-header">
          <h3>🛒 Keranjang</h3><button v-if="cart.length > 0" class="clear-btn" @click="clearCart">Hapus Semua</button>
        </div>
        <!-- Member Section -->
        <div v-if="memberSettings.member_enabled && channel === 'Offline'" class="member-section">
          <div class="member-input">
            <input v-model="custPhone" type="text" placeholder="No. HP member (opsional)" @input="searchCustomer" />
            <span v-if="custLoading" class="ml">⏳</span>
          </div>
          <div v-if="custData" class="member-card">
            <div class="mc-name">{{ custData.name }} <span v-if="custIsBirthday" class="bday">🎂 Ulang Tahun!</span>
            </div>
            <div class="mc-info">Poin: <strong>{{ custData.points }}</strong></div>
          </div>
          <div v-if="custPhone && !custData && !custLoading && custSearched" class="register-form">
            <p class="rf-label">Customer baru — isi data:</p>
            <input v-model="custName" type="text" placeholder="Nama customer" class="rf-input" />
            <input v-model="custBirth" type="date" placeholder="Tgl lahir (opsional)" class="rf-input" />
          </div>
        </div>
        <div class="cart-items">
          <div v-if="cart.length === 0" class="cart-empty">
            <p>Keranjang kosong</p><span>Pilih produk untuk memulai</span>
          </div>
          <div v-for="(item, i) in cart" :key="item.product.id" class="cart-item">
            <div class="ci-info"><span class="ci-name">{{ item.product.name }}</span><span class="ci-price">{{
              fmt(getPrice(item.product)) }}</span></div>
            <div class="ci-controls">
              <button class="qty-btn" @click="changeQty(i, -1)">−</button>
              <span class="qty-val">{{ item.quantity }}</span>
              <button class="qty-btn" @click="changeQty(i, 1)" :disabled="item.quantity >= item.product.stock">+</button>
              <button class="rm-btn" @click="removeItem(i)">✕</button>
            </div>
            <div class="ci-subtotal">{{ fmt(getPrice(item.product) * item.quantity) }}</div>
          </div>
        </div>
        <div v-if="cart.length > 0" class="cart-summary">
          <div class="sum-row"><span>Subtotal</span><span>{{ fmt(subtotal) }}</span></div>
          <div class="sum-row discount-row"><span>Diskon</span>
            <div class="disc-input"><span>Rp</span><input v-model.number="discount" type="number" min="0"
                placeholder="0" /></div>
          </div>
          <div v-if="memberDiscountAmt > 0" class="sum-row disc-line"><span>🏷️ Diskon Member ({{
            memberSettings.member_discount_percent }}%)</span><span>-{{ fmt(memberDiscountAmt) }}</span></div>
          <div v-if="birthdayDiscountAmt > 0" class="sum-row disc-line"><span>🎂 Diskon Birthday ({{
            memberSettings.birthday_discount_percent }}%)</span><span>-{{ fmt(birthdayDiscountAmt) }}</span></div>
          <!-- Point Redemption -->
          <div v-if="custData && custData.points >= memberSettings.point_min_redeem" class="points-section">
            <label class="pts-toggle"><input type="checkbox" v-model="usePoints" /> Pakai Poin ({{ custData.points }}
              pts)</label>
            <div v-if="usePoints" class="pts-input">
              <input v-model.number="pointsToUse" type="number" :min="memberSettings.point_min_redeem"
                :max="custData.points" />
              <span class="pts-val">= {{ fmt(pointsToUse * memberSettings.point_redeem_value) }}</span>
            </div>
          </div>
          <div v-if="pointDiscountAmt > 0" class="sum-row disc-line"><span>🎁 Diskon Poin ({{ pointsToUse }}
              pts)</span><span>-{{ fmt(pointDiscountAmt) }}</span></div>
          <div class="sum-row total-row"><span>Total</span><span>{{ fmt(total) }}</span></div>
          <div v-if="custData && earnPreview > 0" class="earn-preview">🎯 Akan dapat <strong>{{ earnPreview }}
              poin</strong></div>
          <div class="payment-section">
            <label class="pay-label">Metode Pembayaran</label>
            <div class="pay-methods">
              <button :class="['pay-btn', { active: paymentMethod === 'Cash' }]" @click="paymentMethod = 'Cash'">💵
                Cash</button>
              <button :class="['pay-btn', { active: paymentMethod === 'QRIS' }]" @click="paymentMethod = 'QRIS'">📱
                QRIS</button>
              <button :class="['pay-btn', { active: paymentMethod === 'Transfer' }]" @click="paymentMethod = 'Transfer'">🏦
                Transfer</button>
            </div>
            <div v-if="paymentMethod === 'Cash'" class="cash-section">
              <label class="pay-label">Jumlah Bayar</label>
              <div class="cash-input"><span>Rp</span><input v-model.number="paymentAmount" type="number" min="0"
                  placeholder="0" ref="cashInput" /></div>
              <div class="quick-cash"><button v-for="q in quickCash" :key="q" @click="paymentAmount = q">{{ fmtShort(q)
                  }}</button></div>
              <div v-if="change !== null" :class="['change-display', { negative: change < 0 }]">Kembalian: <strong>{{
                  fmt(change) }}</strong></div>
            </div>
          </div>
          <button class="checkout-btn" :disabled="!canCheckout || checkoutForm.processing" @click="doCheckout">
            {{ checkoutForm.processing ? 'Memproses...' : 'Bayar Sekarang (Enter)' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
const props = defineProps({ products: Array, categories: Array, memberSettings: Object });
const channel = ref('Offline');
const search = ref('');
const filterCat = ref('');
const cart = ref([]);
const discount = ref(0);
const paymentMethod = ref('Cash');
const paymentAmount = ref(null);
const searchInput = ref(null);
const cashInput = ref(null);
const checkoutForm = useForm({});
// Member state
const custPhone = ref('');
const custData = ref(null);
const custIsBirthday = ref(false);
const custName = ref('');
const custBirth = ref('');
const custLoading = ref(false);
const custSearched = ref(false);
const usePoints = ref(false);
const pointsToUse = ref(0);
let custTimer = null;
function searchCustomer() {
  custSearched.value = false;
  custData.value = null;
  custIsBirthday.value = false;
  clearTimeout(custTimer);
  if (custPhone.value.length < 4) return;
  custTimer = setTimeout(async () => {
    custLoading.value = true;
    try {
      const r = await fetch(`/api/customers/search?phone=${encodeURIComponent(custPhone.value)}`);
      const d = await r.json();
      custData.value = d.customer;
      custIsBirthday.value = d.isBirthday || false;
      custSearched.value = true;
      if (d.customer) { custName.value = d.customer.name; }
    } catch (e) { console.error(e); }
    custLoading.value = false;
  }, 500);
}
const filteredProducts = computed(() => {
  let list = props.products;
  if (search.value) { const q = search.value.toLowerCase(); list = list.filter(p => p.name.toLowerCase().includes(q) || (p.barcode && p.barcode.includes(q))); }
  if (filterCat.value) list = list.filter(p => p.category === filterCat.value);
  return list;
});
function getPrice(p) { return channel.value === 'Shopee' ? parseFloat(p.price_shopee) : parseFloat(p.price_offline); }
const subtotal = computed(() => cart.value.reduce((s, i) => s + getPrice(i.product) * i.quantity, 0));
const memberDiscountAmt = computed(() => {
  if (!custData.value || !props.memberSettings.member_discount_enabled) return 0;
  if (custIsBirthday.value) return 0; // birthday takes priority if higher
  return Math.floor(subtotal.value * props.memberSettings.member_discount_percent / 100);
});
const birthdayDiscountAmt = computed(() => {
  if (!custData.value || !custIsBirthday.value || !props.memberSettings.birthday_enabled) return 0;
  const bd = Math.floor(subtotal.value * props.memberSettings.birthday_discount_percent / 100);
  const md = Math.floor(subtotal.value * props.memberSettings.member_discount_percent / 100);
  return bd >= md ? bd : 0; // only if birthday > member
});
const autoDiscount = computed(() => birthdayDiscountAmt.value > 0 ? birthdayDiscountAmt.value : memberDiscountAmt.value);
const pointDiscountAmt = computed(() => {
  if (!usePoints.value || pointsToUse.value <= 0) return 0;
  return pointsToUse.value * props.memberSettings.point_redeem_value;
});
const total = computed(() => Math.max(0, subtotal.value - (discount.value || 0) - autoDiscount.value - pointDiscountAmt.value));
const change = computed(() => paymentAmount.value ? paymentAmount.value - total.value : null);
const earnPreview = computed(() => {
  if (!custData.value) return 0;
  const ea = props.memberSettings.point_earn_amount;
  return ea > 0 ? Math.floor(total.value / ea) * props.memberSettings.point_earn_value : 0;
});
const quickCash = computed(() => {
  const t = total.value; if (t <= 0) return [];
  const vals = [t, Math.ceil(t / 5000) * 5000, Math.ceil(t / 10000) * 10000, Math.ceil(t / 50000) * 50000, Math.ceil(t / 100000) * 100000];
  return [...new Set(vals)].sort((a, b) => a - b).slice(0, 5);
});
const canCheckout = computed(() => {
  if (cart.value.length === 0) return false;
  if (paymentMethod.value === 'Cash' && (!paymentAmount.value || paymentAmount.value < total.value)) return false;
  return true;
});
let barcodeBuffer = ''; let barcodeTimer = null;
function handleGlobalKeydown(e) {
  if (e.key === 'F2') { e.preventDefault(); searchInput.value?.focus(); return; }
  if (e.key === 'Escape') { clearCart(); searchInput.value?.focus(); return; }
  const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
  if (e.key === 'Enter') {
    if (barcodeBuffer.length >= 8 && /^\d+$/.test(barcodeBuffer)) {
      e.preventDefault();
      const found = props.products.find(p => p.barcode === barcodeBuffer);
      if (found) { addToCart(found); search.value = ''; }
      barcodeBuffer = ''; clearTimeout(barcodeTimer); return;
    }
    barcodeBuffer = '';
    if (!isInput && canCheckout.value && !checkoutForm.processing) { e.preventDefault(); doCheckout(); }
    return;
  }
  if (/^\d$/.test(e.key)) { barcodeBuffer += e.key; clearTimeout(barcodeTimer); barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 80); }
  else if (e.key.length === 1) { barcodeBuffer = ''; clearTimeout(barcodeTimer); }
}
function onSearch() { clearTimeout(barcodeTimer); barcodeTimer = setTimeout(() => { const val = search.value.trim(); if (val.length >= 8 && /^\d+$/.test(val)) { const found = props.products.find(p => p.barcode === val); if (found) { addToCart(found); search.value = ''; } } }, 300); }
function addToCart(product) { const existing = cart.value.find(i => i.product.id === product.id); if (existing) { if (existing.quantity < product.stock) existing.quantity++; } else { cart.value.push({ product, quantity: 1 }); } }
function changeQty(index, delta) { const item = cart.value[index]; const nq = item.quantity + delta; if (nq <= 0) cart.value.splice(index, 1); else if (nq <= item.product.stock) item.quantity = nq; }
function removeItem(index) { cart.value.splice(index, 1); }
function clearCart() { cart.value = []; discount.value = 0; paymentAmount.value = null; custPhone.value = ''; custData.value = null; custName.value = ''; custBirth.value = ''; usePoints.value = false; pointsToUse.value = 0; custSearched.value = false; }
function doCheckout() {
  if (!canCheckout.value) return;
  checkoutForm.transform(() => ({
    channel: channel.value,
    items: cart.value.map(i => ({ product_id: i.product.id, quantity: i.quantity })),
    discount: discount.value || 0,
    payment_method: paymentMethod.value,
    payment_amount: paymentMethod.value === 'Cash' ? paymentAmount.value : total.value,
    notes: null,
    customer_phone: custPhone.value || null,
    customer_name: custName.value || null,
    customer_birth_date: custBirth.value || null,
    points_used: usePoints.value ? pointsToUse.value : 0,
  })).post('/pos/checkout', { onSuccess: () => clearCart() });
}
function fmt(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); }
function fmtShort(v) { if (v >= 1e6) return `${v / 1e6}jt`; if (v >= 1e3) return `${v / 1e3}rb`; return v; }
onMounted(() => { document.addEventListener('keydown', handleGlobalKeydown); searchInput.value?.focus(); });
onUnmounted(() => document.removeEventListener('keydown', handleGlobalKeydown));
</script>
<style scoped>
.pos-layout {
  display: flex;
  gap: 1rem;
  height: calc(100vh - 96px)
}

.pos-products {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden
}

.pos-toolbar {
  display: flex;
  gap: .5rem;
  margin-bottom: .75rem;
  flex-wrap: wrap;
  align-items: center
}

.channel-switch {
  display: flex;
  gap: .25rem;
  background: var(--c-surface);
  border-radius: 10px;
  padding: 3px
}

.ch-btn {
  padding: .4rem .8rem;
  border: none;
  border-radius: 8px;
  font-size: .8rem;
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  color: var(--c-text-dim);
  transition: all .2s
}

.ch-btn.active {
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: #fff
}

.search-box {
  flex: 1;
  min-width: 200px
}

.search-box input {
  width: 100%;
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 10px;
  padding: .5rem .7rem;
  color: var(--c-text);
  font-size: .8rem;
  outline: none
}

.search-box input:focus {
  border-color: rgba(168, 85, 247, .4)
}

.search-box input::placeholder {
  color: var(--c-text-faint)
}

.cat-filter {
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 10px;
  padding: .5rem .6rem;
  color: var(--c-text);
  font-size: .8rem;
  outline: none;
  cursor: pointer
}

.product-grid {
  flex: 1;
  overflow-y: auto;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: .6rem;
  align-content: start;
  padding-bottom: 1rem
}

.p-card {
  background: var(--c-input-bg);
  border: 1px solid var(--c-border);
  border-radius: 12px;
  cursor: pointer;
  transition: all .2s;
  overflow: hidden
}

.p-card:hover {
  border-color: rgba(168, 85, 247, .3);
  transform: translateY(-2px)
}

.p-card:active {
  transform: scale(.97)
}

.p-img {
  height: 80px;
  background: var(--c-surface);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden
}

.p-img img {
  width: 100%;
  height: 100%;
  object-fit: cover
}

.p-img-ph {
  font-size: 1.5rem
}

.p-info {
  padding: .5rem .6rem;
  display: flex;
  flex-direction: column;
  gap: .15rem;
  min-height: 52px
}

.p-name {
  font-size: .78rem;
  font-weight: 600;
  color: var(--c-text);
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden
}

.p-price {
  font-size: .8rem;
  font-weight: 700;
  color: #c084fc
}

.p-stock {
  font-size: .65rem;
  color: var(--c-text-faint)
}

.empty-grid {
  grid-column: 1/-1;
  text-align: center;
  padding: 3rem;
  color: var(--c-text-faint)
}

.pos-cart {
  width: 360px;
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  overflow: hidden
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: .875rem 1rem;
  border-bottom: 1px solid var(--c-border)
}

.cart-header h3 {
  margin: 0;
  font-size: .95rem;
  font-weight: 600;
  color: var(--c-text-white)
}

.clear-btn {
  background: none;
  border: none;
  color: #f87171;
  font-size: .75rem;
  cursor: pointer;
  font-weight: 500
}

/* Member */
.member-section {
  padding: .6rem .8rem;
  border-bottom: 1px solid var(--c-border);
  background: rgba(168, 85, 247, .03)
}

.member-input {
  display: flex;
  align-items: center;
  gap: .4rem
}

.member-input input {
  flex: 1;
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 8px;
  padding: .4rem .6rem;
  color: var(--c-text);
  font-size: .8rem;
  outline: none
}

.member-input input:focus {
  border-color: rgba(168, 85, 247, .4)
}

.ml {
  font-size: .7rem
}

.member-card {
  margin-top: .4rem;
  background: rgba(168, 85, 247, .08);
  border: 1px solid rgba(168, 85, 247, .15);
  border-radius: 8px;
  padding: .45rem .6rem
}

.mc-name {
  font-size: .82rem;
  font-weight: 600;
  color: var(--c-text)
}

.bday {
  font-size: .72rem;
  background: rgba(251, 191, 36, .15);
  color: #fbbf24;
  padding: .1rem .4rem;
  border-radius: 4px
}

.mc-info {
  font-size: .75rem;
  color: var(--c-text-dim);
  margin-top: .15rem
}

.mc-info strong {
  color: #c084fc
}

.register-form {
  margin-top: .4rem
}

.rf-label {
  font-size: .72rem;
  color: var(--c-text-dim);
  margin: 0 0 .3rem
}

.rf-input {
  width: 100%;
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 8px;
  padding: .35rem .5rem;
  color: var(--c-text);
  font-size: .78rem;
  outline: none;
  margin-bottom: .3rem
}

/* Cart */
.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: .5rem
}

.cart-empty {
  text-align: center;
  padding: 2rem 1rem;
  color: var(--c-text-faint)
}

.cart-empty p {
  margin: 0 0 .25rem;
  font-size: .875rem
}

.cart-empty span {
  font-size: .75rem
}

.cart-item {
  background: var(--c-surface);
  border-radius: 10px;
  padding: .6rem .7rem;
  margin-bottom: .35rem
}

.ci-info {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: .35rem
}

.ci-name {
  font-size: .8rem;
  font-weight: 500;
  color: var(--c-text);
  flex: 1;
  margin-right: .5rem
}

.ci-price {
  font-size: .75rem;
  color: var(--c-text-dim);
  white-space: nowrap
}

.ci-controls {
  display: flex;
  align-items: center;
  gap: .35rem
}

.qty-btn {
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: 1px solid var(--c-border-hover);
  background: var(--c-surface);
  color: var(--c-text-muted);
  font-size: .9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s
}

.qty-btn:hover:not(:disabled) {
  background: rgba(168, 85, 247, .15);
  border-color: rgba(168, 85, 247, .3);
  color: #c084fc
}

.qty-btn:disabled {
  opacity: .3;
  cursor: not-allowed
}

.qty-val {
  font-size: .85rem;
  font-weight: 600;
  color: var(--c-text-white);
  min-width: 20px;
  text-align: center
}

.rm-btn {
  margin-left: auto;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: none;
  background: rgba(239, 68, 68, .1);
  color: #f87171;
  font-size: .7rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center
}

.ci-subtotal {
  text-align: right;
  font-size: .8rem;
  font-weight: 600;
  color: var(--c-text);
  margin-top: .25rem
}

/* Summary */
.cart-summary {
  border-top: 1px solid var(--c-border);
  padding: .75rem 1rem
}

.sum-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .825rem;
  color: var(--c-text-muted);
  margin-bottom: .4rem
}

.discount-row .disc-input {
  display: flex;
  align-items: center;
  background: var(--c-surface);
  border: 1px solid var(--c-input-border);
  border-radius: 8px;
  overflow: hidden
}

.disc-input span {
  padding: 0 0 0 .5rem;
  color: var(--c-text-faint);
  font-size: .75rem
}

.disc-input input {
  width: 80px;
  background: transparent;
  border: none;
  color: var(--c-text);
  font-size: .8rem;
  padding: .3rem .4rem;
  outline: none
}

.disc-line {
  color: #4ade80;
  font-size: .78rem
}

.disc-line span:last-child {
  color: #4ade80
}

.total-row {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--c-text-white);
  padding-top: .5rem;
  border-top: 1px solid var(--c-border);
  margin-top: .25rem
}

.earn-preview {
  font-size: .75rem;
  color: var(--c-text-dim);
  background: rgba(168, 85, 247, .06);
  border-radius: 6px;
  padding: .3rem .5rem;
  margin-bottom: .5rem;
  text-align: center
}

.earn-preview strong {
  color: #c084fc
}

.points-section {
  margin: .4rem 0;
  padding: .4rem .5rem;
  background: rgba(168, 85, 247, .04);
  border: 1px solid rgba(168, 85, 247, .1);
  border-radius: 8px
}

.pts-toggle {
  font-size: .78rem;
  color: var(--c-text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: .4rem
}

.pts-toggle input {
  accent-color: #a855f7
}

.pts-input {
  display: flex;
  align-items: center;
  gap: .4rem;
  margin-top: .3rem
}

.pts-input input {
  width: 80px;
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 6px;
  padding: .3rem .4rem;
  color: var(--c-text);
  font-size: .8rem;
  outline: none
}

.pts-val {
  font-size: .75rem;
  color: #4ade80
}

/* Payment */
.payment-section {
  margin-top: .75rem
}

.pay-label {
  font-size: .72rem;
  font-weight: 600;
  color: var(--c-text-dim);
  text-transform: uppercase;
  letter-spacing: .04em;
  display: block;
  margin-bottom: .4rem
}

.pay-methods {
  display: flex;
  gap: .35rem;
  margin-bottom: .6rem
}

.pay-btn {
  flex: 1;
  padding: .45rem;
  border: 1px solid var(--c-input-border);
  border-radius: 8px;
  background: var(--c-surface);
  color: var(--c-text-dim);
  font-size: .72rem;
  font-weight: 500;
  cursor: pointer;
  transition: all .2s;
  text-align: center
}

.pay-btn.active {
  border-color: rgba(168, 85, 247, .4);
  background: rgba(168, 85, 247, .1);
  color: #c084fc
}

.cash-section {
  margin-bottom: .5rem
}

.cash-input {
  display: flex;
  align-items: center;
  background: var(--c-surface);
  border: 1px solid var(--c-input-border);
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: .4rem
}

.cash-input span {
  padding: 0 0 0 .6rem;
  color: var(--c-text-faint);
  font-size: .8rem
}

.cash-input input {
  flex: 1;
  background: transparent;
  border: none;
  color: var(--c-text);
  font-size: .875rem;
  padding: .45rem;
  outline: none
}

.quick-cash {
  display: flex;
  gap: .25rem;
  flex-wrap: wrap;
  margin-bottom: .4rem
}

.quick-cash button {
  padding: .25rem .5rem;
  border: 1px solid var(--c-input-border);
  border-radius: 6px;
  background: var(--c-surface);
  color: var(--c-text-muted);
  font-size: .7rem;
  cursor: pointer;
  transition: all .15s
}

.quick-cash button:hover {
  border-color: rgba(168, 85, 247, .3);
  color: #c084fc
}

.change-display {
  font-size: .85rem;
  color: #4ade80;
  font-weight: 600;
  text-align: center;
  padding: .4rem;
  background: rgba(34, 197, 94, .08);
  border-radius: 8px
}

.change-display.negative {
  color: #f87171;
  background: rgba(239, 68, 68, .08)
}

.checkout-btn {
  width: 100%;
  padding: .75rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: .9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .2s;
  margin-top: .5rem
}

.checkout-btn:hover:not(:disabled) {
  opacity: .9;
  box-shadow: 0 4px 20px rgba(168, 85, 247, .3)
}

.checkout-btn:disabled {
  opacity: .4;
  cursor: not-allowed
}

@media(max-width:768px) {
  .pos-layout {
    flex-direction: column;
    height: auto
  }

  .pos-cart {
    width: 100%
  }
}
</style>

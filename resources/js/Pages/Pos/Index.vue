<template>
  <AppLayout title="Kasir (POS)">
    <Head title="Kasir (POS)" />

    <div class="pos-layout">
      <!-- Left: Product Area -->
      <div class="pos-products">
        <!-- Channel & Search -->
        <div class="pos-toolbar">
          <div class="channel-switch">
            <button :class="['ch-btn', { active: channel === 'Offline' }]" @click="channel = 'Offline'">🏪 Offline</button>
            <button :class="['ch-btn', { active: channel === 'Shopee' }]" @click="channel = 'Shopee'">🛒 Shopee</button>
          </div>
          <div class="search-box">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="s-icon"><path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" /></svg>
            <input ref="searchInput" v-model="search" type="text" placeholder="Cari produk atau scan barcode... (F2)" @input="onSearch" />
          </div>
          <select v-model="filterCat" class="cat-filter">
            <option value="">Semua</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>

        <!-- Product Grid -->
        <div class="product-grid">
          <div v-for="p in filteredProducts" :key="p.id" class="p-card" @click="addToCart(p)">
            <div class="p-img">
              <img v-if="p.image_url" :src="p.image_url" :alt="p.name" />
              <div v-else class="p-img-ph">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v.75a.75.75 0 01-1.5 0v-.75a1.5 1.5 0 00-3 0v.75a.75.75 0 01-1.5 0v-.75z" clip-rule="evenodd" /></svg>
              </div>
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

      <!-- Right: Cart -->
      <div class="pos-cart">
        <div class="cart-header">
          <h3>🛒 Keranjang</h3>
          <button v-if="cart.length > 0" class="clear-btn" @click="clearCart">Hapus Semua</button>
        </div>

        <div class="cart-items">
          <div v-if="cart.length === 0" class="cart-empty">
            <p>Keranjang kosong</p>
            <span>Pilih produk untuk memulai</span>
          </div>
          <div v-for="(item, i) in cart" :key="item.product.id" class="cart-item">
            <div class="ci-info">
              <span class="ci-name">{{ item.product.name }}</span>
              <span class="ci-price">{{ fmt(getPrice(item.product)) }}</span>
            </div>
            <div class="ci-controls">
              <button class="qty-btn" @click="changeQty(i, -1)">−</button>
              <span class="qty-val">{{ item.quantity }}</span>
              <button class="qty-btn" @click="changeQty(i, 1)" :disabled="item.quantity >= item.product.stock">+</button>
              <button class="rm-btn" @click="removeItem(i)">✕</button>
            </div>
            <div class="ci-subtotal">{{ fmt(getPrice(item.product) * item.quantity) }}</div>
          </div>
        </div>

        <!-- Totals -->
        <div v-if="cart.length > 0" class="cart-summary">
          <div class="sum-row"><span>Subtotal</span><span>{{ fmt(subtotal) }}</span></div>
          <div class="sum-row discount-row">
            <span>Diskon</span>
            <div class="disc-input">
              <span>Rp</span>
              <input v-model.number="discount" type="number" min="0" placeholder="0" />
            </div>
          </div>
          <div class="sum-row total-row"><span>Total</span><span>{{ fmt(total) }}</span></div>

          <!-- Payment -->
          <div class="payment-section">
            <label class="pay-label">Metode Pembayaran</label>
            <div class="pay-methods">
              <button :class="['pay-btn', { active: paymentMethod === 'Cash' }]" @click="paymentMethod = 'Cash'">💵 Cash</button>
              <button :class="['pay-btn', { active: paymentMethod === 'QRIS' }]" @click="paymentMethod = 'QRIS'">📱 QRIS</button>
              <button :class="['pay-btn', { active: paymentMethod === 'Transfer' }]" @click="paymentMethod = 'Transfer'">🏦 Transfer</button>
            </div>

            <div v-if="paymentMethod === 'Cash'" class="cash-section">
              <label class="pay-label">Jumlah Bayar</label>
              <div class="cash-input">
                <span>Rp</span>
                <input v-model.number="paymentAmount" type="number" min="0" placeholder="0" ref="cashInput" />
              </div>
              <div class="quick-cash">
                <button v-for="q in quickCash" :key="q" @click="paymentAmount = q">{{ fmtShort(q) }}</button>
              </div>
              <div v-if="change !== null" :class="['change-display', { negative: change < 0 }]">
                Kembalian: <strong>{{ fmt(change) }}</strong>
              </div>
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

const props = defineProps({ products: Array, categories: Array });

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

const filteredProducts = computed(() => {
  let list = props.products;
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(p => p.name.toLowerCase().includes(q) || (p.barcode && p.barcode.includes(q)));
  }
  if (filterCat.value) list = list.filter(p => p.category === filterCat.value);
  return list;
});

function getPrice(p) {
  return channel.value === 'Shopee' ? parseFloat(p.price_shopee) : parseFloat(p.price_offline);
}

const subtotal = computed(() => cart.value.reduce((s, i) => s + getPrice(i.product) * i.quantity, 0));
const total = computed(() => Math.max(0, subtotal.value - (discount.value || 0)));
const change = computed(() => paymentAmount.value ? paymentAmount.value - total.value : null);

const quickCash = computed(() => {
  const t = total.value;
  if (t <= 0) return [];
  const vals = [t, Math.ceil(t / 5000) * 5000, Math.ceil(t / 10000) * 10000, Math.ceil(t / 50000) * 50000, Math.ceil(t / 100000) * 100000];
  return [...new Set(vals)].sort((a, b) => a - b).slice(0, 5);
});

const canCheckout = computed(() => {
  if (cart.value.length === 0) return false;
  if (paymentMethod.value === 'Cash' && (!paymentAmount.value || paymentAmount.value < total.value)) return false;
  return true;
});

// --- Barcode Scanner (global listener) ---
// USB scanners type digits rapidly (<50ms between keys) then press Enter.
// We buffer rapid keystrokes and match barcode on Enter.
let barcodeBuffer = '';
let barcodeTimer = null;
const BARCODE_TIMEOUT = 80; // ms — max gap between scanner keystrokes

function handleGlobalKeydown(e) {
  // Keyboard shortcuts
  if (e.key === 'F2') { e.preventDefault(); searchInput.value?.focus(); return; }
  if (e.key === 'Escape') { clearCart(); searchInput.value?.focus(); return; }

  // Don't intercept if user is typing in an input (except search for barcode)
  const tag = e.target.tagName;
  const isInput = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

  // Enter key: checkout OR barcode submit
  if (e.key === 'Enter') {
    // Check barcode buffer first
    if (barcodeBuffer.length >= 8 && /^\d+$/.test(barcodeBuffer)) {
      e.preventDefault();
      const found = props.products.find(p => p.barcode === barcodeBuffer);
      if (found) {
        addToCart(found);
        search.value = '';
        if (searchInput.value) searchInput.value.value = '';
      }
      barcodeBuffer = '';
      clearTimeout(barcodeTimer);
      return;
    }
    barcodeBuffer = '';
    // Checkout shortcut (only if not in an input)
    if (!isInput && canCheckout.value && !checkoutForm.processing) {
      e.preventDefault();
      doCheckout();
    }
    return;
  }

  // Buffer digit keys for barcode scanner detection
  if (/^\d$/.test(e.key)) {
    barcodeBuffer += e.key;
    clearTimeout(barcodeTimer);
    barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, BARCODE_TIMEOUT);
  } else if (e.key.length === 1) {
    // Non-digit character typed — reset buffer (user is typing normally)
    barcodeBuffer = '';
    clearTimeout(barcodeTimer);
  }
}

function onSearch() {
  clearTimeout(barcodeTimer);
  barcodeTimer = setTimeout(() => {
    const val = search.value.trim();
    if (val.length >= 8 && /^\d+$/.test(val)) {
      const found = props.products.find(p => p.barcode === val);
      if (found) { addToCart(found); search.value = ''; }
    }
  }, 300);
}

function addToCart(product) {
  const existing = cart.value.find(i => i.product.id === product.id);
  if (existing) {
    if (existing.quantity < product.stock) existing.quantity++;
  } else {
    cart.value.push({ product, quantity: 1 });
  }
}

function changeQty(index, delta) {
  const item = cart.value[index];
  const newQty = item.quantity + delta;
  if (newQty <= 0) cart.value.splice(index, 1);
  else if (newQty <= item.product.stock) item.quantity = newQty;
}

function removeItem(index) { cart.value.splice(index, 1); }
function clearCart() { cart.value = []; discount.value = 0; paymentAmount.value = null; }

function doCheckout() {
  if (!canCheckout.value) return;
  checkoutForm.transform(() => ({
    channel: channel.value,
    items: cart.value.map(i => ({ product_id: i.product.id, quantity: i.quantity })),
    discount: discount.value || 0,
    payment_method: paymentMethod.value,
    payment_amount: paymentMethod.value === 'Cash' ? paymentAmount.value : total.value,
    notes: null,
  })).post('/pos/checkout', {
    onSuccess: () => { clearCart(); },
  });
}

function fmt(v) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v);
}
function fmtShort(v) {
  if (v >= 1000000) return `${v / 1000000}jt`;
  if (v >= 1000) return `${v / 1000}rb`;
  return v;
}

onMounted(() => { document.addEventListener('keydown', handleGlobalKeydown); searchInput.value?.focus(); });
onUnmounted(() => document.removeEventListener('keydown', handleGlobalKeydown));
</script>

<style scoped>
.pos-layout { display: flex; gap: 1rem; height: calc(100vh - 96px); }

/* Products */
.pos-products { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.pos-toolbar { display: flex; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap; align-items: center; }
.channel-switch { display: flex; gap: 0.25rem; background: rgba(0,0,0,0.3); border-radius: 10px; padding: 3px; }
.ch-btn { padding: 0.4rem 0.8rem; border: none; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: transparent; color: #71717a; transition: all 0.2s; }
.ch-btn.active { background: linear-gradient(135deg, #a855f7, #ec4899); color: white; }
.search-box { flex: 1; min-width: 200px; position: relative; }
.s-icon { position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #52525b; }
.search-box input { width: 100%; background: rgba(24,24,27,0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 0.5rem 0.7rem 0.5rem 2.2rem; color: #e4e4e7; font-size: 0.8rem; outline: none; }
.search-box input:focus { border-color: rgba(168,85,247,0.4); }
.search-box input::placeholder { color: #3f3f46; }
.cat-filter { background: rgba(24,24,27,0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 0.5rem 0.6rem; color: #e4e4e7; font-size: 0.8rem; outline: none; cursor: pointer; }

/* Product Grid */
.product-grid { flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.6rem; align-content: start; padding-bottom: 1rem; }
.p-card { background: rgba(24,24,27,0.7); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; cursor: pointer; transition: all 0.2s; overflow: hidden; }
.p-card:hover { border-color: rgba(168,85,247,0.3); transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.3); }
.p-card:active { transform: scale(0.97); }
.p-img { height: 90px; background: rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.p-img img { width: 100%; height: 100%; object-fit: cover; }
.p-img-ph { color: #27272a; }
.p-info { padding: 0.5rem 0.6rem; display: flex; flex-direction: column; gap: 0.15rem; }
.p-name { font-size: 0.75rem; font-weight: 500; color: #d4d4d8; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.p-price { font-size: 0.8rem; font-weight: 700; color: #c084fc; }
.p-stock { font-size: 0.65rem; color: #52525b; }
.empty-grid { grid-column: 1 / -1; text-align: center; padding: 3rem; color: #52525b; }

/* Cart */
.pos-cart { width: 340px; background: linear-gradient(180deg, rgba(24,24,27,0.9), rgba(18,18,22,0.95)); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; }
.cart-header { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
.cart-header h3 { margin: 0; font-size: 0.95rem; font-weight: 600; color: white; }
.clear-btn { background: none; border: none; color: #f87171; font-size: 0.75rem; cursor: pointer; font-weight: 500; }
.clear-btn:hover { text-decoration: underline; }

.cart-items { flex: 1; overflow-y: auto; padding: 0.5rem; }
.cart-empty { text-align: center; padding: 2rem 1rem; color: #3f3f46; }
.cart-empty p { margin: 0 0 0.25rem; font-size: 0.875rem; color: #52525b; }
.cart-empty span { font-size: 0.75rem; }

.cart-item { background: rgba(255,255,255,0.03); border-radius: 10px; padding: 0.6rem 0.7rem; margin-bottom: 0.35rem; }
.ci-info { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem; }
.ci-name { font-size: 0.8rem; font-weight: 500; color: #d4d4d8; flex: 1; margin-right: 0.5rem; }
.ci-price { font-size: 0.75rem; color: #71717a; white-space: nowrap; }
.ci-controls { display: flex; align-items: center; gap: 0.35rem; }
.qty-btn { width: 26px; height: 26px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); color: #a1a1aa; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
.qty-btn:hover:not(:disabled) { background: rgba(168,85,247,0.15); border-color: rgba(168,85,247,0.3); color: #c084fc; }
.qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.qty-val { font-size: 0.85rem; font-weight: 600; color: white; min-width: 20px; text-align: center; }
.rm-btn { margin-left: auto; width: 26px; height: 26px; border-radius: 6px; border: none; background: rgba(239,68,68,0.1); color: #f87171; font-size: 0.7rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.rm-btn:hover { background: rgba(239,68,68,0.2); }
.ci-subtotal { text-align: right; font-size: 0.8rem; font-weight: 600; color: #e4e4e7; margin-top: 0.25rem; }

/* Summary */
.cart-summary { border-top: 1px solid rgba(255,255,255,0.06); padding: 0.75rem 1rem; }
.sum-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.825rem; color: #a1a1aa; margin-bottom: 0.4rem; }
.discount-row .disc-input { display: flex; align-items: center; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; overflow: hidden; }
.disc-input span { padding: 0 0 0 0.5rem; color: #52525b; font-size: 0.75rem; }
.disc-input input { width: 80px; background: transparent; border: none; color: #e4e4e7; font-size: 0.8rem; padding: 0.3rem 0.4rem; outline: none; }
.total-row { font-size: 1.05rem; font-weight: 700; color: white; padding-top: 0.5rem; border-top: 1px solid rgba(255,255,255,0.06); margin-top: 0.25rem; }

/* Payment */
.payment-section { margin-top: 0.75rem; }
.pay-label { font-size: 0.72rem; font-weight: 600; color: #71717a; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.4rem; }
.pay-methods { display: flex; gap: 0.35rem; margin-bottom: 0.6rem; }
.pay-btn { flex: 1; padding: 0.45rem; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; background: rgba(0,0,0,0.2); color: #71717a; font-size: 0.72rem; font-weight: 500; cursor: pointer; transition: all 0.2s; text-align: center; }
.pay-btn.active { border-color: rgba(168,85,247,0.4); background: rgba(168,85,247,0.1); color: #c084fc; }
.cash-section { margin-bottom: 0.5rem; }
.cash-input { display: flex; align-items: center; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; overflow: hidden; margin-bottom: 0.4rem; }
.cash-input span { padding: 0 0 0 0.6rem; color: #52525b; font-size: 0.8rem; }
.cash-input input { flex: 1; background: transparent; border: none; color: #e4e4e7; font-size: 0.875rem; padding: 0.45rem; outline: none; }
.quick-cash { display: flex; gap: 0.25rem; flex-wrap: wrap; margin-bottom: 0.4rem; }
.quick-cash button { padding: 0.25rem 0.5rem; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; background: rgba(255,255,255,0.03); color: #a1a1aa; font-size: 0.7rem; cursor: pointer; transition: all 0.15s; }
.quick-cash button:hover { border-color: rgba(168,85,247,0.3); color: #c084fc; }
.change-display { font-size: 0.85rem; color: #4ade80; font-weight: 600; text-align: center; padding: 0.4rem; background: rgba(34,197,94,0.08); border-radius: 8px; }
.change-display.negative { color: #f87171; background: rgba(239,68,68,0.08); }

.checkout-btn { width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #a855f7, #ec4899); color: white; border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.2s; margin-top: 0.5rem; }
.checkout-btn:hover:not(:disabled) { opacity: 0.9; box-shadow: 0 4px 20px rgba(168,85,247,0.3); }
.checkout-btn:disabled { opacity: 0.4; cursor: not-allowed; }

@media (max-width: 768px) {
  .pos-layout { flex-direction: column; height: auto; }
  .pos-cart { width: 100%; }
  .product-grid { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); }
}
</style>

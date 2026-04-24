# Purchase & Stock Management - Detailed Documentation

## Overview

Fitur **Purchase & Stock Management** memungkinkan pemilik toko untuk:
- 📦 Mencatat pembelian stok dari supplier
- 📊 Tracking HPP (Harga Pokok Penjualan) yang akurat
- 🔄 Auto-update inventory setelah pembelian
- 📝 Manajemen data supplier
- ⚖️ Stock adjustment untuk koreksi manual
- 📈 Audit trail lengkap pergerakan stok

---

## 1. Purchase Order (Pembelian Stok)

### Use Case
Pemilik toko membeli stok kosmetik dari distributor (misal: PT Wardah, PT Paragon, dll).

### Flow Chart

```
Start
  ↓
Buka halaman "Pembelian"
  ↓
Klik "Buat Pembelian Baru"
  ↓
Pilih/Input Supplier
  ↓
Tambah Produk (scan/pilih)
  ↓
Input Quantity & Harga Beli
  ↓
Sistem Auto-Calculate Total
  ↓
(Opsional) Input No. Invoice
  ↓
Save Purchase
  ↓
Sistem Update:
  - Stok produk (+quantity)
  - HPP produk (weighted average)
  - Stock movement log
  ↓
End
```

### Form Fields

#### Header Purchase
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| Tanggal | Date | Yes | Tanggal pembelian (default: hari ini) |
| Supplier | Dropdown/Autocomplete | Yes | Pilih dari list supplier atau add new |
| No. Invoice | Text | No | Nomor invoice dari supplier |
| Catatan | Textarea | No | Notes tambahan |

#### Purchase Items (Detail)
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| Produk | Autocomplete/Barcode | Yes | Pilih produk atau scan barcode |
| Quantity | Number | Yes | Jumlah yang dibeli |
| Harga Beli | Number | Yes | Harga beli per unit (Rp) |
| Subtotal | Number (readonly) | - | Auto-calculate: qty × harga beli |

**Grand Total:** Auto-sum semua subtotal

---

## 2. Supplier Management

### Use Case
Mengelola data supplier/distributor tempat beli stok.

### CRUD Operations

#### Create Supplier
**Form Fields:**
- Nama Supplier (required)
- Nomor Telepon/WA (required)
- Email (optional)
- Alamat (optional)

#### Read/List Suppliers
**Display:**
- Tabel list supplier
- Search by nama
- Kolom: Nama, Kontak, Total Pembelian (lifetime)
- Action: Edit, Delete

#### Update Supplier
Edit informasi supplier yang sudah ada.

#### Delete Supplier
- Soft delete (tidak benar-benar hapus data)
- Supplier dengan history pembelian tidak bisa dihapus
- Show warning jika ada relasi data

---

## 3. Stock Adjustment

### Use Case
Koreksi stok manual untuk kasus:
- Barang rusak/pecah
- Barang kadaluarsa
- Stock opname (selisih fisik vs sistem)
- Barang hilang
- Return ke supplier

### Flow Chart

```
Start
  ↓
Buka halaman "Produk"
  ↓
Pilih produk yang mau adjust
  ↓
Klik "Adjust Stok"
  ↓
Pilih Tipe Adjustment:
  - Tambah Stok (adjustment_in)
  - Kurangi Stok (adjustment_out)
  ↓
Input Quantity
  ↓
Input Alasan (wajib)
  ↓
Konfirmasi
  ↓
Sistem Update:
  - Stok produk
  - Stock movement log
  ↓
End
```

### Form Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| Produk | Display only | - | Nama produk yang dipilih |
| Stok Saat Ini | Display only | - | Stok sebelum adjustment |
| Tipe | Radio/Dropdown | Yes | "Tambah" atau "Kurangi" |
| Quantity | Number | Yes | Jumlah perubahan |
| Alasan | Textarea | Yes | Wajib diisi (misal: "Barang rusak", "Stock opname") |
| Stok Setelah | Display only | - | Preview stok setelah adjustment |

---

## 4. HPP Calculation (Weighted Average)

### Formula

```
New HPP = ((Old Stock × Old HPP) + (New Purchase Qty × New Cost Price)) / (Old Stock + New Purchase Qty)
```

### Contoh Kasus

**Kondisi Awal:**
- Produk: Wardah Lip Matte
- Stok: 10 pcs
- HPP saat ini: Rp 25,000

**Pembelian Baru:**
- Quantity: 20 pcs
- Harga beli: Rp 27,000

**Kalkulasi HPP Baru:**
```
New HPP = ((10 × 25,000) + (20 × 27,000)) / (10 + 20)
        = (250,000 + 540,000) / 30
        = 790,000 / 30
        = Rp 26,333
```

**Hasil:**
- Stok baru: 30 pcs
- HPP baru: Rp 26,333

### Alternative: FIFO (First In First Out)

Untuk produk dengan expired date, bisa pakai FIFO:
- Track batch/lot number
- Setiap batch punya HPP sendiri
- Pas jual, ambil dari batch paling lama
- (Fitur advanced - bisa jadi future enhancement)

---

## 5. Stock Movement Log (Audit Trail)

### Purpose
Tracking semua perubahan stok untuk:
- Transparansi
- Audit
- Troubleshooting discrepancy
- Analisis inventory

### Log Entry Structure

| Field | Description | Example |
|-------|-------------|---------|
| Product | Nama produk | Wardah Lip Matte |
| Type | Tipe movement | purchase / sale / adjustment_in / adjustment_out |
| Quantity | Perubahan (+/-) | +20 (pembelian), -5 (penjualan) |
| Stock Before | Stok sebelum | 10 |
| Stock After | Stok setelah | 30 |
| Reference | Link ke transaksi | PO#001, Sales#123, Manual Adj |
| Notes | Catatan | "Pembelian dari PT Wardah" |
| Date | Tanggal & waktu | 2026-04-24 14:30 |

### Movement Types

| Type | Trigger | Stock Change | HPP Impact |
|------|---------|--------------|------------|
| **purchase** | Pembelian stok baru | + | Yes (recalculate) |
| **sale** | Transaksi penjualan | - | No |
| **adjustment_in** | Manual tambah stok | + | No (pakai HPP existing) |
| **adjustment_out** | Manual kurang stok | - | No |

---

## 6. UI/UX Mockup

### Halaman Purchase List

```
┌─────────────────────────────────────────────────────┐
│ 📦 Pembelian Stok                    [+ Buat Baru]  │
├─────────────────────────────────────────────────────┤
│                                                      │
│ Filter: [Date Range] [Supplier: Semua ▼]  [Cari..] │
│                                                      │
│ ┌──────────────────────────────────────────────┐   │
│ │ No.     Tanggal   Supplier      Total   [...]│   │
│ ├──────────────────────────────────────────────┤   │
│ │ PO#003  24/04/26  PT Wardah    1.5jt   [👁️]│   │
│ │ PO#002  23/04/26  CV Kosmetik  800rb   [👁️]│   │
│ │ PO#001  20/04/26  PT Paragon   2.3jt   [👁️]│   │
│ └──────────────────────────────────────────────┘   │
│                                                      │
│ Total Pembelian Bulan Ini: Rp 4,600,000            │
└─────────────────────────────────────────────────────┘
```

### Form Buat Purchase

```
┌─────────────────────────────────────────────────────┐
│ Buat Pembelian Baru                     [X]         │
├─────────────────────────────────────────────────────┤
│                                                      │
│ Tanggal: [24/04/2026]                               │
│ Supplier: [PT Wardah Kosmetik ▼] [+ Add New]       │
│ No. Invoice: [INV-2026-001234]                      │
│                                                      │
│ ── Items ──────────────────────────────────────     │
│                                                      │
│ [Scan Barcode / Cari Produk...]                     │
│                                                      │
│ ┌──────────────────────────────────────────────┐   │
│ │ Produk         Qty    Harga     Subtotal     │   │
│ ├──────────────────────────────────────────────┤   │
│ │ Wardah Lip     20    27,000     540,000  [X] │   │
│ │ Cushion Stay   10    60,000     600,000  [X] │   │
│ │ [+ Tambah Produk]                            │   │
│ └──────────────────────────────────────────────┘   │
│                                                      │
│                          Grand Total: Rp 1,140,000  │
│                                                      │
│ Catatan: [...]                                      │
│                                                      │
│              [Batal]  [Simpan Purchase]             │
└─────────────────────────────────────────────────────┘
```

### Stock Adjustment Dialog

```
┌─────────────────────────────────────────────────────┐
│ Adjust Stok - Wardah Lip Matte              [X]     │
├─────────────────────────────────────────────────────┤
│                                                      │
│ Stok Saat Ini: 10 pcs                               │
│                                                      │
│ Tipe Adjustment:                                    │
│   ( ) Tambah Stok   (•) Kurangi Stok               │
│                                                      │
│ Quantity: [3]                                       │
│                                                      │
│ Alasan: [Barang rusak saat display]                │
│         ___________________________________         │
│                                                      │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━        │
│ Stok Setelah Adjustment: 7 pcs                     │
│                                                      │
│              [Batal]  [Konfirmasi]                  │
└─────────────────────────────────────────────────────┘
```

---

## 7. Reports & Analytics

### Purchase Report
**Metrics:**
- Total pembelian per periode
- Top suppliers by volume/value
- Average purchase frequency
- Purchase trend (chart)

### Inventory Report
**Metrics:**
- Current stock value (qty × HPP)
- Stock turnover ratio
- Dead stock (produk tidak laku lama)
- Stock movement history

### HPP Analysis
**Metrics:**
- HPP changes over time per product
- Cost variance analysis
- Margin analysis (selling price - HPP)

---

## 8. Validations & Business Rules

### Purchase Order
- ✅ Supplier harus dipilih
- ✅ Minimal 1 produk dalam purchase
- ✅ Quantity harus > 0
- ✅ Harga beli harus > 0
- ✅ Tanggal tidak boleh masa depan
- ⚠️ Warning jika harga beli jauh berbeda dari HPP existing

### Stock Adjustment
- ✅ Alasan wajib diisi (min 10 karakter)
- ✅ Quantity adjustment tidak boleh 0
- ✅ Adjustment_out tidak boleh > stok saat ini
- ⚠️ Konfirmasi jika adjustment > 50% dari stok

### Supplier
- ✅ Nama supplier unique
- ✅ Nomor kontak format valid (Indonesia)
- ❌ Supplier dengan purchase history tidak bisa dihapus

---

## 9. Error Handling

### Common Errors

**Error:** Insufficient stock for adjustment_out
```
❌ Gagal: Stok tidak cukup
   Stok saat ini: 5 pcs
   Yang mau dikurangi: 10 pcs
   
   [OK]
```

**Error:** Duplicate supplier name
```
❌ Supplier sudah ada
   Nama "PT Wardah Kosmetik" sudah terdaftar.
   
   [OK]
```

**Error:** Invalid price
```
❌ Harga tidak valid
   Harga beli harus lebih dari Rp 0
   
   [OK]
```

---

## 10. Data Migration & Import

### Bulk Import Purchases (CSV)

**Format CSV:**
```csv
tanggal,supplier,invoice,produk,quantity,harga_beli
2026-04-20,PT Wardah,INV001,Wardah Lip Matte,20,27000
2026-04-20,PT Wardah,INV001,Wardah Cushion,10,60000
2026-04-21,CV Kosmetik,INV002,Maybelline Fit Me,15,45000
```

**Import Process:**
1. Upload CSV
2. Sistem validasi format
3. Preview data yang akan diimport
4. User konfirmasi
5. Batch insert ke database
6. Update stok & HPP otomatis

---

## 11. Future Enhancements

### Phase 2 Ideas
- 🔄 Return to supplier (retur barang)
- 📅 Scheduled/recurring purchase orders
- 🔔 Auto reorder notification (reorder point)
- 💰 Hutang/piutang supplier tracking
- 📊 Purchase forecasting based on sales
- 🏷️ Batch/lot tracking untuk expired date
- 📦 Multi-warehouse support

---

## 12. Testing Checklist

### Functional Testing
- [ ] Create purchase dengan 1 produk
- [ ] Create purchase dengan multiple produk
- [ ] Edit purchase sebelum disimpan
- [ ] Delete item dari purchase
- [ ] Verify stok bertambah setelah save
- [ ] Verify HPP terupdate dengan benar
- [ ] Create supplier baru
- [ ] Edit supplier data
- [ ] Stock adjustment (tambah)
- [ ] Stock adjustment (kurangi)
- [ ] View purchase history
- [ ] Filter purchase by date range
- [ ] Filter purchase by supplier
- [ ] Export purchase report
- [ ] View stock movement log

### Edge Cases
- [ ] Purchase dengan quantity 0 (should fail)
- [ ] Purchase dengan harga beli 0 (should fail)
- [ ] Adjustment out > stok (should fail)
- [ ] Delete supplier yang punya purchase (should fail)
- [ ] Import CSV dengan format salah
- [ ] Import CSV dengan produk tidak ada

### Performance
- [ ] Load 1000+ purchases
- [ ] Load stock movement dengan 10,000+ entries
- [ ] Filter & search performance
- [ ] Export large dataset

---

**Dokumentasi ini dapat berkembang sesuai feedback dan kebutuhan bisnis.**

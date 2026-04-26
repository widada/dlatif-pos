# PRD: Membership & Point System
## Feature Add-on untuk POS Toko Kosmetik & Aksesoris

---

**Versi:** 1.1
**Tanggal:** 25 April 2026
**Status:** Draft
**Scope:** Membership & Point Reward System + Receipt Format

---

## 1. Overview

Fitur tambahan untuk sistem POS yang sudah ada, berupa **program membership** dan **poin reward** untuk membangun loyalitas customer toko offline.

### 1.1 Tujuan
- Membangun loyalitas customer setia
- Mendorong customer balik belanja secara berkala
- Mendapatkan data customer (no. HP) untuk komunikasi/marketing
- Memberikan benefit yang menarik untuk customer member

### 1.2 Scope
✅ **In-Scope:**
- Customer/Member management
- Point earning system
- Point redemption (sebagai diskon)
- Member discount (diskon otomatis)
- Birthday treat
- Configurable settings via admin panel
- Receipt/struk format dengan info member & poin

❌ **Out-of-Scope:**
- Tier membership (Bronze/Silver/Gold)
- WhatsApp/SMS notification
- Reward catalog (tukar poin dengan produk)
- Referral system

---

## 2. User Stories

### 2.1 Sebagai Pemilik Toko
- Saya ingin customer setia balik belanja terus, jadi saya butuh sistem loyalty
- Saya ingin bisa atur sendiri rate poin & diskon tanpa minta tolong developer
- Saya ingin tahu siapa customer paling sering belanja
- Saya ingin track riwayat poin customer untuk transparansi

### 2.2 Sebagai Customer
- Saya ingin dapat reward kalau sering belanja di toko ini
- Saya ingin tahu berapa poin yang saya punya
- Saya ingin bisa pakai poin untuk dapat diskon
- Saya ingin dapat treat khusus di hari ulang tahun saya

---

## 3. Core Features

### 3.1 Customer/Member Management

#### Description
Sistem manajemen data customer yang otomatis terdaftar saat memberikan nomor HP di kasir.

#### Requirements

**Data yang Disimpan:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | String | Yes | Nama customer |
| phone | String | Yes | Nomor HP (unique, sebagai ID member) |
| birth_date | Date | No | Tanggal lahir (untuk birthday treat) |
| points | Integer | Auto | Saldo poin saat ini |
| total_spent | Decimal | Auto | Total belanja lifetime |
| total_transactions | Integer | Auto | Total jumlah transaksi |
| last_purchase_at | DateTime | Auto | Tanggal terakhir belanja |
| last_birthday_used_at | DateTime | Auto | Terakhir pakai birthday discount |
| notes | Text | No | Catatan tambahan |

**Operations:**
- Auto-register saat customer kasih no. HP di kasir (quick form: nama + HP)
- CRUD manual via halaman Customer Management
- Search by nama atau no. HP
- View detail customer (info, history transaksi, history poin)
- Soft delete (tidak hapus permanen, untuk preserve history)

**Validations:**
- Nomor HP harus unique (1 nomor = 1 member)
- Format nomor HP: Indonesia (08xx atau +628xx)
- Nama wajib diisi minimal 2 karakter

---

### 3.2 Point Earning System

#### Description
Customer otomatis dapat poin setiap belanja di toko offline.

#### Requirements

**Earning Rules:**
- Poin didapat dari **total setelah diskon** (bukan dari subtotal)
- Hanya berlaku untuk channel **Offline** (Shopee tidak)
- Walk-in customer (tanpa member) tidak dapat poin
- Rate configurable (default: Rp 10,000 = 1 poin)

**Formula:**
```
Earned Points = floor(total_after_discount / earn_amount) * earn_value
```

**Contoh Kalkulasi:**
- Belanja Rp 50,000 → 5 poin
- Belanja Rp 99,500 → 9 poin (floor)
- Belanja Rp 150,000 → 15 poin

**Logging:**
- Setiap earning poin dicatat di `point_logs`
- Type: `earn`
- Linked ke transaction_id
- Tanggal expired di-calculate (jika expired aktif)

---

### 3.3 Point Redemption System

#### Description
Customer bisa pakai poin yang terkumpul sebagai diskon di transaksi berikutnya.

#### Requirements

**Redemption Rules:**
- Minimum redeem (configurable, default: 100 poin)
- 1 poin = nilai diskon configurable (default: Rp 100)
- Tidak bisa pakai poin lebih dari saldo
- Bisa dikombinasikan dengan diskon member & birthday discount
- Hanya berlaku di channel Offline

**Formula:**
```
Discount Amount = points_used * redeem_value
```

**Contoh:**
- 100 poin × Rp 100 = Rp 10,000 diskon
- 250 poin × Rp 100 = Rp 25,000 diskon
- 1000 poin × Rp 100 = Rp 100,000 diskon

**Validations:**
- `points_used <= customer.points`
- `points_used >= min_redeem_points`
- Total transaksi tidak boleh negatif setelah diskon poin

**Logging:**
- Type: `redeem`
- Poin berkurang setelah transaksi sukses
- Linked ke transaction_id

---

### 3.4 Member Benefits

#### Description
Benefit otomatis untuk customer yang sudah jadi member.

#### Benefit 1: Diskon Member
- Diskon otomatis setiap belanja (default: 5%)
- Persentase configurable
- Berlaku di channel offline (Shopee bisa toggle)
- Bisa di-set minimum belanja
- Apply BEFORE point redemption

#### Benefit 2: Poin Reward
- Sudah dijelaskan di section 3.2 & 3.3

#### Benefit 3: Birthday Treat
- Diskon spesial di periode ulang tahun (default: 20%)
- Periode configurable (default: 7 hari sebelum & sesudah)
- Maksimal pakai 1x per tahun
- Otomatis ter-apply saat customer member belanja di periode ultah
- Tidak bisa stack dengan diskon member (pilih yang lebih besar)

**Logic Birthday Period:**
```php
$validDays = 7; // configurable
$today = now();
$birthday = $customer->birth_date->setYear($today->year);

$start = $birthday->copy()->subDays($validDays);
$end = $birthday->copy()->addDays($validDays);

$isInBirthdayPeriod = $today->between($start, $end);
```

---

### 3.5 Settings & Configuration

#### Description
Halaman admin untuk mengatur konfigurasi sistem membership tanpa harus ubah kode.

#### Configurable Settings

**Member System:**
- `member_enabled` (boolean) - On/off member system
- `auto_register_on_phone` (boolean) - Auto register customer dari no. HP

**Point Earning:**
- `point_earn_amount` (integer) - Setiap belanja Rp X
- `point_earn_value` (integer) - Dapat Y poin
- `point_earn_channels` (json) - Channel yang dapat poin (default: ["offline"])

**Point Redemption:**
- `point_redeem_value` (integer) - 1 poin = Rp X
- `point_min_redeem` (integer) - Minimum poin untuk redeem
- `point_expired_enabled` (boolean) - Aktifkan expiry
- `point_expired_months` (integer) - Berapa bulan expired

**Member Discount:**
- `member_discount_enabled` (boolean) - Aktifkan diskon member
- `member_discount_percent` (integer) - Persentase diskon (0-100)
- `member_discount_channels` (json) - Channel berlaku (default: ["offline"])
- `member_discount_min_purchase` (integer) - Minimum belanja

**Birthday Treat:**
- `birthday_enabled` (boolean) - Aktifkan birthday discount
- `birthday_discount_percent` (integer) - Persentase diskon
- `birthday_valid_days` (integer) - Periode berlaku (hari sebelum/sesudah)
- `birthday_max_per_year` (integer) - Max pakai per tahun (default: 1)

#### UI Requirements
- Form dengan grouping yang jelas (Member, Poin, Diskon, Birthday)
- Preview perubahan sebelum save (e.g., "Belanja Rp 50,000 = 5 poin")
- Validasi input (range, type)
- Tombol "Reset Default"
- Audit log perubahan settings (siapa & kapan)

---

### 3.6 Receipt/Struk Format

#### Description
Format struk yang menampilkan informasi lengkap transaksi termasuk member info, breakdown diskon, dan informasi poin reward.

#### Receipt Sections

**Section 1: Header (Info Toko)**
- Logo toko (opsional)
- Nama toko
- Alamat
- Nomor telepon/WA
- Social media (Instagram/TikTok handle)

**Section 2: Info Transaksi**
- Nomor transaksi
- Tanggal & waktu
- Nama kasir
- Channel (Offline/Shopee)

**Section 3: Info Member** (kondisional, hanya jika ada customer)
- Nama member
- Nomor HP (di-mask: 0812-XXXX-7890)
- Birthday badge (jika sedang periode ulang tahun)

**Section 4: Detail Items**
- Nama produk
- Quantity x harga satuan
- Subtotal per item

**Section 5: Subtotal & Diskon Breakdown**
- Subtotal (sebelum diskon)
- Diskon Member (jika ada) - tampilkan persentase
- Diskon Birthday (jika ada) - tampilkan persentase
- Diskon Poin (jika ada) - tampilkan jumlah poin yang dipakai
- Diskon manual lain (jika ada)
- **TOTAL** (highlighted)

**Section 6: Pembayaran**
- Metode pembayaran
- Jumlah dibayar
- Kembalian (untuk Cash)

**Section 7: Info Poin Reward** (kondisional, hanya jika member)
- Poin sebelumnya (saldo sebelum transaksi)
- Poin dipakai (jika redeem)
- Poin diperoleh (dari transaksi ini)
- **Sisa Poin** (highlighted)

**Section 8: Footer**
- Pesan terima kasih
- Total hemat hari ini (highlighted, untuk customer satisfaction)
- Info kontak (WA, IG)
- Promo/info member (marketing)

#### Privacy Rules

| Field | Rule |
|-------|------|
| Nama customer | Tampilkan penuh |
| Nomor HP | Mask sebagian: `0812-XXXX-7890` |
| Birth date | Jangan tampilkan tanggal lengkap, cukup badge "🎂 Birthday" |
| HPP / margin | TIDAK PERNAH ditampilkan |
| Stock info | TIDAK ditampilkan |
| Catatan internal | TIDAK ditampilkan |

#### Receipt Format Settings (Configurable)

| Setting Key | Type | Default | Description |
|-------------|------|---------|-------------|
| `receipt_show_logo` | boolean | true | Tampilkan logo toko |
| `receipt_show_member_info` | boolean | true | Tampilkan section member |
| `receipt_show_point_info` | boolean | true | Tampilkan section poin |
| `receipt_show_savings` | boolean | true | Tampilkan total hemat |
| `receipt_show_promo_footer` | boolean | true | Tampilkan promo di footer |
| `receipt_header_text` | string | "Toko Kosmetik XYZ" | Custom nama toko |
| `receipt_address` | string | - | Alamat toko |
| `receipt_phone` | string | - | Nomor telepon |
| `receipt_social_media` | string | - | Instagram/TikTok handle |
| `receipt_footer_text` | string | "Terima kasih sudah belanja!" | Footer message |
| `receipt_promo_text` | string | - | Promo/marketing message |
| `receipt_paper_size` | enum | "80mm" | Lebar struk: 58mm, 80mm, atau digital |

#### Format Specifications

**Thermal Printer (58mm):**
- Lebar: 32 karakter
- Font: Monospace
- Hindari emoji (compatibility issue)

**Thermal Printer (80mm):**
- Lebar: 48 karakter
- Font: Monospace
- Emoji minimal (test compatibility)

**Digital Receipt (PDF/HTML):**
- Flexible layout
- Logo & warna brand
- QR code untuk reorder via WA
- Link clickable

#### Output Methods

1. **Print Langsung** - Via thermal printer (Bluetooth/USB)
2. **Preview di Layar** - Tampil di layar untuk dilihat dulu
3. **PDF Download** - Generate PDF untuk simpan/share
4. **Send via WhatsApp** - Kirim ke nomor customer (future enhancement)

#### Receipt Template Example

```
═══════════════════════════════════
        TOKO KOSMETIK XYZ
    Jl. Merdeka No. 123, Sragen
       Telp: 0812-3456-7890
        IG: @tokokosmetikxyz
═══════════════════════════════════

No. Transaksi : TRX-20260425-001
Tanggal       : 25 Apr 2026, 14:30
Kasir         : Owner
Channel       : Offline

─────────────────────────────────
🎉 MEMBER
─────────────────────────────────
Nama          : Bu Siti
No. HP        : 0812-XXXX-7890
🎂 SELAMAT ULANG TAHUN!

─────────────────────────────────
ITEM
─────────────────────────────────
Wardah Lip Matte
  2 x 50,000           100,000
Maybelline Fit Me
  1 x 95,000            95,000
─────────────────────────────────
Subtotal               195,000

🎂 Diskon Birthday(20%) -39,000
Diskon Poin (100 pts)  -10,000
─────────────────────────────────
TOTAL                  146,000
═════════════════════════════════

Bayar (Cash)           150,000
Kembali                  4,000

─────────────────────────────────
🎁 POIN REWARD
─────────────────────────────────
Poin sebelumnya     :     250
Poin dipakai        :    -100
Poin diperoleh      :     +14
─────────────────────────────────
SISA POIN           :     164
─────────────────────────────────

  Anda hemat Rp 49,000 hari ini! 🎉
   Terima kasih sudah belanja!

─────────────────────────────────
   Info & Reorder via WhatsApp
        0812-3456-7890
   IG: @tokokosmetikxyz
═════════════════════════════════
```

#### Validations & Logic

- Section Member hanya muncul kalau `transaction.customer_id != null`
- Section Poin hanya muncul kalau customer member DAN ada earning/redeem
- Birthday badge hanya muncul saat birthday discount ter-apply
- "Total hemat" = sum dari semua diskon (member + birthday + poin)
- Mask nomor HP: tampilkan 4 digit awal + 4 digit akhir, sisanya XXXX
- Tanggal format: "DD MMM YYYY, HH:mm" (e.g., "25 Apr 2026, 14:30")
- Currency format: "Rp X,XXX" (no decimal, comma separator)

#### Helper Function - Mask Phone Number

```php
function maskPhone($phone): string
{
    // Normalize: hapus +62 atau 62 di awal, ganti ke 0
    $phone = preg_replace('/^(\+?62|0)/', '0', $phone);

    if (strlen($phone) < 8) return $phone;

    // 0812-XXXX-7890
    $first = substr($phone, 0, 4);
    $last = substr($phone, -4);
    return $first . '-XXXX-' . $last;
}
```

#### Helper Function - Calculate Total Savings

```php
function calculateTotalSavings($transaction): float
{
    return $transaction->member_discount
         + $transaction->birthday_discount
         + $transaction->point_discount
         + $transaction->discount;
}
```

---

## 4. User Flows

### 4.1 Flow: Customer Baru Daftar Member

```
1. Customer datang ke kasir
2. Kasir scan barang & total Rp 100,000
3. Kasir tanya: "Ada nomor HP, Bu? Buat dapat poin"
4. Customer kasih: 0812-3456-7890
5. Kasir input no. HP di sistem
6. Sistem cek: belum terdaftar
7. Sistem tampilkan form quick register:
   - Nama: [Bu Siti]
   - No. HP: 0812-3456-7890 (auto-fill)
   - Tgl Lahir: [opsional]
8. Save → Customer otomatis jadi member
9. Lanjut transaksi normal dengan benefit member
```

### 4.2 Flow: Member Existing Belanja

```
1. Customer datang ke kasir
2. Kasir input no. HP: 0812-3456-7890
3. Sistem tampilkan: "Bu Siti - Poin: 250 - Member sejak Jan 2026"
4. Kasir scan produk (subtotal: Rp 100,000)
5. Sistem auto-apply:
   - Diskon Member 5%: -Rp 5,000
6. Kasir tanya: "Mau pakai poin? Punya 250 poin"
7. Customer: "Iya, pakai 100 poin"
8. Sistem hitung:
   - Diskon poin: 100 × Rp 100 = Rp 10,000
9. Total bayar: Rp 100,000 - Rp 5,000 - Rp 10,000 = Rp 85,000
10. Customer bayar Rp 85,000
11. Sistem update:
    - Poin terpakai: 100
    - Poin baru: floor(85,000/10,000) = 8 poin
    - Saldo akhir: 250 - 100 + 8 = 158 poin
12. Cetak struk dengan info poin
```

### 4.3 Flow: Member Belanja di Hari Ulang Tahun

```
1. Bu Siti belanja tanggal 14 April (ultah: 15 April)
2. Kasir input no. HP
3. Sistem detect: "🎂 Bu Siti ulang tahun! Berhak diskon 20%"
4. Subtotal: Rp 100,000
5. Sistem apply Birthday Discount: -Rp 20,000
   (Diskon member 5% TIDAK di-apply, karena birthday > member)
6. Total: Rp 80,000
7. Customer bayar
8. Sistem update last_birthday_used_at = today
9. Tahun ini Bu Siti gak bisa pakai birthday discount lagi
```

### 4.4 Flow: Walk-in Customer (Non-Member)

```
1. Customer datang
2. Kasir tanya: "Ada nomor HP?"
3. Customer: "Gak usah, buru-buru"
4. Kasir pilih "Walk-in Customer" / skip
5. Transaksi normal tanpa benefit member
6. Tidak ada poin, tidak ada diskon member
```

### 4.5 Flow: Admin Edit Settings

```
1. Owner buka halaman Settings → Member
2. Lihat current settings (e.g., diskon member 5%)
3. Edit: Diskon member dari 5% → 7%
4. Sistem tampilkan preview:
   "Belanja Rp 100,000 → diskon Rp 7,000 (sebelumnya Rp 5,000)"
5. Klik Simpan
6. Konfirmasi: "Yakin ubah?"
7. Save → Setting baru langsung berlaku
8. Audit log tercatat
```

---

## 5. Data Model

### 5.1 Customer

```sql
CREATE TABLE customers (
    id VARCHAR PRIMARY KEY,
    name VARCHAR NOT NULL,
    phone VARCHAR UNIQUE NOT NULL,
    birth_date DATE NULL,
    points INTEGER NOT NULL DEFAULT 0,
    total_spent DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_transactions INTEGER NOT NULL DEFAULT 0,
    last_purchase_at TIMESTAMP NULL,
    last_birthday_used_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_phone (phone),
    INDEX idx_name (name),
    INDEX idx_birth_date (birth_date)
);
```

### 5.2 PointLog

```sql
CREATE TABLE point_logs (
    id VARCHAR PRIMARY KEY,
    customer_id VARCHAR NOT NULL,
    transaction_id VARCHAR NULL,
    type ENUM('earn', 'redeem', 'expired', 'adjustment') NOT NULL,
    points INTEGER NOT NULL,
    balance_after INTEGER NOT NULL,
    notes TEXT NULL,
    expired_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,

    INDEX idx_customer (customer_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
);
```

### 5.3 Setting

```sql
CREATE TABLE settings (
    id VARCHAR PRIMARY KEY,
    `key` VARCHAR UNIQUE NOT NULL,
    value TEXT NOT NULL,
    type ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `group` VARCHAR NULL,
    label VARCHAR NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,

    INDEX idx_key (`key`),
    INDEX idx_group (`group`)
);
```

### 5.4 Update: Transaction Table

Tambah kolom-kolom berikut ke tabel `transactions` yang sudah ada:

```sql
ALTER TABLE transactions
    ADD COLUMN customer_id VARCHAR NULL AFTER channel,
    ADD COLUMN member_discount DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN birthday_discount DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN points_used INTEGER DEFAULT 0,
    ADD COLUMN point_discount DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN points_earned INTEGER DEFAULT 0,
    ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    ADD INDEX idx_customer (customer_id);
```

---

## 6. Business Logic

### 6.1 Diskon Calculation Order

Saat checkout, urutan apply diskon:

```
1. Subtotal (sum dari semua items)
2. Apply Member Discount ATAU Birthday Discount (pilih yang lebih besar)
3. Apply Point Redemption (dari subtotal yang sudah didiskon)
4. Final Total = Subtotal - All Discounts
5. Earn Points dari Final Total
```

**Contoh Kalkulasi:**
```
Subtotal:           Rp 100,000

Step 1 - Diskon Member/Birthday:
  Member discount:    Rp 5,000 (5%)
  Birthday discount:  Rp 20,000 (20%)
  Pilih yang besar:   Rp 20,000

Step 2 - Setelah diskon member/birthday:
  Subtotal:           Rp 80,000

Step 3 - Apply Point Redemption (100 poin):
  Point discount:     Rp 10,000

Step 4 - Final Total:
  Total bayar:        Rp 70,000

Step 5 - Earn Points:
  Earned:             floor(70,000 / 10,000) = 7 poin
```

### 6.2 Point Logic Helper

```php
// Earn points
public function earnPoints(Customer $customer, Transaction $transaction)
{
    $earnAmount = Setting::get('point_earn_amount', 10000);
    $earnValue = Setting::get('point_earn_value', 1);

    $earnedPoints = floor($transaction->total / $earnAmount) * $earnValue;

    // Update customer points
    $customer->increment('points', $earnedPoints);

    // Calculate expiry date
    $expiredAt = null;
    if (Setting::get('point_expired_enabled', false)) {
        $months = Setting::get('point_expired_months', 12);
        $expiredAt = now()->addMonths($months);
    }

    // Log
    PointLog::create([
        'customer_id' => $customer->id,
        'transaction_id' => $transaction->id,
        'type' => 'earn',
        'points' => $earnedPoints,
        'balance_after' => $customer->points,
        'notes' => "Earn dari transaksi {$transaction->transaction_number}",
        'expired_at' => $expiredAt,
    ]);

    return $earnedPoints;
}

// Redeem points
public function redeemPoints(Customer $customer, int $pointsUsed, Transaction $transaction)
{
    $minRedeem = Setting::get('point_min_redeem', 100);
    $redeemValue = Setting::get('point_redeem_value', 100);

    // Validations
    if ($pointsUsed < $minRedeem) {
        throw new Exception("Minimum redeem {$minRedeem} poin");
    }

    if ($pointsUsed > $customer->points) {
        throw new Exception("Poin tidak cukup");
    }

    // Calculate discount
    $discount = $pointsUsed * $redeemValue;

    // Deduct points
    $customer->decrement('points', $pointsUsed);

    // Log
    PointLog::create([
        'customer_id' => $customer->id,
        'transaction_id' => $transaction->id,
        'type' => 'redeem',
        'points' => -$pointsUsed,
        'balance_after' => $customer->points,
        'notes' => "Redeem {$pointsUsed} poin = Rp " . number_format($discount),
    ]);

    return $discount;
}

// Check birthday period
public function isBirthdayPeriod(Customer $customer): bool
{
    if (!$customer->birth_date) return false;
    if (!Setting::get('birthday_enabled', true)) return false;

    // Check kalau udah pakai tahun ini
    if ($customer->last_birthday_used_at) {
        $lastUsedYear = $customer->last_birthday_used_at->year;
        if ($lastUsedYear === now()->year) {
            return false;
        }
    }

    $validDays = Setting::get('birthday_valid_days', 7);
    $today = now();
    $birthday = $customer->birth_date->copy()->setYear($today->year);

    $start = $birthday->copy()->subDays($validDays);
    $end = $birthday->copy()->addDays($validDays);

    return $today->between($start, $end);
}
```

---

## 7. Settings Default Values

```php
// Seed default settings
$defaults = [
    // Member System
    ['key' => 'member_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'member'],
    ['key' => 'auto_register_on_phone', 'value' => 'true', 'type' => 'boolean', 'group' => 'member'],

    // Point Earning
    ['key' => 'point_earn_amount', 'value' => '10000', 'type' => 'integer', 'group' => 'point'],
    ['key' => 'point_earn_value', 'value' => '1', 'type' => 'integer', 'group' => 'point'],
    ['key' => 'point_earn_channels', 'value' => '["offline"]', 'type' => 'json', 'group' => 'point'],

    // Point Redemption
    ['key' => 'point_redeem_value', 'value' => '100', 'type' => 'integer', 'group' => 'point'],
    ['key' => 'point_min_redeem', 'value' => '100', 'type' => 'integer', 'group' => 'point'],
    ['key' => 'point_expired_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'point'],
    ['key' => 'point_expired_months', 'value' => '12', 'type' => 'integer', 'group' => 'point'],

    // Member Discount
    ['key' => 'member_discount_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'discount'],
    ['key' => 'member_discount_percent', 'value' => '5', 'type' => 'integer', 'group' => 'discount'],
    ['key' => 'member_discount_channels', 'value' => '["offline"]', 'type' => 'json', 'group' => 'discount'],
    ['key' => 'member_discount_min_purchase', 'value' => '0', 'type' => 'integer', 'group' => 'discount'],

    // Birthday Treat
    ['key' => 'birthday_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'birthday'],
    ['key' => 'birthday_discount_percent', 'value' => '20', 'type' => 'integer', 'group' => 'birthday'],
    ['key' => 'birthday_valid_days', 'value' => '7', 'type' => 'integer', 'group' => 'birthday'],
    ['key' => 'birthday_max_per_year', 'value' => '1', 'type' => 'integer', 'group' => 'birthday'],

    // Receipt/Struk
    ['key' => 'receipt_show_logo', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt'],
    ['key' => 'receipt_show_member_info', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt'],
    ['key' => 'receipt_show_point_info', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt'],
    ['key' => 'receipt_show_savings', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt'],
    ['key' => 'receipt_show_promo_footer', 'value' => 'true', 'type' => 'boolean', 'group' => 'receipt'],
    ['key' => 'receipt_header_text', 'value' => 'Toko Kosmetik XYZ', 'type' => 'string', 'group' => 'receipt'],
    ['key' => 'receipt_address', 'value' => '', 'type' => 'string', 'group' => 'receipt'],
    ['key' => 'receipt_phone', 'value' => '', 'type' => 'string', 'group' => 'receipt'],
    ['key' => 'receipt_social_media', 'value' => '', 'type' => 'string', 'group' => 'receipt'],
    ['key' => 'receipt_footer_text', 'value' => 'Terima kasih sudah belanja!', 'type' => 'string', 'group' => 'receipt'],
    ['key' => 'receipt_promo_text', 'value' => '', 'type' => 'string', 'group' => 'receipt'],
    ['key' => 'receipt_paper_size', 'value' => '80mm', 'type' => 'string', 'group' => 'receipt'],
];
```

---

## 8. UI/UX Requirements

### 8.1 Halaman Customers
- List view dengan search & filter
- Kolom: Nama, No. HP, Total Belanja, Poin, Last Purchase, Member Since
- Action: View Detail, Edit, Delete
- Tombol "Add Customer" manual

### 8.2 Halaman Customer Detail
- Info section: nama, HP, ulang tahun, member sejak
- Stats card: Total belanja, Total transaksi, Saldo poin
- Tab "Transaction History": list transaksi customer ini
- Tab "Point History": log earn & redeem poin

### 8.3 Kasir (Existing Page) - Modifications
- Field input no. HP (opsional, di header)
- Auto-detect: kalau no. HP terdaftar, tampil info member
- Quick register form (kalau no. HP belum ada)
- Section "Pakai Poin" di checkout
- Display benefit yang ter-apply (member discount, birthday, dll)

### 8.4 Halaman Settings - Member Configuration
- Grouped sections (Member, Poin, Diskon, Birthday)
- Toggle on/off untuk setiap fitur
- Input fields dengan validasi
- Live preview perubahan
- Tombol Save & Reset Default

---

## 9. Edge Cases & Validations

### 9.1 Edge Cases

| Case | Behavior |
|------|----------|
| Customer ganti no. HP | Update di profile, no. HP lama jadi history |
| 2 customer same name | OK, dibedakan by no. HP (unique) |
| Member dgn 0 poin redeem | Block, harus min_redeem |
| Birthday di tanggal 29 Feb | Treat sebagai 28 Feb di tahun non-kabisat |
| Member discount > Total | Total minimum Rp 0, gak boleh negatif |
| Settings di-disable saat ada poin | Poin existing tetap valid, hanya stop earning baru |
| Customer punya birth_date null | Skip birthday check |
| Settings invalid value | Validate sebelum save, rollback kalau error |

### 9.2 Concurrent Transaction
Saat 2 transaksi di waktu bersamaan untuk customer sama:
- Pakai database transaction & row lock
- Atau optimistic locking dengan version field

---

## 10. Reports & Analytics

### 10.1 Customer Report
- Top 10 customer by total spending
- Top 10 customer by frequency
- Customer yang lama gak balik (>30 hari)
- New members per period
- Total active members

### 10.2 Point Report
- Total poin di-earn per period
- Total poin di-redeem per period
- Outstanding points (total saldo poin semua customer)
- Poin yang akan expired dalam 30 hari

### 10.3 Member Discount Report
- Total nilai diskon yang dikasih (cost ke toko)
- Conversion rate (% transaksi yang pakai member benefit)
- ROI member program (revenue dari member vs non-member)

---

## 11. Success Metrics

- **Adoption Rate:** % customer yang daftar member dari total transaksi
- **Retention Rate:** % member yang balik belanja dalam 30 hari
- **Point Redemption Rate:** % poin yang di-redeem vs di-earn
- **Member Frequency:** Rata-rata kunjungan member per bulan
- **Member Spend Lift:** Selisih average spend member vs non-member

**Target:**
- Adoption rate > 50% dalam 3 bulan pertama
- Retention rate > 40%
- Member spend 30% lebih tinggi dari non-member

---

## 12. Development Checklist

### Phase 1: Foundation (2-3 hari)
- [ ] Migration: customers, point_logs, settings
- [ ] Migration: ALTER transactions table
- [ ] Models: Customer, PointLog, Setting
- [ ] Seeders: Default settings

### Phase 2: Customer Management (1-2 hari)
- [ ] CRUD customer (list, create, edit, delete)
- [ ] Customer detail page with tabs
- [ ] Search & filter

### Phase 3: Point System (2-3 hari)
- [ ] Earn points logic on transaction
- [ ] Redeem points UI di kasir
- [ ] Point validation
- [ ] Point log audit trail

### Phase 4: Member Benefits (2 hari)
- [ ] Auto-apply member discount
- [ ] Birthday detection & discount
- [ ] Discount calculation order

### Phase 5: Settings (1-2 hari)
- [ ] Settings page UI
- [ ] Settings helper class
- [ ] Validation & preview
- [ ] Audit log

### Phase 6: Receipt/Struk (2 hari)
- [ ] Receipt template (58mm, 80mm, digital)
- [ ] Helper: maskPhone, calculateTotalSavings
- [ ] Receipt settings page
- [ ] Print integration (thermal printer)
- [ ] PDF generation
- [ ] Receipt preview

### Phase 7: Reports (1-2 hari)
- [ ] Customer reports
- [ ] Point reports
- [ ] Member analytics

### Phase 8: Testing & Polish (2 hari)
- [ ] Unit tests
- [ ] Integration tests
- [ ] Edge case handling
- [ ] UI polish

**Total Estimasi: 13-18 hari kerja**

---

## 13. Future Enhancements (Out of Scope)

- 🏆 **Tier System** (Bronze/Silver/Gold/Platinum)
- 📱 **WhatsApp Integration** untuk notif poin & promo
- 🎁 **Reward Catalog** (tukar poin dengan produk)
- 👥 **Referral Program** (poin dari referral)
- 🎯 **Targeted Promotion** (segmentasi customer)
- 📊 **Customer Lifetime Value** analytics
- 🎨 **Custom Member Card** (digital card dengan QR)

---

*Dokumen ini fokus pada fitur Membership & Point System untuk implementasi tahap awal. Fitur lain di POS sudah didokumentasikan terpisah.*

# PRODUCT REQUIREMENTS DOCUMENT
## POS System untuk Toko Kosmetik & Aksesoris

---

**Versi:** 1.0  
**Tanggal:** 23 April 2026  
**Status:** Draft

---

## 1. Executive Summary

Aplikasi Point of Sale (POS) berbasis web untuk toko kosmetik dan aksesoris yang mendukung penjualan multi-channel (offline dan online marketplace). Sistem ini dirancang untuk mengelola transaksi, inventory, dan laporan penjualan dengan antarmuka yang sederhana namun powerful, khusus disesuaikan untuk kebutuhan bisnis retail kecil hingga menengah.

---

## 2. Problem Statement

### 2.1 Challenges

- Toko menjual produk melalui dua channel (offline dan Shopee) dengan harga yang berbeda per channel
- Produk kosmetik branded sudah memiliki barcode dari pabrik, perlu sistem yang bisa memanfaatkan barcode existing
- Kesulitan tracking inventory secara real-time antara penjualan offline dan online
- Membutuhkan laporan terpisah per channel untuk analisis performa penjualan
- Belum ada sistem untuk mencatat transaksi online marketplace seperti Shopee

---

## 3. Goals & Objectives

### 3.1 Primary Goals

1. Menyediakan aplikasi kasir yang cepat dan mudah digunakan untuk transaksi offline
2. Mendukung pencatatan manual penjualan online (Shopee) dengan harga yang berbeda dari offline
3. Mengelola inventory terpusat dengan update real-time untuk semua channel
4. Mengintegrasikan barcode scanner untuk mempercepat proses transaksi
5. Menyediakan laporan penjualan dan profit per channel

### 3.2 Success Metrics

- Waktu checkout per transaksi < 30 detik (dengan barcode scanner)
- Akurasi stok 99% (sinkronisasi real-time antar channel)
- User dapat mengoperasikan sistem tanpa training khusus (intuitif)
- Data tersimpan aman dan dapat dibackup/export

---

## 4. Target Users

### 4.1 Primary User: Pemilik Toko

- Menjalankan transaksi kasir harian
- Mencatat penjualan Shopee secara manual
- Mengelola produk dan stok
- Melihat laporan penjualan dan profit

### 4.2 Technical Requirements

- Dapat diakses via laptop/PC di toko (untuk kasir)
- Dapat diakses via smartphone (untuk input orderan Shopee kapan saja)
- Support barcode scanner USB (sebagai input keyboard)
- Support scan barcode via kamera HP (alternatif scanner hardware)

---

## 5. Core Features

### 5.1 Multi-Channel Transaction Management

#### Description
Sistem mendukung pencatatan transaksi dari berbagai channel penjualan dengan harga yang berbeda-beda.

#### Requirements
- User dapat memilih channel penjualan: Offline (Toko) atau Online (Shopee)
- Setiap produk memiliki 2 harga: harga offline dan harga Shopee
- Harga otomatis menyesuaikan berdasarkan channel yang dipilih saat transaksi
- Stok berkurang otomatis terlepas dari channel mana produk terjual

#### User Flow
1. User membuka halaman transaksi
2. User memilih channel: Offline atau Shopee
3. User menambahkan produk (scan barcode atau pilih manual)
4. Sistem menampilkan harga sesuai channel yang dipilih
5. User menyelesaikan transaksi, stok berkurang otomatis

---

### 5.2 Barcode Integration

#### Description
Sistem dapat membaca barcode yang sudah ada di kemasan produk branded (Maybelline, Wardah, dll) menggunakan scanner USB atau kamera smartphone.

#### Requirements
- Support barcode scanner USB (input sebagai keyboard)
- Support scan barcode via kamera HP (Web API)
- Setiap produk dapat menyimpan barcode number
- Produk tanpa barcode tetap dapat dicari manual by nama
- Sistem langsung menambahkan produk ke keranjang setelah scan berhasil

#### Technical Specs
- Barcode format: EAN-13, UPC-A, Code 128 (standar retail)
- Camera scanner menggunakan WebRTC/getUserMedia API
- Debounce input untuk mencegah duplicate scan

---

### 5.3 Product & Inventory Management

#### Description
Mengelola database produk dengan kategori, harga multi-channel, dan tracking stok real-time.

#### Requirements
- CRUD operations untuk produk (Create, Read, Update, Delete)
- Field produk: nama, kategori, barcode, harga offline, harga Shopee, stok, HPP (Harga Pokok Penjualan)
- Kategori: Skincare, Makeup, Haircare, Aksesoris, dll (customizable)
- Alert notifikasi stok rendah (threshold dapat di-set)
- Bulk import produk via CSV/Excel
- Search & filter produk by kategori atau nama

---

### 5.4 Cashier (Point of Sale)

#### Description
Antarmuka kasir yang cepat dan intuitif untuk proses checkout transaksi offline.

#### Requirements
- Shopping cart untuk menampung item sebelum checkout
- Tambah/kurangi quantity produk di cart
- Hapus item dari cart
- Auto-calculate subtotal, diskon (opsional), dan total
- Input pembayaran pelanggan dan hitung kembalian
- Support multiple payment methods: Cash, QRIS, Transfer
- Cetak/tampilkan struk digital setelah transaksi
- Keyboard shortcuts untuk aksi cepat (Enter = checkout, Esc = clear cart, dll)

---

### 5.5 Sales Reporting & Analytics

#### Description
Dashboard dan laporan untuk memantau performa penjualan per channel dan menganalisis profit.

#### Requirements
- Dashboard overview: total penjualan hari ini, minggu ini, bulan ini
- Laporan penjualan per channel (Offline vs Shopee)
- Laporan profit (revenue - HPP)
- Best selling products
- Filter laporan by date range
- Export laporan ke Excel/CSV
- Grafik visualisasi penjualan (line chart/bar chart)

---

## 6. Technical Architecture

### 6.1 Technology Stack

| Component | Technology |
|-----------|------------|
| **Frontend** | Laravel Inertia + Vue 3 + Tailwind CSS |
| **Data Storage** | Database MySQL |
| **Barcode Scanner** | USB Scanner (keyboard input) + Camera (WebRTC API + library seperti QuaggaJS/ZXing) |
| **Deployment** | Hosting di cPanel |

### 6.2 Data Model

#### Product

| Field | Type | Description |
|-------|------|-------------|
| id | String | Unique identifier |
| name | String | Nama produk |
| category | String | Kategori produk |
| barcode | String | Barcode number (opsional) |
| priceOffline | Number | Harga jual toko offline |
| priceShopee | Number | Harga jual Shopee |
| costPrice | Number | Harga Pokok Penjualan (HPP) |
| stock | Number | Jumlah stok available |
| minStock | Number | Threshold minimum stok alert |

#### Transaction

| Field | Type | Description |
|-------|------|-------------|
| id | String | Unique identifier |
| date | DateTime | Tanggal & waktu transaksi |
| channel | Enum | 'Offline' \| 'Shopee' |
| items | Array | List of { productId, quantity, price, costPrice } |
| subtotal | Number | Total sebelum diskon |
| discount | Number | Diskon (opsional) |
| total | Number | Total setelah diskon |
| paymentMethod | String | 'Cash' \| 'QRIS' \| 'Transfer' |

---

## 7. UI/UX Requirements

### 7.1 Design Principles

- **Simple & Intuitive:** Minimalis, tidak overwhelming dengan banyak fitur di satu layar
- **Fast Navigation:** Maksimal 3 klik untuk akses fitur utama
- **Mobile-Friendly:** Responsive design untuk akses via smartphone
- **Keyboard Shortcuts:** Support shortcut untuk power user
- **Visual Feedback:** Loading states, success/error messages yang jelas

### 7.2 Key Screens

1. **Dashboard** - Overview sales, quick stats, low stock alerts
2. **Cashier (POS)** - Product search/scan, shopping cart, checkout
3. **Products** - Product list, add/edit product form
4. **Transactions** - Transaction history, filter by channel
5. **Reports** - Sales charts, profit analysis, export options

---

## 8. Security & Data Management

### 8.1 Data Security

- Data tersimpan di browser LocalStorage (client-side)
- Enkripsi data sensitif (opsional untuk future enhancement)
- No server-side storage (untuk menghindari biaya hosting database)

### 8.2 Backup & Recovery

- Export seluruh data ke JSON/CSV untuk backup
- Import data dari file backup untuk restore
- Auto-backup reminder (notifikasi mingguan untuk backup manual)

---

## 9. Future Enhancements (Optional)

- Integrasi langsung dengan API Shopee/Tokopedia untuk auto-sync orderan
- Multi-user support dengan role management (admin, kasir)
- Program loyalty/membership untuk pelanggan setia
- Print struk fisik via thermal printer
- Cloud sync untuk akses data dari multiple devices
- WhatsApp notification untuk orderan Shopee
- Advanced analytics: customer segmentation, product bundling recommendations

---

## 10. Success Criteria

Produk dianggap sukses jika:

- User dapat menyelesaikan transaksi offline dalam < 30 detik dengan barcode scanner
- User dapat mencatat orderan Shopee dalam < 1 menit per order
- Tidak ada ketidaksesuaian stok (discrepancy) > 1% antara sistem dan stok fisik
- User dapat generate laporan penjualan bulanan dalam < 10 detik
- Aplikasi berjalan lancar di perangkat dengan spesifikasi minimum (Chrome on 4GB RAM laptop/smartphone)
- User satisfaction score ≥ 4/5 dalam usability testing

---

## 11. Development Timeline (Estimasi)

| Phase | Deliverables | Duration |
|-------|--------------|----------|
| **Phase 1: MVP** | Basic POS, Product management, Multi-channel pricing | 1 week |
| **Phase 2** | Barcode integration (USB + Camera), Transaction history | 3-5 days |
| **Phase 3** | Reporting & Analytics, Export/Import, Dashboard | 3-5 days |
| **Phase 4** | Polish UI/UX, Testing, Bug fixes | 2-3 days |

**Total Estimated Time: 2-3 minggu**

---

*Dokumen ini akan diupdate sesuai perkembangan requirement dan feedback dari user testing.*

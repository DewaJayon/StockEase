# StockEase Implementation Plan: Future Enhancements

Rencana ini merinci fitur-fitur tambahan yang direkomendasikan untuk meningkatkan kapabilitas sistem kasir dan gudang **StockEase**. Fokus utama adalah pada akurasi data, analisis keuangan, dan efisiensi operasional.

## 1. Modul Gudang & Inventaris (Warehouse)

### A. Stock Opname (Penyesuaian Stok)
*   **Masalah:** Selisih antara stok di sistem dan stok fisik di gudang.
*   **Fitur:** Form input hasil audit fisik, perhitungan otomatis selisih, dan otorisasi oleh admin.
*   **Teknis:** Table `stock_adjustments` dan integrasi ke `stock_logs`.

### B. Pelacakan Tanggal Kedaluwarsa (Expiry Date)
*   **Masalah:** Kerugian akibat produk yang kedaluwarsa tanpa terdeteksi.
*   **Fitur:** Input tanggal kadaluwarsa saat pembelian (`Purchase`), notifikasi otomatis 30/60/90 hari sebelum kadaluwarsa.
*   **Teknis:** Update tabel `purchase_items` dan cron job untuk pengecekan harian.

### C. Multi-Gudang (Multi-Warehouse)
*   **Masalah:** Pengelolaan stok di beberapa lokasi fisik (misal: Toko A, Toko B, Gudang Pusat).
*   **Fitur:** Pemindahan stok antar gudang, filter laporan per gudang.
*   **Teknis:** Tabel `warehouses` dan tabel pivot `warehouse_product`.

## 2. Modul Penjualan & POS (Cashier)

### A. Manajemen Shift & Kas (Shift Management)
*   **Masalah:** Kesulitan melacak arus kas fisik saat pergantian kasir.
*   **Fitur:** Buka shift (input modal awal), tutup shift (input uang fisik), laporan selisih kas per shift.
*   **Teknis:** Tabel `shifts` dengan relasi ke `users` dan `sales`.

### B. Manajemen Diskon & Promo
*   **Masalah:** Sistem diskon masih manual atau belum fleksibel.
*   **Fitur:** Diskon persentase/nominal, promo beli X gratis Y, diskon khusus kategori tertentu atau periode waktu (Flash Sale).
*   **Teknis:** Tabel `promotions` dan integrasi logika di `PosService`.

### C. Retur Penjualan (Sales Returns)
*   **Masalah:** Penanganan barang yang dikembalikan oleh pelanggan.
*   **Fitur:** Form retur berdasarkan ID Transaksi, opsi pengembalian uang atau tukar barang, otomatisasi penyesuaian stok.
*   **Teknis:** Tabel `sales_returns`.

## 3. Laporan & Analisis (Finance)

### A. Laporan Laba/Rugi (Profit & Loss)
*   **Masalah:** Sistem saat ini hanya mencatat omzet, bukan keuntungan bersih.
*   **Fitur:** Perhitungan HPP (Harga Pokok Penjualan) secara otomatis menggunakan metode FIFO atau Average, laporan margin profit per produk.
*   **Teknis:** Integrasi harga beli di tabel `sale_items`.

### B. Analisis Produk (Fast & Slow Moving)
*   **Masalah:** Penumpukan stok pada produk yang tidak laku.
*   **Fitur:** Grafik analisis produk yang paling cepat terjual vs yang paling lama mengendap di gudang.
*   **Teknis:** Agregasi data dari `sale_items` dan `stock_logs`.

## 4. Pengalaman Pengguna & Integrasi (Technical)

### A. Progressive Web App (PWA)
*   **Fitur:** Memungkinkan aplikasi diinstal di smartphone/desktop dan meningkatkan performa loading.
*   **Teknis:** Integrasi `vite-plugin-pwa` dan Service Workers.

### B. Integrasi Printer Thermal
*   **Fitur:** Cetak struk langsung ke printer thermal (Bluetooth/USB) dari browser.
*   **Teknis:** Menggunakan Web Bluetooth API atau library seperti `print-js`.

## Prioritas Implementasi

| Fase | Fitur | Perkiraan Waktu |
| :--- | :--- | :--- |
| **Fase 1 (Urgent)** | Stock Opname & HPP (Laba/Rugi) | 2 Minggu |
| **Fase 2 (Operasional)** | Shift Management & Retur | 2 Minggu |
| **Fase 3 (Growth)** | Expiry Date & Promo | 3 Minggu |
| **Fase 4 (Scale)** | Multi-Gudang & PWA | 4 Minggu |

---
*Dibuat oleh: Gemini CLI (Senior AI Engineer)*

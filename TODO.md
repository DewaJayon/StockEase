<!-- TODO: 04/04/2026 -->

- buat unit test untuk controller yang ada di folder dashboard (Done)
- upgrade ke laravel 13 (Done)
- install laravel boost (Done)
- fix bug di cart page (Done)
- refactor controller pindahil logic ke service class (Done)

<!-- TODO: 05/04/2026 -->

- fix CI github (Done)
- buatin halaman custom error agar tidak pakai bawaan laravel (Done)
- untuk notifikasi itu jangan pakai pooling biar hemat request dan tidak membebankan server (Done)
- buatin CD ke cpanel (Done)

<!-- TODO: 12/04/2026 -->

- fix CI github (Done)
- fix CD ke cpanel (Done)
- fix query duplikat (Done)
- security di pos karena disana perhitungannya masih di frontend validasi di backend lagi (Done)
- cek di database untuk field currency itu jadikan aja desimal biar karena ini untuk harga (Done)
- refactor struktur folder controller dan service (Done)
- refactor struktur folder request (Done)
- fix dark mode (Done)
- ux di harga di pos page isikan titik (Done)
- bug ketika barang dimasukkan ke keranjang itu malah kehitung terjual di grafic penjualan mingguan dashboard (Done)
- fix error message di cart ketika memasukkan angka 9 banyak itu errornya database alangkah baiknya errornya bukan database message (Done)
- fix bug di pos bagian cart itu ketika menggunakan metode pembayaran qris kembaliannya masih ke list (Done)
- fitur satuan itu biarin bisa CRUD aja (Done)

<!-- TODO: 19/04/2026 -->

- scan barcode di pos sistem dan langsung masukkan ke keranjang (Done)
- bug Grafik Penjualan Mingguan di dashboard itu ga mau nampilin data di server tapi di local mau (Done)
- Stock Opname (Penyesuaian Stok) (Done)
- Pelacakan Tanggal Kedaluwarsa (Expiry Date) (Done)
- untuk mengubah harga beli dan jual itu buatkan page khusus untuk mempermudah audit (Done)
- penambahan grafik dll di halaman dashboard (Done)
- penambahan exired di tambah produk (Done)
- logika FEFO (First Expired, First Out) untuk penjualan product (Done)
- buat action class untuk reduceStockFromSaleItems dan updateExpiryDate supaya tidak di taruh di model product (Done)
- di update product experied itu di disable aja kan udah ada di purcase itu biar disana nambahin product baru (Done)

<!-- TODO: 26/04/2026 -->

- update depedency js (Done)
- Laporan Laba/Rugi (Profit & Loss) (Done)
- refactor app sidebar (Done)
- untuk calculateTotal di model sale dipindah buat action class (Done)
- di laporan laba rugi itu date filternya pakai date rage dari shadcn vue (Done)
- fix tabel biar rapi dan sejajar (Done)
- refactor isikan pagination di profit loss bagian Rincian Per Produk Analisis margin dan profitabilitas setiap produk (Done)
- penambahan test case di dashboard test (Done)
- penambahan test case di unit test (Done)
- penambahan test case di category controller test (Done)
- fix bug di profit loss filter datatable bentrok dengan filter date di page (Done)
- refactor SaleReportController buatin service class dan sempurnakan testnya (Done)
- refactor PurchaseReportController buatin service class dan sempurnakan testnya (Done)
- refactor StockReportController buatin service class dan sempurnakan testnya (Done)
- refactor ExpiryReportController buatin service class dan buatkan unit test (Done)
- refactor ProfitLossReportController buatin service class dan sempurnakan testnya (Done)
- refactor LogStockController buatin service class dan buatkan unit test (Done)
- fix bug di expiry page itu filternya bentrok dengan filter di datatabel (Done)

<!-- TODO: 03/05/2026 -->

- rapikan struktur controller (pindahkan Dashboard, Promotion, dan Unit ke subfolder) (Done)
- rapikan penempatan file test di folder tests/Feature agar lebih terorganisir (Done)
- fix n+1 query, query duplikat, dan jangan pakai datatable limit aja 5 atau 10 data di dashboard (Done)
- penambahan test case untuk PurchaseController (Done)
- penambahan test case untuk SupplierController (Done)
- penambahan test case untuk ProductController (Done)
- hapus file code auth yang sudah tidak terpakai (Done)
- improve ui/ux di halaman report (Done)
- fix ui di halaman POS (Done)
- fitur Analisis Produk (Fast & Slow Moving) (Done)
- fitur Manajemen Diskon & Promo (Done)
- fix fitur Manajemen Diskon & Promo beserta UI/UX nya dan tambahkan lagi testnya (Done)
- fix date form di diskon page (Done)
- perbaikan UI/UX di halaman tambah produk, edit dan show produk (Done)
- fix bug sidebar active (Done)
- database seeder untuk promo diskon (Done)
- fix filter pencarian datatable di diskon promo page (Done)
- fitur filter tanggal di halaman promo diskon (Done)
- fix bug di update form promo diskon itu ketika typenya persen nilai diskonnya malah kayak harga (Done)
- refactor tabel di halaman promo untuk type dan nilai itu dipisah (Done)
- unit test untuk file manager controller (Done)
- fix bug di sidebar active laporan laba rugi dan analisis produk (Done)
- fix Form edit pembelian produk di tanggal Kadaluwarsa itu pakai date shadcn vue (Done)
- refactor route web buatkan folder web dengan route sesuai fitur (Done)

<!-- TODO: -->

- tambahin lagi beberapa laporan atau fitur di laporan penjualan
- tambahkan test case untuk semua logic
- fix github CD ke cpanel
- bikin API untuk mobile app

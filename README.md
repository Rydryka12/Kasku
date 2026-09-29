# KasKu - Aplikasi Tabungan & Catatan Keuangan

Aplikasi pencatatan keuangan pribadi, alokasi anggaran, target tabungan terencana, dan pemantauan arus kas multi-sumber dana dengan standar integritas saldo perbankan.

Dibangun dengan arsitektur **Laravel 12**, **Livewire 3**, **Alpine.js**, dan **Tailwind CSS**.

---

## Fitur Utama

1. **Multi-Sumber Dana & Integritas Saldo**
   - Mendukung berbagai jenis sumber dana (Bank, Dompet Digital / E-Wallet, Uang Tunai, Investasi).
   - **K-01**: Perlindungan saldo negatif otomatis (transaksi/transfer ditolak jika saldo tidak mencukupi) dengan database row locking (`lockForUpdate`).
   - **K-02 & K-03**: Pembaruan dan penghapusan transaksi menerapkan pemulihan saldo atomik tanpa celah desinkronisasi.
   - **CLI Verifikasi Saldo**: Perintah `php artisan balances:verify` untuk memvalidasi integritas saldo tercatat terhadap riwayat transaksi matematis.

2. **Pencatatan Transaksi & Transfer Saldo**
   - Transaksi pemasukan dan pengeluaran dengan dukungan perhitungan pajak (PB1, PPN) dan catatan pajak.
   - Transfer antar sumber saldo dengan pencatatan biaya admin atomik dalam satu transaksi database.
   - Filter lengkap: rentang tanggal, jenis transaksi, kategori, sumber dana, pencarian teks, dan pagination instan.

3. **Target Tabungan (Savings Goals)**
   - Alokasi dana tabungan dari sumber saldo aktif ke target khusus (misal: Dana Darurat, Liburan, Gadget).
   - Progress visual, persentase, sisa dana, dan proyeksi estimasi waktu tercapai berdasarkan tren menabung.
   - **K-09**: Pembatalan atau penghapusan target tabungan otomatis mengembalikan seluruh saldo yang tersimpan kembali ke sumber dana pilihan pengguna.
   - **K-10**: Dashboard menampilkan ringkasan terpisah: **Saldo Tersedia**, **Total Tabungan**, dan **Total Kekayaan**.

4. **Laporan Keuangan & Ekspor Berkas**
   - Periode fleksibel: Rekapitulasi Bulanan dan Tahunan.
   - Tab analisis: **Ringkasan Arus Kas**, **Pengeluaran per Kategori**, **Mutasi per Sumber Dana**, dan **Rincian Pajak & Biaya Transfer**.
   - Ekspor laporan dalam format **PDF** (`barryvdh/laravel-dompdf`) dan **Excel** (`maatwebsite/excel`).
   - Ekspor daftar transaksi ke Excel mengikuti filter aktif di layar.
   - **Riwayat Ekspor**: Penyimpanan otomatis file laporan yang digenerate pada halaman `/exports` untuk kemudahan unduh ulang.

5. **Anggaran Kategori (Budgets)**
   - Alokasi pagu pengeluaran per kategori per bulan.
   - Visualisasi progress pemakaian dengan indikator warna (Aman <80%, Peringatan 80–99%, Melebihi ≥100%).
   - Fitur "Salin Anggaran dari Bulan Lalu" untuk efisiensi setup bulanan.
   - Peringatan informatif otomatis pada form pencatatan pengeluaran saat anggaran mendekati batas.

6. **Transaksi Berulang (Recurring Transactions)**
   - Penjadwalan transaksi otomatis harian, mingguan, bulanan, atau tahunan.
   - **K-12**: Eksekusi idempoten berbasis constraint database unik `(recurring_id, run_date)`.
   - Otomatis mengejar tanggal jatuh tempo yang terlewat dan penanganan klamp tanggal akhir bulan (29, 30, 31).
   - Penanganan saldo tidak cukup dengan pencatatan log kegagalan dan pengiriman notifikasi ke lonceng pengguna.

7. **Manajemen Hutang & Piutang**
   - **K-11**: Pencatatan hutang/piutang baru tidak mempengaruhi saldo kas (saldo hanya bergerak saat pembayaran cicilan).
   - Pembayaran cicilan parsial atau pelunasan dengan validasi batas sisa tagihan.
   - Penanda jatuh tempo (H-3 dan terlewat) dan pelacakan status (`Belum Lunas`, `Cicilan`, `Lunas`).

8. **Rekonsiliasi Saldo Fisik**
   - **K-04**: Fitur penyesuaian saldo riil vs saldo aplikasi. Transaksi koreksi ditandai secara khusus (`is_adjustment = true`) sehingga tidak mendistorsi grafik pendapatan maupun pengeluaran operasional.

9. **Panel Admin Terisolasi (Zero Financial Exposure)**
   - **K-14**: Admin memiliki akses terpisah `/admin` yang **hanya** menampilkan metrik agregat kuantitatif (**COUNT** user, **COUNT** transaksi, grafik pendaftaran). Admin dilarang keras mengakses angka saldo maupun nominal keuangan user.
   - Manajemen aktivasi pengguna dengan proteksi terhadap penonaktifan diri sendiri dan admin aktif terakhir.
   - Pengelolaan template kategori default sistem untuk pengguna baru.

10. **Desain & Aksesibilitas Premium**
    - Tema gelap penuh (Dark Mode) dengan pemilih preferensi (Terang / Gelap / Ikuti Sistem) di profil.
    - Desain responsif hingga lebar layar terkecil (360px) dengan bottom navigation di mobile.
    - Aksesibilitas WCAG: outline fokus kontras `:focus-visible`, dukungan `prefers-reduced-motion`, serta komponen loading skeleton dan dialog konfirmasi.

---

## Kebutuhan Sistem

- PHP >= 8.2 (ekstensi: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd` / `imagick`)
- Composer >= 2.2
- Node.js >= 18.x & NPM
- Database: MySQL >= 8.0, MariaDB >= 10.4, atau SQLite >= 3.35

---

## Panduan Instalasi Lokal

1. **Clone & Masuk ke Direktori Proyek**:
   ```bash
   git clone <repository-url> keuangan-app
   cd keuangan-app
   ```

2. **Instal Dependensi PHP & Frontend**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Lingkungan (`.env`)**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Pastikan pengaturan basis data pada `.env` telah disesuaikan (misal: MySQL pada Laragon atau SQLite).

4. **Tautkan Storage & Migrasi Database**:
   ```bash
   php artisan storage:link
   php artisan migrate:fresh --seed
   ```

5. **Kredensial Akun Demo Bawaan**:
   - **Pengguna Reguler**:
     - Email: `user@kasku.test`
     - Password: `password`
   - **Administrator**:
     - Email: `admin@kasku.test`
     - Password: `password`

6. **Jalankan Server Pengembangan**:
   ```bash
   npm run dev
   # Buka terminal lain untuk web server (jika tidak menggunakan Laragon / Valet):
   php artisan serve
   ```
   Buka peramban di `http://127.0.0.1:8000` atau URL virtual host Laragon Anda.

---

## Panduan Deployment ke Produksi

### 1. Pengaturan File `.env` Produksi
Pastikan konfigurasi lingkungan produksi telah diatur secara aman:
```ini
APP_NAME=KasKu
APP_ENV=production
APP_DEBUG=false
APP_URL=https://keuangan.domainanda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasku_prod
DB_USERNAME=kasku_user
DB_PASSWORD=rahasia_sangat_kuat

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 2. Kompilasi Aset Frontend
Lakukan kompilasi bundle aset CSS & JS untuk performa maksimal:
```bash
npm ci
npm run build
```

### 3. Migrasi & Storage Link
```bash
php artisan storage:link
php artisan migrate --force
```

### 4. Optimasi Cache Laravel
Jalankan perintah optimasi untuk mempercepat booting framework:
```bash
php artisan optimize
# Atau secara spesifik:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 5. Konfigurasi Cron Scheduler (Wajib)
Jadwalkan cron job di server hosting / VPS agar eksekusi transaksi berulang (`recurring:run`) berjalan otomatis setiap hari:
```bash
* * * * * cd /path/to/keuangan-app && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Queue Worker (Opsional / Background Task)
Jika menggunakan background queue worker untuk notifikasi atau ekspor besar, jalankan supervisor / systemd:
```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### 7. Strategi Backup Database Rutin
Disarankan mengatur backup harian otomatis untuk basis data:
```bash
mysqldump -u kasku_user -p'rahasia_sangat_kuat' kasku_prod | gzip > /backups/kasku_$(date +\%F).sql.gz
```

---

## Perintah Artisan Khusus

- **Verifikasi Konsistensi Saldo Seluruh Akun**:
  ```bash
  php artisan balances:verify
  ```
  Memeriksa apakah saldo tercatat setiap akun sama persis dengan kalkulasi mutasi buku besar. Mengembalikan exit code `0` jika MATCH dan `1` jika terdapat DISCREPANCY.

- **Jalankan Transaksi Berulang Jatuh Tempo Secara Manual**:
  ```bash
  php artisan recurring:run
  ```

---

## Pengujian Otomatis & Standar Kualitas Kode

- **Menjalankan Seluruh Feature Tests (92 Pengujian)**:
  ```bash
  php artisan test
  ```
- **Memeriksa & Memformat Standar Kode PSR-12 (Laravel Pint)**:
  ```bash
  vendor/bin/pint --test
  # Untuk auto-fix:
  vendor/bin/pint
  ```

---

## Lisensi

Aplikasi ini dikembangkan untuk kebutuhan manajemen keuangan pribadi dengan lisensi [MIT](LICENSE).

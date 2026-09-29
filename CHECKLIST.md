# Checklist Progres Pengembangan "Aplikasi Tabungan & Catatan Keuangan"

Dokumen ini memantau progres pengembangan sesuai spesifikasi `SRS.md`, `DESIGN.md`, dan `.agent/skills/keuangan-app/SKILL.md`.

---

## Tahap Persiapan & Desain
- [-] Desain ERD *(Digantikan oleh SRS.md Bagian 6)*
- [-] Wireframe & Mockup *(Digantikan oleh DESIGN.md)*
- [x] Konfigurasi environment (.env: timezone `Asia/Jakarta`, locale `id`, `id_ID`)
- [x] Package pendukung (`maatwebsite/excel`, `barryvdh/laravel-dompdf`, Alpine terintegrasi bawaan Livewire)
- [x] Helper `rupiah()` dan autoload di `composer.json`

---

## Fase 1: MVP (Minimum Viable Product)

### 1. Fondasi Tampilan & Komponen (DESIGN.md)
- [x] Token warna (`kertas`, `permukaan`, `tinta`, `tinta-redup`, `garis`, `batik`, `masuk`, `keluar`, `kunyit`) pada Tailwind & CSS variable
- [x] Tipografi Plus Jakarta Sans & Tabular Nums
- [x] Komponen Blade dasar:
  - [x] `<x-money>`
  - [x] `<x-money-input>` (Alpine Intl.NumberFormat)
  - [x] `<x-modal>` (Bottom sheet di mobile, centered dialog di desktop)
  - [x] `<x-dropdown>` & `<x-dropdown-link>`
  - [x] `<x-toast>`
  - [x] `<x-confirm>`
  - [x] `<x-skeleton>`
  - [x] `<x-badge>`
  - [x] `<x-progress>`
  - [x] `<x-empty>`
  - [x] `<x-source-dot>`
  - [x] `<x-money-bar>`
  - [x] `<x-nav-item>`
- [x] Layout aplikasi (`layouts.app`) dengan sidebar desktop, bottom nav mobile, header aksi catat global, responsif (360px)
- [x] Layout admin terpisah (`layouts.admin`)
- [x] Pengaturan preferensi tema (Light, Dark, Sistem) di halaman profil

### 2. Autentikasi & Otorisasi
- [x] Kolom `role` (`user`, `admin`) dan `is_active` (boolean) di tabel `users`
- [x] Enum `Role`
- [x] Middleware `EnsureUserIsActive` (`active`) & `EnsureUserHasRole` (`role`)
- [x] Registrasi publik default role `user` dan `is_active = true`
- [x] Seeder admin (`AdminSeeder`) dengan akun `admin@kasku.test`
- [x] Isolasi role: Admin dialihkan ke `/admin`, User ke `/dashboard`

### 3. Modul Kategori
- [x] Migration, model `Category`, enum `CategoryType`
- [x] `DefaultCategorySeeder` untuk template kategori sistem (`user_id = null`)
- [x] `CategoryService::copyDefaultCategoriesToUser()` (K-18: otomatis salin saat registrasi)
- [x] Komponen Livewire `Categories\Index` (tab Pemasukan/Pengeluaran, CRUD, pengarsipan K-06)
- [x] Otorisasi `CategoryPolicy` (isolasi user, proteksi kategori sistem)

### 4. Modul Sumber Saldo
- [x] Migration, model `AccountSource`, enum `SourceType`
- [x] Komponen Livewire `Sources\Index` (CRUD, validasi arsip saldo = 0 sesuai K-05)
- [x] Komponen Livewire `Sources\Show` (Buku besar gabungan transaksi & transfer)
- [x] Otorisasi `AccountSourcePolicy` (isolasi antar user)

### 5. BalanceService & Verifikasi Integritas Saldo
- [x] `BalanceService::credit()` dengan `DB::transaction` + `lockForUpdate`
- [x] `BalanceService::debit()` dengan validasi saldo cukup (K-01) & `InsufficientBalanceException`
- [x] `BalanceService::verify()` menghitung saldo matematis konsisten (K-02)
- [x] Artisan command `php artisan balances:verify`

### 6. Modul Transaksi
- [x] Migration & model `Transaction`, enum `TransactionType`
- [x] `TransactionService` (create, update dengan rollback lama + terapkan baru K-02, delete dengan restore saldo K-03)
- [x] Modal global catat transaksi `Transactions\FormModal` (dapat dipanggil dari header/bottom nav via Alpine dispatch)
- [x] Komponen Livewire `Transactions\Index` (pencarian, filter tipe/sumber/kategori, date range, pagination, URL binding)
- [x] Otorisasi `TransactionPolicy`

### 7. Modul Transfer
- [x] Migration & model `Transfer`
- [x] `TransferService` (debit sumber asal nominal + admin fee, kredit tujuan nominal secara atomik, rollback saat delete)
- [x] Komponen Livewire `Transfers\Index` (riwayat transfer, form modal transfer, batalkan transfer)
- [x] Otorisasi `TransferPolicy`

### 8. Dashboard Dasar
- [x] Tampilan ringkasan `dashboard.blade.php`
- [x] Komponen lazy `TotalBalanceWidget` (Total saldo tersedia, bar proporsi per sumber)
- [x] Komponen lazy `RecentTransactionsWidget` (10 mutasi terbaru)

### 9. Admin Area (Fase 1)
- [x] `Admin\Dashboard` (Statistik jumlah user, transaksi)
- [x] `Admin\UsersIndex` (Daftar user, toggle aktif/nonaktif, proteksi K-ADM-06)
- [x] `Admin\CategoriesIndex` (CRUD template kategori sistem)

### 10. Pengujian Otomatis & Standar Kode
- [x] Feature tests lengkap:
  - `BalanceServiceTest` (Kredit, Debit, Tolak saldo kurang, Verifikasi rumus saldo)
  - `CategoryTest` (CRUD, Kloning kategori K-18, Soft-archive K-06, Isolasi user)
  - `AccountSourceTest` (CRUD, Larangan arsip saldo > 0 K-05, Isolasi user)
  - `TransactionTest` (Pemasukan, Pengeluaran, Update atomik K-02, Hapus & restore K-03, Tolak hapus pemasukan jika saldo negatif)
  - `TransferTest` (Debit + biaya admin, Kredit tujuan, Tolak saldo kurang, Rollback hapus)
  - `AdminAccessTest` (Proteksi role, isolasi admin dari data keuangan, blokir user nonaktif)
- [x] Semua 54 feature tests lulus (100% pass)
- [x] Standar kode bersih PSR-12 via `vendor/bin/pint`

---

## Fase 2: Tabungan & Laporan (Selesai)

### 1. Modul Tabungan (FR-SAV)
- [x] Migration `savings_goals` & `savings_entries`
- [x] Model `SavingsGoal` (relasi, cast, `progressPercentage()`, `remainingAmount()`, `estimateAchievedMonth()`)
- [x] Model `SavingsEntry` (relasi ke User, Goal, AccountSource, cast Enum `EntryType`)
- [x] Enum `GoalStatus` (`active`, `achieved`, `cancelled`) & `EntryType` (`deposit`, `withdraw`)
- [x] Otorisasi `SavingsGoalPolicy`
- [x] `SavingService`:
  - [x] `deposit()` memotong saldo sumber via `BalanceService` & mencatat entri tabungan
  - [x] `withdraw()` menambah saldo sumber via `BalanceService` & memvalidasi saldo tabungan cukup
  - [x] `cancel()` membatalkan target & mengembalikan seluruh dana ke sumber saldo pilihan (K-09, FR-SAV-07)
  - [x] `delete()` mengembalikan dana jika target masih berisi sebelum hapus
  - [x] `syncStatus()` otomatis memperbarui status `achieved` saat mencapai target
  - [x] `getTotalSavings()` menghitung total target berstatus `active` + `achieved` (K-10)
- [x] Komponen Livewire `Savings\Index` (Daftar target, progress, sisa, perkiraan tercapai, modal target baru/edit, modal setor/tarik dengan pratinjau saldo)
- [x] Komponen Livewire `Savings\Show` (Detail target, riwayat entri buku besar, tombol batalkan & hapus target dengan pengembalian dana)
- [x] Integrasi Dashboard: widget saldo kini menampilkan **Saldo Tersedia**, **Total Tabungan**, dan **Total Kekayaan** (FR-SAV-06, FR-DSH-01, K-09, K-10)
- [x] Rumus verifikasi integritas saldo CLI `balances:verify` diperbarui memperhitungkan setoran dan penarikan tabungan (SRS 6.3)

### 2. Modul Laporan & Export (FR-RPT)
- [x] `ReportService` sebagai sumber kebenaran tunggal angka laporan:
  - [x] `summary()` (Pemasukan, pengeluaran, biaya transfer, arus kas bersih, rincian waktu harian/bulanan)
  - [x] `byCategory()` (Pengeluaran per kategori, porsi persentase, nominal)
  - [x] `bySource()` (Pemasukan, pengeluaran, transfer masuk/keluar, selisih, saldo saat ini)
  - [x] `tax()` (Pajak & biaya transfer dengan daftar rincian per DESIGN 5.11)
- [x] Halaman `/reports` (`App\Livewire\Reports\Index`):
  - [x] Segmented period: Bulanan & Tahunan dengan navigator periode
  - [x] Tab Ringkasan (tiga angka berdampingan dipisah garis vertikal sesuai DESIGN.md 5.11)
  - [x] Tab Kategori, Tab Sumber Saldo, Tab Pajak & Biaya
- [x] Export PDF (`barryvdh/laravel-dompdf`): template `exports.report-pdf`, styling profesional, terisolasi per user
- [x] Export Excel (`maatwebsite/excel`):
  - [x] `ReportExport` via template `exports.report-excel`
  - [x] `TransactionsExport` via template `exports.transactions-excel`
  - [x] Tombol "Export Excel" pada halaman `/transactions` mengikuti filter aktif
- [x] Rute export terdaftar: `/reports/export/pdf`, `/reports/export/excel`, `/transactions/export/excel`

### 3. Pengujian Otomatis & Standar Kode Fase 2
- [x] Feature test tabungan (`tests/Feature/SavingTest.php`):
  - Setor memotong saldo & menambah tabungan
  - Tarik menambah saldo & mengurangi tabungan
  - Tolak setor bila saldo tidak cukup
  - Tolak tarik melebihi saldo tabungan
  - Batalkan target mengembalikan seluruh dana ke sumber saldo pilihan
  - Total tabungan hanya menghitung status `active` dan `achieved`
  - Isolasi data: user A tidak bisa melihat/mengubah target user B
- [x] Feature test laporan & export (`tests/Feature/ReportTest.php`):
  - Akses halaman dan navigasi tab
  - Validasi perhitungan `ReportService`
  - **Uji konsistensi angka**: Angka pada halaman == PDF == Excel
  - Verifikasi response download PDF (`application/pdf`) dan Excel
  - Isolasi data laporan antar user
- [x] Semua 67 feature tests lulus (100% pass, 184 assertions)
- [x] Kode bersih PSR-12 via `vendor/bin/pint`

---

---

## Fase 3: Budget, Transaksi Berulang, Hutang/Piutang, Rekonsiliasi (Selesai)

### 1. Modul Budget (FR-BDG)
- [x] Migration `budgets` dengan `unique(['user_id', 'category_id', 'month'])`
- [x] Model `Budget` & `BudgetPolicy`
- [x] `BudgetService`:
  - [x] `usage()` menghitung pemakaian non-penyesuaian dengan basis nominal + pajak (K-08)
  - [x] `status()` mengembalikan `safe` (<80%), `warning` (80–99%), `exceeded` (≥100%)
  - [x] `copyFromPreviousMonth()` menyalin budget bulan lalu ke bulan target (FR-BDG-05)
  - [x] `checkWarningForExpense()` mendeteksi potensi peringatan 80% / 100% pada form pencatatan
- [x] Halaman `/budgets` (`App\Livewire\Budgets\Index`):
  - [x] Progress bar pemakaian, warna indikator (kunyit / keluar / hijau), badge status
  - [x] Navigasi bulan, tombol "Salin dari bulan lalu", CRUD budget per kategori
- [x] Form Catat transaksi: informasi peringatan budget 80%/100% (FR-BDG-04, bersifat informatif tidak memblokir)

### 2. Modul Transaksi Berulang (FR-REC, K-12)
- [x] Enums `RecurringFrequency`, `RecurringStatus`
- [x] Migration `recurring_transactions` & `recurring_logs` dengan constraint `unique(['recurring_id', 'run_date'])`
- [x] Model `RecurringTransaction`, `RecurringLog`, `RecurringTransactionPolicy`
- [x] `RecurringService`:
  - [x] `runDue()` mengejar semua tanggal jatuh tempo ≤ hari ini secara berurutan (K-12)
  - [x] `runOne()` idempoten per tanggal, memotong saldo via `BalanceService` hanya jika cukup
  - [x] Saldo kurang: status `failed`, user dikirimi notifikasi database `RecurringFailedNotification`
  - [x] `nextRunDate()` klamp tanggal 29–31 pada frekuensi bulanan/tahunan (K-12)
- [x] Command scheduler `php artisan recurring:run` terdaftar harian di `routes/console.php`
- [x] Halaman `/recurring` (`App\Livewire\Recurring\Index`):
  - [x] Daftar jadwal, status, tombol jeda/aktifkan, ubah, hapus
  - [x] Panel riwayat eksekusi dan tombol "Jalankan ulang" untuk eksekusi yang gagal

### 3. Modul Notifikasi (FR-NTF-01, K-13)
- [x] Tabel `notifications` terpasang
- [x] Ikon lonceng di header layout dengan badge jumlah belum dibaca (`App\Livewire\Notifications\Dropdown`)
- [x] Dropdown notifikasi dengan aksi buka tautan terkait dan tombol "Tandai semua dibaca"

### 4. Modul Hutang & Piutang (FR-DBT, K-11)
- [x] Enums `DebtType`, `DebtStatus`
- [x] Migration `debts` & `debt_payments`
- [x] Model `Debt`, `DebtPayment`, `DebtPolicy`
- [x] `DebtService`:
  - [x] Hutang/piutang baru **tidak mengubah saldo** (K-11)
  - [x] `pay()` mencatat cicilan/pembayaran (hutang: debit saldo; piutang: kredit saldo)
  - [x] Validasi cicilan tidak boleh melebihi sisa pinjaman
  - [x] `refreshStatus()` otomatis mengupdate status (`unpaid`, `partial`, `paid`)
- [x] Halaman `/debts` (`App\Livewire\Debts\Index`):
  - [x] Ringkasan total hutang belum lunas & total piutang belum lunas
  - [x] Tab segmentasi Hutang vs Piutang
  - [x] Progress pelunasan, penanda dan peringatan jatuh tempo (H-3 dan terlewat)
  - [x] Modal bayar cicilan dengan pilihan sumber saldo & pratinjau saldo

### 5. Modul Rekonsiliasi Saldo (FR-RCN, K-04)
- [x] Migration `reconciliations`
- [x] Model `Reconciliation`, `ReconciliationPolicy`
- [x] `ReconciliationService`:
  - [x] `preview()` menghitung selisih saldo aplikasi vs saldo asli
  - [x] `reconcile()` mencatat rekonsiliasi dan opsional membuat transaksi penyesuaian otomatis
  - [x] Transaksi penyesuaian: `is_adjustment = true`, `category_id = null`, tidak dihitung di total pemasukan/pengeluaran standar (K-04)
- [x] Halaman `/reconciliations` (`App\Livewire\Reconciliations\Index`):
  - [x] Form perbandingan saldo dengan tombol "Sesuaikan Saldo" dan "Simpan Tanpa Menyesuaikan"
  - [x] Tabel riwayat rekonsiliasi beserta status koreksi

### 6. Dashboard: Panel "Perlu Perhatian" (FR-DSH-06)
- [x] Komponen lazy `AttentionWidget` (`livewire:dashboard.attention-widget`):
  - [x] Peringatan budget pemakaian ≥80% dan ≥100%
  - [x] Peringatan tagihan berulang yang jatuh tempo dalam 7 hari ke depan
  - [x] Peringatan hutang jatuh tempo (H-3 atau terlewat)
  - [x] Panel otomatis tersembunyi bila tidak ada item yang perlu perhatian

### 7. Pengujian Otomatis & Standar Kode Fase 3
- [x] Feature test SRS 10.2 nomor 4 (`tests/Feature/RecurringTest.php`):
  - Idempoten: command dijalankan dua kali di hari yang sama tidak menggandakan transaksi
  - Saldo kurang menghasilkan log `failed` dan notifikasi database
  - Mengejar tanggal terlewat
  - Klamp tanggal 31 pada bulan pendek/Februari
- [x] Feature test SRS 10.2 nomor 5 (`tests/Feature/DebtTest.php`):
  - Hutang baru tidak mengubah saldo (K-11)
  - Cicilan mengubah saldo dan memperbarui status (`unpaid` -> `partial` -> `paid`)
  - Cicilan tidak boleh melebihi sisa tagihan
- [x] Feature test SRS 10.2 nomor 6 (`tests/Feature/ReconciliationTest.php`):
  - Selisih positif/negatif membuat penyesuaian saldo yang benar
  - Transaksi penyesuaian tidak masuk ke total pemasukan/pengeluaran standar laporan
- [x] Feature test budget (`tests/Feature/BudgetTest.php`):
  - Kalkulasi pemakaian & status (`safe`, `warning`, `exceeded`)
  - Salin budget dari bulan lalu
- [x] Semua 86 feature tests lulus (100% pass, 248 assertions)
- [x] Standar kode bersih PSR-12 via `vendor/bin/pint`

---

## Fase 4: Polishing UI, Fitur Admin Lengkap, Riwayat Export, Testing Akhir & Deployment (Selesai)

### 1. Fitur Admin Lengkap (FR-ADM, K-14)
- [x] `/admin` Ringkasan:
  - [x] Hanya menampilkan metrik agregasi non-finansial (**COUNT** pengguna, **COUNT** transaksi, user baru bulan ini)
  - [x] Grafik pertumbuhan pendaftaran pengguna 6 bulan terakhir
  - [x] **Zero Financial Exposure**: Admin dilarang memiliki query, view, atau akses ke data finansial/nominal pengguna (K-14, FR-ADM-04, FR-ADM-05)
- [x] `/admin/users` (`App\Livewire\Admin\UsersIndex`):
  - [x] Daftar pengguna dan filter pencarian/status
  - [x] Aksi toggle status aktif/nonaktif
  - [x] Proteksi: Tombol dinonaktifkan dengan tooltip jika akun adalah diri sendiri atau admin aktif terakhir (FR-ADM-02, FR-ADM-06, DESIGN 5.13)
- [x] `/admin/categories` (`App\Livewire\Admin\CategoriesIndex`):
  - [x] CRUD template kategori default sistem (`user_id = null`)
  - [x] Banner informasi peringatan: "Perubahan hanya berlaku untuk pengguna baru" (FR-ADM-03, K-18, DESIGN 5.13)

### 2. Modul Riwayat Export (FR-RPT-06, FR-RPT-07)
- [x] Migration `export_histories` (`user_id`, `type`, `file_name`, `file_path`, `filters`, `created_at`)
- [x] Model `ExportHistory` & `ExportHistoryPolicy`
- [x] Penyimpanan otomatis file fisik ke storage saat user generate laporan PDF, laporan Excel, maupun daftar transaksi Excel
- [x] Download controller `ExportDownloadController` dengan verifikasi kepemilikan file & otorisasi ketat
- [x] Halaman `/exports` (`App\Livewire\Exports\Index`):
  - [x] Daftar riwayat ekspor, filter tipe, tanggal, tombol unduh ulang, dan hapus riwayat
  - [x] Tombol pintasan "Riwayat Export" di halaman laporan `/reports`

### 3. Polishing UI & Aksesibilitas (DESIGN.md Bagian 8)
- [x] Dark mode menyeluruh terintegrasi Tailwind class `dark:` dengan token warna konsisten
- [x] Pilihan tema di halaman profil (Terang / Gelap / Ikuti sistem) tersimpan di `localStorage`
- [x] Responsivitas mobile 360px (semua tabel dibungkus `overflow-x-auto`, form grid responsif)
- [x] Aksesibilitas:
  - [x] Indikator fokus `:focus-visible` dengan outline kontras
  - [x] Dukungan `@media (prefers-reduced-motion: reduce)` menonaktifkan animasi bagi pengguna sensitif
  - [x] Kontras teks standar WCAG AA
- [x] Komponen empty state, skeleton loading lazy-load, dan dialog konfirmasi hapus (`wire:confirm`)

### 4. Hardening Keamanan
- [x] Audit 100% Policy otorisasi: `AccountSourcePolicy`, `TransactionPolicy`, `TransferPolicy`, `DebtPolicy`, `BudgetPolicy`, `RecurringTransactionPolicy`, `ReconciliationPolicy`, `SavingsGoalPolicy`, `ExportHistoryPolicy`
- [x] Larangan edit & hapus transaksi penyesuaian rekonsiliasi (`is_adjustment = true`) di level Policy dan `TransactionService` (K-04)
- [x] Seluruh query aplikasi diproteksi dengan scope kepemilikan `user_id = auth()->id()`
- [x] Middleware `active` memblokir akses pengguna nonaktif dan me-logout otomatis jika dinonaktifkan admin
- [x] Rate limiting pada endpoint autentikasi & CSRF token protection bawaan Laravel

### 5. Pengujian Otomatis & Standar Kode Fase 4
- [x] Feature tests lengkap mencakup seluruh skenario SRS 10.2:
  - `AdminAccessTest.php`: Proteksi role, penolakan akses data finansial user oleh admin, proteksi nonaktifkan diri sendiri & admin aktif terakhir
  - `ExportHistoryTest.php`: Pencatatan riwayat saat export PDF/Excel, unduh ulang file, proteksi unduh file antar user
- [x] Seluruh 92 feature tests lulus (100% pass, 259 assertions)
- [x] Verifikasi konsistensi saldo `php artisan balances:verify` menghasilkan 100% MATCH
- [x] Format kode 100% lulus standar PSR-12 via `vendor/bin/pint --test`

### 6. Seeder & Deployment
- [x] `DemoDataSeeder` dengan akun demo `user@kasku.test` (password: `password`) dan `admin@kasku.test` (password: `password`)
- [x] Dokumentasi deployment produksi terperinci di `README.md`

---

## Catatan Implementasi
1. Format uang disimpan dalam `bigInteger` integer rupiah tanpa float/decimal.
2. Setiap mutasi saldo melewati `BalanceService` dengan row locking (`lockForUpdate`).
3. Transaksi penyesuaian rekonsiliasi (`is_adjustment = true`) tidak dihitung di rekap pemasukan/pengeluaran, grafik kategori, maupun budget, namun ditampilkan sebagai baris tersendiri pada laporan sumber saldo (K-04).
4. Transaksi berulang idempoten menggunakan `unique(['recurring_id', 'run_date'])` pada tabel `recurring_logs` (K-12).
5. Pencatatan hutang/piutang baru tidak mempengaruhi saldo akun; mutasi hanya terjadi saat transaksi pembayaran cicilan (K-11).
6. Admin memiliki layout dan route tersendiri (`/admin`), dengan larangan total terhadap akses saldo dan detail transaksi pengguna (K-14).
7. Operasi git ditangguhkan sesuai arahan user ("perihal git nanti saja").


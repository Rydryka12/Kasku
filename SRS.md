# Software Requirements Specification (SRS)

**Nama Aplikasi:** Aplikasi Tabungan & Catatan Keuangan
**Versi:** 1.1 (didesain ulang, siap implementasi)
**Penyusun:** Ryan Adryan Kusmana
**Dokumen pendamping:** `DESIGN.md` (tampilan), `CHECKLIST.md` (progres), `README.md` (setup), `.agent/skills/keuangan-app/SKILL.md` (aturan kerja agent)

> Jika ada perbedaan antara dokumen ini dan README/CHECKLIST, **dokumen ini yang menang**. Untuk urusan tampilan, `DESIGN.md` yang menang.

---

## 1. Pendahuluan

### 1.1 Tujuan
Mendefinisikan kebutuhan fungsional dan non-fungsional aplikasi web pencatatan keuangan pribadi sebagai acuan pengembangan dan pengujian.

### 1.2 Ruang Lingkup
Pengguna dapat: mencatat pemasukan/pengeluaran, mengelola banyak sumber saldo (Cash, e-wallet, bank), transfer antar sumber saldo, menabung untuk target, memantau budget, hutang piutang, transaksi berulang, rekonsiliasi, serta melihat laporan dan mengekspor ke PDF/Excel.

Aplikasi **tidak terhubung** ke Gopay, Dana, atau bank. Saldo adalah pencatatan mandiri oleh pengguna.

### 1.3 Definisi

| Istilah | Arti |
|---|---|
| Sumber saldo | Tempat uang berada: Cash, Gopay, Dana, BCA, dll |
| Saldo tersedia | Jumlah saldo seluruh sumber saldo aktif (bukan arsip) |
| Tabungan | Target menabung; dananya terpisah dari saldo tersedia |
| Total kekayaan | Saldo tersedia + total tabungan |
| Rekonsiliasi | Mencocokkan saldo aplikasi dengan saldo asli |
| Budget | Batas pengeluaran per kategori per bulan |
| Penyesuaian | Transaksi otomatis hasil rekonsiliasi (`is_adjustment = true`) |

### 1.4 Teknologi
Laravel (versi terpasang di project), Blade, Livewire, Alpine.js (bawaan Livewire, jangan install ganda), Tailwind CSS, MySQL/MariaDB, Chart.js, `maatwebsite/excel`, `barryvdh/laravel-dompdf`.

### 1.5 Perubahan dari v1.0
- Menambah bagian **2. Keputusan Desain** untuk menutup semua hal yang sebelumnya ambigu.
- Rancangan data dilengkapi tipe kolom, constraint, dan index; `user_id` ditambahkan ke semua tabel anak agar Policy sederhana.
- Menambah spesifikasi Service class, peta route, dan acceptance criteria per fase.
- "Total tabungan" kini mencakup target berstatus aktif **dan** tercapai (uangnya masih ada).
- Memperbaiki penamaan ID `FR-REC-BAL` menjadi `FR-RCN`.
- Menambah kebutuhan baru: FR-SRC-08, FR-TRX-10, FR-BDG-05, FR-SYS-01, FR-NTF-01, dan NFR-13.

---

## 2. Keputusan Desain

Keputusan berikut mengikat. Agent/developer tidak perlu bertanya lagi soal hal ini.

| ID | Topik | Keputusan |
|---|---|---|
| K-01 | Saldo tidak cukup | **Selalu ditolak** (tidak ada mode konfigurasi). Berlaku untuk pengeluaran, transfer, setoran tabungan, dan pembayaran hutang. |
| K-02 | Edit transaksi | Dalam satu `DB::transaction`: kunci baris sumber (`lockForUpdate`), balikkan efek transaksi lama, terapkan yang baru, validasi saldo cukup. Jika gagal, semua dibatalkan. |
| K-03 | Hapus transaksi/transfer | Efek saldo dibalikkan otomatis. Penghapusan pemasukan ditolak bila membuat saldo sumber menjadi negatif. |
| K-04 | Penyesuaian rekonsiliasi | Disimpan sebagai `transactions` dengan `is_adjustment = true`, `category_id = null`. Tipe `income` jika selisih positif, `expense` jika negatif. **Tidak dihitung** di total pemasukan/pengeluaran, grafik kategori, maupun budget. Ditampilkan sebagai baris terpisah "Penyesuaian saldo" di laporan sumber saldo. |
| K-05 | Arsip sumber saldo | Boleh diarsipkan hanya jika saldonya **0**. Jika belum 0, user diminta transfer dulu. Sumber arsip tidak masuk saldo tersedia dan tidak muncul di dropdown, tapi riwayatnya tetap terlihat. Tanpa riwayat sama sekali → boleh hapus permanen. |
| K-06 | Arsip kategori | Kategori yang sudah dipakai diarsipkan; tidak muncul di dropdown baru, riwayat lama tetap menampilkannya. |
| K-07 | Pajak | Hanya ada di **pengeluaran**, **transfer**, dan **transaksi berulang bertipe pengeluaran**. Pemasukan tidak punya pajak. Saldo berkurang sebesar nominal + pajak. |
| K-08 | Budget | Menghitung pengeluaran non-penyesuaian pada kategori & bulan tersebut, dengan basis **nominal + pajak**. Status: aman (<80%), waspada (80–99%), lewat (≥100%). Budget bersifat per bulan; tersedia tombol "Salin dari bulan lalu". |
| K-09 | Tabungan | `current_amount` = setoran − penarikan. Target mencapai `target_amount` → status otomatis `achieved` (tetap bisa ditarik). Target `cancelled` wajib mengembalikan seluruh dana ke sumber saldo pilihan user. Menghapus target berisi dana → sama, dana dikembalikan dulu. |
| K-10 | Total tabungan | Jumlah `current_amount` target berstatus `active` + `achieved`. |
| K-11 | Hutang/piutang baru | **Tidak mengubah saldo.** Hanya pembayaran/cicilan yang mengubah saldo (hutang: saldo berkurang; piutang: saldo bertambah). Jika user menerima uang pinjaman, ia mencatat pemasukan sendiri. |
| K-12 | Transaksi berulang | Semua tanggal jatuh tempo ≤ hari ini diproses berurutan (mengejar yang terlewat). Tanggal 29–31 pada frekuensi bulanan dipangkas ke hari terakhir bulan itu. Idempoten lewat `unique(recurring_id, run_date)`. Yang gagal karena saldo kurang dicatat `failed`, `next_run` tetap maju, dan user dapat menekan "Jalankan ulang" manual. |
| K-13 | Notifikasi | Memakai Laravel Database Notifications (tabel `notifications`), ditampilkan sebagai ikon lonceng + daftar. Tidak ada email pada v1. |
| K-14 | Admin | Dibuat hanya lewat seeder/artisan. Registrasi publik selalu `role = user`. Admin memakai layout dan route `/admin/*` terpisah, **tidak punya query apa pun ke tabel keuangan selain `COUNT`** untuk statistik. |
| K-15 | Export | Dibuat sinkron (tanpa queue). Excel dan PDF mengikuti filter aktif di halaman. Riwayat export opsional (Fase 4). |
| K-16 | Integritas saldo | `sources.current_balance` disimpan dan hanya diubah lewat `BalanceService`. Artisan command `balances:verify` menghitung ulang dari riwayat dan melaporkan selisih. |
| K-17 | Bahasa dan format | UI bahasa Indonesia; nama kelas/tabel/kolom bahasa Inggris. Rupiah `Rp 1.000.000` (tanpa desimal), tanggal `d M Y` (mis. 28 Sep 2026), zona waktu Asia/Jakarta. |
| K-18 | Registrasi | User baru otomatis mendapat salinan semua kategori default aktif. Sumber saldo tidak dibuat otomatis (onboarding di dashboard mengajak user menambah sumber pertama). |

---

## 3. Deskripsi Umum

### 3.1 Peran Pengguna

| Peran | Deskripsi | Akses |
|---|---|---|
| **User** | Pengguna umum | Seluruh fitur keuangan miliknya sendiri |
| **Admin** | Pengelola sistem | Kelola user, kategori default, statistik umum. **Tidak dapat melihat data keuangan user** |

### 3.2 Asumsi dan Batasan
- Mata uang tunggal Rupiah, disimpan sebagai integer (`bigint`).
- Data setiap user terisolasi.
- Transaksi berulang membutuhkan scheduler (cron) aktif.

---

## 4. Kebutuhan Fungsional

### 4.1 Autentikasi & Akun (FR-AUTH)

| ID | Kebutuhan |
|---|---|
| FR-AUTH-01 | Registrasi (nama, email, password) |
| FR-AUTH-02 | Login dan logout |
| FR-AUTH-03 | Reset password |
| FR-AUTH-04 | Ubah profil (nama, email) dan password |
| FR-AUTH-05 | Akun nonaktif (`is_active = false`) tidak dapat login; sesi aktifnya ditolak di request berikutnya |
| FR-AUTH-06 | Halaman dibatasi role lewat middleware; setelah login, user ke `/dashboard`, admin ke `/admin` |
| FR-AUTH-07 | User baru mendapat kategori default (K-18) |

### 4.2 Sumber Saldo (FR-SRC)

| ID | Kebutuhan |
|---|---|
| FR-SRC-01 | Tambah sumber: nama, tipe (`cash`/`ewallet`/`bank`/`other`), saldo awal, warna, ikon |
| FR-SRC-02 | Daftar dan detail sumber beserta saldo terkini dan riwayat |
| FR-SRC-03 | Ubah nama, tipe, warna, ikon (saldo tidak diubah manual; gunakan rekonsiliasi) |
| FR-SRC-04 | Arsipkan sumber (aturan K-05) |
| FR-SRC-05 | Hapus permanen hanya jika tanpa riwayat |
| FR-SRC-06 | Saldo tersedia = jumlah saldo sumber aktif, tampil di dashboard |
| FR-SRC-07 | Nama sumber unik per user |
| FR-SRC-08 | Batalkan arsip (pulihkan) sumber |

### 4.3 Kategori (FR-CAT)

| ID | Kebutuhan |
|---|---|
| FR-CAT-01 | CRUD kategori pemasukan dan pengeluaran milik user |
| FR-CAT-02 | Atribut: nama, tipe, warna, ikon |
| FR-CAT-03 | Kategori terpakai diarsipkan, bukan dihapus (K-06) |

### 4.4 Transaksi (FR-TRX)

| ID | Kebutuhan |
|---|---|
| FR-TRX-01 | Tambah **pemasukan**: sumber, kategori, nominal, tanggal, catatan |
| FR-TRX-02 | Tambah **pengeluaran**: sumber, kategori, nominal, tanggal, catatan |
| FR-TRX-03 | Pengeluaran punya **pajak opsional** (nominal dan keterangan) |
| FR-TRX-04 | Saldo berkurang sebesar nominal + pajak |
| FR-TRX-05 | Ubah dan hapus transaksi dengan koreksi saldo otomatis (K-02, K-03) |
| FR-TRX-06 | Pengeluaran ditolak bila saldo sumber tidak cukup (K-01) |
| FR-TRX-07 | Filter: rentang tanggal, tipe, kategori, sumber; pencarian pada catatan |
| FR-TRX-08 | Pagination |
| FR-TRX-09 | Perubahan saldo atomik (`DB::transaction`) |
| FR-TRX-10 | Transaksi penyesuaian (K-04) tidak dapat diubah/dihapus dari daftar transaksi biasa |

### 4.5 Transfer Antar Saldo (FR-TRF)

| ID | Kebutuhan |
|---|---|
| FR-TRF-01 | Transfer dari sumber A ke B: nominal, tanggal, catatan |
| FR-TRF-02 | Asal dan tujuan tidak boleh sama |
| FR-TRF-03 | Pajak/biaya admin opsional, dipotong dari sumber asal |
| FR-TRF-04 | Total saldo tidak berubah kecuali sebesar pajak/biaya |
| FR-TRF-05 | Transfer tidak dihitung sebagai pemasukan/pengeluaran di laporan |
| FR-TRF-06 | Lihat, ubah, hapus riwayat transfer dengan koreksi saldo |
| FR-TRF-07 | Ditolak bila saldo asal < nominal + pajak |

### 4.6 Tabungan (FR-SAV)

| ID | Kebutuhan |
|---|---|
| FR-SAV-01 | CRUD target: nama, nominal target, tenggat (opsional), ikon |
| FR-SAV-02 | **Setor** ke target dari sumber pilihan; saldo sumber berkurang |
| FR-SAV-03 | **Tarik** dari target ke sumber pilihan; saldo sumber bertambah |
| FR-SAV-04 | Progress (%), sisa nominal, estimasi tercapai (berdasarkan rata-rata setoran 3 bulan terakhir; jika belum cukup data tampilkan "belum bisa diperkirakan") |
| FR-SAV-05 | Status: `active`, `achieved`, `cancelled` (K-09) |
| FR-SAV-06 | Dashboard menampilkan **Saldo tersedia**, **Total tabungan**, **Total kekayaan** |
| FR-SAV-07 | Hapus/batalkan target berisi dana → dana dikembalikan ke sumber pilihan |
| FR-SAV-08 | Riwayat setoran/penarikan per target |

### 4.7 Dashboard (FR-DSH)

| ID | Kebutuhan |
|---|---|
| FR-DSH-01 | Total kekayaan, saldo tersedia, total tabungan, dan saldo per sumber (bar proporsi, lihat DESIGN.md) |
| FR-DSH-02 | Pemasukan vs pengeluaran bulan berjalan |
| FR-DSH-03 | Grafik pengeluaran per kategori |
| FR-DSH-04 | Grafik tren pemasukan/pengeluaran 6 bulan terakhir |
| FR-DSH-05 | Transaksi terbaru (5–8 baris) |
| FR-DSH-06 | Panel "Perlu perhatian": budget ≥80%, tagihan berulang 7 hari ke depan, hutang jatuh tempo; panel tersembunyi bila kosong |
| FR-DSH-07 | Skeleton loading (`#[Lazy]` + `placeholder()`) |
| FR-DSH-08 | Jika user belum punya sumber saldo, tampilkan onboarding "Tambah sumber saldo pertama" |

### 4.8 Laporan & Export (FR-RPT)

| ID | Kebutuhan |
|---|---|
| FR-RPT-01 | Rekap bulanan dan tahunan: pemasukan, pengeluaran, selisih |
| FR-RPT-02 | Laporan per kategori dan per sumber saldo |
| FR-RPT-03 | **Laporan pajak** per periode, dari pengeluaran dan transfer |
| FR-RPT-04 | Export laporan ke **PDF** |
| FR-RPT-05 | Export laporan dan daftar transaksi ke **Excel (.xlsx)** lewat tombol di halaman terkait |
| FR-RPT-06 | Export memakai data terbaru dan mengikuti filter aktif |
| FR-RPT-07 | Riwayat Export (opsional, Fase 4) |

### 4.9 Budget (FR-BDG)

| ID | Kebutuhan |
|---|---|
| FR-BDG-01 | Batas pengeluaran per kategori per bulan |
| FR-BDG-02 | Tampilkan pemakaian (nominal dan persen) |
| FR-BDG-03 | Peringatan di 80% dan saat ≥100% (K-08) |
| FR-BDG-04 | Peringatan tampil di dashboard dan di form saat menambah pengeluaran pada kategori tersebut (tidak memblokir) |
| FR-BDG-05 | Tombol "Salin dari bulan lalu" |

### 4.10 Transaksi Berulang (FR-REC)

| ID | Kebutuhan |
|---|---|
| FR-REC-01 | Jadwal: tipe, nominal, pajak (khusus pengeluaran), kategori, sumber, frekuensi (harian/mingguan/bulanan/tahunan), tanggal mulai, tanggal berakhir opsional |
| FR-REC-02 | Scheduler harian menjalankan transaksi jatuh tempo (K-12) |
| FR-REC-03 | Pengeluaran berulang memotong saldo **hanya jika cukup** |
| FR-REC-04 | Saldo kurang → log `failed`, saldo tidak berubah, user dapat notifikasi (K-13) |
| FR-REC-05 | Jeda, aktifkan, ubah, hapus jadwal |
| FR-REC-06 | Riwayat eksekusi (berhasil/gagal) dan tombol "Jalankan ulang" untuk yang gagal |
| FR-REC-07 | Idempoten per tanggal |

### 4.11 Rekonsiliasi Saldo (FR-RCN)

| ID | Kebutuhan |
|---|---|
| FR-RCN-01 | User memasukkan saldo asli suatu sumber |
| FR-RCN-02 | Sistem menampilkan selisih terhadap saldo aplikasi |
| FR-RCN-03 | User dapat membuat transaksi penyesuaian otomatis untuk menutup selisih (K-04) |
| FR-RCN-04 | Riwayat rekonsiliasi tersimpan |

### 4.12 Hutang & Piutang (FR-DBT)

| ID | Kebutuhan |
|---|---|
| FR-DBT-01 | Catat hutang (kita berhutang) dan piutang: pihak, nominal, tanggal, jatuh tempo, catatan |
| FR-DBT-02 | Catat pembayaran/cicilan lewat sumber saldo; saldo berubah (K-11); cicilan ≤ sisa |
| FR-DBT-03 | Status: `unpaid`, `partial`, `paid` (otomatis) |
| FR-DBT-04 | Penanda dan peringatan jatuh tempo (H-3 dan terlewat) |
| FR-DBT-05 | Ringkasan total hutang dan total piutang yang belum lunas |

### 4.13 Fitur Admin (FR-ADM)

| ID | Kebutuhan |
|---|---|
| FR-ADM-01 | Daftar user: nama, email, status, tanggal daftar |
| FR-ADM-02 | Aktif/nonaktifkan user |
| FR-ADM-03 | CRUD kategori default (disalin ke user baru; perubahan tidak memengaruhi user lama) |
| FR-ADM-04 | Statistik umum: jumlah user, jumlah transaksi (COUNT), pertumbuhan user per bulan |
| FR-ADM-05 | Admin **tidak dapat** melihat isi transaksi, saldo, atau tabungan user |
| FR-ADM-06 | Admin tidak dapat menghapus/menonaktifkan dirinya sendiri atau admin terakhir |

### 4.14 Sistem & Notifikasi

| ID | Kebutuhan |
|---|---|
| FR-SYS-01 | Command `php artisan balances:verify` melaporkan sumber yang `current_balance`-nya tidak cocok dengan hitungan dari riwayat (K-16) |
| FR-NTF-01 | Ikon lonceng menampilkan notifikasi belum dibaca (transaksi berulang gagal, budget lewat, hutang jatuh tempo), dapat ditandai dibaca |

---

## 5. Kebutuhan Non-Fungsional

| ID | Kategori | Kebutuhan |
|---|---|---|
| NFR-01 | Performa | Navigasi `wire:navigate`; halaman utama < 2 detik; hindari N+1 (pakai eager loading) |
| NFR-02 | UX | Skeleton loading di dashboard, tabel, grafik |
| NFR-03 | UX | Responsif (mobile, tablet, desktop); Alpine.js untuk modal, dropdown, toast, konfirmasi |
| NFR-04 | Keamanan | Password ter-hash, CSRF, validasi input, proteksi XSS |
| NFR-05 | Keamanan | Otorisasi Policy; setiap query difilter `user_id` |
| NFR-06 | Integritas | Uang integer; mutasi saldo dalam DB transaction dengan `lockForUpdate` |
| NFR-07 | Integritas | Soft delete/arsip pada data dengan riwayat |
| NFR-08 | Ketersediaan | Backup database berkala |
| NFR-09 | Kompatibilitas | Chrome, Firefox, Edge, Safari terbaru |
| NFR-10 | Maintainability | Logika bisnis di Service class, PSR-12 (Laravel Pint), ada seeder dan factory |
| NFR-11 | Lokalisasi | Bahasa Indonesia, format Rupiah dan tanggal (K-17) |
| NFR-12 | Pengujian | Feature test untuk saldo, transfer, tabungan, berulang, hutang, rekonsiliasi, dan isolasi data |
| NFR-13 | Aksesibilitas | Kontras teks memenuhi WCAG AA, fokus keyboard terlihat, informasi tidak hanya lewat warna (selalu ada tanda +/− atau label), hormati `prefers-reduced-motion` |

---

## 6. Rancangan Data

Semua kolom uang: `unsignedBigInteger` kecuali disebut lain. Semua tabel punya `id` dan `timestamps`. Semua foreign key `cascadeOnDelete` kecuali disebut lain.

### 6.1 Tabel

| Tabel | Kolom | Constraint / Index |
|---|---|---|
| `users` | name, email, password, `role` enum(`user`,`admin`) default `user`, `is_active` bool default true | unique(email) |
| `sources` | user_id, name, `type` enum(`cash`,`ewallet`,`bank`,`other`), initial_balance, `current_balance` (signed bigint), color (char 7), icon, archived_at null | unique(user_id, name); index(user_id, archived_at) |
| `categories` | user_id **null** (null = template default), name, `type` enum(`income`,`expense`), color, icon, archived_at null | index(user_id, type, archived_at); keunikan nama divalidasi di aplikasi |
| `transactions` | user_id, source_id, category_id null, `type` enum(`income`,`expense`), `is_adjustment` bool default false, amount (>0), tax_amount default 0, tax_note null, date, note null, recurring_id null | index(user_id, date); index(user_id, source_id); index(user_id, category_id); FK source/category/recurring `restrictOnDelete` / `nullOnDelete` untuk recurring |
| `transfers` | user_id, from_source_id, to_source_id, amount (>0), tax_amount default 0, date, note null | check from ≠ to (validasi + constraint bila didukung); index(user_id, date) |
| `savings_goals` | user_id, name, target_amount, current_amount default 0, deadline null, icon, `status` enum(`active`,`achieved`,`cancelled`) | index(user_id, status) |
| `savings_entries` | user_id, goal_id, source_id, `type` enum(`deposit`,`withdraw`), amount, date, note null | index(goal_id, date) |
| `budgets` | user_id, category_id, `month` char(7) format `YYYY-MM`, limit_amount | unique(user_id, category_id, month) |
| `recurring_transactions` | user_id, source_id, category_id null, `type` enum(`income`,`expense`), amount, tax_amount default 0, note null, `frequency` enum(`daily`,`weekly`,`monthly`,`yearly`), start_date, next_run, end_date null, `status` enum(`active`,`paused`,`ended`) | index(status, next_run) |
| `recurring_logs` | user_id, recurring_id, run_date, `status` enum(`success`,`failed`), message null, transaction_id null | **unique(recurring_id, run_date)** |
| `reconciliations` | user_id, source_id, app_balance (signed), actual_balance (signed), difference (signed), adjusted bool default false, adjustment_transaction_id null, date | index(user_id, source_id) |
| `debts` | user_id, `type` enum(`debt`,`receivable`), person, amount, paid_amount default 0, due_date null, `status` enum(`unpaid`,`partial`,`paid`), note null | index(user_id, status, due_date) |
| `debt_payments` | user_id, debt_id, source_id, amount, date, note null | index(debt_id) |
| `export_histories` (opsional) | user_id, type, filters json, file_path | Fase 4 |
| `notifications` | bawaan Laravel (`php artisan notifications:table`) | |

### 6.2 Relasi
- User 1—N Sources, Categories, Transactions, Transfers, SavingsGoals, Budgets, RecurringTransactions, Debts, Reconciliations
- Source 1—N Transactions, Transfers (asal/tujuan), SavingsEntries, DebtPayments, Reconciliations
- SavingsGoal 1—N SavingsEntries; Debt 1—N DebtPayments; RecurringTransaction 1—N RecurringLogs, 1—N Transactions

### 6.3 Rumus Saldo

```
saldo_sumber = saldo_awal
             + pemasukan (termasuk penyesuaian positif)
             - (pengeluaran + pajak)        (termasuk penyesuaian negatif)
             + transfer_masuk
             - (transfer_keluar + pajak_transfer)
             - setoran_tabungan + penarikan_tabungan
             - pembayaran_hutang + penerimaan_piutang

saldo_tersedia = jumlah saldo_sumber (sumber tidak diarsipkan)
total_tabungan = jumlah current_amount (target active + achieved)
total_kekayaan = saldo_tersedia + total_tabungan
total_pajak    = jumlah tax_amount (transactions, non-adjustment) + tax_amount (transfers)
```

---

## 7. Spesifikasi Service (`app/Services`)

Komponen Livewire hanya memvalidasi input, memanggil service, lalu menampilkan hasil. Semua service melempar exception domain (`InsufficientBalanceException`, dll) yang ditangkap komponen dan ditampilkan sebagai pesan error di field terkait.

| Service | Method utama | Catatan |
|---|---|---|
| `BalanceService` | `credit(Source, int)`, `debit(Source, int)`, `assertSufficient(Source, int)`, `recalculate(Source): int` | Satu-satunya tempat yang mengubah `current_balance`; selalu `lockForUpdate` di dalam `DB::transaction` |
| `TransactionService` | `create(User, array)`, `update(Transaction, array)`, `delete(Transaction)` | Menolak ubah/hapus transaksi penyesuaian |
| `TransferService` | `create`, `update`, `delete` | Debit asal (nominal + pajak), kredit tujuan (nominal) |
| `SavingService` | `deposit`, `withdraw`, `cancel(Goal, Source)`, `syncStatus(Goal)` | Menjaga `current_amount` dan status |
| `RecurringService` | `runDue(Carbon $today)`, `runOne(Recurring, Carbon)`, `nextRunDate(Recurring)` | Idempoten (K-12); menulis `recurring_logs` dan mengirim notifikasi gagal |
| `DebtService` | `create`, `pay(Debt, Source, int, date)`, `refreshStatus(Debt)` | Cicilan tidak boleh melebihi sisa |
| `ReconciliationService` | `preview(Source, int actual)`, `reconcile(Source, int actual, bool adjust)` | Membuat transaksi penyesuaian (K-04) |
| `ReportService` | `summary`, `byCategory`, `bySource`, `tax` | Dipakai bersama oleh halaman, PDF, dan Excel agar angkanya identik |
| `BudgetService` | `usage(Budget)`, `status(Budget)`, `copyFromPreviousMonth(User, month)` | |

---

## 8. Peta Route dan Halaman

| URL | Komponen Livewire | Role |
|---|---|---|
| `/login`, `/register`, `/forgot-password`, `/reset-password/{token}` | dari starter kit auth | guest |
| `/dashboard` | `Dashboard` (+ sub-komponen lazy) | user |
| `/sources`, `/sources/{source}` | `Sources\Index`, `Sources\Show` | user |
| `/categories` | `Categories\Index` | user |
| `/transactions` | `Transactions\Index` (form catat sebagai modal global) | user |
| `/transfers` | `Transfers\Index` | user |
| `/savings`, `/savings/{goal}` | `Savings\Index`, `Savings\Show` | user |
| `/budgets` | `Budgets\Index` | user |
| `/recurring` | `Recurring\Index` | user |
| `/debts` | `Debts\Index` | user |
| `/reconciliations` | `Reconciliations\Index` | user |
| `/reports` | `Reports\Index` (+ controller export PDF/Excel) | user |
| `/profile` | `Profile` | user, admin |
| `/admin` | `Admin\Dashboard` | admin |
| `/admin/users` | `Admin\Users` | admin |
| `/admin/categories` | `Admin\DefaultCategories` | admin |

Route user dibungkus `auth`, `active`, `role:user`; route admin dibungkus `auth`, `active`, `role:admin`.

---

## 9. Antarmuka

Seluruh spesifikasi tampilan (design token, komponen, wireframe per halaman, copywriting) ada di **`DESIGN.md`**. Setiap halaman list memakai skeleton loading dan navigasi memakai `wire:navigate`.

---

## 10. Prioritas, Acceptance Criteria, dan Definition of Done

### 10.1 Fase

| Fase | Isi | Acceptance criteria (ringkas) |
|---|---|---|
| 1 (MVP) | Auth + role, sumber saldo, kategori, transaksi (+pajak), transfer (+pajak), dashboard dasar, layout sesuai DESIGN.md | User baru bisa daftar → tambah sumber → catat pemasukan/pengeluaran → transfer; saldo selalu benar; user A tidak bisa mengakses data user B; admin tidak bisa membuka data keuangan; `balances:verify` bersih |
| 2 | Tabungan, laporan, export PDF/Excel, laporan pajak | Setor/tarik mengubah saldo sumber dengan benar; total kekayaan = saldo + tabungan; angka halaman = PDF = Excel |
| 3 | Budget + peringatan, transaksi berulang, hutang piutang, rekonsiliasi | Scheduler dua kali di hari yang sama tidak menggandakan transaksi; saldo kurang → log gagal + notifikasi; cicilan mengubah saldo dan status hutang |
| 4 | Fitur admin lengkap, riwayat export, polishing UI, dark mode, testing, deployment | Semua feature test lulus; tampilan rapi di lebar 360 px; deployment + cron aktif |

### 10.2 Feature test minimum (NFR-12)
1. Pengeluaran/pemasukan/edit/hapus menjaga saldo benar; pengeluaran ditolak saat saldo kurang.
2. Transfer: asal ≠ tujuan, pajak terpotong dari asal, hapus mengembalikan saldo.
3. Tabungan: setor, tarik, batalkan target berisi dana.
4. Berulang: idempoten, saldo kurang menghasilkan `failed`, mengejar tanggal terlewat, klamp tanggal 31.
5. Hutang: cicilan mengubah saldo dan status; tidak boleh melebihi sisa.
6. Rekonsiliasi: selisih positif/negatif membuat penyesuaian yang benar dan tidak masuk total pemasukan/pengeluaran.
7. Isolasi: user A mendapat 403/404 untuk data user B di semua modul; admin mendapat 403 pada route user; user biasa mendapat 403 pada route admin; akun nonaktif tidak bisa login.

### 10.3 Definition of Done per fitur
Migration + factory + model + enum → Service → Policy → komponen Livewire tipis → Blade sesuai `DESIGN.md` (skeleton, empty state, error state, responsif, dark mode) → feature test lulus → `vendor/bin/pint` bersih → item `CHECKLIST.md` dicentang → commit.

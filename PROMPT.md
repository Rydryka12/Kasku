# Prompt untuk Antigravity

Tempel prompt sesuai fase. Mulai dari **Prompt 1**. Pindah ke fase berikutnya hanya setelah hasil fase sebelumnya kamu cek dan commit.

---

## Persiapan (sekali saja, sebelum prompt 1)

Taruh file di root project Laravel:

```
keuangan-app/
├── SRS.md                      ← ganti dengan versi baru
├── DESIGN.md
├── CHECKLIST.md
├── README.md
├── PROMPT.md                   (opsional, buat arsip)
└── .agent/skills/keuangan-app/SKILL.md
```

Lalu:

```bash
git add . && git commit -m "docs: SRS v1.1, DESIGN, skill agent"
```

Restart sesi agent di Antigravity supaya skill terbaca.

---

## Prompt 1: Fase 1 (MVP)

```
Kamu adalah senior Laravel engineer. Kita membangun "Aplikasi Tabungan & Catatan Keuangan" di project Laravel ini (setup awal sudah selesai).

LANGKAH 0: BACA DULU (jangan menulis kode sebelum selesai membaca):
1. .agent/skills/keuangan-app/SKILL.md (aturan kerja wajib, ikuti persis)
2. SRS.md (sumber kebenaran fungsional; pahami terutama bagian 2 "Keputusan Desain", bagian 6 "Rancangan Data", bagian 7 "Spesifikasi Service", bagian 8 "Peta Route")
3. DESIGN.md (sumber kebenaran tampilan: token, komponen, wireframe, copywriting)
4. CHECKLIST.md dan README.md
5. Inspeksi project: composer.json, package.json, routes/, app/, resources/views, .env, dan starter kit auth yang terpasang. Catat versi Laravel/Livewire dan sesuaikan sintaks kode dengan versi tersebut. Jangan menimpa auth yang sudah berjalan.

Bila SRS.md bertentangan dengan README/CHECKLIST, SRS.md menang. ERD dan wireframe sudah digantikan oleh SRS bagian 6 dan DESIGN.md, jadi tandai dua item itu di CHECKLIST.md sebagai dilewati [-].

TUGAS: kerjakan FASE 1 (MVP) sampai selesai, berurutan:
1. Sisa setup: cek .env (timezone Asia/Jakarta, locale id, database), pastikan Livewire dan Alpine tidak terpasang ganda, pasang chart.js, maatwebsite/excel, barryvdh/laravel-dompdf bila belum.
2. Fondasi tampilan sesuai DESIGN.md: token warna (CSS variable + Tailwind theme, light/dark), font Plus Jakarta Sans, layout aplikasi (sidebar desktop, bottom nav mobile), layout admin terpisah, helper rupiah(), dan Blade component dasar (money, money-input, modal, dropdown, toast, confirm, skeleton, badge, progress, empty, source-dot).
3. Auth + role: kolom role & is_active, enum Role, middleware active dan role, seeder admin, redirect sesuai role, registrasi selalu role user, halaman profil.
4. Kategori: migration/model, CRUD user, seeder kategori default, salin kategori default ke user baru saat registrasi (K-18), pengarsipan.
5. Sumber saldo: migration/model, CRUD Livewire, arsip sesuai K-05, policy, total saldo tersedia, halaman detail dengan riwayat.
6. BalanceService (credit, debit, assertSufficient, recalculate) + command balances:verify.
7. Transaksi: migration/model, TransactionService (create/update/delete dengan koreksi saldo, pajak opsional pada pengeluaran, tolak saldo kurang), form Catat sebagai modal global, halaman /transactions dengan filter, pencarian, pagination, filter tersimpan di URL.
8. Transfer: migration/model, TransferService (biaya admin opsional), halaman /transfers dengan riwayat, edit/hapus dengan koreksi saldo.
9. Dashboard dasar: total kekayaan, bar proporsi per sumber, pemasukan vs pengeluaran bulan ini, grafik pengeluaran per kategori, transaksi terbaru, onboarding jika belum ada sumber. Setiap blok #[Lazy] dengan skeleton.
10. Feature test sesuai SRS 10.2 nomor 1, 2, dan 7 (saldo, transfer, isolasi data & role). Seeder data demo (user demo + sumber + beberapa transaksi).

CARA KERJA:
- Buat daftar tugas singkat lalu LANGSUNG eksekusi tanpa menunggu persetujuan. Jangan berhenti untuk bertanya kecuali benar-benar terblokir; untuk hal yang belum jelas, pakai keputusan di SRS bagian 2, atau ambil pilihan paling sederhana dan catat di "Catatan implementasi" di akhir CHECKLIST.md.
- Kerjakan satu modul per giliran: migration → model/enum/factory → service → policy → route → komponen Livewire → Blade sesuai DESIGN.md → test → jalankan `php artisan test` dan `vendor/bin/pint` → centang CHECKLIST.md → git commit (pesan jelas, satu commit per modul).
- Ikuti "Aturan wajib" di SKILL.md, khususnya: uang integer, saldo hanya lewat BalanceService dalam DB::transaction + lockForUpdate, semua query difilter user_id + Policy, admin tidak boleh menyentuh data keuangan, logika bisnis di Service.
- Uji tampilan di lebar 360 px dan 1280 px, light dan dark, bila kamu punya akses browser.

HASIL AKHIR (laporkan singkat):
- Daftar modul yang selesai + item CHECKLIST yang dicentang
- Hasil `php artisan test` dan `balances:verify`
- Cara menjalankan (perintah dan akun admin/demo dari seeder)
- Catatan implementasi atau keputusan yang kamu ambil sendiri
- Hal yang belum selesai atau perlu saya cek
Jangan lanjut ke Fase 2 sebelum saya minta.
```

---

## Prompt 2: Fase 2 (Tabungan, Laporan, Export)

```
Lanjutkan ke FASE 2. Baca ulang .agent/skills/keuangan-app/SKILL.md, SRS.md (bagian 4.6, 4.8, 6, 7), dan DESIGN.md (5.6, 5.11), lalu cek state project saat ini (git log, CHECKLIST.md).

Kerjakan berurutan:
1. Tabungan: migration/model savings_goals & savings_entries, SavingService (deposit, withdraw, cancel dengan pengembalian dana, syncStatus), CRUD target, setor/tarik dengan pratinjau saldo, progress + estimasi tercapai, riwayat per target, dashboard menampilkan saldo tersedia, total tabungan, total kekayaan (K-09, K-10).
2. ReportService (summary, byCategory, bySource, tax) sebagai satu-satunya sumber angka laporan.
3. Halaman /reports: rekap bulanan/tahunan, per kategori, per sumber saldo, laporan pajak (DESIGN 5.11).
4. Export PDF (DomPDF) dan Excel (maatwebsite/excel) untuk laporan dan daftar transaksi, memakai ReportService dan mengikuti filter aktif; tombol di halaman terkait.
5. Feature test SRS 10.2 nomor 3, plus test bahwa angka halaman = PDF = Excel.

Cara kerja, aturan, dan format hasil akhir sama seperti Fase 1 (satu modul per commit, test hijau, pint bersih, centang CHECKLIST). Jangan lanjut ke Fase 3 sebelum saya minta.
```

---

## Prompt 3: Fase 3 (Budget, Berulang, Hutang, Rekonsiliasi)

```
Lanjutkan ke FASE 3. Baca ulang SKILL.md, SRS.md (bagian 4.9 sampai 4.12, 4.14, K-08, K-11, K-12, K-13), dan DESIGN.md (5.7 sampai 5.10), lalu cek state project.

Kerjakan berurutan:
1. Budget: model, BudgetService (usage, status, copyFromPreviousMonth), CRUD per kategori per bulan, progress + peringatan 80%/100%, peringatan di form Catat (informasi, tidak memblokir).
2. Transaksi berulang: model recurring_transactions & recurring_logs, RecurringService (runDue, runOne, nextRunDate, idempoten lewat unique(recurring_id, run_date), mengejar tanggal terlewat, klamp tanggal 29-31), command harian yang didaftarkan di scheduler, potong saldo hanya jika cukup, log gagal + notifikasi database, halaman /recurring dengan riwayat eksekusi dan "Jalankan ulang".
3. Notifikasi: tabel notifications, ikon lonceng di header, tandai dibaca.
4. Hutang & piutang: model debts & debt_payments, DebtService (pay, refreshStatus; cicilan tidak boleh melebihi sisa; hutang baru tidak mengubah saldo sesuai K-11), halaman /debts, peringatan jatuh tempo.
5. Rekonsiliasi: ReconciliationService (preview, reconcile), transaksi penyesuaian sesuai K-04, riwayat, halaman /reconciliations.
6. Dashboard: panel "Perlu perhatian" (budget, tagihan berulang 7 hari, hutang jatuh tempo).
7. Feature test SRS 10.2 nomor 4, 5, 6 (termasuk menjalankan command dua kali di hari yang sama tidak menggandakan transaksi).

Cara kerja, aturan, dan format hasil akhir sama seperti Fase 1. Jangan lanjut ke Fase 4 sebelum saya minta.
```

---

## Prompt 4: Fase 4 (Admin, Polishing, Testing, Deployment)

```
Lanjutkan ke FASE 4. Baca ulang SKILL.md, SRS.md (4.13, 5, 10), DESIGN.md (5.13, 6, 7, 8), lalu cek state project.

Kerjakan:
1. Admin lengkap: /admin ringkasan (COUNT saja, pertumbuhan user), /admin/users (aktif/nonaktifkan, tidak bisa diri sendiri/admin terakhir), /admin/categories (CRUD kategori default). Pastikan admin tidak punya jalur apa pun ke data keuangan user; tambah test untuk itu.
2. Riwayat export (export_histories) dan halaman untuk mengunduh ulang.
3. Polishing sesuai DESIGN.md bagian 8: dark mode penuh + pilihan tema di profil, responsif 360 px, aksesibilitas (fokus, kontras, aria, reduced motion), skeleton/empty/error di semua halaman, konfirmasi hapus.
4. Hardening keamanan: audit semua Policy, semua query ber-user_id, validasi kepemilikan id dari client, rate limit login, cek CSRF/XSS.
5. Lengkapi feature test sampai semua poin SRS 10.2 tercakup; jalankan `php artisan test`, `balances:verify`, dan `vendor/bin/pint`.
6. Seeder data demo yang rapi.
7. Dokumentasi deployment di README.md: environment produksi, `php artisan optimize`, build aset, cron `* * * * * php /path/artisan schedule:run`, backup database.

Format hasil akhir sama seperti Fase 1, ditambah ringkasan audit keamanan dan daftar risiko yang tersisa.
```

---

## Prompt perbaikan (pakai kapan saja)

```
Ada masalah: <jelaskan gejala, halaman, dan pesan error>.
Baca SKILL.md dan bagian SRS/DESIGN yang relevan. Cari akar masalah (jangan hanya menutup gejala), perbaiki, tambahkan test yang mengunci perbaikannya, jalankan `php artisan test` dan `vendor/bin/pint`, lalu commit dengan pesan `fix(...)`. Jelaskan singkat penyebab dan perbaikannya.
```

## Prompt review (opsional, sebelum commit besar)

```
Lakukan code review pada perubahan terakhir terhadap aturan wajib di SKILL.md bagian 3 dan Definition of Done bagian 7. Cari: update saldo di luar BalanceService, query tanpa user_id, komponen yang berisi logika bisnis, N+1, tampilan yang menyimpang dari DESIGN.md, dan test yang hilang. Laporkan temuan berurutan berdasarkan tingkat bahaya, lalu perbaiki yang bahayanya tinggi.
```

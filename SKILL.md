---
name: keuangan-app
description: Use this skill for ANY work on the Aplikasi Tabungan & Catatan Keuangan Laravel project (Livewire + Alpine + Tailwind): creating migrations, models, services, policies, Livewire components, Blade views, tests, or updating the checklist. It defines the source-of-truth documents (SRS.md, DESIGN.md, CHECKLIST.md), mandatory money/balance rules, architecture conventions, the per-feature workflow, and the definition of done. Trigger whenever the task touches sources, categories, transactions, transfers, savings, budgets, recurring transactions, debts, reconciliation, reports/export, admin, dashboard, or UI of this app.
---

# Skill: keuangan-app

Panduan kerja wajib untuk membangun "Aplikasi Tabungan & Catatan Keuangan". Baca seluruh file ini sebelum menulis kode.

## 1. Sumber kebenaran (urutan prioritas)

1. `SRS.md`: fungsional, data, service, route, keputusan (bagian 2 "Keputusan Desain" **mengikat**, jangan bertanya ulang).
2. `DESIGN.md`: seluruh tampilan (token warna, tipografi, komponen, wireframe, copywriting).
3. `CHECKLIST.md`: progres. Centang `[x]` hanya setelah fitur benar-benar selesai dan teruji.
4. `README.md`: setup dan aturan teknis ringkas.

Jika dokumen bertentangan, ikuti urutan di atas. Jika ada hal yang tidak dicakup dokumen, ambil keputusan paling sederhana yang konsisten dengan SRS, lalu catat di bagian "Catatan implementasi" pada akhir `CHECKLIST.md`. Bertanya ke user hanya jika benar-benar terblokir.

## 2. Sesuaikan dengan project yang terpasang

Sebelum kode pertama, periksa `composer.json`, `package.json`, `routes/`, `resources/views/layouts`, dan starter kit auth yang terpasang (Breeze Livewire, Livewire starter kit + Volt/Flux, atau lainnya). **Jangan berasumsi versi.** Ikuti sintaks Livewire/Laravel versi yang terpasang (mis. atribut `#[Lazy]`, `#[Url]`, `#[Validate]`, Form Object). Jangan menimpa scaffolding auth yang sudah berjalan; modifikasi seperlunya.

Livewire sudah membawa Alpine.js. **Jangan install Alpine lewat npm/CDN** (menyebabkan "Alpine already initialized").

## 3. Aturan wajib (tidak boleh dilanggar)

**Uang dan saldo**
1. Uang = `bigint` integer rupiah. Dilarang `float`/`decimal`/`double`.
2. `sources.current_balance` hanya boleh diubah lewat `BalanceService` (`credit`/`debit`) di dalam `DB::transaction` dengan `lockForUpdate()` pada baris sumber. Dilarang `$source->increment()` atau update saldo langsung di komponen, controller, atau model observer.
3. Setiap operasi yang mengubah saldo (transaksi, transfer, tabungan, hutang, rekonsiliasi, berulang) memakai Service class dan atomik.
4. Saldo tidak cukup → selalu tolak (K-01) dengan `InsufficientBalanceException`, tampil sebagai error di field.
5. Edit = balikkan efek lama + terapkan efek baru dalam satu transaksi DB (K-02). Hapus = balikkan efek (K-03).
6. Penyesuaian rekonsiliasi (`is_adjustment`) tidak masuk total pemasukan/pengeluaran, grafik kategori, budget, atau pajak (K-04).

**Keamanan dan isolasi**
7. Setiap query data user difilter `user_id` **dan** dilindungi Policy. Jangan pernah mengambil record lewat id tanpa scoping ke user.
8. Admin tidak boleh menyentuh tabel keuangan selain `COUNT` untuk statistik. Layout, route, dan komponen admin terpisah. Ada test yang membuktikannya.
9. Middleware: `auth`, `active` (tolak akun nonaktif), `role:user` / `role:admin`. Registrasi publik selalu `role = user`.
10. Validasi lewat Form Object Livewire atau `#[Validate]`. Jangan percaya id dari client: pastikan `source_id`, `category_id`, dsb. milik user yang sedang login dan tidak diarsipkan.
11. Gunakan `{{ }}` (escaped) di Blade; `{!! !!}` dilarang kecuali ada alasan tertulis.

**Arsitektur**
12. Logika bisnis di `app/Services`. Komponen Livewire tipis: validasi → panggil service → dispatch toast → render.
13. Enum PHP untuk `Role`, `TransactionType`, `SourceType`, `CategoryType`, `GoalStatus`, `EntryType`, `DebtType`, `DebtStatus`, `RecurringFrequency`, `RecurringStatus`, dll, di `app/Enums`.
14. Arsip/soft delete sesuai SRS (K-05, K-06). Jangan hapus permanen data yang punya riwayat.
15. Hindari N+1: pakai eager loading dan `withSum`/agregasi SQL untuk laporan. Tambahkan index sesuai SRS bagian 6.
16. Kode mengikuti PSR-12; jalankan `vendor/bin/pint` sebelum commit.

**Tampilan**
17. Semua tampilan mengikuti `DESIGN.md`: token warna lewat CSS variable/Tailwind theme (dilarang hex langsung di Blade), Plus Jakarta Sans, daftar sebagai baris buku besar, bar proporsi di dashboard, tanpa gradien dekoratif.
18. Setiap halaman/daftar wajib punya: skeleton loading (`#[Lazy]` + `placeholder()` atau `wire:loading`), empty state, error state, responsif sampai 360 px, dark mode, fokus keyboard terlihat.
19. Navigasi memakai `wire:navigate`. Interaksi (modal, dropdown, toast, konfirmasi hapus) memakai Alpine.js lewat Blade component di `DESIGN.md` bagian 4.
20. Teks UI berbahasa Indonesia mengikuti tabel copywriting `DESIGN.md`; nama kelas/tabel/kolom bahasa Inggris. Format Rupiah lewat helper `rupiah()`; tanggal `d M Y`; timezone `Asia/Jakarta`; locale `id`.

## 4. Konvensi struktur

```
app/Enums/  app/Models/  app/Policies/  app/Services/  app/Exports/
app/Livewire/{Sources,Categories,Transactions,Transfers,Savings,Budgets,Recurring,Debts,Reconciliations,Reports,Admin}/
app/Console/Commands/ (RunRecurring, VerifyBalances)
app/Exceptions/ (InsufficientBalanceException, dll)
resources/views/components/   (komponen dari DESIGN.md)
resources/views/livewire/
database/{migrations,factories,seeders}/
tests/Feature/
```

Penamaan: model tunggal (`Source`, `Transaction`), tabel jamak snake_case sesuai SRS bagian 6, komponen Livewire per halaman `Index`/`Show`, service `NamaService`.

## 5. Alur kerja per fitur (ulangi untuk setiap item)

1. Baca FR terkait di SRS dan wireframe di DESIGN.
2. Migration + model + enum + factory (+ seeder bila perlu).
3. Service (+ exception domain).
4. Policy dan pendaftaran route (dengan middleware yang benar).
5. Komponen Livewire tipis + Blade sesuai DESIGN (skeleton, empty, error, responsif, dark).
6. Feature test (lihat SRS 10.2). Test wajib untuk semua Service yang mengubah saldo dan untuk isolasi data.
7. Jalankan `php artisan test` dan `vendor/bin/pint`. Perbaiki sampai hijau.
8. Centang `CHECKLIST.md`, lalu commit.

Jangan mengubah 10 modul sekaligus tanpa pengujian. Selesaikan satu modul, uji, commit, lanjut.

## 6. Urutan pengerjaan per fase

- **Fase 1 (MVP):** layout + token desain + helper/komponen dasar → auth + role + active middleware + seeder admin → kategori (default + salin ke user baru) → sumber saldo → `BalanceService` → transaksi (+pajak) → transfer (+pajak) → dashboard dasar (bar proporsi, bulan ini, terbaru).
- **Fase 2:** tabungan → laporan (rekap, kategori, sumber, pajak) → export PDF/Excel via `ReportService` yang sama.
- **Fase 3:** budget → transaksi berulang (command + scheduler + notifikasi) → hutang piutang → rekonsiliasi → panel "Perlu perhatian" di dashboard.
- **Fase 4:** admin lengkap, riwayat export, polishing (dark mode, mobile, a11y), seeder demo, `balances:verify`, hardening keamanan, dokumentasi deployment.

## 7. Definition of Done

Fitur dianggap selesai bila: semua aturan di bagian 3 terpenuhi; test fitur lulus (`php artisan test` hijau); `pint` bersih; tampilan sesuai `DESIGN.md` di 360 px dan 1280 px, light dan dark; item `CHECKLIST.md` dicentang; commit dengan pesan jelas (`feat(transactions): add expense with tax`, `test(transfer): ...`, `fix(...)`).

## 8. Larangan cepat
- Tidak ada `float` untuk uang. Tidak ada update saldo di luar `BalanceService`.
- Tidak ada query tanpa `user_id`. Tidak ada akses admin ke data keuangan.
- Tidak ada logika bisnis di Blade/komponen. Tidak ada `{!! !!}` untuk input user.
- Tidak install Alpine dua kali. Tidak menambah package di luar daftar SRS 1.4 tanpa alasan tertulis.
- Tidak mengklaim selesai tanpa menjalankan test.

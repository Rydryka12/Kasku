# DESIGN.md: Mockup dan Sistem Desain

Dokumen ini adalah **sumber kebenaran tampilan** untuk Aplikasi Tabungan & Catatan Keuangan. Fungsinya menggantikan wireframe/mockup. Implementasi memakai Blade + Livewire + Alpine.js + Tailwind CSS.

---

## 1. Konsep

**Subjek:** buku kas pribadi untuk orang Indonesia yang uangnya tersebar di Cash, Gopay, Dana, dan rekening bank.

**Tugas utama aplikasi:** mencatat satu transaksi dalam waktu kurang dari 10 detik, dan menjawab "uangku sekarang ada di mana saja?" dalam sekali lihat.

**Karakter visual:** tenang, rapi, seperti halaman buku kas. Daftar dibuat sebagai **baris buku besar** dengan garis pemisah tipis, bukan tumpukan kartu yang sama semua. Satu elemen menjadi ciri khas: **bar proporsi uang** di dashboard, yaitu satu batang horizontal yang dibagi per sumber saldo dengan warna masing-masing. Elemen lain dibuat diam.

**Yang dihindari:** gradien dekoratif, kartu bergaya sama dengan bayangan sama, label ALL CAPS di atas tiap judul, angka besar + gradien sebagai hero, animasi masuk di setiap seksi.

---

## 2. Design Token

### 2.1 Warna

| Nama | Light | Dark | Dipakai untuk |
|---|---|---|---|
| `kertas` | `#F5F6F3` | `#0F1416` | latar halaman |
| `permukaan` | `#FFFFFF` | `#171E21` | panel, modal, sidebar |
| `tinta` | `#17212B` | `#E7ECEE` | teks utama |
| `tinta-redup` | `#5B6772` | `#93A0A8` | teks sekunder (kontras ≥ 4.5:1 di atas `kertas`) |
| `garis` | `#DDE1DC` | `#2A3438` | border dan pemisah |
| `batik` (primer) | `#2B3A8C` | `#8FA0F2` | tombol utama, link, fokus, item nav aktif |
| `masuk` | `#0B7A55` | `#4CC79B` | pemasukan |
| `keluar` | `#B93A2B` | `#F08A7C` | pengeluaran, error, budget lewat |
| `kunyit` | `#B7791F` | `#E6B450` | tabungan, budget waspada (80–99%), peringatan |

Warna sumber saldo dipilih user dari palet 10 warna (`#2B3A8C`, `#0B7A55`, `#B7791F`, `#B93A2B`, `#6B3FA0`, `#0E7C86`, `#C2547A`, `#4F6B2D`, `#7A5230`, `#44515C`) dan dipakai sebagai titik/garis kecil dan segmen bar proporsi.

Aturan: **warna tidak pernah menjadi satu-satunya penanda.** Pemasukan selalu berawalan `+`, pengeluaran `−`, status selalu punya teks ("Waspada", "Lewat").

Definisikan sebagai CSS variables di `resources/css/app.css` (`:root` dan `.dark`), lalu petakan ke Tailwind lewat `theme.extend.colors` agar dark mode otomatis.

### 2.2 Tipografi

Satu keluarga: **Plus Jakarta Sans** (dirancang di Indonesia, angkanya jelas). Muat lewat Bunny Fonts atau Google Fonts, dengan fallback `ui-sans-serif, system-ui, sans-serif`.

| Peran | Ukuran / berat | Catatan |
|---|---|---|
| Angka besar (total kekayaan) | 40 px / 800, `tabular-nums`, tracking −0.02em | hanya di dashboard |
| Judul halaman | 24 px / 700 | |
| Judul panel | 16 px / 700 | huruf kecil biasa (sentence case) |
| Isi | 15 px / 400, line-height 1.5 | |
| Nominal dalam daftar | 15 px / 600, `tabular-nums`, rata kanan | |
| Keterangan kecil | 13 px / 500, warna `tinta-redup` | |

Semua angka uang memakai `font-variant-numeric: tabular-nums`. Panjang baris teks < 75 karakter.

### 2.3 Bentuk dan jarak
- Skala jarak: 4, 8, 12, 16, 24, 32, 48.
- Radius berjenjang: input dan baris 8 px; panel dan modal 14 px; badge dan chip 999 px. Bukan satu radius untuk semua.
- Struktur dibuat dengan **border 1 px `garis`**, bukan bayangan. Bayangan hanya untuk elemen melayang (modal, dropdown, toast).
- Konten maksimum 1120 px di tengah pada desktop.

### 2.4 Gerak
- Tanpa animasi masuk otomatis, kecuali **satu**: bar proporsi di dashboard tumbuh dari kiri saat pertama dimuat (500 ms).
- Gerak yang menjawab aksi user: modal/sheet membuka, toast muncul, baris terhapus memudar (150–200 ms).
- Hormati `prefers-reduced-motion`: matikan semua gerak di atas.

---

## 3. Layout Aplikasi

### 3.1 Desktop (≥ 1024 px)

```
┌───────────┬───────────────────────────────────────────────────────┐
│ ◈ Kasku   │ Judul halaman                    [🔔] [◐] [+ Catat]    │
│           ├───────────────────────────────────────────────────────┤
│ Beranda   │                                                       │
│ Transaksi │                  konten halaman                       │
│ Transfer  │            (maks 1120 px, rata kiri)                  │
│ Tabungan  │                                                       │
│ Budget    │                                                       │
│ Berulang  │                                                       │
│ Hutang    │                                                       │
│ Sumber    │                                                       │
│ Rekonsil. │                                                       │
│ Laporan   │                                                       │
│ ───────── │                                                       │
│ Profil    │                                                       │
└───────────┴───────────────────────────────────────────────────────┘
```
Sidebar 232 px, item aktif memakai garis kiri 3 px warna `batik` dan teks tebal. Nama aplikasi sementara "Kasku" (mudah diganti di satu tempat: `config('app.name')`).

### 3.2 Mobile (< 768 px)

```
┌──────────────────────────┐
│ Judul halaman     [🔔][◐]│
├──────────────────────────┤
│                          │
│      konten satu kolom   │
│                          │
├──────────────────────────┤
│ Beranda Transaksi (+) Tabungan Lainnya │
└──────────────────────────┘
```
Bottom nav 5 slot; tombol tengah **(+)** membuka form Catat (bottom sheet). "Lainnya" membuka sheet berisi menu sisanya. Area sentuh minimal 44 × 44 px. Tablet (768–1023 px): sidebar hanya ikon.

### 3.3 Layout admin
Sidebar terpisah dengan menu: Ringkasan, Pengguna, Kategori default, Profil. Bagian atas sidebar diberi penanda "Admin". Tidak ada tombol Catat dan tidak ada menu keuangan.

---

## 4. Komponen

Buat sebagai Blade component di `resources/views/components/`.

| Komponen | Perilaku |
|---|---|
| `<x-money>` | Menampilkan integer sebagai `Rp 1.250.000`; prop `signed` menambahkan `+`/`−` dan warna `masuk`/`keluar`. Helper PHP `rupiah(int)` dipakai di seluruh app. |
| `<x-money-input>` | Input nominal dengan pemisah ribuan otomatis (Alpine), `inputmode="numeric"`, nilai yang dikirim ke Livewire adalah integer polos. |
| `<x-modal>` | Alpine `x-show`; di mobile tampil sebagai **bottom sheet** yang bisa ditutup dengan geser/tombol Esc; fokus dikunci di dalamnya. |
| `<x-dropdown>` | Alpine, tutup saat klik di luar dan saat Esc. |
| `<x-toast>` | Store Alpine global; posisi kanan-atas di desktop, atas-tengah di mobile; hilang otomatis 4 detik; dipicu dari Livewire lewat `$this->dispatch('toast', ...)`. |
| `<x-confirm>` | Dialog konfirmasi hapus; tombol berlabel aksi nyata ("Hapus transaksi"), bukan "OK". |
| `<x-skeleton>` | Blok abu berdenyut halus (mati saat reduced motion). Varian: `row`, `panel`, `chart`. Dipakai di `placeholder()` komponen `#[Lazy]`. |
| `<x-badge>` | Pil kecil dengan teks: Aktif, Diarsipkan, Waspada, Lewat, Lunas, Gagal. |
| `<x-progress>` | Bar tipis 8 px. Warna: `batik` (<80%), `kunyit` (80–99%), `keluar` (≥100%), selalu disertai teks persen. Untuk tabungan memakai `kunyit`. |
| `<x-empty>` | Ikon kecil + kalimat singkat + satu tombol aksi. |
| `<x-source-dot>` | Titik 10 px berwarna sumber, diikuti nama. |
| `<x-money-bar>` | Bar proporsi per sumber (lihat 5.1). |

### Formulir
- Label di atas input, teks bantu di bawah, error di bawah field dengan warna `keluar` dan ikon (bukan hanya warna).
- Validasi real-time (`wire:model.blur`) dan tombol simpan menampilkan status memuat (`wire:loading`) serta nonaktif saat proses.
- Fokus terlihat jelas: outline 2 px `batik`, offset 2 px.

### Copywriting
Bahasa Indonesia, huruf kecil biasa, kata kerja aktif, spesifik. Nama aksi konsisten di seluruh alur: tombol "Simpan transaksi" → toast "Transaksi disimpan".

| Konteks | Teks |
|---|---|
| Tombol utama global | Catat |
| Simpan | Simpan transaksi / Simpan sumber saldo / Simpan target |
| Hapus | Hapus transaksi (konfirmasi: "Saldo Gopay akan dikembalikan Rp 25.000.") |
| Error saldo | "Saldo Gopay tidak cukup. Tersedia Rp 80.000, dibutuhkan Rp 105.000." |
| Empty transaksi | "Belum ada transaksi bulan ini." + [Catat transaksi pertama] |
| Empty sumber | "Tambahkan tempat uangmu, misalnya Cash atau Gopay." + [Tambah sumber saldo] |
| Peringatan budget | "Makan & minum sudah 85% dari batas bulan ini." |

Error tidak meminta maaf dan selalu menjelaskan cara memperbaiki.

---

## 5. Wireframe Halaman

Notasi: `[Tombol]`, `(pilihan)`, `▓` bagian terisi. Rata kiri untuk teks, rata kanan untuk angka.

### 5.1 Dashboard (`/dashboard`)

Elemen ciri khas: **bar proporsi uang**.

```
Halo, Ryan
Selasa, 28 Sep 2026

Total kekayaan
Rp 12.450.000
= Saldo tersedia Rp 9.200.000  +  Tabungan Rp 3.250.000

[▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓]
 batang dibagi: Cash | Gopay | BCA | Dana | Tabungan (warna kunyit)
 ● Cash  Rp 1.200.000   ● Gopay Rp 500.000   ● BCA Rp 7.000.000  ...

────────────────────────────────────────────────────────────────
Bulan ini                          │ Pengeluaran per kategori
Masuk    +Rp 5.000.000  ▓▓▓▓▓▓▓▓▓▓ │ Makan & minum   ▓▓▓▓▓▓▓▓  1.2 jt
Keluar   −Rp 3.100.000  ▓▓▓▓▓▓     │ Transport       ▓▓▓▓▓     800 rb
Selisih  +Rp 1.900.000             │ Belanja         ▓▓▓       450 rb
                                   │ (bar horizontal Chart.js)
────────────────────────────────────────────────────────────────
Perlu perhatian  (disembunyikan jika kosong)
 ! Makan & minum 85% dari budget          → Budget
 ! Listrik Rp 450.000 jatuh tempo 3 hari  → Berulang
 ! Hutang ke Andi jatuh tempo besok       → Hutang
────────────────────────────────────────────────────────────────
Tren 6 bulan (bar ganda masuk/keluar)   │ Transaksi terbaru
                                        │ 28 Sep  Makan siang     −25.000
                                        │ 27 Sep  Gaji           +5.000.000
                                        │ 27 Sep  Cash → Gopay     100.000
                                        │ [Lihat semua transaksi]
```
- Setiap blok adalah komponen `#[Lazy]` dengan skeleton sendiri.
- Bar proporsi: segmen minimal lebar 2%; klik segmen menuju halaman sumber tersebut; ada `aria-label` yang menyebut nominal per sumber.
- Onboarding (belum ada sumber): bar diganti panel `<x-empty>` "Tambahkan tempat uangmu…".
- Mobile: semua blok menjadi satu kolom berurutan; legenda bar berupa daftar vertikal.

### 5.2 Form Catat (modal global, tombol `+ Catat`)

```
┌ Catat transaksi ─────────────────────────────┐
│ (Pengeluaran) (Pemasukan)     ← segmented     │
│                                              │
│ Rp  [ 25.000            ]  ← fokus otomatis  │
│                                              │
│ Sumber saldo   [● Gopay · Rp 500.000     ▾]  │
│ Kategori       [Makan & minum            ▾]  │
│ Tanggal        [28 Sep 2026]                 │
│ Catatan        [Makan siang              ]   │
│ [ ] Ada pajak                                │
│     Nominal pajak [ 2.500 ]  Keterangan [PB1]│
│                                              │
│ Saldo Gopay: Rp 500.000 → Rp 472.500         │
│ ! Makan & minum akan mencapai 92% budget     │
│                                              │
│                   [Batal] [Simpan transaksi] │
└──────────────────────────────────────────────┘
```
- Default tab Pengeluaran; tanggal default hari ini; sumber default = terakhir dipakai.
- Pratinjau saldo dihitung langsung; jika tidak cukup, tampil error K-01 dan tombol simpan nonaktif.
- Pemasukan tidak menampilkan opsi pajak; kategori difilter sesuai tipe.
- Peringatan budget bersifat informasi, tidak memblokir.

### 5.3 Transaksi (`/transactions`)

```
Transaksi                                   [Export Excel] [+ Catat]

(Semua) (Masuk) (Keluar)   Tanggal [1 Sep – 30 Sep ▾]
Kategori [Semua ▾]  Sumber [Semua ▾]  🔍 [cari catatan…]
──────────────────────────────────────────────────────────────────
28 Sep 2026                                      Total hari ini −25.000
 ● Makan siang            Makan & minum · Gopay         −Rp 25.000
 ● Kopi                   Makan & minum · Cash          −Rp 18.000
27 Sep 2026
 ● Gaji September         Gaji · BCA                +Rp 5.000.000
──────────────────────────────────────────────────────────────────
                                    ‹ 1 2 3 ›   (pagination)
```
- Dikelompokkan per tanggal; titik kiri berwarna kategori; nominal rata kanan dengan tanda.
- Klik baris membuka form edit (modal yang sama) dengan tombol "Hapus transaksi" di kiri bawah.
- Transaksi penyesuaian tampil dengan badge "Penyesuaian" dan tidak dapat diedit.
- Filter tersimpan di query string (`#[Url]`) supaya export mengikuti filter aktif.
- Loading: 6 baris `<x-skeleton variant="row">`.

### 5.4 Sumber saldo (`/sources`)

```
Sumber saldo                               [+ Tambah sumber saldo]
Saldo tersedia  Rp 9.200.000

▌ Cash        Cash                                    Rp 1.200.000
▌ Gopay       E-wallet                                  Rp 500.000
▌ BCA         Bank                                    Rp 7.500.000
  (garis kiri 4 px berwarna sumber; klik → detail + riwayat)

▸ Diarsipkan (2)     ← daftar lipat
```
Menu titik tiga per baris: Ubah, Arsipkan, Hapus (hanya tanpa riwayat). Arsipkan dengan saldo ≠ 0 menampilkan pesan "Saldo harus 0 dulu. [Transfer saldo]".

### 5.5 Transfer (`/transfers`)

```
Transfer

┌ Pindahkan uang ───────────────────────────┐
│ Dari [● BCA · 7.500.000 ▾]                │
│            ↓                              │
│ Ke   [● Gopay · 500.000 ▾]                │
│ Rp [ 100.000 ]   Biaya admin [ 2.500 ]    │
│ Tanggal [28 Sep 2026]  Catatan [ … ]      │
│ BCA: 7.500.000 → 7.397.500                │
│ Gopay: 500.000 → 600.000                  │
│                     [Simpan transfer]     │
└───────────────────────────────────────────┘

Riwayat transfer (baris buku besar; klik untuk ubah/hapus)
```
Dua kolom di desktop (form kiri, riwayat kanan), bertumpuk di mobile. Dropdown "Ke" tidak menampilkan sumber yang dipilih di "Dari".

### 5.6 Tabungan (`/savings`, `/savings/{goal}`)

```
Tabungan                                  [+ Target baru]
Total tabungan Rp 3.250.000

Dana darurat                            Rp 2.000.000 / 6.000.000
[▓▓▓▓▓▓░░░░░░░░░░░░░░]  33%   Sisa Rp 4.000.000
Perkiraan tercapai: Mar 2027           [Setor] [Tarik]

Liburan Bali                            Rp 1.250.000 / 5.000.000
[▓▓▓▓░░░░░░░░░░░░░░░░]  25%   Tenggat 31 Des 2026
```
Detail target: ringkasan di atas, tombol Setor/Tarik (modal: pilih sumber + nominal + pratinjau saldo), riwayat setoran/penarikan di bawah. Target tercapai mendapat badge "Tercapai". Aksi "Batalkan target" meminta memilih sumber tujuan pengembalian dana.

### 5.7 Budget (`/budgets`)

```
Budget            ‹ September 2026 ›        [Salin dari bulan lalu] [+ Budget]

Makan & minum   Rp 1.700.000 / 2.000.000   85%  [Waspada]
[▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░]   (kunyit)
Transport       Rp 300.000 / 800.000       38%  [Aman]
Hiburan         Rp 620.000 / 500.000      124%  [Lewat Rp 120.000]  (keluar)
```

### 5.8 Transaksi berulang (`/recurring`)

Tabel (mobile: baris bertumpuk): Nama/catatan, nominal, frekuensi, jatuh tempo berikutnya, sumber, status (Aktif, Dijeda, Selesai, badge "Gagal terakhir" bila ada). Aksi: Jeda/Aktifkan, Ubah, Hapus, "Jalankan ulang" pada yang gagal. Panel kanan/bawah: **Riwayat eksekusi** (tanggal, berhasil/gagal, pesan).

### 5.9 Hutang & piutang (`/debts`)

```
Hutang & piutang
Hutang belum lunas  Rp 1.500.000     Piutang belum lunas  Rp 800.000

(Hutang) (Piutang)                                 [+ Catat]
Andi      Jatuh tempo 29 Sep  [Jatuh tempo besok]
          Rp 500.000 dari Rp 1.000.000 dibayar   [▓▓▓▓▓░░░░░]  [Bayar cicilan]
```
Modal bayar cicilan: pilih sumber saldo, nominal (maks. sisa), tanggal, pratinjau saldo.

### 5.10 Rekonsiliasi (`/reconciliations`)

```
Rekonsiliasi saldo

Sumber [● Gopay ▾]
Saldo di aplikasi     Rp 500.000
Saldo asli            Rp [ 485.000 ]
Selisih               −Rp 15.000  (di aplikasi lebih banyak)
                      [Sesuaikan saldo]  [Simpan tanpa menyesuaikan]

Riwayat rekonsiliasi (tanggal, sumber, selisih, status disesuaikan/tidak)
```

### 5.11 Laporan (`/reports`)

```
Laporan                        [Export PDF] [Export Excel]
(Bulanan) (Tahunan)   ‹ September 2026 ›
(Ringkasan) (Kategori) (Sumber saldo) (Pajak)

Ringkasan: Masuk, Keluar, Selisih (tiga angka berdampingan tanpa kartu,
dipisah garis vertikal), lalu tabel rincian.
```
Tab Pajak: total pajak periode, dipecah "Pajak pengeluaran" dan "Biaya/pajak transfer", dengan daftar rincian.

### 5.12 Profil (`/profile`)
Tiga bagian bertumpuk dengan pemisah garis: Informasi profil, Ubah password, Tampilan (pilihan Terang/Gelap/Ikuti sistem).

### 5.13 Halaman Admin

- **`/admin` Ringkasan:** tiga angka (jumlah user, jumlah transaksi, user baru bulan ini) + grafik pertumbuhan user per bulan. Tanpa angka nominal uang.
- **`/admin/users`:** tabel nama, email, status (badge), tanggal daftar, tombol Nonaktifkan/Aktifkan (dengan konfirmasi). Tombol untuk diri sendiri/admin terakhir nonaktif dengan tooltip alasannya.
- **`/admin/categories`:** dua kolom Pemasukan | Pengeluaran, tambah/ubah/arsipkan kategori default; catatan "Perubahan hanya berlaku untuk pengguna baru."

### 5.14 Auth (`/login`, `/register`)
Satu kolom terpusat lebar maks 400 px di atas latar `kertas`, judul, form, tautan sekunder. Tanpa ilustrasi besar. Pesan error jelas ("Email atau password salah.").

---

## 6. Aturan Grafik (Chart.js)
- Warna dari token (baca CSS variable saat inisialisasi, perbarui saat dark mode berganti).
- Sumbu dan legenda memakai `tinta-redup`; grid memakai `garis`.
- Format angka sumbu singkat: `1,2 jt`, `800 rb`. Tooltip menampilkan Rupiah lengkap.
- Setiap grafik memiliki ringkasan teks tersembunyi (`sr-only`) atau tabel alternatif untuk pembaca layar.
- Muat lewat `#[Lazy]` + skeleton `chart`.

## 7. Dark Mode
- Strategi `class` pada `<html>`; pilihan tersimpan di `localStorage` (`theme` = `light`/`dark`/`system`) dan diterapkan lewat skrip kecil di `<head>` sebelum render supaya tidak berkedip.
- Semua warna lewat token; dilarang memakai warna hex langsung di Blade.

## 8. Checklist Kualitas Tampilan
- [ ] Lebar 360 px tanpa scroll horizontal (tabel lebar di dalam container `overflow-x-auto`)
- [ ] Semua elemen interaktif bisa dijangkau keyboard, fokus terlihat
- [ ] Kontras teks AA di light dan dark
- [ ] Setiap daftar punya skeleton, empty state, dan error state
- [ ] Angka uang memakai `tabular-nums`, rata kanan
- [ ] Tidak ada informasi yang hanya dibedakan oleh warna
- [ ] `prefers-reduced-motion` dihormati

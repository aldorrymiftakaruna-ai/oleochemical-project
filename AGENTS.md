# AGENTS.md — mss-project

## PENTING — ROOT PROJECT
Project utama dan SATU-SATUNYA target semua perubahan:
C:\Users\ASUS\oleochemicalReport

Setiap kali menyebutkan kondisi suatu file ("file X sudah ada", "kolom
Y begini"), WAJIB sertakan path lengkap absolut yang benar-benar dibaca
saat itu juga. Jangan mengandalkan laporan dari sesi sebelumnya tanpa
verifikasi ulang.

## KEPUTUSAN ARSITEKTUR FINAL (JANGAN TANYA ULANG, SUDAH DIPUTUSKAN)

1. Teknisi/user bot -> App\Models\Employee (tabel employees),
   kolom telegram_id (bigint, nullable, unique)
2. Laporan -> App\Models\MaintenanceReport (tabel maintenance_reports)
3. Tabel maintenance_reports sudah punya kolom (JANGAN dibuat ulang):
   report_code, work_duration_minutes, root_cause,
   photo_documentation (json), wizard_started_at, submitted_at,
   ai_suggestion_json (json), ai_analyzed, ai_confidence,
   shift (enum '1','2','3','reguler', NOT NULL, fallback ke 'reguler'
   jika tidak diisi wizard).
4. ai_aliases: pakai employee_id (bukan technician_id), TIDAK ada
   kolom area_id sama sekali (dihapus dari desain, mss-project tidak
   punya konsep Area/functional_loc).
5. Asset di mss-project TIDAK punya tech_ident_no, functional_loc,
   atau area_id. Kolom yang ada: tag_no, description, company_id.
   Pencarian asset pakai tag_no + description saja.
6. Tema visual WAJIB: warna aksen teal #0E9E8E (BUKAN biru/blue).
   Vanilla JS (BUKAN Alpine.js x-data). Layout memakai
   @section('page-title', ...) dan @section('page-sub', ...) --
   BUKAN struktur @yield('breadcrumb'). Referensi pola styling yang
   sudah benar: resources/views/cm/index.blade.php dan
   resources/views/ai-providers/index.blade.php.
7. layouts/app.blade.php WAJIB punya @stack('scripts') sebelum </body>
   dan <meta name="csrf-token" content="{{ csrf_token() }}"> di <head>
   (sudah ditambahkan, jangan dihapus).
8. Config Telegram HANYA disimpan di config/telegram.php, diakses via
   config('telegram.bot_token'), config('telegram.bot_username'), dst.
   config/services.php JUGA punya key 'telegram' sebagai peninggalan —
   TIDAK dihapus, tapi kode BARU harus konsisten pakai
   config('telegram.*') saja, BUKAN config('services.telegram.*').

## ATURAN WAJIB EDIT FILE (supaya tidak gagal apply / macet)

1. File BARU atau perubahan >30% isi file: overwrite penuh
   (create_file), JANGAN find_and_replace/diff parsial.
2. Perubahan KECIL (<30%): find_and_replace dengan target pencarian
   PENDEK (maksimal 5-10 baris).
3. Satu file, satu bagian besar per panggilan tool. Pecah jadi
   beberapa panggilan berurutan untuk file besar (>250-300 baris),
   laporkan hasil tiap panggilan sebelum lanjut.
4. Baca ulang file dari disk SEBELUM find_and_replace, jangan andalkan
   isi yang dibaca di awal sesi atau giliran sebelumnya.
5. Jika tool edit GAGAL, STOP -- jangan coba lagi dengan variasi teks
   berkali-kali, dan JANGAN eskalasi ke PowerShell. Laporkan: nama
   file, potongan teks yang dicari, dugaan penyebab gagal. Tunggu
   instruksi lanjutan — opsi teraman adalah manusia menulis manual
   langsung di editor.
6. File .blade.php: DEFAULT overwrite penuh, hindari find_and_replace
   parsial kecuali perubahan 1 baris tunggal yang unik.
7. JANGAN PERNAH gunakan terminal/PowerShell/python -c untuk menulis
   isi file .php atau .blade.php -- selalu pakai tool file bawaan.
8. Baca file lewat tool baca file (view/read_file) langsung, JANGAN
   verifikasi keberadaan/isi file lewat command PowerShell dengan
   php -r atau file_exists() di terminal. Untuk file kritis, verifikasi
   ukuran file (byte) sebagai pengecekan tambahan, jangan hanya percaya
   isi yang ditampilkan tool.

## RIWAYAT INSIDEN (pelajaran, jangan diulang)

1. bootstrap/app.php sempat KEHILANGAN tag pembuka <?php akibat proses
   edit yang gagal separuh jalan -- menyebabkan seluruh situs down
   total (fatal error "handleRequest() on int"). Pemicunya: mencoba
   menulis file PHP lewat command PowerShell dengan escaping karakter
   {{ }} dan kutip yang rumit. Kejadian SERUPA terulang lagi saat
   restrukturisasi routes/web.php (tool edit menolak perubahan >30%,
   lalu dipaksa lewat PowerShell heredoc, sempat gagal berkali-kali
   karena tool cache masih menganggap file lama ada padahal sudah
   dihapus). Akhirnya diselesaikan dengan menulis file baru secara
   MANUAL langsung di VSCode (New File, paste, save) — bukan lewat
   tool maupun PowerShell.

2. AdminMiddleware.php didaftarkan sebagai alias di bootstrap/app.php
   TAPI file class-nya tidak pernah benar-benar dibuat (folder
   app/Http/Middleware/ bahkan sempat tidak ada). Menyebabkan error
   "Target class AdminMiddleware does not exist". Laporan "sudah
   selesai" dari sesi sebelumnya TERBUKTI SALAH.

3. File config/telegram.php sempat menjadi 0 byte (kosong total)
   akibat proses tool write yang gagal secara diam-diam, menyebabkan
   config('telegram') mengembalikan integer 1 alih-alih array (ini
   adalah perilaku default PHP: file kosong yang di-include tanpa
   statement `return` akan menghasilkan return value 1). Akibatnya
   config('telegram.bot_token') selalu null walau .env sudah benar
   berisi TELEGRAM_BOT_TOKEN. Ditemukan lewat php artisan tinker:
   mengetik config('telegram') menampilkan angka 1, bukan array.
   Tool read_file bahkan sempat menampilkan ISI PALSU (isi yang
   seharusnya ada) padahal file di disk benar-benar 0 byte — tool
   tidak bisa dipercaya penuh untuk verifikasi, HARUS dicek ukuran
   file juga (misal lewat dir/ls), bukan cuma isi yang ditampilkan.

<!-- Tambahkan insiden baru di bawah ini, nomor urut lanjut -->

## ATURAN PENULISAN KODE

- Kode rapi, konsisten gaya yang sudah ada
- Tidak ada emoji di kode, komentar, atau string apapun
- Komentar dalam Bahasa Indonesia
- Setiap method baru wajib docblock (parameter + return)
- Early return untuk hindari nesting dalam
- Hapus kode tidak terpakai, jangan di-comment out tanpa alasan

## ALUR KERJA SETIAP SESI BARU

1. Baca ulang file AGENTS.md ini  secara penuh sebelum
   mulai kerja apapun
2. Sebutkan file apa saja yang akan disentuh sebelum mulai edit
3. Untuk fitur baru/kompleks ATAU untuk bug fix: baca dan laporkan dulu
   kondisi file terkait, TUNGGU konfirmasi manusia sebelum menulis kode
4. Setiap file yang diubah ditulis ulang lengkap (kecuali perubahan
   kecil, lihat Aturan Wajib Edit File poin 2)
5. Jika file dibutuhkan tapi belum jelas isinya, baca dulu, jangan
   mengarang

   ## ATURAN WAJIB: VERIFIKASI DULU, BARU KODING

Sebelum menulis SATU BARIS kode query, migration, atau apapun yang
menyentuh nama kolom/tabel/relasi database:

1. JANGAN asumsikan nama kolom dari "logikanya harusnya ada" atau dari
   pola umum framework/training data. WAJIB cek struktur asli dulu
   dengan salah satu cara ini (urut dari yang paling disarankan):
   - php artisan model:show NamaModel (paling disarankan, langsung
     menampilkan kolom, tipe, dan relasi tanpa raw SQL)
   - Baca file migration terkait di database/migrations/
   - Baca $fillable dan relasi di Model terkait (app/Models/*.php)

2. Untuk RELASI ANTAR TABEL (join, where, whereHas), WAJIB baca dulu
   Model-nya untuk lihat relasi yang SUDAH didefinisikan. Jangan
   asumsikan ada kolom foreign key langsung kalau belum diverifikasi
   -- relasi bisa saja tidak langsung (lewat tabel perantara).

3. Kalau ragu apakah suatu kolom/relasi ada: STOP, laporkan dulu ke
   user "saya perlu cek struktur X dulu sebelum menulis kode Y",
   jalankan verifikasi, baru lanjut menulis kode setelah dikonfirmasi.

4. Setelah menulis query/kode yang menyentuh database, SEBELUM
   melaporkan "selesai": pastikan semua nama kolom yang dipakai sudah
   diverifikasi ada di Langkah 1-2, bukan hasil tebakan.

Contoh insiden nyata pelanggaran aturan ini: query filter dashboard
sempat ditulis dengan where('company_id', $id) langsung ke tabel
employees, padahal kolom tersebut TIDAK ADA di tabel employees.
Error: "Column not found: 1054 Unknown column company_id". Penyebab:
kode ditulis berdasarkan asumsi struktur umum, bukan hasil verifikasi.

## ATURAN WAJIB: PERINTAH TINKER/TERMINAL DI POWERSHELL

PowerShell TIDAK memakai backslash (\) sebagai karakter escape untuk
tanda dollar ($) -- ini beda dari Bash/Linux. Perintah seperti
--execute="...\$var..." akan gagal dengan parse error yang
membingungkan.

WAJIB ikuti pola ini untuk semua perintah php artisan tinker --execute:

1. SELALU bungkus seluruh argumen --execute dengan tanda kutip TUNGGAL
   ('...'), BUKAN tanda kutip ganda ("..."). Kutip tunggal di
   PowerShell tidak melakukan interpolasi variabel sama sekali,
   sehingga $variabel PHP di dalamnya aman tanpa perlu escape apapun.

   BENAR:
   php artisan tinker --execute='print_r(Schema::getColumnListing("employees"));'

   SALAH (akan gagal):
   php artisan tinker --execute="print_r(Schema::getColumnListing('employees'));"

2. Untuk kode PHP yang butuh tanda kutip di dalamnya, gunakan kutip
   ganda di DALAM kutip tunggal luar, seperti contoh di atas.

3. Jika logic yang mau dijalankan lebih dari 1-2 baris atau perlu
   loop/foreach kompleks: JANGAN paksa lewat --execute satu baris.
   Gunakan php artisan model:show NamaModel, php artisan route:list,
   atau tool bawaan Laravel lain yang relevan -- lebih aman daripada
   raw SQL manual lewat satu baris command.

4. Untuk cek struktur tabel, LEBIH DIUTAMAKAN pakai:
   php artisan model:show NamaModel
   Ini bawaan Laravel, otomatis menampilkan kolom, tipe, dan relasi
   tanpa perlu menulis raw SQL/Schema::getColumnListing sama sekali.
CARA MENJALANKAN PROJECT "ujian_asts"
======================================

Kebutuhan: XAMPP / Laragon (Apache + MySQL + PHP)

LANGKAH-LANGKAH:

1. Salin seluruh folder "ujian_asts" ke folder htdocs XAMPP Anda:
   Contoh (Windows): C:\xampp\htdocs\ujian_asts\
   Contoh (Laragon):  C:\laragon\www\ujian_asts\

2. Jalankan Apache dan MySQL dari XAMPP/Laragon Control Panel.

3. Buat database:
   - Buka http://localhost/phpmyadmin
   - Klik menu "Import"
   - Pilih file database.sql yang ada di folder ini
   - Klik "Go" / "Kirim"
   - Database "ujian_asts" beserta tabel "users" dan 10 data
     awal akan otomatis terbuat.

4. Cek REST API:
   Buka di browser: http://localhost/ujian_asts/data_json.php
   Karena sekarang API dilindungi login, Anda akan melihat pesan
   JSON "Anda belum login" jika belum login lewat browser yang sama.
   Login dulu lewat index.php/login.php, baru API bisa diakses.

5. Buka halaman utama:
   http://localhost/ujian_asts/index.php
   Anda akan diarahkan ke halaman login terlebih dahulu.
   Login dengan username: admin, password: admin123
   Setelah berhasil, tabel akan otomatis terisi data dari data_json.php.

6. Fitur Tambahan (Tugas Mandiri):
   - Tombol "+ Tambah Data"  -> menambah data baru (add.php)
   - Tombol "Edit"           -> mengubah data (update.php)
   - Tombol "Hapus"          -> menghapus data (delete.php)
   Semua aksi langsung tersimpan ke database MySQL dan tabel
   akan otomatis refresh setelah berhasil.

STRUKTUR FILE:
--------------
ujian_asts/
├── database.sql       -> Script pembuatan database, tabel users & admin + data awal
├── config.php         -> Koneksi ke database MySQL
├── auth_check.php     -> Helper proteksi login untuk endpoint API
├── login.php          -> Halaman login
├── logout.php         -> Proses logout (hapus session)
├── data_json.php      -> REST API (GET) - menampilkan semua data (butuh login)
├── add.php            -> REST API (POST) - tambah data (butuh login)
├── update.php         -> REST API (POST) - ubah data (butuh login)
├── delete.php         -> REST API (POST) - hapus data (butuh login)
├── index.php          -> Halaman tampilan tabel + form Add/Edit/Hapus (butuh login)
└── README.txt         -> Panduan ini

FITUR LOGIN:
------------
- Buka http://localhost/ujian_asts/  atau  index.php -> akan otomatis
  diarahkan ke login.php jika belum login.
- Akun default: username = admin | password = admin123
  (password disimpan di database dalam bentuk hash bcrypt, aman)
- Setelah login berhasil, session tersimpan dan Anda bisa membuka
  index.php serta menggunakan seluruh API (data_json.php, add.php,
  update.php, delete.php) yang sekarang hanya bisa diakses jika sudah login.
- Tombol "Logout" ada di pojok kanan atas halaman utama.
- Untuk mengganti password admin, buka phpMyAdmin -> tabel admin,
  atau buat halaman ganti password sendiri (opsional, belum disediakan).

CATATAN:
--------
- Jika username/password MySQL Anda berbeda dari default XAMPP
  (user: root, password: kosong), silakan ubah di file config.php.
- Jika folder project Anda diberi nama berbeda dari "ujian_asts",
  ubah variabel API_URL di dalam index.php (bagian <script>)
  agar sesuai dengan nama folder Anda.
- Jangan lupa import ulang database.sql jika Anda pernah mengimport
  versi sebelumnya (yang belum ada tabel admin), supaya fitur login berfungsi.

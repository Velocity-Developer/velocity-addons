# Debug admin-double-render [OPEN]

## Gejala

- Halaman admin Velocity Addons tampil dobel.
- Dugaan awal user: Alpine JS ter-load 2x.

## Hipotesis

1. Callback render halaman license dipanggil 2x dalam 1 request.
2. Routing `page_velocity_addons()` memanggil callback lalu ada render kedua dari path lain.
3. Template/page method tertentu mencetak wrapper markup 2x.
4. JS admin memodifikasi DOM hingga menyalin blok halaman.
5. Ada hook WordPress lain pada admin page yang memanggil renderer sama.

## Bukti

- Ditemukan 2 instansiasi class `Custom_Admin_Option_Page` di [admin/class-velocity-option-page.php#L1542-L1545](file:///d:/local-site/dev/app/public/wp-content/plugins/velocity-addons/admin/class-velocity-option-page.php#L1542-L1545).
- Karena constructor class ini mendaftarkan `admin_menu`, `admin_init`, dan callback halaman, semua hook admin terpasang 2x.
- Ini menjelaskan render halaman ganda tanpa perlu Alpine load 2x.

## Status hipotesis

1. Benar paling mungkin. Instansiasi class ganda menyebabkan callback halaman terdaftar ganda.
2. Salah sementara. Routing sendiri tidak tampak dobel; sumbernya registrasi ganda.
3. Salah sementara. Template license hanya satu method.
4. Salah sementara. Bukti codebase menunjuk server-side registration, bukan DOM clone JS.
5. Benar dalam bentuk lebih spesifik: hook WordPress sama terdaftar 2x oleh class yang sama.

## Fix

- Hapus 1 instansiasi duplikat `new Custom_Admin_Option_Page()`.
- Instrumentasi komentar debug tetap dipertahankan sementara untuk verifikasi post-fix.

## Langkah verifikasi

- Reload halaman `admin.php?page=admin_velocity_addons&sub=license`.
- Pastikan blok halaman tidak lagi tampil 2x.
- Jika perlu, cek source HTML dan pastikan comment `velocity-debug` masing-masing muncul sekali.

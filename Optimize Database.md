# Optimize Database — Penjelasan & Dampak

Dokumen ini menjelaskan setiap opsi pembersihan yang tersedia di halaman “Optimize Database”, termasuk dampak, potensi konflik, dan kompatibilitas dengan page builder.

## Ikhtisar Tampilan
- Kolom “Row” menunjukkan jumlah baris (row) yang akan dihapus sesuai kondisi tiap item.
- Kolom “Estimasi Ukuran” adalah estimasi total byte konten yang terlibat (bukan ukuran fisik file tabel), diformat agar mudah dibaca.

## Item Pembersihan

- Hapus Revisions
  - Apa: Menghapus semua post dengan `post_type='revision'` di `wp_posts`.
  - Dampak: Mengurangi ukuran DB tanpa mengubah konten utama. Revisi tidak tampil di front-end.
  - Konflik: Tidak ada.

- Hapus Auto Draft
  - Apa: Menghapus post dengan `post_status='auto-draft'`.
  - Dampak: Bersih-bersih draft otomatis yang ditinggalkan editor.
  - Konflik: Tidak ada, data ini bukan konten yang dipublikasikan.

- Hapus Posts di Trash
  - Apa: Menghapus post dengan `post_status='trash'`.
  - Dampak: Mengosongkan “Trash” secara permanen.
  - Konflik: Tidak ada, namun konten di Trash tidak dapat dipulihkan setelah dihapus.

- Hapus Orphan Postmeta
  - Apa: Menghapus baris `wp_postmeta` yang tidak punya induk di `wp_posts`.
  - Dampak: Mengurangi bloat meta yang tidak terpakai.
  - Konflik: Tidak ada, karena hanya baris tanpa induk yang dihapus.

- Hapus Orphan Term Relationships (Object)
  - Apa: Menghapus `wp_term_relationships` yang `object_id`-nya tidak ada di `wp_posts`.
  - Dampak: Membersihkan relasi taksonomi yatim.
  - Konflik: Tidak ada.

- Hapus Orphan Term Relationships (Taxonomy)
  - Apa: Menghapus relasi di `wp_term_relationships` yang `term_taxonomy_id`-nya tidak ada di `wp_term_taxonomy`.
  - Dampak: Menata ulang konsistensi relasi.
  - Konflik: Tidak ada.

- Hapus Orphan Termmeta
  - Apa: Menghapus `wp_termmeta` yang term induknya tidak ada di `wp_terms`.
  - Dampak: Mengurangi bloat meta term yang tidak digunakan.
  - Konflik: Tidak ada.

- Hapus Komentar Spam & Trash
  - Apa: Menghapus `wp_comments` dengan `comment_approved IN ('spam','trash')`.
  - Dampak: Mengurangi bloat komentar tidak relevan.
  - Konflik: Tidak ada.

- Hapus Komentar Pending > 90 Hari
  - Apa: Menghapus komentar pending lebih dari 90 hari.
  - Dampak: Membersihkan antrian moderasi lama.
  - Konflik: Hanya jika Anda berniat meninjau komentar pending lama; umumnya aman.

- Hapus Orphan Commentmeta
  - Apa: Menghapus `wp_commentmeta` tanpa induk komentar.
  - Dampak: Mengurangi bloat meta komentar.
  - Konflik: Tidak ada.

- Hapus Transients Kedaluwarsa
  - Apa: Menghapus transient yang sudah lewat waktu di `wp_options` dan pasangan nilainya.
  - Dampak: Mengosongkan cache aplikasi yang kadaluarsa; akan terisi kembali saat dibutuhkan.
  - Konflik: Tidak ada; transient yang valid dan belum expired tidak dihapus.

- Hapus Cache oEmbed
  - Apa: Menghapus meta `_oembed_cache` dan `_oembed_%` di `wp_postmeta`.
  - Dampak: Cache embed akan diregenerasi saat halaman yang relevan diakses.
  - Konflik: Tidak ada.

## Kompatibilitas dengan Beaver Builder
- Aman: Tidak ada penghapusan meta builder spesifik (mis. `fl-builder` atau template milik Beaver). Item “orphan” hanya menghapus baris yang kehilangan induk, sehingga tidak menyentuh konten aktif.
- Transient kedaluwarsa aman untuk dihapus; builder atau plugin lain akan mengisi ulang cache sesuai kebutuhan.
- Revisions/auto-draft/trash adalah entitas WordPress standar dan tidak memengaruhi halaman yang sudah dipublikasikan melalui builder.

## Notifikasi Hasil
Setelah menjalankan pembersihan:
- Muncul notifikasi ringkas di atas halaman berisi jumlah baris yang dihapus, contoh:
  - “Optimize selesai — Revisions: 1.245 | Auto Draft: 36 | Trash Posts: 82 | Orphan Postmeta: 3.412 | …”
- Notifikasi membantu verifikasi cepat bahwa aksi telah berjalan.

## Praktik Aman
- Selalu backup database penuh sebelum eksekusi.
- Gunakan tampilan pratinjau (“Row” dan Ukuran”) untuk menilai dampak.
- Jalankan di staging jika ragu, kemudian produksi.
- Prefix tabel: jika bukan `wp_`, sistem akan otomatis menggunakan prefix situs aktif (`$wpdb->prefix`).

## FAQ
- Apakah ini akan merusak halaman builder?
  - Tidak. Pembersihan berfokus pada data yang jelas tidak dipakai (yatim), cadangan, sampah, dan cache kedaluwarsa.
- Bisakah saya menghapus semuanya sekaligus?
  - Ya, tombol “Hapus Semua” tersedia. Disarankan backup dulu.
- Mengapa ukuran 0 B pada beberapa item?
  - Estimasi berbasis jumlah byte dari kolom tertentu; beberapa kategori (relasi) tidak dihitung ukuran kontennya, sehingga tampil 0 B meski jumlah baris > 0.

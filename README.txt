=== Velocity Addons ===
Contributors: velocitydeveloper
Donate link: https://velocitydeveloper.com
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 6.2
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Menonaktifkan komentar, menyembunyikan notifikasi, batasi login, maintenance mode, blokir akses, dan kontrol lebih pada WordPress.
== Description ==

Plugin "Velocity Addons" adalah sebuah plugin yang menyediakan berbagai fitur tambahan untuk mengatur dan meningkatkan pengalaman admin WordPress Anda. Plugin ini memberikan kontrol yang lebih besar atas beberapa aspek penting dalam pengelolaan situs WordPress Anda. Fitur-fitur yang disediakan oleh plugin ini antara lain:

== Installation ==

This section describes how to install the plugin and get it working.
1. Upload `velocity-addons.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Changelog ==

=1.6.4=
- Import statistik legacy otomatis (additive) dengan baseline total kunjungan/pengunjung agar konsisten antar versi.
- Tambah tombol Reset Statistik pada halaman admin untuk mengosongkan semua data statistik & meta hit secara aman.
- Perapian UI statistik + perbaikan minor dokumentasi dan logika statistik.

=1.6.3=
- Update dokumentasi statistik: tambah keterangan dan contoh penggunaan parameter pengunjung online (with_online, label_online) di halaman admin dan dashboard.
- Peningkatan UI dokumentasi shortcode statistik.
- Minor copy update pada pengaturan Statistik.

=1.6.2=
- Penambahan fitur snippet
- Penambahan statistik pengunjung 
- Penambahan background image pada maintenance mode
- Perbaikan bug

=1.3.1=
- Fix bug Setting SEO Single title

=1.3.1=
- Tambah Fitur Autoupdate Versi Beta

=1.3.0=
- Tambah Fitur SEO di post/page
- Tambah shortcode post/page
- Hide badge count update

=1.2.15=
- Hapus Statistik

=1.2.14=
- Perbaikan Bug Recaptcha

=1.2.13=
- Perbaikan Floating Whatsapp

=1.2.12=
- Perbaikan Floating Whatsapp & Scroll Top

= 1.2.11 =
- Perbaikan bug list sub menu jika fitur non-aktif
- Tambah fitur scroll top & perbaikan Floating Whatsapp


= 1.2.0 =
- Perbaikan bug di versi sebelumnya
- Tambah Fitur License
- Tambah Fitur Floating Whatsapp 
- Perapian dan Pengelompokan Menu
- Tambah Dashboard Rangkuman Statistik, QC Check, dan Jumlah Page, Post, Media yang sudah diupload.

= 1.1.51 =
- Tambah fitur import artikel dari API Velocity
- Tambah fitur lisence (tahap pembuatan)
- Tambah fitur QC Checker saat Maintenance Mode

= 1.1.5 =
- Perbaikan bug recaptcha 'lost_password' & tampilan shortcode

= 1.1.4 =
- Perbaikan bug dan perapian tampilan

= 1.0.5 =
- Perbaikan bug block wp-admin

= 1.0.4 =
- Perbaikan bug Permalink
- Exclude halaman myaccount dari maintenance mode

= 1.0.3 =
add Classic Widget : Kembalikan pengelolaan widget ke tampilan klasik
Add Standar Editor TinyMCE
Add Remove Slug Category

= 1.0.2 =
Auto Update Plugin

= 1.0.1 =
Add Fully Disable Comment: Menonaktifkan fitur komentar di situs WordPress.
Add Hide Admin Notice: Menyembunyikan notifikasi admin di dashboard WordPress.
Add Limit Login Attempts: Membatasi jumlah percobaan login yang diperbolehkan.
Add Maintenance Mode: Menampilkan mode perawatan saat melakukan perbaikan atau pembaruan.
Add Disable XML-RPC: Memblokir akses XML-RPC pada situs WordPress.
Add Disable Rest API: Mematikan akses Rest API pada situs WordPress.
Add Disable Gutenberg: Mematikan editor Gutenberg dan menggunakan Classic Editor.
Add Whitelist Country: Membatasi akses ke halaman admin WordPress berdasarkan negara yang terdaftar.
Add Block WP Login: Memblokir akses ke halaman wp-login.php berdasarkan negara yang terdaftar.

= 1.0.0 =
Framework Kosong

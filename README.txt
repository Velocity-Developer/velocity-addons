=== Velocity Addons ===
Contributors: velocitydeveloper
Donate link: https://velocitydeveloper.com
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 6.2
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Here is a short description of the plugin.  This should be no more than 150 characters.  No markup here.

== Description ==

TPlugin "Velocity Addons" adalah sebuah plugin yang menyediakan berbagai fitur tambahan untuk mengatur dan meningkatkan pengalaman admin WordPress Anda. Plugin ini memberikan kontrol yang lebih besar atas beberapa aspek penting dalam pengelolaan situs WordPress Anda. Fitur-fitur yang disediakan oleh plugin ini antara lain:

Fully Disable Comment: Memungkiri fitur komentar secara keseluruhan pada situs WordPress Anda. Dengan mengaktifkan opsi ini, Anda dapat menonaktifkan kemampuan pengguna untuk menambahkan komentar di seluruh halaman dan postingan situs Anda.

Hide Admin Notice: Menyembunyikan notifikasi admin yang muncul di dashboard WordPress. Dengan mengaktifkan opsi ini, Anda dapat menghilangkan notifikasi yang sering muncul dan memberikan tampilan yang lebih bersih dan terorganisir untuk dashboard admin Anda.

Limit Login Attempts: Membatasi jumlah percobaan login yang diperbolehkan pada halaman login WordPress. Dengan fitur ini, Anda dapat melindungi situs Anda dari serangan brute force dengan membatasi jumlah upaya login yang gagal.

Maintenance Mode: Menampilkan mode perawatan kepada pengunjung situs Anda. Dengan mengaktifkan opsi ini, Anda dapat mengaktifkan tampilan halaman perawatan sementara saat Anda melakukan perbaikan atau pembaruan pada situs.

Disable XML-RPC: Memblokir akses XML-RPC pada situs WordPress Anda. Dengan mengaktifkan opsi ini, Anda dapat meningkatkan keamanan situs dengan memblokir potensi serangan melalui protokol XML-RPC.

Disable Rest API: Mematikan akses Rest API pada situs WordPress Anda. Dengan mengaktifkan opsi ini, Anda dapat mengontrol dan membatasi akses API situs Anda untuk meningkatkan keamanan dan privasi data.

Disable Gutenberg: Mematikan editor Gutenberg dan mengembalikan penggunaan Classic Editor pada situs Anda. Dengan mengaktifkan opsi ini, Anda dapat tetap menggunakan editor lama yang lebih Anda kenal dan nyaman.

Whitelist Country: Memungkinkan Anda untuk membatasi akses ke halaman admin WordPress hanya untuk negara-negara tertentu yang terdaftar dalam daftar whitelist. Dengan fitur ini, hanya pengunjung dari negara-negara yang terdaftar dalam daftar tersebut yang diperbolehkan mengakses halaman admin WordPress.

Block WP Login: Memblokir akses ke halaman wp-login.php, kecuali untuk negara-negara yang terdaftar dalam daftar whitelist. Fitur ini melibatkan penggunaan API dari http://www.geoplugin.net/json.gp untuk mendapatkan informasi negara pengunjung.

Plugin "Velocity Addons" memberikan Anda kontrol yang lebih baik atas fitur-fitur penting pada situs WordPress Anda dan memberikan lapisan keamanan tambahan. Dengan fitur-fitur ini, Anda dapat mengoptimalkan pengalaman pengguna, meningkatkan keamanan, dan mengatur situs WordPress Anda sesuai dengan kebutuhan Anda.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `velocity-addons.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.


== Changelog ==

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

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`
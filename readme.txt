=== SGEOBIZ SEO ===
Contributors: sgeobiz
Tags: seo, schema, local seo, google business profile, structured data, sitemap, meta tags, indonesia
Requires at least: 6.7
Tested up to: 6.9
Requires PHP: 7.4.0
Stable tag: 1.0.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Plugin SEO canggih untuk WordPress — otomasi penuh meta tag, structured data Schema.org, Google Business Profile, dan optimasi bisnis lokal Indonesia.

== Description ==

**SGEOBIZ SEO adalah plugin WordPress yang dirancang khusus untuk kebutuhan SEO bisnis lokal Indonesia.**

Aktifkan plugin, isi data bisnis Anda, dan biarkan SGEOBIZ bekerja — menghasilkan meta tag, structured data, dan sitemap secara otomatis tanpa konfigurasi rumit.

### Fitur Utama

* **Meta Tag Otomatis** — Title, description, dan canonical URL dibuat otomatis berdasarkan konten halaman.
* **Schema LocalBusiness** — Structured data JSON-LD untuk bisnis lokal: nama, alamat, koordinat GPS, jam operasional, telepon, dan semua platform digital.
* **Google Business Profile (GBP)** — Panel pengaturan terpusat untuk data NAP (Name, Address, Phone) dan link ke semua platform.
* **Sitemap XML** — Sitemap otomatis dengan notifikasi ke Google dan Bing setiap ada konten baru.
* **Open Graph & Twitter Cards** — Optimasi tampilan konten saat dibagikan di media sosial.
* **SEO Bar** — Indikator warna di daftar post/page untuk memantau status SEO setiap halaman.
* **Breadcrumb Schema** — Breadcrumb tersembunyi untuk mesin pencari + shortcode untuk tampilan visual.
* **Robots.txt Generator** — Termasuk blokir AI crawler (GPTBot, CCBot, dll.) dan SEO crawler.
* **WooCommerce Schema** — Structured data produk untuk toko online.
* **IndexNow** — Notifikasi instan ke Bing dan Yandex saat konten diperbarui.
* **Multi-bahasa** — Mendukung WPML, Polylang, WPGlobus, dan MultilingualPress.

### Fitur Khusus Indonesia

* **Profil Bisnis Lengkap** — Panel pengaturan NAP + koordinat GPS + 34 provinsi Indonesia
* **Marketplace Lokal** — Link ke Tokopedia, Shopee, Lazada, Blibli, Zalora
* **Food Delivery** — Link ke GoFood, GrabFood, ShopeeFood
* **Sosial Media Lengkap** — Facebook, Instagram, TikTok, YouTube, Twitter/X, LinkedIn, Threads, Pinterest

### Komponen Plugin (.local)

Plugin ini menyertakan fitur-fitur canggih yang dibangun modular:

* **AI Robots** — Pemblokiran crawler AI dari konten Anda
* **Article Schema** — Structured data artikel otomatis
* **Auto Alt Image** — Pengisian alt text gambar secara otomatis
* **Custom Schema** — Tambah schema JSON-LD kustom per halaman
* **Focus** — Panduan penulisan konten terarah
* **GBP Settings** — Panel pengaturan Google Business Profile
* **Geo Block** — Pemblokiran akses berdasarkan geolokasi
* **Geo Meta** — Meta tag geolokasi untuk konten lokal
* **HTTP 304** — Optimasi caching header tidak berubah
* **IndexNow** — Notifikasi URL ke Bing/Yandex secara instan
* **Product Enhancer** — Peningkatan schema produk WooCommerce
* **Redirect 404** — Pengalihan halaman 404 yang cerdas
* **Schema Geo** — GeoCoordinates untuk peta dan Maps
* **Schema Local** — LocalBusiness schema lengkap
* **Semantic HTML Sanitizer** — Pembersihan HTML semantik
* **Silo Links** — Struktur internal linking berbasis silo

== Installation ==

= Persyaratan =

* PHP 7.4 atau lebih baru
* WordPress 6.7 atau lebih baru

= Langkah Instalasi =

1. Upload folder `sgeobiz-seo` ke direktori `/wp-content/plugins/`
2. Aktifkan plugin melalui menu **Plugin** di WordPress
3. Plugin langsung aktif — SEO dasar dikonfigurasi otomatis
4. Kunjungi **SGEOBIZ SEO → Profil Bisnis** untuk mengisi data bisnis Anda

== Frequently Asked Questions ==

= Apakah SGEOBIZ SEO gratis? =

Ya, sepenuhnya gratis. Tidak ada versi premium tersembunyi, tidak ada iklan, tidak ada tracking.

= Apakah plugin ini mengirim data ke server eksternal? =

Tidak. Semua data tersimpan di database WordPress Anda sendiri.

= Bagaimana cara menampilkan breadcrumb? =

Gunakan shortcode `[sgeobiz_breadcrumb]` di template atau halaman yang diinginkan.

= Apakah kompatibel dengan WooCommerce? =

Ya. Plugin menyertakan WooCommerce schema untuk produk dan toko.

= Bagaimana cara mendapatkan koordinat GPS? =

1. Buka Google Maps dan cari lokasi bisnis Anda
2. Klik kanan pada pin lokasi
3. Salin koordinat yang muncul (misal: `-6.2088, 106.8456`)
4. Angka pertama = Latitude, angka kedua = Longitude

= Apakah Bukalapak masih didukung? =

Tidak. Sejak Januari 2025, Bukalapak menghentikan marketplace produk fisik.
Field Bukalapak telah dihapus dari pengaturan — diganti dengan Zalora.

= Apakah JD.id didukung? =

Tidak. JD.id resmi tutup sejak Maret 2023.

== Screenshots ==

1. Halaman Profil Bisnis — input data NAP, koordinat, dan semua platform digital
2. Bagian Media Sosial & Marketplace — Facebook, Instagram, TikTok, Tokopedia, Shopee, GoFood, dll.
3. Pengaturan Jam Operasional — toggle tutup per hari dengan toggle switch
4. SEO Bar — indikator status SEO di daftar post/page
5. Panel Pengaturan SEO Utama — tab berdasarkan kategori pengaturan

== Changelog ==

= 1.0.1 =
* Perbaikan: Penanda warna gold pada submenu sidebar admin kini mengikuti halaman yang aktif secara dinamis (sebelumnya macet di Umum).
* Fitur: Menambahkan metabox widget iklan CredibleMark.com di urutan teratas Dashboard Utama WordPress.
* Fitur: Iklan di dashboard akan ditampilkan ulang secara otomatis setiap kali admin login baru.
* Refactor: Mengganti nama menu utama sidebar admin dari "SGEOBIZ SEO" menjadi "SEO" (huruf kapital).
* Refactor: Mengganti semua tautan bantuan/dokumentasi sgeobiz.com di dalam kode ke GitHub Pages mandiri.

= 1.0.0 =
* Rilis pertama SGEOBIZ SEO
* Fitur: SEO meta otomatis, Schema LocalBusiness, Profil Bisnis, Sitemap XML
* Fitur: Open Graph, Twitter Cards, Breadcrumb schema
* Fitur: Robots.txt generator dengan blokir AI crawler
* Fitur: Dukungan marketplace Indonesia (Tokopedia, Shopee, Lazada, Blibli, Zalora)
* Fitur: Food delivery (GoFood, GrabFood, ShopeeFood)
* Fitur: 34 provinsi Indonesia di dropdown alamat
* Fitur: Koordinat GPS (GeoCoordinates) untuk Google Maps
* Fitur: IndexNow untuk notifikasi instan ke Bing/Yandex
* Fitur: WooCommerce schema produk
* Fitur: Panel admin modern dengan desain card minimalis

== Upgrade Notice ==

= 1.0.1 =
Pembaruan penting untuk memperbaiki highlighting menu gold dan menambahkan widget promosi baru.

= 1.0.0 =
Rilis awal SGEOBIZ SEO. Tidak ada upgrade dari versi sebelumnya.

== Other Notes ==

Dokumentasi lengkap tersedia di: https://rasyiqi-code.github.io/SGEOBIZ/

Kode sumber tersedia di: https://github.com/rasyiqi-code/SGEOBIZ

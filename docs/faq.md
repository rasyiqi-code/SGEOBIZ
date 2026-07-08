---
layout: default
title: FAQ
nav_order: 5
---

# Pertanyaan yang Sering Diajukan (FAQ)
{: .no_toc }

## Daftar Isi
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Umum

### Apakah SGEOBIZ SEO gratis?

Ya, sepenuhnya gratis. Tidak ada versi premium, tidak ada iklan, tidak ada tracking, dan tidak ada nag screen. Plugin ini adalah *freeware*, bukan *crippleware*.

### Apakah ada ekstensi berbayar?

Ada beberapa ekstensi opsional yang tersedia melalui **Extension Manager** (plugin pendamping gratis), antara lain:
- **Focus** — analisis kata kunci dan sinonim
- **Articles** — structured data artikel otomatis
- **Local** — informasi bisnis lokal lanjutan
- **Cord** — integrasi Google Analytics & Meta Pixel

### Apakah plugin ini mengirim data ke server eksternal?

Tidak. SGEOBIZ SEO tidak mengirim informasi apapun ke server kami dan tidak membuat cookie.

---

## SEO

### Mengapa tidak ada fitur analitik atau pemantauan 404?

Analisis SEO yang akurat harus dilakukan dari luar situs. Jika dijalankan di dalam situs, bot buruk akan menciptakan ribuan false positive yang mengisi database dengan data tidak berguna. Tools seperti Google Search Console, Ahrefs, atau SEMrush jauh lebih akurat untuk tujuan ini.

### Mengapa tidak ada focus keyword?

Google [secara eksplisit memperingatkan](https://developers.google.com/search/docs/advanced/guidelines/irrelevant-keywords) tentang pendekatan keyword stuffing. Mesin pencari modern menggunakan AI untuk memahami konteks konten — menulis konten relevan lebih efektif daripada mengoptimasi keyword secara manual.

### Apa arti warna di SEO Bar?

| Warna | Arti |
|---|---|
| 🟢 Hijau | SEO sudah baik |
| 🔵 Biru | Informasi situasional |
| 🟡 Kuning | Peringatan ringan |
| 🔴 Merah | Error — harus segera diperbaiki |
| ⚫ Abu-abu | Tidak terdefinisi / tidak dapat diproses |

### Sitemap saya tidak berisi kategori/gambar, apakah ini masalah?

Tidak. Mesin pencari sangat mengenal struktur WordPress dan bisa menemukannya sendiri. Tidak memiliki setiap halaman di sitemap bukan masalah — fokus pada kualitas konten dan pengalaman pengguna.

### Sitemap berisi halaman yang tidak saya inginkan?

Aktifkan opsi **noindex** pada halaman tersebut. Halaman dengan noindex otomatis dihapus dari sitemap.

---

## Teknis

### Plugin ini kompatibel dengan apa saja?

SGEOBIZ SEO kompatibel dengan:
- **Multisite** WordPress
- **WooCommerce** dan Easy Digital Downloads
- **WPML**, Polylang, WPGlobus, MultilingualPress
- **bbPress** dan wpForo
- **Headless mode** via konstanta PHP
- RTL (Arab, Ibrani, Farsi) dan Unicode penuh

### Bagaimana cara menampilkan breadcrumb secara visual?

Gunakan shortcode berikut di template theme atau halaman:

```
[sgeobiz_breadcrumb]
```

Detail konfigurasi shortcode tersedia di [dokumentasi API](/api/).

### Script application/ld+json itu apa?

JSON-LD adalah format structured data yang direkomendasikan Google. Script ini memberitahu mesin pencari tentang struktur situs Anda — koneksi sosial media, jenis bisnis, lokasi, jam operasional, dll. Ini disebut **structured data** dan merupakan kunci untuk mendapatkan rich results di SERP.

### Bisakah saya menjalankan plugin ini di mode headless (tanpa WordPress frontend)?

Ya. Definisikan konstanta berikut di `wp-config.php`:

```php
define( 'SGEOBIZ_HEADLESS', true );
```

---

## Profil Bisnis

### Data apa saja yang digunakan untuk Schema LocalBusiness?

Semua data yang diisi di **Profil Bisnis** — nama, alamat, koordinat, telepon, WhatsApp, jam operasional, dan semua URL sosial media/marketplace.

### Apakah Bukalapak masih bisa diisi sebagai marketplace?

Tidak lagi disarankan. Sejak **Januari 2025**, Bukalapak menghentikan layanan marketplace produk fisik dan beralih ke produk virtual (pulsa, token listrik). Oleh karena itu, field Bukalapak telah dihapus dari pengaturan.

### Bagaimana cara mendapatkan koordinat GPS lokasi bisnis?

1. Buka [Google Maps](https://maps.google.com)
2. Cari lokasi bisnis Anda
3. Klik kanan pada pin lokasi
4. Salin koordinat yang muncul (format: `-6.2088, 106.8456`)
5. Angka pertama = Latitude, angka kedua = Longitude

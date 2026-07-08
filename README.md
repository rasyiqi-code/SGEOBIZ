# SGEOBIZ SEO

> Plugin SEO canggih untuk WordPress — otomasi penuh meta tag, structured data Schema.org, Google Business Profile, dan optimasi bisnis lokal Indonesia.

[![WordPress](https://img.shields.io/badge/WordPress-6.7%2B-blue?logo=wordpress)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv3-green)](https://www.gnu.org/licenses/gpl-3.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange)](https://github.com/sgeobiz/sgeobiz-seo/releases)

---

## Deskripsi

**SGEOBIZ SEO** adalah plugin WordPress yang dirancang khusus untuk kebutuhan SEO bisnis lokal Indonesia. Plugin ini mengotomasi meta tag, structured data Schema.org, sitemap XML, dan menyediakan panel pengaturan Google Business Profile (GBP) yang lengkap — termasuk integrasi marketplace dan platform food delivery lokal.

**Filosofi**: Lakukan lebih sedikit, raih hasil lebih baik.

---

## Fitur

### SEO Dasar
- ✅ Meta title & description otomatis berdasarkan konten
- ✅ Canonical URL otomatis dengan pelacak real-time saat editing
- ✅ Open Graph (Facebook, Discord, WhatsApp)
- ✅ Twitter / X Cards
- ✅ SEO Bar — indikator warna status SEO di daftar post/page
- ✅ Breadcrumb Schema.org + shortcode `[sgeobiz_breadcrumb]`

### Structured Data (Schema.org JSON-LD)
- ✅ `WebSite` — nama situs dan search action
- ✅ `WebPage` / `BlogPosting` / `Article`
- ✅ `LocalBusiness` — bisnis lokal lengkap
- ✅ `BreadcrumbList` — breadcrumb navigasi
- ✅ `Product` — produk WooCommerce

### Google Business Profile
- ✅ Panel NAP (Name, Address, Phone) terpusat
- ✅ 25+ tipe bisnis Schema.org (Restaurant, Store, Clinic, dll.)
- ✅ Koordinat GPS (GeoCoordinates) untuk Google Maps
- ✅ Jam operasional per hari dengan toggle tutup
- ✅ 34 provinsi Indonesia di dropdown
- ✅ Upload logo bisnis via Media Library

### Platform Digital Indonesia

| Kategori | Platform |
|---|---|
| **Media Sosial** | Facebook, Instagram, TikTok, YouTube, Twitter/X, LinkedIn, Threads, Pinterest |
| **Marketplace** | Tokopedia, Shopee, Lazada, Blibli, Zalora |
| **Food Delivery** | GoFood (Gojek), GrabFood, ShopeeFood |
| **Lainnya** | Google Business Profile / Maps |

### Teknis Lanjutan
- ✅ Sitemap XML otomatis + notifikasi Google & Bing
- ✅ Robots.txt generator berbasis prioritas
- ✅ Blokir AI crawler (GPTBot, CCBot, Anthropic, Perplexity, Apple)
- ✅ Blokir SEO crawler (Ahrefs, Moz, SEMrush, Majestic)
- ✅ IndexNow — notifikasi instan ke Bing & Yandex
- ✅ HTTP 304 caching optimization
- ✅ Dukungan WPML, Polylang, WPGlobus, MultilingualPress
- ✅ Dukungan RTL (Arab, Ibrani, Farsi)
- ✅ Headless mode via konstanta PHP
- ✅ Full keyboard navigation & screen reader accessible

---

## Instalasi

### Persyaratan

| Komponen | Versi Minimum |
|---|---|
| PHP | 7.4 |
| WordPress | 6.7 |
| Browser | Modern (Chrome, Firefox, Edge, Safari) |

### Cara Install

```bash
# Via WP-CLI
wp plugin install sgeobiz-seo --activate

# Atau manual
# 1. Upload folder ke /wp-content/plugins/
# 2. Aktifkan di Dashboard > Plugin
```

Setelah aktif, kunjungi **WordPress Dashboard → SGEOBIZ SEO → Profil Bisnis** untuk mengisi data bisnis Anda.

---

## Struktur Plugin

```
sgeobiz-seo/
├── autodescription.php       # Main plugin file
├── readme.txt                # WordPress.org readme
├── readme.md                 # GitHub readme (ini)
├── bootstrap/                # Loader & upgrade scripts
├── inc/                      # Core plugin classes
│   ├── classes/              # PHP classes
│   ├── views/                # Template views (PHP)
│   └── functions/            # Helper functions
├── lib/                      # Assets
│   ├── css/                  # Compiled CSS
│   │   └── settings/         # Modular CSS source
│   └── js/                   # Compiled JS
│       └── settings/         # Modular JS source
├── .local/                   # Fitur kustom SGEOBIZ
│   ├── sgeobiz.php           # Loader fitur kustom
│   ├── class-sgeobiz-gbp-settings.php
│   ├── class-sgeobiz-schema-local.php
│   ├── class-sgeobiz-schema-geo.php
│   ├── class-sgeobiz-article-schema.php
│   ├── class-sgeobiz-product-enhancer.php
│   ├── class-sgeobiz-indexnow.php
│   ├── class-sgeobiz-ai-robots.php
│   ├── class-sgeobiz-geo-block.php
│   ├── class-sgeobiz-geo-meta.php
│   ├── class-sgeobiz-redirect-404.php
│   ├── class-sgeobiz-focus.php
│   ├── class-sgeobiz-silo-links.php
│   ├── class-sgeobiz-auto-alt-image.php
│   ├── class-sgeobiz-custom-schema.php
│   ├── class-sgeobiz-http-304.php
│   └── class-sgeobiz-semantic-html-sanitizer.php
├── docs/                     # Dokumentasi GitHub Pages
│   ├── _config.yml           # Konfigurasi Jekyll
│   ├── index.md              # Beranda dokumentasi
│   ├── panduan/              # Panduan pengaturan
│   ├── fitur/                # Dokumentasi fitur
│   └── faq.md                # FAQ
└── language/                 # File terjemahan (.pot)
```

---

## Dokumentasi

📖 Dokumentasi lengkap tersedia di: **[sgeobiz.com/docs](https://sgeobiz.com/docs/)**

| Halaman | Link |
|---|---|
| Panduan Instalasi | [/panduan/instalasi](/docs/panduan/) |
| Pengaturan Umum | [/panduan/pengaturan-umum](/docs/panduan/pengaturan-umum.md) |
| Profil Bisnis GBP | [/panduan/profil-bisnis](/docs/panduan/profil-bisnis.md) |
| Schema & Structured Data | [/fitur/schema](/docs/fitur/schema.md) |
| Sitemap & Robots.txt | [/fitur/sitemap-robots](/docs/fitur/sitemap-robots.md) |
| FAQ | [/faq](/docs/faq.md) |

---

## Kontribusi

Plugin ini dikembangkan oleh tim **SGEOBIZ**. Kontribusi dalam bentuk:
- 🐛 Bug report — buka [Issues](https://github.com/sgeobiz/sgeobiz-seo/issues)
- 💡 Feature request — buka [Discussions](https://github.com/sgeobiz/sgeobiz-seo/discussions)
- 🔀 Pull request — fork → branch → PR

### Development Setup

```bash
# Clone repo
git clone https://github.com/sgeobiz/sgeobiz-seo.git
cd sgeobiz-seo

# Build CSS (modular source → compiled)
python3 scripts/build_settings.py

# Struktur CSS modular ada di lib/css/settings/
# Edit file di sana, bukan di lib/css/settings.css langsung
```

---

## Changelog

### 1.0.0 — Rilis Perdana
- Fitur SEO meta otomatis (title, description, canonical)
- Schema LocalBusiness lengkap untuk bisnis lokal Indonesia
- Panel Profil Bisnis dengan 34 provinsi, koordinat GPS, jam operasional
- Integrasi marketplace Indonesia aktif (Tokopedia, Shopee, Lazada, Blibli, Zalora)
- Integrasi food delivery (GoFood, GrabFood, ShopeeFood)
- Sitemap XML + IndexNow (Bing/Yandex)
- Robots.txt generator + blokir AI & SEO crawler
- WooCommerce product schema
- Admin UI modern dengan design system CSS modular
- Full keyboard navigation & screen reader support

---

## Lisensi

SGEOBIZ SEO © 2024–2025 SGEOBIZ. Dilisensikan di bawah [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

```
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 3 of the License.
```

---

## Dukungan

- 🌐 Website: [sgeobiz.com](https://sgeobiz.com/)
- 📖 Dokumentasi: [sgeobiz.com/docs](https://sgeobiz.com/docs/)
- 💬 Support: [sgeobiz.com/support](https://sgeobiz.com/support/)
- 🐛 Issues: [GitHub Issues](https://github.com/sgeobiz/sgeobiz-seo/issues)

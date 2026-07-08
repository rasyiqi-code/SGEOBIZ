---
layout: default
title: Profil Bisnis (GBP)
parent: Panduan Pengaturan
nav_order: 2
---

# Profil Bisnis — Google Business Profile
{: .no_toc }

Panduan pengisian data bisnis untuk output Schema.org dan integrasi platform digital.
{: .fs-6 .fw-300 }

## Daftar Isi
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Akses Halaman

**WordPress Dashboard → SGEOBIZ SEO → Profil Bisnis**

---

## Informasi Bisnis Utama

| Field | Keterangan | Wajib |
|---|---|---|
| **Nama Bisnis** | Nama resmi bisnis sesuai GBP | ✅ |
| **Tipe Bisnis (Schema)** | Jenis bisnis untuk Schema.org | ✅ |
| **Deskripsi** | Deskripsi singkat bisnis (maks 750 karakter) | — |
| **Website** | URL website utama bisnis | — |
| **Email Bisnis** | Alamat email kontak resmi | — |

### Tipe Bisnis yang Tersedia

Plugin menyediakan lebih dari 25 tipe bisnis Schema.org, antara lain:

- `LocalBusiness` — Bisnis lokal umum
- `Restaurant` / `CafeOrCoffeeShop` / `Bakery` — Kuliner
- `MedicalBusiness` / `Dentist` / `Pharmacy` — Kesehatan
- `HairSalon` / `BeautySalon` / `SpaOrBeautyService` — Kecantikan
- `AutoRepair` / `AutomotiveBusiness` — Otomotif
- `Hotel` / `LodgingBusiness` — Penginapan
- `Store` — Toko retail
- `FinancialService` / `LegalService` — Layanan profesional
- `RealEstateAgent` / `HomeAndConstructionBusiness` — Properti & konstruksi
- `EducationalOrganization` / `TravelAgency` — Pendidikan & perjalanan

---

## Kontak

| Field | Format | Contoh |
|---|---|---|
| **Telepon** | E.164 internasional | `+6221xxxxxxxx` |
| **WhatsApp** | Format tanpa `+` | `628xxxxxxxxx` |
| **Area Layanan** | Kode negara/wilayah, pisahkan koma | `ID, SG, MY` |
| **Bahasa Tersedia** | Pisahkan koma | `Indonesian, English` |

---

## Alamat & Lokasi

### Alamat Utama

Isi alamat lengkap bisnis utama Anda. Data ini digunakan untuk output structured data `PostalAddress` di Schema.org.

| Field | Contoh |
|---|---|
| **Jalan** | Jl. Sudirman No. 123 |
| **Kota** | Jakarta Selatan |
| **Provinsi** | DKI Jakarta |
| **Kode Pos** | 12190 |
| **Negara** | Indonesia (otomatis) |

### Koordinat GPS

Digunakan untuk output `GeoCoordinates` di Schema.org — meningkatkan akurasi lokasi di Google Maps.

**Cara mendapatkan koordinat dari Google Maps:**
1. Buka [Google Maps](https://maps.google.com)
2. Cari lokasi bisnis Anda
3. Klik kanan pada pin lokasi
4. Salin angka yang muncul (format: `-6.2088, 106.8456`)
5. Angka pertama = **Latitude**, angka kedua = **Longitude**

{: .note }
Latitude Jakarta berkisar `-6.1` hingga `-6.4`. Longitude Jakarta berkisar `106.7` hingga `107.0`.

---

## Media Sosial & Marketplace

### Media Sosial

| Platform | Contoh URL |
|---|---|
| **Facebook** | `https://facebook.com/namabisnis` |
| **Instagram** | `https://instagram.com/namabisnis` |
| **TikTok** | `https://tiktok.com/@namabisnis` |
| **YouTube** | `https://youtube.com/@namabisnis` |
| **Twitter / X** | `https://x.com/namabisnis` |
| **LinkedIn** | `https://linkedin.com/company/namabisnis` |
| **Threads** | `https://threads.net/@namabisnis` |
| **Pinterest** | `https://pinterest.com/namabisnis` |

### Marketplace (Aktif per 2025)

| Platform | Status | Catatan |
|---|---|---|
| **Tokopedia** | ✅ Aktif | Marketplace terbesar di Indonesia |
| **Shopee** | ✅ Aktif | Platform multi-kategori |
| **Lazada** | ✅ Aktif | Bagian dari Alibaba Group |
| **Blibli** | ✅ Aktif | Ekosistem omnichannel |
| **Zalora** | ✅ Aktif | Spesialisasi fashion |
| ~~Bukalapak~~ | ⚠️ Dihapus | Tidak lagi marketplace produk fisik (sejak Jan 2025) |
| ~~JD.id~~ | ❌ Dihapus | Tutup total sejak Maret 2023 |

### Google & Pesan Antar Makanan

| Platform | Keterangan |
|---|---|
| **Google Business / Maps** | URL profil GBP atau short link Maps |
| **GoFood (Gojek)** | Link restoran di aplikasi GoFood |
| **GrabFood** | Link restoran di aplikasi GrabFood |
| **ShopeeFood** | Link restoran di aplikasi ShopeeFood |

---

## Jam Operasional

Atur jam buka-tutup untuk setiap hari dalam seminggu. Data ini digunakan untuk output `openingHoursSpecification` di Schema.org.

- **Toggle "Tutup"** — Centang jika bisnis tutup di hari tersebut
- Saat toggle "Tutup" aktif, field jam akan dinonaktifkan otomatis
- Format waktu: `HH:MM` (24 jam), contoh `08:00` – `21:00`

---

## Logo Bisnis

Upload logo bisnis untuk digunakan di output Schema.org `logo`. 

**Rekomendasi:**
- Format: PNG atau SVG
- Ukuran: minimal 112×112 px, rasio 1:1 (persegi) lebih disarankan
- Background: transparan atau putih

Klik **Pilih / Ganti Logo** untuk membuka Media Library WordPress.

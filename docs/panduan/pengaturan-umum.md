---
layout: default
title: Pengaturan Umum
parent: Panduan Pengaturan
nav_order: 1
---

# Pengaturan Umum
{: .no_toc }

## Daftar Isi
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Akses Halaman Pengaturan

Setelah plugin diaktifkan, akses pengaturan melalui:

**WordPress Dashboard → SGEOBIZ SEO → Pengaturan**

---

## Tab Pengaturan

SGEOBIZ SEO memiliki beberapa tab pengaturan utama:

| Tab | Fungsi |
|---|---|
| **Umum** | Informasi situs, canonical URL, penanda halaman |
| **Judul & Deskripsi** | Format meta title & description global |
| **Sosial** | Open Graph, Twitter Cards, Facebook |
| **Schema** | Structured data (JSON-LD) |
| **Privasi & Robot** | Noindex, nofollow, robots.txt |
| **Sitemap** | Konfigurasi XML Sitemap |
| **Webmaster** | Verifikasi Google Search Console, Bing, dll. |

---

## Informasi Situs

### Nama Situs
Plugin akan mengambil nama situs dari **Pengaturan → Umum → Judul Situs** WordPress. Anda bisa menggantinya di sini jika ingin nama berbeda di meta tag.

### Tagline
Tagline digunakan sebagai fallback untuk description halaman beranda jika belum diisi secara manual.

---

## Format Judul (Meta Title)

Format default: `Judul Halaman ‹ Nama Situs`

Anda bisa mengubah:
- **Pemisah judul** — karakter pemisah antara judul halaman dan nama situs (contoh: `‹`, `|`, `—`, `•`)
- **Posisi nama situs** — di kiri atau kanan judul halaman

### Rekomendasi
Gunakan pemisah yang ramping seperti `‹` atau `|`. Hindari pemisah yang panjang karena memakan karakter title yang berharga (maks ~60 karakter oleh Google).

---

## Deskripsi Otomatis

SGEOBIZ SEO akan secara otomatis mengambil deskripsi dari:
1. Field deskripsi yang diisi manual di meta box halaman/postingan
2. Excerpt postingan (jika diisi)
3. Paragraf pertama konten halaman

### Tips Panjang Deskripsi
Google menampilkan sekitar **155–160 karakter** di SERP. Plugin menyertakan counter karakter real-time di editor.

---

## Eksklusi Halaman dari SEO

Di tab **Umum → Eksklusi**, Anda bisa:
- Menandai tipe post tertentu agar tidak diindex
- Mengecualikan kategori atau tag dari sitemap
- Menonaktifkan SEO Bar untuk tipe post yang tidak relevan

---

## SEO Bar

SEO Bar adalah indikator warna yang muncul di daftar post/page:

| Warna | Arti |
|---|---|
| 🟢 **Hijau** | SEO baik |
| 🔵 **Biru** | Informasional / situasional |
| 🟡 **Kuning** | Peringatan ringan |
| 🔴 **Merah** | Error — harus diperbaiki |
| ⚫ **Abu-abu** | Tidak terdefinisi |

Hover pada setiap indikator untuk melihat penjelasan detail.

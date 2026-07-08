---
layout: default
title: Schema & Structured Data
parent: Fitur
nav_order: 1
---

# Schema & Structured Data
{: .no_toc }

SGEOBIZ SEO mengoutput JSON-LD Schema.org secara otomatis untuk meningkatkan *rich results* di Google.
{: .fs-6 .fw-300 }

## Daftar Isi
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Apa itu Structured Data?

Structured data adalah format standar untuk memberikan informasi tentang halaman Anda kepada mesin pencari. Google menggunakannya untuk menampilkan **rich results** (hasil pencarian kaya) seperti:

- ⭐ Rating bintang
- 📍 Lokasi bisnis di Maps
- 🍞 Breadcrumb navigasi
- 📅 Event dan jadwal
- ❓ FAQ accordion

SGEOBIZ SEO menggunakan standar [Schema.org](https://schema.org) dalam format **JSON-LD** — direkomendasikan Google.

---

## Tipe Schema yang Dioutput

### WebSite
Otomatis dioutput di semua halaman, berisi:
- Nama situs
- URL situs
- Search action (jika pencarian internal aktif)

### WebPage / BlogPosting / Article
Dioutput di setiap halaman/postingan, berisi:
- Judul halaman
- Deskripsi
- Tanggal publish & update
- Penulis (untuk postingan)
- Breadcrumb

### LocalBusiness
Dioutput berdasarkan data **Profil Bisnis**, berisi:
- Nama bisnis
- Tipe bisnis (Restaurant, Store, dll.)
- Alamat lengkap (`PostalAddress`)
- Koordinat GPS (`GeoCoordinates`)
- Nomor telepon & WhatsApp
- Jam operasional (`openingHoursSpecification`)
- URL semua platform sosial media & marketplace
- Logo bisnis

### BreadcrumbList
Breadcrumb otomatis untuk semua halaman berdasarkan hierarki WordPress:

```
Beranda > Kategori > Judul Postingan
```

Anda juga bisa menampilkan breadcrumb secara visual menggunakan shortcode:

```
[sgeobiz_breadcrumb]
```

---

## Contoh Output JSON-LD

### LocalBusiness

```json
{
  "@context": "https://schema.org",
  "@type": "Restaurant",
  "name": "Warung Pak Budi",
  "description": "Warung makan nasi padang terenak di Jakarta Selatan",
  "url": "https://warungpakbudi.com",
  "telephone": "+62217654321",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Jl. Melawai Raya No. 25",
    "addressLocality": "Jakarta Selatan",
    "addressRegion": "DKI Jakarta",
    "postalCode": "12160",
    "addressCountry": "ID"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": -6.2428,
    "longitude": 106.7986
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
      "opens": "08:00",
      "closes": "21:00"
    }
  ],
  "sameAs": [
    "https://instagram.com/warungpakbudi",
    "https://tokopedia.com/warungpakbudi",
    "https://maps.app.goo.gl/xxxxx"
  ]
}
```

---

## Validasi Structured Data

Setelah mengisi Profil Bisnis, validasi output schema menggunakan:

1. [Google Rich Results Test](https://search.google.com/test/rich-results) — uji apakah halaman eligible untuk rich results
2. [Schema Markup Validator](https://validator.schema.org/) — validasi syntax Schema.org
3. **Google Search Console → Enhancements** — pantau status structured data di seluruh situs

---

## Tips Optimasi

{: .tip }
Isi **semua field** di Profil Bisnis untuk mendapatkan structured data yang paling lengkap. Setiap field yang kosong berarti informasi yang hilang dari index Google.

{: .important }
Pastikan data di Profil Bisnis **konsisten** dengan data di Google Business Profile (GBP) yang terdaftar. Inkonsistensi data NAP (Name, Address, Phone) bisa menurunkan kepercayaan Google terhadap bisnis Anda.

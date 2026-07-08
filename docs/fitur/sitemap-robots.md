---
layout: default
title: Sitemap & Robots.txt
parent: Fitur
nav_order: 2
---

# Sitemap & Robots.txt
{: .no_toc }

## Daftar Isi
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## XML Sitemap

SGEOBIZ SEO menghasilkan sitemap XML otomatis yang dapat diakses di:

```
https://domain-anda.com/sitemap.xml
```

Sitemap secara otomatis mencakup:
- Semua halaman & postingan yang dapat diindex
- Custom Post Type (CPT) yang publik
- Halaman arsip kategori dan tag

### Notifikasi Otomatis ke Mesin Pencari

Setiap kali konten di situs diperbarui, SGEOBIZ SEO otomatis mengirim notifikasi ke:
- **Google Search Console** (via Ping)
- **Bing Webmaster Tools** (via Ping)

### Apa yang Tidak Ada di Sitemap?

Halaman yang ditandai **noindex** tidak akan muncul di sitemap — ini adalah perilaku yang benar. Google tidak perlu diperlihatkan halaman yang tidak ingin Anda indeks.

---

## Robots.txt

SGEOBIZ SEO menyertakan generator `robots.txt` berbasis prioritas. Akses di:

```
https://domain-anda.com/robots.txt
```

### Default Robots.txt

```
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://domain-anda.com/sitemap.xml
```

### Blokir AI Crawler

Di pengaturan **Privasi & Robot**, Anda bisa mengaktifkan pemblokiran untuk:

| Crawler | Digunakan oleh |
|---|---|
| `GPTBot` | OpenAI / ChatGPT |
| `CCBot` | Common Crawl (banyak AI) |
| `anthropic-ai` | Anthropic Claude |
| `PerplexityBot` | Perplexity AI |
| `Applebot-Extended` | Apple AI |

### Blokir SEO Crawler

| Crawler | Digunakan oleh |
|---|---|
| `AhrefsBot` | Ahrefs |
| `MJ12bot` | Majestic |
| `DotBot` | Moz |
| `SemrushBot` | SEMrush |

{: .warning }
Memblokir SEO crawler berarti data backlink dan authority situs Anda tidak akan muncul di tools tersebut. Pertimbangkan ini jika Anda atau klien menggunakan tools tersebut untuk analisis SEO.

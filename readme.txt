=== WP Builder ===
Contributors: websweet
Tags: page builder, ai, chat, landing page
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Page builder berbasis chat. Buat halaman, edit section, header, dan footer langsung lewat percakapan dengan AI.

== Description ==

WP Builder adalah page builder yang dikendalikan sepenuhnya lewat chat, mirip Lovable. Kamu cukup mengetik instruksi dalam bahasa alami, dan AI akan:

* Membuat halaman baru lengkap dengan section (hero, cards, CTA, galeri, dan lain-lain).
* Mengedit judul, teks, tombol, dan gambar di section tertentu.
* Menambah, menghapus, dan memindahkan section.
* Mengedit header dan footer situs.
* Mempublikasikan halaman.

Semua dikerjakan melalui tool-calling ke provider AI OpenAI-compatible pilihanmu (OpenAI, OpenRouter, DeepSeek, Groq, Ollama, dan lain-lain).

= Fitur =

* Chat interface dengan preview live di wp-admin.
* 14 jenis section bawaan.
* Header/footer terpisah yang dipakai halaman-halaman Builder.
* Preview halaman sebelum dipublikasikan.
* API key dibawa sendiri (tidak tergantung layanan pihak ketiga).

= Cara pakai =

1. Aktifkan plugin, buka menu **Builder → Pengaturan**.
2. Isi Base URL, API Key, dan Model dari provider AI-mu, lalu klik **Uji Koneksi**.
3. Buka tab **Chat** dan mulailah, contoh:
   * "Buat landing page untuk jasa desain grafis"
   * "Ubah judul hero jadi 'Desain yang Memikat'"
   * "Tambah section harga dengan 3 kartu"
   * "Edit header: tambah logo dan menu"
   * "Publish halaman"

== Frequently Asked Questions ==

= Provider AI apa yang didukung? =

Semua provider yang punya API kompatibel dengan OpenAI `/chat/completions`, termasuk OpenAI, OpenRouter, DeepSeek, Groq, dan Ollama.

= Apakah header/footer berlaku untuk seluruh situs? =

Pada versi ini, header/footer berlaku untuk halaman yang dibuat dengan Builder. Halaman biasa (post, arsip) tetap memakai header/footer tema.

== Changelog ==

= 0.1.0 =
* Versi awal: chat builder, section tools, header/footer parts, preview live.

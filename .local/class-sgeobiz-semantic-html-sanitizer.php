<?php
/**
 * SGEOBIZ Automated HTML Semantic Sanitizer
 *
 * Menjaga validitas dan kepatuhan semantik HTML draf artikel di front-end:
 * 1. Single H1 Enforcement: Menurunkan tag H1 ganda di dalam draf konten menjadi H2
 *    agar H1 judul halaman tetap tunggal secara mutlak.
 * 2. Heading Hierarchy Alignment: Memperbaiki lompatan tingkatan heading yang tidak
 *    logis (misal dari H2 langsung ke H4) menjadi berurutan secara otomatis (H2 -> H3).
 *
 * Hal ini meminimalkan pemborosan perayapan Google (crawl budget) akibat hierarki kode yang salah.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Semantic_HTML_Sanitizer {

	/**
	 * Daftarkan hook filter content.
	 */
	public static function init() {
		$instance = new self();
		// Prioritas tinggi 20 agar dijalankan paling akhir setelah plugin lain selesai memproses konten
		add_filter( 'the_content', [ $instance, 'sanitize_headings' ], 20 );
	}

	/**
	 * Sanitasi hierarki heading konten.
	 *
	 * @param string $content HTML konten.
	 * @return string Konten termodifikasi.
	 */
	public function sanitize_headings( $content ) {
		// Hanya jalankan di halaman postingan tunggal front-end
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Cari semua tag heading H1-H6 di dalam konten draf
		$pattern = '/<(h[1-6])([^>]*)>(.*?)<\/\1>/is';
		
		if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			return $content;
		}

		$prev_level = 1; // Judul luar artikel di tema bertindak sebagai H1 utama

		foreach ( $matches as $match ) {
			$raw_tag       = $match[1]; // e.g. "h4"
			$attributes    = $match[2]; // e.g. " class='abc'"
			$inner_content = $match[3];
			$original_tag  = $match[0]; // Kode utuh tag asli

			$current_level = intval( substr( $raw_tag, 1 ) );

			// 1. Single H1 Enforcement: Paksa H1 di dalam editor konten turun ke H2
			if ( $current_level === 1 ) {
				$current_level = 2;
			}

			// 2. Heading Hierarchy Alignment: Mencegah lompatan hierarki (misal H2 langsung ke H4)
			if ( $current_level - $prev_level > 1 ) {
				$current_level = $prev_level + 1;
			}

			$new_tag = 'h' . $current_level;

			// Jika ada perubahan tingkatan tag, lakukan replace di HTML draf
			if ( $new_tag !== $raw_tag ) {
				$replacement = "<{$new_tag}{$attributes}>{$inner_content}</{$new_tag}>";
				// Replace tag asli dengan tag yang sudah disembuhkan secara presisi
				$content = str_replace( $original_tag, $replacement, $content );
			}

			// Catat tingkat heading saat ini untuk perbandingan berikutnya
			$prev_level = $current_level;
		}

		return $content;
	}
}

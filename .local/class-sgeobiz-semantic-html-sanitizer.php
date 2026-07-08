<?php
/**
 * SGEOBIZ Automated HTML Semantic Sanitizer
 *
 * Menjaga validitas dan kepatuhan semantik HTML konten artikel di front-end:
 * 1. Single H1 Enforcement: Menurunkan tag H1 ganda di dalam konten menjadi H2
 *    agar H1 judul halaman tetap tunggal secara mutlak.
 * 2. Heading Hierarchy Alignment: Memperbaiki lompatan tingkatan heading yang tidak
 *    logis (misal dari H2 langsung ke H4) menjadi berurutan secara otomatis.
 * 3. Auto ID pada Heading: Menambahkan id="slug" pada tiap heading agar dapat dijadikan
 *    jump link (anchor) oleh AI Overviews dan Featured Snippets.
 *
 * Fix 2026: Menggunakan PREG_OFFSET_CAPTURE + preg_replace_callback per-occurance
 * untuk menghindari bug str_replace saat dua heading memiliki teks yang identik.
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
		// Prioritas 20 agar dijalankan setelah plugin lain selesai memproses konten
		add_filter( 'the_content', [ $instance, 'sanitize_headings' ], 20 );
	}

	/**
	 * Sanitasi hierarki heading konten menggunakan callback-based replacement
	 * agar aman terhadap heading ganda dengan teks identik.
	 *
	 * @param string $content HTML konten.
	 * @return string Konten termodifikasi.
	 */
	public function sanitize_headings( string $content ): string {
		// Hanya jalankan di halaman postingan tunggal front-end
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$prev_level  = 1; // H1 judul halaman di template tema dianggap sudah ada
		$id_registry = []; // Lacak ID yang sudah digunakan untuk mencegah duplikasi

		// Gunakan preg_replace_callback agar setiap heading diproses secara individual
		// dan aman terhadap heading dengan konten teks identik
		$content = preg_replace_callback(
			'/<(h[1-6])([^>]*)>(.*?)<\/\1>/is',
			function ( $matches ) use ( &$prev_level, &$id_registry ) {
				$raw_tag       = $matches[1]; // e.g. "h4"
				$attributes    = $matches[2]; // e.g. " class='abc'"
				$inner_content = $matches[3]; // Konten di dalam tag

				$current_level = (int) substr( $raw_tag, 1 );

				// 1. Single H1 Enforcement: Paksa H1 di dalam konten turun ke H2
				if ( $current_level === 1 ) {
					$current_level = 2;
				}

				// 2. Heading Hierarchy Alignment: Cegah lompatan hierarki (H2 → H4 diubah ke H2 → H3)
				if ( $current_level - $prev_level > 1 ) {
					$current_level = $prev_level + 1;
				}

				$prev_level = $current_level;
				$new_tag    = 'h' . $current_level;

				// 3. Tambahkan ID otomatis jika belum diset (diperlukan untuk AI jump link)
				$has_id = preg_match( '/\bid\s*=\s*(["\'])(.*?)\1/is', $attributes );
				if ( ! $has_id ) {
					$base_slug = sanitize_title( wp_strip_all_tags( $inner_content ) );
					if ( ! empty( $base_slug ) ) {
						// Tambahkan suffix -2, -3 dst jika ID sudah digunakan sebelumnya di halaman ini
						$unique_slug = $base_slug;
						$counter     = 2;
						while ( isset( $id_registry[ $unique_slug ] ) ) {
							$unique_slug = $base_slug . '-' . $counter;
							$counter++;
						}
						$id_registry[ $unique_slug ] = true;
						$attributes = ' id="' . esc_attr( $unique_slug ) . '"' . $attributes;
					}
				}

				return "<{$new_tag}{$attributes}>{$inner_content}</{$new_tag}>";
			},
			$content
		);

		return $content ?? '';
	}
}

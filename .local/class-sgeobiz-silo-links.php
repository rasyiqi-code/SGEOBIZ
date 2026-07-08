<?php
/**
 * SGEOBIZ Enterprise Auto Silo Hyperlinker
 *
 * Mengotomasi penyebaran Tautan Internal (Internal Link Silo) di tengah paragraf
 * konten artikel draf dengan dua tipe optimasi (seperti media berita besar):
 * 1. Tipe Inline Block "Baca Juga" setelah paragraf ke-3.
 * 2. Tipe Contextual Auto-Hyperlinking kata kunci target (maksimal 2 link per post).
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Silo_Links {

	/**
	 * Daftarkan hook filter content.
	 */
	public static function init() {
		$instance = new self();
		add_filter( 'the_content', [ $instance, 'process_silo_links' ] );
	}

	/**
	 * Proses dan sisipkan link silo kontekstual di dalam konten artikel.
	 *
	 * @param string $content HTML konten artikel asli.
	 * @return string Konten termodifikasi.
	 */
	public function process_silo_links( $content ) {
		// Hanya proses di halaman post tunggal utama (bukan feed, widget, dsb)
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		// Cari kategori yang diasosiasikan dengan artikel ini
		$categories = get_the_category( $post_id );
		if ( empty( $categories ) ) {
			return $content;
		}

		$cat_ids = wp_list_pluck( $categories, 'term_id' );

		// Query 5 artikel terbaru di bawah kategori yang sama
		$related_posts = get_posts( [
			'category__in'   => $cat_ids,
			'post__not_in'   => [ $post_id ],
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		if ( empty( $related_posts ) ) {
			return $content;
		}

		// Enqueue dashicons agar ikon wordpress dimuat di front-end
		wp_enqueue_style( 'dashicons' );

		// 1. Jalankan Contextual Auto-Hyperlinker
		$content = $this->apply_contextual_hyperlinks( $content, $related_posts );

		// 2. Jalankan Inline Block "Baca Juga"
		$content = $this->inject_inline_block_link( $content, $related_posts );

		// 3. Tambahkan styling CSS premium
		$css = '
		<style>
			.sgeobiz-inline-silo-blockquote {
				margin: 24px 0;
				padding: 12px 18px;
				border-left: 4px solid #0073aa;
				background: #f8fafc;
				font-size: 14px;
				line-height: 1.6;
				display: flex;
				align-items: center;
				gap: 8px;
				box-shadow: 0 1px 2px rgba(0,0,0,0.05);
				border-top: 1px solid #e2e8f0;
				border-right: 1px solid #e2e8f0;
				border-bottom: 1px solid #e2e8f0;
				border-radius: 0 6px 6px 0;
				text-align: left;
			}
			.sgeobiz-inline-silo-blockquote .dashicons {
				color: #0073aa;
				font-size: 16px;
				width: 16px;
				height: 16px;
				line-height: 1;
				margin: 0;
			}
			.sgeobiz-inline-silo-blockquote a {
				font-weight: 600;
				color: #0073aa;
				text-decoration: none;
			}
			.sgeobiz-inline-silo-blockquote a:hover {
				text-decoration: underline;
				color: #005177;
			}
			a.sgeobiz-context-link {
				color: #0073aa;
				text-decoration: underline;
				font-weight: 500;
			}
			a.sgeobiz-context-link:hover {
				color: #005177;
			}
		</style>
		';

		return $css . $content;
	}

	/**
	 * Otomatis memindai teks artikel dan mengubah frasa kata kunci target
	 * (judul artikel kategori sejenis) menjadi contextual hyperlink aktif.
	 *
	 * @param string $content HTML konten.
	 * @param array  $posts   List postingan sejenis.
	 * @return string Konten termodifikasi.
	 */
	private function apply_contextual_hyperlinks( string $content, array $posts ) {
		$link_count = 0;
		$max_links  = 2; // Maksimal 2 inline link per artikel agar natural
		$keywords   = [];

		// Kumpulkan kata kunci dari postingan sejenis
		foreach ( $posts as $p ) {
			// Coba ambil bare title kustom jika ada (biasanya lebih pendek & kaya kata kunci)
			$custom_title = get_post_meta( $p->ID, '_sgeobiz_custom_title', true );
			$keyword      = ! empty( $custom_title ) ? $custom_title : $p->post_title;
			$keyword      = trim( wp_strip_all_tags( $keyword ) );

			if ( strlen( $keyword ) > 4 ) {
				$keywords[ $keyword ] = get_permalink( $p->ID );
			}
		}

		if ( empty( $keywords ) ) {
			return $content;
		}

		// Pecah konten berdasarkan tag HTML agar kita HANYA mengganti teks biasa
		// dan tidak merusak / mengganti kata di dalam tag atau atribut HTML (seperti href, alt, dsb)
		$parts = preg_split( '/(<[^>]+>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );

		foreach ( $keywords as $kw => $url ) {
			if ( $link_count >= $max_links ) {
				break;
			}

			// Optimasi: lewati jika kata kunci tidak ada sama sekali di dalam konten
			if ( stripos( $content, $kw ) === false ) {
				continue;
			}

			// Cocokkan kata kunci sebagai kata utuh (word boundary)
			$quoted_kw = preg_quote( $kw, '/' );
			$pattern   = '/\b' . $quoted_kw . '\b/iu';

			foreach ( $parts as $i => $part ) {
				// Hanya proses jika part tersebut bukan tag HTML
				if ( strpos( $part, '<' ) !== 0 ) {
					if ( preg_match( $pattern, $part ) ) {
						// Ganti hanya kemunculan pertama kata kunci tersebut secara global
						$parts[ $i ] = preg_replace( $pattern, '<a href="' . esc_url( $url ) . '" class="sgeobiz-context-link">$0</a>', $part, 1 );
						$link_count++;
						break; // Hentikan part loop untuk kata kunci ini, lanjut ke kata kunci berikutnya
					}
				}
			}
		}

		return implode( '', $parts );
	}

	/**
	 * Sisipkan blok kutipan "Baca Juga" di tengah artikel (setelah paragraf ke-3).
	 *
	 * @param string $content HTML konten.
	 * @param array  $posts   List postingan sejenis.
	 * @return string Konten termodifikasi.
	 */
	private function inject_inline_block_link( string $content, array $posts ) {
		// Pilih artikel pertama dari query untuk dijadikan block link
		$target_post = $posts[0] ?? null;
		if ( ! $target_post ) {
			return $content;
		}

		$custom_title = get_post_meta( $target_post->ID, '_sgeobiz_custom_title', true );
		$anchor_text  = ! empty( $custom_title ) ? $custom_title : $target_post->post_title;
		$permalink    = get_permalink( $target_post->ID );

		// Buat HTML blockquote premium
		$inline_html = sprintf(
			'<blockquote class="sgeobiz-inline-silo-blockquote"><span class="dashicons dashicons-arrow-right-alt"></span> <strong>%s</strong> <a href="%s">%s</a></blockquote>',
			esc_html__( 'Baca Juga:', 'default' ),
			esc_url( $permalink ),
			esc_html( wp_strip_all_tags( $anchor_text ) )
		);

		// Pecah konten berdasarkan tag penutup paragraf </p>
		$paragraphs = explode( '</p>', $content );

		if ( count( $paragraphs ) > 3 ) {
			// Sisipkan tepat setelah paragraf ke-3
			$paragraphs[2] .= '</p>' . $inline_html;
			return implode( '</p>', $paragraphs );
		}

		// Fallback jika artikel pendek (kurang dari 3 paragraf), tempelkan di akhir
		return $content . $inline_html;
	}
}

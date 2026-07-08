<?php
/**
 * SGEOBIZ Article Schema Injector (SEO 2026)
 *
 * Mengupgrade node WebPage menjadi Article atau BlogPosting di halaman
 * post artikel, dengan menyertakan properti kunci untuk AI Overviews:
 *
 * - @type      : ["Article", "WebPage"] (dual-type, backward-compatible)
 * - headline   : Judul terenkode, max 110 karakter (batas Google)
 * - wordCount  : Jumlah kata konten bersih → sinyal depth untuk AI
 * - keywords   : Dari Focus meta (_sgeobiz_focus_keywords) jika terisi
 * - articleBody: 200 kata pertama konten bersih → sinyal extractability AI
 * - mainEntityOfPage: Referensi ke WebPage @id agar graph terhubung
 *
 * @see https://schema.org/Article
 * @see https://developers.google.com/search/docs/appearance/structured-data/article
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Article_Schema {

	/** Singleton instance. */
	private static $inst = null;

	/**
	 * Inisialisasi modul.
	 */
	public static function init() {
		if ( self::$inst ) return;
		self::$inst = new self();

		// Priority 15 → setelah Schema_GEO (priority 12), sebelum filter umum
		add_filter( 'sgeobiz_seo_schema_graph_data', [ self::$inst, 'inject_article_schema' ], 15, 2 );
	}

	/**
	 * Inject properti Article ke node WebPage yang sudah ada di graph.
	 *
	 * @param array      $graph Array graph JSON-LD bawaan SGEOBIZ SEO.
	 * @param array|null $args  Query args.
	 * @return array Graph termodifikasi.
	 */
	public function inject_article_schema( array $graph, $args = null ) {
		// Hanya proses di halaman post standar di front-end
		if ( ! is_singular( 'post' ) ) {
			return $graph;
		}

		$post_id = get_the_ID();
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || 'publish' !== $post->post_status ) {
			return $graph;
		}

		// Siapkan data Article
		$content_raw  = wp_strip_all_tags( do_shortcode( $post->post_content ) );
		$content_raw  = preg_replace( '/\s+/', ' ', trim( $content_raw ) );
		$word_count   = str_word_count( $content_raw );
		$headline     = mb_substr( html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' ), 0, 110 );
		$article_body = $this->get_article_body( $content_raw, 200 );
		$keywords     = $this->get_focus_keywords( $post_id );

		// Loop graph, cari dan upgrade node WebPage
		foreach ( $graph as &$entity ) {
			if ( ! isset( $entity['@type'] ) ) {
				continue;
			}

			$types = (array) $entity['@type'];

			if ( ! in_array( 'WebPage', $types, true ) ) {
				continue;
			}

			// Upgrade @type ke dual-type
			$entity['@type']     = [ 'Article', 'WebPage' ];
			$entity['headline']  = $headline;
			$entity['wordCount'] = $word_count;

			if ( $article_body ) {
				$entity['articleBody'] = $article_body;
			}

			if ( ! empty( $keywords ) ) {
				$entity['keywords'] = $keywords;
			}

			// mainEntityOfPage → sinyal utama konten untuk Google AI
			if ( ! empty( $entity['@id'] ) ) {
				$entity['mainEntityOfPage'] = [
					'@type' => 'WebPage',
					'@id'   => $entity['@id'],
				];
			}

			break; // Hanya modifikasi 1 node WebPage
		}
		unset( $entity );

		return $graph;
	}

	/**
	 * Ambil ringkasan articleBody dari N kata pertama konten bersih.
	 *
	 * @param string $text      Konten teks bersih.
	 * @param int    $max_words Jumlah kata maksimal.
	 * @return string Teks ringkasan.
	 */
	private function get_article_body( string $text, int $max_words ): string {
		if ( empty( $text ) ) {
			return '';
		}

		$words = explode( ' ', $text );

		if ( count( $words ) <= $max_words ) {
			return $text;
		}

		return implode( ' ', array_slice( $words, 0, $max_words ) ) . '…';
	}

	/**
	 * Ambil keywords dari meta Focus module sebagai array string.
	 *
	 * @param int $post_id ID post.
	 * @return array List keyword bersih.
	 */
	private function get_focus_keywords( int $post_id ): array {
		$raw = get_post_meta( $post_id, '_sgeobiz_focus_keywords', true );

		if ( empty( $raw ) ) {
			return [];
		}

		$decoded = json_decode( $raw, true );

		if ( is_array( $decoded ) ) {
			return array_values( array_filter( array_map( 'sanitize_text_field', $decoded ) ) );
		}

		$single = sanitize_text_field( $raw );
		return $single ? [ $single ] : [];
	}
}

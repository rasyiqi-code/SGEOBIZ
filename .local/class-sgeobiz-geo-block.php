<?php
/**
 * SGEOBIZ GEO Answer Block & Shortcode
 *
 * Menyediakan komponen penulisan ringkasan singkat (40-60 kata) yang dioptimasi
 * khusus untuk perayap AI (GEO / AI Overviews). Komponen ini menghasilkan container
 * HTML dengan kelas `.ringkasan-artikel-geo` yang otomatis dideteksi oleh skema
 * SpeakableSpecification untuk memicu visualisasi cuplikan jawaban AI di Google.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_GEO_Block {

	/**
	 * Inisialisasi modul.
	 */
	public static function init() {
		$instance = new self();
		
		// Daftarkan shortcode [ringkasan_geo]
		add_shortcode( 'ringkasan_geo', [ $instance, 'render_shortcode' ] );

		// Otomatis inject ringkasan dari metabox ke bagian atas konten artikel di front-end
		add_filter( 'the_content', [ $instance, 'auto_inject_geo_summary' ], 8 );
		
		// Daftarkan inline CSS di front-end untuk memberikan visualisasi premium
		add_action( 'wp_enqueue_scripts', [ $instance, 'enqueue_frontend_styles' ] );
	}

	/**
	 * Otomatis inject ringkasan GEO di awal artikel jika field diisi di editor.
	 *
	 * @param string $content HTML konten.
	 * @return string HTML konten termodifikasi.
	 */
	public function auto_inject_geo_summary( $content ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id     = get_the_ID();
		$geo_summary = $post_id ? get_post_meta( $post_id, '_sgeobiz_geo_summary', true ) : '';

		if ( ! empty( $geo_summary ) ) {
			// Jika user sudah menulis shortcode secara manual di dalam pos, abaikan auto-inject
			if ( has_shortcode( $content, 'ringkasan_geo' ) || strpos( $content, 'ringkasan-artikel-geo' ) !== false ) {
				return $content;
			}

			$html    = $this->render_html( $geo_summary );
			$content = $html . $content;
		}

		return $content;
	}

	/**
	 * Render keluaran HTML dari shortcode [ringkasan_geo] dengan visual premium.
	 *
	 * @param array  $atts    Atribut shortcode.
	 * @param string $content Konten teks ringkasan.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts, $content = '' ) {
		if ( empty( $content ) ) {
			return '';
		}
		return $this->render_html( $content );
	}

	/**
	 * Bangun HTML wrapper premium untuk GEO block.
	 *
	 * @param string $content Teks ringkasan.
	 * @return string HTML output.
	 */
	private function render_html( string $content ): string {
		// Bersihkan tag bersarang tapi pertahankan format teks dasar
		$clean_content = wp_kses_post( $content );

		// Render container dengan struktur glassmorphic premium
		$html = '<div class="ringkasan-artikel-geo">';
		$html .= '  <div class="ringkasan-geo-header">';
		$html .= '    <span class="ringkasan-geo-icon">';
		// SVG Icon AI Sparkle yang elegan
		$html .= '      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2a1 1 0 0 1 .897.553l2.25 4.5 4.964.721a1 1 0 0 1 .554 1.706l-3.592 3.5 1.026 5.86a1 1 0 0 1-1.487 1.08L12 17.25l-4.612 2.67a1 1 0 0 1-1.487-1.08l1.026-5.86-3.592-3.5a1 1 0 0 1 .554-1.706l4.964-.721 2.25-4.5A1 1 0 0 1 12 2zm0 2.915l-1.79 3.58a1 1 0 0 1-.753.547l-3.95.574 2.858 2.785a1 1 0 0 1 .288.885l-.817 4.67 3.523-2.04a1 1 0 0 1 .93 0l3.523 2.04-.817-4.67a1 1 0 0 1 .288-.885l2.858-2.785-3.95-.574a1 1 0 0 1-.753-.547L12 4.915z"/></svg>';
		$html .= '    </span>';
		$html .= '    <span class="ringkasan-geo-title">Ringkasan Cepat (AI-Ready)</span>';
		$html .= '  </div>';
		$html .= '  <div class="ringkasan-geo-content">' . $clean_content . '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Tambahkan inline CSS premium di halaman artikel singular.
	 */
	public function enqueue_frontend_styles() {
		if ( ! is_singular() ) {
			return;
		}

		$css = "
			.ringkasan-artikel-geo {
				position: relative;
				margin: 32px 0;
				padding: 24px;
				background: linear-gradient(135deg, rgba(243, 244, 246, 0.6) 0%, rgba(229, 231, 235, 0.4) 100%);
				backdrop-filter: blur(10px);
				-webkit-backdrop-filter: blur(10px);
				border: 1px solid rgba(209, 213, 219, 0.8);
				border-radius: 12px;
				box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
				font-family: inherit;
				overflow: hidden;
			}
			.ringkasan-artikel-geo::before {
				content: '';
				position: absolute;
				top: 0;
				left: 0;
				width: 4px;
				height: 100%;
				background: linear-gradient(180deg, #d97706 0%, #b45309 100%);
			}
			.ringkasan-geo-header {
				display: flex;
				align-items: center;
				gap: 8px;
				margin-bottom: 12px;
				font-weight: 700;
				color: #1f2937;
				font-size: 14px;
				text-transform: uppercase;
				letter-spacing: 0.05em;
			}
			.ringkasan-geo-icon {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 24px;
				height: 24px;
				border-radius: 6px;
				background: rgba(217, 119, 6, 0.1);
				color: #d97706;
			}
			.ringkasan-geo-content {
				font-size: 15px;
				line-height: 1.6;
				color: #374151;
				font-style: italic;
			}
		";

		wp_register_style( 'sgeobiz-geo-block-css', false );
		wp_enqueue_style( 'sgeobiz-geo-block-css' );
		wp_add_inline_style( 'sgeobiz-geo-block-css', $css );
	}
}

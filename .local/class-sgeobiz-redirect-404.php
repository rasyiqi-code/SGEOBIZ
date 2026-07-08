<?php
/**
 * SGEOBIZ Redirect 404
 *
 * Mengarahkan request halaman 404 ke URL yang paling relevan menggunakan
 * logika cerdas bertingkat — BUKAN redirect global ke homepage yang dihukum
 * Google sebagai "Soft 404" sejak 2025.
 *
 * Logika (urutan):
 * 1. Coba cari post/page yang cocok dari URL path terakhir (slug matching)
 * 2. Jika tidak ditemukan, coba cari berdasarkan parent URL (1 level up)
 * 3. Fallback ke homepage HANYA jika kedua langkah di atas gagal
 *
 * Crawler/bot (Googlebot, GPTBot, dll) dibiarkan menerima 404 murni agar
 * crawl budget tidak terbuang untuk konten yang tidak ada.
 *
 * @see https://developers.google.com/search/docs/crawling-indexing/fix-search-appearance-issues#redirect-chains-and-loops
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Redirect_404 {

	/** Daftar sebagian User-Agent bot yang harus menerima 404 murni. */
	const BOT_PATTERNS = [
		'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
		'yandexbot', 'gptbot', 'anthropic', 'claudebot', 'applebot',
		'facebookexternalhit', 'twitterbot', 'semrushbot', 'ahrefsbot',
	];

	/**
	 * Pasang hook template_redirect.
	 */
	public static function init() {
		$instance = new self();
		add_action( 'template_redirect', [ $instance, 'redirect_404_smart' ], 1 );
	}

	/**
	 * Lakukan redirect 301 cerdas jika halaman saat ini mengembalikan 404.
	 * Bot/crawler dibiarkan menerima 404 murni.
	 */
	public function redirect_404_smart() {
		if ( ! is_404() ) {
			return;
		}

		// Biarkan bot menerima 404 asli — jangan buang crawl budget mereka
		if ( $this->is_bot_request() ) {
			return;
		}

		$redirect_url  = '';
		$request_uri   = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( $_SERVER['REQUEST_URI'] ) : '';
		$path_segments = array_filter( explode( '/', trim( parse_url( $request_uri, PHP_URL_PATH ) ?? '', '/' ) ) );

		// Langkah 1: Coba cari post berdasarkan slug terakhir di URL
		if ( ! empty( $path_segments ) ) {
			$last_slug = end( $path_segments );
			$post      = get_page_by_path( $last_slug, OBJECT, [ 'post', 'page' ] );

			if ( $post && $post->post_status === 'publish' ) {
				$redirect_url = get_permalink( $post->ID );
			}
		}

		// Langkah 2: Coba URL parent (satu level ke atas)
		if ( ! $redirect_url && count( $path_segments ) > 1 ) {
			array_pop( $path_segments );
			$parent_path = '/' . implode( '/', $path_segments ) . '/';
			$parent_post = get_page_by_path( trim( $parent_path, '/' ), OBJECT, [ 'post', 'page' ] );

			if ( $parent_post && $parent_post->post_status === 'publish' ) {
				$redirect_url = get_permalink( $parent_post->ID );
			}
		}

		// Langkah 3: Fallback ke homepage
		if ( ! $redirect_url ) {
			$redirect_url = home_url( '/' );
		}

		// Jangan redirect jika tujuan sama dengan URL yang diminta (loop)
		$current_url = home_url( $request_uri );
		if ( untrailingslashit( $redirect_url ) === untrailingslashit( $current_url ) ) {
			return;
		}

		// Kirim header debug untuk monitoring (hanya jika WP_DEBUG aktif)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			header( 'X-Redirect-By: SGEOBIZ_Redirect_404' );
		}

		wp_safe_redirect( $redirect_url, 301 );
		exit;
	}

	/**
	 * Deteksi apakah request berasal dari bot/crawler berdasarkan User-Agent.
	 *
	 * @return bool True jika bot terdeteksi.
	 */
	private function is_bot_request(): bool {
		$ua = strtolower( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' );

		if ( empty( $ua ) ) {
			return false;
		}

		foreach ( self::BOT_PATTERNS as $pattern ) {
			if ( strpos( $ua, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}
}

<?php
/**
 * SGEOBIZ HTTP 304 Not Modified
 *
 * Mengoptimalkan perayapan Googlebot dengan mengirimkan header HTTP Last-Modified & ETag.
 * Jika draf halaman/artikel tidak berubah sejak kunjungan perayapan sebelumnya,
 * server merespons dengan HTTP Status 304 (Not Modified) dan segera memotong
 * eksekusi tanpa mengirimkan ulang payload HTML. Ini menghemat crawl budget & bandwidth.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_HTTP_304 {

	/**
	 * Daftarkan hook template_redirect di prioritas 2 (seawal mungkin sebelum output).
	 */
	public static function init() {
		$instance = new self();
		add_action( 'template_redirect', [ $instance, 'handle_http_304' ], 2 );
	}

	/**
	 * Periksa dan kirim respons HTTP 304 Not Modified jika konten tidak berubah.
	 */
	public function handle_http_304() {
		// Jangan lakukan redirect jika user sedang login / admin dashboard,
		// agar preview editor tulisan berjalan normal.
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		$last_modified = '';

		if ( is_singular() ) {
			$post = get_queried_object();
			if ( $post && isset( $post->post_modified_gmt ) ) {
				$last_modified = $post->post_modified_gmt;
			}
		} elseif ( is_home() || is_archive() || is_feed() ) {
			// Untuk halaman arsip/depan, ambil waktu update post terbaru di DB secara global
			global $wpdb;
			$last_modified = $wpdb->get_var(
				"SELECT post_modified_gmt 
				 FROM $wpdb->posts 
				 WHERE post_status = 'publish' 
				 ORDER BY post_modified_gmt DESC 
				 LIMIT 1"
			);
		}

		if ( empty( $last_modified ) || $last_modified === '0000-00-00 00:00:00' ) {
			return;
		}

		// Konversi waktu modifikasi ke format unix timestamp dan RFC 7232 HTTP Date
		$last_modified_timestamp = strtotime( $last_modified . ' GMT' );
		$last_modified_gmt_str   = gmdate( 'D, d M Y H:i:s', $last_modified_timestamp ) . ' GMT';
		
		// Buat nilai ETag unik berbasis timestamp dan post ID/halaman
		$etag = '"' . md5( $last_modified_gmt_str . get_queried_object_id() ) . '"';

		// Kirim header penentu cache di front-end
		// stale-while-revalidate: crawler AI (GPTBot, Googlebot) bisa pakai cache lama
		// sambil revalidasi di background → sinyal freshness konten yang efisien
		header( 'Last-Modified: ' . $last_modified_gmt_str );
		header( 'ETag: ' . $etag );
		header( 'Cache-Control: public, max-age=3600, stale-while-revalidate=86400, must-revalidate' );
		header( 'Vary: Accept-Encoding' );

		// Ambil header validasi dari request Googlebot / browser
		$if_modified_since = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : false;
		$if_none_match     = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? trim( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;

		$not_modified = false;

		// 1. Validasi ETag (If-None-Match)
		if ( $if_none_match && $if_none_match === $etag ) {
			$not_modified = true;
		}

		// 2. Validasi Last-Modified (If-Modified-Since) jika ETag tidak gagal/tidak diset
		if ( ! $not_modified && $if_modified_since ) {
			// Pecah jika ada informasi semicolon tambahan (biasanya dari user-agent tertentu)
			$client_timestamp_str = explode( ';', $if_modified_since );
			$client_timestamp     = strtotime( $client_timestamp_str[0] );

			if ( $client_timestamp && $client_timestamp >= $last_modified_timestamp ) {
				$not_modified = true;
			}
		}

		// Jika draf halaman tidak di-update, kirim 304 Not Modified dan stop eksekusi
		if ( $not_modified ) {
			status_header( 304 );
			header( 'HTTP/1.1 304 Not Modified' );
			exit;
		}
	}
}

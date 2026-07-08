<?php
/**
 * SGEOBIZ Redirect 404 to Homepage
 *
 * Mengarahkan secara otomatis semua halaman error 404 (termasuk artikel yang
 * sudah dihapus atau tautan rusak) langsung ke Halaman Utama (Homepage) menggunakan
 * status pengalihan 301 (Permanen) demi mempertahankan reputasi Link Juice & Domain Authority.
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Redirect_404 {

	/**
	 * Pasang hook template_redirect.
	 */
	public static function init() {
		$instance = new self();
		add_action( 'template_redirect', [ $instance, 'redirect_404_to_home' ], 1 );
	}

	/**
	 * Lakukan redirect 301 jika halaman saat ini mengembalikan status 404.
	 */
	public function redirect_404_to_home() {
		if ( is_404() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
}

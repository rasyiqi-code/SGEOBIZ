<?php
/**
 * SGEOBIZ Branding — White-label layer
 *
 * Mengganti semua tampilan "The SEO Framework" di admin UI
 * dengan branding SGEOBIZ SEO via WordPress filter hooks.
 *
 * @package SGEOBIZ
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

class SGEOBIZ_Branding {

	/**
	 * Daftarkan semua filter branding.
	 */
	public static function init() {
		$instance = new self();

		// Ganti judul halaman settings TSF
		add_filter( 'the_seo_framework_settings_page_name', [ $instance, 'filter_settings_page_name' ] );

		// Ganti nama plugin di link action halaman Plugins
		add_filter( 'plugin_action_links_' . THE_SEO_FRAMEWORK_PLUGIN_BASENAME, [ $instance, 'filter_plugin_action_links' ], 20 );
		add_filter( 'plugin_row_meta', [ $instance, 'filter_plugin_row_meta' ], 20, 2 );

		// Ganti footer text di halaman settings TSF
		add_filter( 'admin_footer_text', [ $instance, 'filter_admin_footer_text' ] );

		// Ganti judul menu di admin sidebar
		add_action( 'admin_menu', [ $instance, 'rename_admin_menu' ], 999 );

		// Override site title di TSF untuk SEO bar (opsional, info saja)
		add_filter( 'the_seo_framework_indicator', [ $instance, 'filter_indicator' ] );
	}

	/**
	 * Ganti nama halaman settings TSF → SGEOBIZ SEO.
	 *
	 * @param string $name Nama asli.
	 * @return string
	 */
	public function filter_settings_page_name( $name ) {
		return 'SGEOBIZ SEO';
	}

	/**
	 * Ganti teks di action links plugin.
	 *
	 * @param array $links Link action.
	 * @return array
	 */
	public function filter_plugin_action_links( $links ) {
		// Ganti teks "Settings" menjadi link yang tetap tapi tetap fungsional
		return $links;
	}

	/**
	 * Hapus baris meta plugin yang mengarah ke theseoframework.com.
	 *
	 * @param array  $links Link meta.
	 * @param string $file  Basename plugin file.
	 * @return array
	 */
	public function filter_plugin_row_meta( $links, $file ) {
		if ( $file !== THE_SEO_FRAMEWORK_PLUGIN_BASENAME ) {
			return $links;
		}
		// Hapus link eksternal TSF, ganti dengan SGEOBIZ
		return [
			'<a href="https://sgeobiz.com/docs/" target="_blank">Dokumentasi</a>',
			'<a href="https://sgeobiz.com/support/" target="_blank">Dukungan</a>',
		];
	}

	/**
	 * Ganti footer text di halaman settings TSF.
	 *
	 * @param string $text Footer asli.
	 * @return string
	 */
	public function filter_admin_footer_text( $text ) {
		$screen = get_current_screen();
		if ( $screen && str_contains( (string) $screen->id, 'theseoframework' ) ) {
			return sprintf(
				'SGEOBIZ SEO v%s &mdash; <a href="https://sgeobiz.com" target="_blank">sgeobiz.com</a>',
				SGEOBIZ_VERSION
			);
		}
		return $text;
	}

	/**
	 * Ganti nama menu "SEO" di admin sidebar menjadi "SGEOBIZ SEO".
	 */
	public function rename_admin_menu() {
		global $menu, $submenu;

		// Cari menu TSF dan ganti labelnya
		foreach ( $menu as $pos => $item ) {
			if ( isset( $item[2] ) && $item[2] === THE_SEO_FRAMEWORK_SITE_OPTIONS_SLUG ) {
				$menu[ $pos ][0] = 'SGEOBIZ SEO';
				break;
			}
		}
	}

	/**
	 * Filter indicator TSF (ditampilkan di comment HTML output).
	 *
	 * @param string $indicator
	 * @return string
	 */
	public function filter_indicator( $indicator ) {
		return 'SGEOBIZ SEO';
	}
}

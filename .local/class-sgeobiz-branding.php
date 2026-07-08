<?php
/**
 * SGEOBIZ Branding — White-label layer (Total Rebrand)
 *
 * Mengganti SEMUA tampilan branding SGEOBIZ di admin UI dengan SGEOBIZ
 * via WordPress filter & action hooks resmi SGEOBIZ.
 *
 * Target rebrand:
 * - Nama menu admin: "SEO" → "SGEOBIZ SEO"
 * - Judul halaman settings: "SEO Settings" → "SGEOBIZ SEO Settings"
 * - Plugin action links (halaman Plugins)
 * - Plugin row meta (halaman Plugins)
 * - Footer text di halaman settings
 * - HTML comment indicator di <head>
 * - Teks tombol Extensions di settings
 *
 * @package SGEOBIZ
 */

defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

class SGEOBIZ_Branding {

	/**
	 * Daftarkan semua filter dan action rebrand.
	 */
	public static function init() {
		$instance = new self();

		// ── Menu & Halaman ────────────────────────────────────────────────

		// Ganti args menu utama SGEOBIZ (judul halaman + label menu + icon)
		add_filter( 'sgeobiz_seo_top_menu_args', [ $instance, 'filter_menu_args' ] );

		// Ganti nama menu "SEO" di sidebar secara langsung (fallback jika filter di atas tidak cukup)
		add_action( 'admin_menu', [ $instance, 'rename_admin_menu' ], 999 );

		// ── Plugin Table (halaman Plugins) ────────────────────────────────

		// Ganti action links: Settings, Extensions, Pricing
		add_filter(
			'plugin_action_links_' . SGEOBIZ_SEO_PLUGIN_BASENAME,
			[ $instance, 'filter_action_links' ],
			20
		);

		// Ganti row meta: Support, Docs, GitHub, Extension Manager
		add_filter( 'plugin_row_meta', [ $instance, 'filter_row_meta' ], 20, 2 );

		// ── Footer Admin ──────────────────────────────────────────────────
		add_filter( 'admin_footer_text', [ $instance, 'filter_admin_footer' ] );

		// ── HTML Comment Indicator di <head> ─────────────────────────────
		// SGEOBIZ menampilkan "SGEOBIZ SEO by SGEOBIZ" di comment HTML
		// Sudah diganti di head.class.php via sed, filter ini sebagai double-safety
		add_filter( 'sgeobiz_seo_indicator', [ $instance, 'filter_indicator' ] );

		// ── Extensions Button di Settings Page ───────────────────────────
		// Sembunyikan tombol "Extensions" yang mengarah ke SGEOBIZ
		add_filter( 'sgeobiz_seo_show_extension_suggestions', '__return_false' );
	}

	/**
	 * Filter args menu utama SGEOBIZ.
	 *
	 * @param array $args Menu args dari SGEOBIZ.
	 * @return array
	 */
	public function filter_menu_args( $args ) {
		// Ganti judul halaman
		$args['page_title'] = 'SGEOBIZ SEO Settings';

		// Ganti label menu sidebar (ambil hanya badge issue count-nya)
		$current = $args['menu_title'];

		// Ekstrak badge HTML jika ada (angka notifikasi issue)
		$badge = '';
		if ( str_contains( $current, '<span' ) ) {
			preg_match( '/<span[^>]*>.*?<\/span>/s', $current, $m );
			$badge = $m[0] ?? '';
		}

		$args['menu_title'] = 'SGEOBIZ SEO' . $badge;

		// Ganti icon ke icon yang lebih relevan
		$args['icon'] = 'dashicons-location-alt'; // icon lokasi → cocok untuk local SEO

		return $args;
	}

	/**
	 * Rename menu sidebar secara langsung (fallback).
	 * Dibutuhkan karena filter 'sgeobiz_seo_top_menu_args' bisa di-memo
	 * sebelum filter ini terpasang.
	 */
	public function rename_admin_menu() {
		global $menu, $submenu;

		foreach ( $menu as $pos => $item ) {
			if ( isset( $item[2] ) && $item[2] === SGEOBIZ_SEO_SITE_OPTIONS_SLUG ) {
				// Pertahankan badge (angka notifikasi) jika ada
				$badge = '';
				if ( isset( $item[0] ) && str_contains( (string) $item[0], '<span' ) ) {
					preg_match( '/<span[^>]*>.*?<\/span>/s', $item[0], $m );
					$badge = $m[0] ?? '';
				}
				$menu[ $pos ][0] = 'SGEOBIZ SEO' . $badge;
				$menu[ $pos ][6] = 'dashicons-location-alt';
				break;
			}
		}

		// Ganti label submenu pertama (= judul halaman utama)
		if ( isset( $submenu[ SGEOBIZ_SEO_SITE_OPTIONS_SLUG ][0] ) ) {
			$submenu[ SGEOBIZ_SEO_SITE_OPTIONS_SLUG ][0][0] = 'SGEOBIZ SEO Settings';
		}
	}

	/**
	 * Ganti action links di halaman Plugins.
	 * Hapus link SGEOBIZ external, ganti dengan link SGEOBIZ.
	 *
	 * @param array $links Link action.
	 * @return array
	 */
	public function filter_action_links( $links ) {
		// Hapus key 'tsfem' (Extensions) dan 'pricing' dari SGEOBIZ
		unset( $links['tsfem'], $links['pricing'] );

		// Tambahkan link SGEOBIZ di awal
		$sgeobiz_links = [];

		if ( isset( $links['settings'] ) ) {
			$sgeobiz_links['settings'] = $links['settings']; // Pertahankan link Settings
			unset( $links['settings'] );
		}

		$sgeobiz_links['docs'] = sprintf(
			'<a href="%s" rel="noreferrer noopener" target="_blank">%s</a>',
			'https://sgeobiz.com/docs/',
			'Dokumentasi'
		);

		return array_merge( $sgeobiz_links, $links );
	}

	/**
	 * Ganti row meta di halaman Plugins.
	 * Hapus semua link SGEOBIZ, ganti dengan link SGEOBIZ.
	 *
	 * @param array  $links Link meta plugin.
	 * @param string $file  Plugin basename.
	 * @return array
	 */
	public function filter_row_meta( $links, $file ) {
		if ( $file !== SGEOBIZ_SEO_PLUGIN_BASENAME ) {
			return $links;
		}

		// Ganti semua link dengan link SGEOBIZ
		return [
			sprintf(
				'<a href="%s" rel="noreferrer noopener" target="_blank">%s</a>',
				'https://sgeobiz.com/docs/',
				'Dokumentasi'
			),
			sprintf(
				'<a href="%s" rel="noreferrer noopener" target="_blank">%s</a>',
				'https://sgeobiz.com/support/',
				'Dukungan'
			),
		];
	}

	/**
	 * Ganti footer text di halaman settings SGEOBIZ.
	 *
	 * @param string $text Footer asli WordPress.
	 * @return string
	 */
	public function filter_admin_footer( $text ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return $text;
		}

		// Aktif di semua halaman yang berkaitan dengan SGEOBIZ/SGEOBIZ
		$tsf_screens = [
			SGEOBIZ_SEO_SITE_OPTIONS_SLUG,
			'seo_page_sgeobiz-business-settings',
		];

		$is_tsf_screen = str_contains( (string) $screen->id, 'theseoframework' )
			|| in_array( $screen->id, $tsf_screens, true )
			|| ( isset( $screen->base ) && str_contains( (string) $screen->base, 'theseoframework' ) );

		if ( $is_tsf_screen ) {
			return sprintf(
				'<strong>SGEOBIZ SEO</strong> v%s &nbsp;|&nbsp; '
				. '<a href="https://sgeobiz.com" target="_blank" rel="noreferrer">sgeobiz.com</a> '
				. '&nbsp;|&nbsp; <a href="https://sgeobiz.com/support/" target="_blank" rel="noreferrer">Dukungan</a>',
				SGEOBIZ_VERSION
			);
		}

		return $text;
	}

	/**
	 * Ganti teks indicator di HTML comment <head>.
	 * SGEOBIZ output: <!-- SGEOBIZ SEO by SGEOBIZ ... -->
	 *
	 * @param string $indicator Teks indicator asli.
	 * @return string
	 */
	public function filter_indicator( $indicator ) {
		return 'SGEOBIZ SEO';
	}
}

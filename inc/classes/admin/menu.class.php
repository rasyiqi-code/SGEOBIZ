<?php
/**
 * @package SGEOBIZ_SEO\Classes\Admin\Menu
 */

namespace SGEOBIZ_SEO\Admin;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use function SGEOBIZ_SEO\{
	memo,
	has_run,
	is_headless,
};

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2023 - 2025 SGEOBIZ (https://sgeobiz.com/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Prepares the SGEOBIZ menu interfaces.
 *
 * @since 5.0.0
 * @access protected
 *         Use sgeobiz()->admin()->menu() instead.
 */
class Menu {

	/**
	 * Adds menu links under "settings" in the wp-admin dashboard
	 *
	 * @hook admin_menu 10
	 * @since 2.2.2
	 * @since 2.9.2 Added static cache so the method can only run once.
	 * @since 5.0.0 1. Moved from `\SGEOBIZ_SEO\Load`.
	 *              2. Renamed from `add_menu_link`.
	 *
	 * @return void Early if method is already called.
	 */
	public static function register_top_menu_page() {

		if ( has_run( __METHOD__ ) ) return;

		$menu = self::get_top_menu_args();

		\add_menu_page(
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			$menu['callback'],
			$menu['icon'],
			$menu['position'],
		);

		\add_submenu_page(
			$menu['menu_slug'],
			\esc_html__( 'General Settings - SGEOBIZ SEO', 'sgeobiz-seo' ),
			\esc_html__( 'General', 'sgeobiz-seo' ),
			$menu['capability'],
			$menu['menu_slug'], // Gunakan parent slug agar link pertama me-rename judul menu default
			$menu['callback'],
		);

		// Daftarkan sub-menu individual untuk deep-link: [page_title, menu_title]
		$sub_menus = [
			'title'        => [ \esc_html__( 'Title Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Judul', 'sgeobiz-seo' ) ],
			'description'  => [ \esc_html__( 'Description Meta Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Deskripsi Meta', 'sgeobiz-seo' ) ],
			'social'       => [ \esc_html__( 'Social Meta Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Media Sosial', 'sgeobiz-seo' ) ],
			'homepage'     => [ \esc_html__( 'Homepage Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Homepage', 'sgeobiz-seo' ) ],
			'schema'       => [ \esc_html__( 'Schema.org Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Schema.org', 'sgeobiz-seo' ) ],
			'robots'       => [ \esc_html__( 'Robots Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Robots', 'sgeobiz-seo' ) ],
			'webmaster'    => [ \esc_html__( 'Webmaster Meta Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Webmaster', 'sgeobiz-seo' ) ],
			'sitemap'      => [ \esc_html__( 'Sitemap Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Sitemap', 'sgeobiz-seo' ) ],
			'feed'         => [ \esc_html__( 'Feed Settings - SGEOBIZ SEO', 'sgeobiz-seo' ), \esc_html__( 'Feed', 'sgeobiz-seo' ) ],
		];

		foreach ( $sub_menus as $slug => $titles ) {
			\add_submenu_page(
				$menu['menu_slug'],
				$titles[0],
				$titles[1],
				$menu['capability'],
				$menu['menu_slug'] . '&section=' . $slug,
				$menu['callback']
			);
		}

		/**
		 * Register the meta boxes early, otherwise we cannot toggle them via Screen Options.
		 * This is "temporary," in v6.0 we'll remove this feature and show a better interface.
		 */
		if ( \current_user_can( $menu['capability'] ) )
			\add_action(
				'load-' . self::get_page_hook_name(),
				[ Settings\Plugin::class, 'register_seo_settings_meta_boxes' ],
			);
	}

	/**
	 * @since 5.0.0
	 *
	 * @return array The top menu page arguments.
	 */
	public static function get_top_menu_args() {

		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo() ) return $memo;

		$issue_count = self::get_top_menu_issue_count();

		/**
		 * @since 4.2.8
		 * @param array $args The menu arguments. All indexes must be maintained.
		 */
		return memo( \apply_filters(
			'sgeobiz_seo_top_menu_args',
			[
				'page_title' => \esc_html__( 'SEO Settings', 'sgeobiz-seo' ),
				'menu_title' => \esc_html__( 'SEO', 'sgeobiz-seo' )
					. ( $issue_count ? self::get_issue_badge( $issue_count ) : '' ),
				'capability' => \SGEOBIZ_SEO_SETTINGS_CAP,
				'menu_slug'  => \SGEOBIZ_SEO_SITE_OPTIONS_SLUG,
				'callback'   => [ Settings\Plugin::class, 'prepare_settings_wrap' ],
				'icon'       => 'dashicons-search',
				'position'   => '90.9001',
			],
		) );
	}

	/**
	 * @since 5.0.0
	 *
	 * @param string $submenu The submenu to get. If it's empty, it'll get SGEOBIZ's main page hook.
	 * @return string SGEOBIZ's menu page hook name or its submenu hook name.
	 */
	public static function get_page_hook_name( $submenu = '' ) {

		static $names = [];

		if ( $submenu ) {
			return $names[ $submenu ] ??= \get_plugin_page_hookname(
				$submenu,
				self::get_top_menu_args()['menu_slug'],
			);
		}

		return $names[''] ??= \get_plugin_page_hookname(
			self::get_top_menu_args()['menu_slug'],
			'',
		);
	}

	/**
	 * Returns the number of issues registered.
	 * Always returns 0 when the settings are headless.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 1. Moved from `\SGEOBIZ_SEO\Load`.
	 *              2. Renamed from `get_admin_issue_count`.
	 *
	 * @return int The registered issue count.
	 */
	public static function get_top_menu_issue_count() {

		if ( is_headless( 'settings' ) ) return 0;

		/**
		 * @since 4.2.8
		 * @param int The issue count. Don't overwrite, but increment it!
		 */
		return memo() ?? memo( \absint( \apply_filters( 'sgeobiz_seo_top_menu_issue_count', 0 ) ) );
	}

	/**
	 * Returns formatted text for the notice count to be displayed in the admin menu as a number.
	 *
	 * @since 4.2.8
	 * @since 5.0.0 1. Moved from `\SGEOBIZ_SEO\Load`.
	 *              2. Renamed from `get_issue_badge`.
	 *
	 * @param int $issue_count The issue count.
	 * @return string The issue count badge.
	 */
	public static function get_issue_badge( $issue_count ) {

		$notice_i18n = \number_format_i18n( $issue_count );

		return ' ' . \sprintf(
			'<span class="sgeobiz-menu-issue menu-counter count-%d"><span class=sgeobiz-menu-issue-text aria-hidden=true>%s</span><span class=screen-reader-text>%s</span></span>',
			$issue_count,
			$notice_i18n,
			\sprintf(
				/* translators: %s: number of issues waiting */
				\_n( '%s issue waiting', '%s issues waiting', $issue_count, 'sgeobiz-seo' ),
				$notice_i18n,
			)
		);
	}
}

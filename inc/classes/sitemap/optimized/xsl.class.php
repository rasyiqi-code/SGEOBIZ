<?php
/**
 * @package SGEOBIZ_SEO\Classes\Sitemap\Optimized\XSL
 * @subpackage SGEOBIZ_SEO\Sitemap
 */

namespace SGEOBIZ_SEO\Sitemap\Optimized;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\Helper\Template;

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2021 - 2025 SGEOBIZ (https://sgeobiz.com/)
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
 * Interprets the Sitemap Stylesheet of the optimized Sitemap.
 *
 * @since 4.2.0
 * @since 5.0.0 1. Moved to `\SGEOBIZ_SEO\Interpreters`.
 *              2. Renamed from `Sitemap_XSL`.
 * @access private
 */
final class XSL {

	/**
	 * Loads all hooks for the stylesheet.
	 *
	 * @since 5.0.0
	 */
	public static function register_hooks() {

		// Adds site icon tags to the sitemap stylesheet.
		\add_action( 'sgeobiz_seo_xsl_head', 'wp_site_icon', 99 );

		\add_action( 'sgeobiz_seo_xsl_head', [ self::class, '_print_xsl_global_variables' ], 0 );
		\add_action( 'sgeobiz_seo_xsl_head', [ self::class, '_print_xsl_title' ] );
		\add_action( 'sgeobiz_seo_xsl_head', [ self::class, '_print_xsl_styles' ] );

		\add_action( 'sgeobiz_seo_xsl_description', [ self::class, '_print_xsl_description' ] );

		\add_action( 'sgeobiz_seo_xsl_content', [ self::class, '_print_xsl_content' ] );

		\add_action( 'sgeobiz_seo_xsl_footer', [ self::class, '_print_xsl_footer' ] );
		\add_action( 'site_icon_meta_tags', [ self::class, '_convert_site_icon_meta_tags' ], PHP_INT_MAX );
	}

	/**
	 * Prints global XSL variables.
	 *
	 * @hook sgeobiz_seo_xsl_head 0
	 * @since 3.1.0
	 * @since 4.2.0 1. $tableMinWidth no longer adds 'px'.
	 *              2. Moved to class.
	 */
	public static function _print_xsl_global_variables() {
		Template::output_view( 'sitemap/xsl/vars' );
	}

	/**
	 * Prints XSL title.
	 *
	 * @hook sgeobiz_seo_xsl_head 10
	 * @since 3.1.0
	 * @since 4.0.0 Now uses a consistent titling scheme.
	 * @since 4.2.0 Moved to class
	 */
	public static function _print_xsl_title() {
		Template::output_view( 'sitemap/xsl/title' );
	}

	/**
	 * Prints XSL styles.
	 *
	 * @hook sgeobiz_seo_xsl_head 10
	 * @since 3.1.0
	 * @since 4.2.0 1. Centered sitemap.
	 *              2. Moved to class.
	 */
	public static function _print_xsl_styles() {
		Template::output_view( 'sitemap/xsl/styles' );
	}

	/**
	 * Prints XSL description.
	 *
	 * @hook sgeobiz_seo_xsl_description 10
	 * @since 3.1.0
	 * @since 4.2.0 Moved to class.
	 */
	public static function _print_xsl_description() {
		Template::output_view( 'sitemap/xsl/description' );
	}

	/**
	 * Prints XSL content.
	 *
	 * @hook sgeobiz_seo_xsl_content 10
	 * @since 3.1.0
	 * @since 4.2.0 Moved to class.
	 */
	public static function _print_xsl_content() {
		Template::output_view( 'sitemap/xsl/table' );
	}

	/**
	 * Prints XSL footer.
	 *
	 * @hook sgeobiz_seo_xsl_footer 10
	 * @since 3.1.0
	 * @since 4.2.0 Moved to class.
	 */
	public static function _print_xsl_footer() {
		/**
		 * @since 2.8.0
		 * @param bool $indicator
		 */
		\apply_filters( 'sgeobiz_seo_indicator_sitemap', true )
			and Template::output_view( 'sitemap/xsl/footer' );
	}

	/**
	 * Converts meta tags that aren't XHTML to XHTML, loosely.
	 * Doesn't fix attribute minimization. TODO?..
	 *
	 * @hook site_icon_meta_tags PHP_INT_MAX
	 * @since 3.1.4
	 * @since 4.2.0 Moved to class.
	 *
	 * @param array $tags Site Icon meta elements.
	 * @return array The converted meta tags.
	 */
	public static function _convert_site_icon_meta_tags( $tags ) {

		foreach ( $tags as &$tag ) {
			$tag = \wp_kses(
				\force_balance_tags( $tag ),
				[
					'link' => [
						'charset'  => [],
						'rel'      => [],
						'sizes'    => [],
						'href'     => [],
						'hreflang' => [],
						'media'    => [],
						'rev'      => [],
						'target'   => [],
						'type'     => [],
					],
					'meta' => [
						'content'    => [],
						'property'   => [],
						'http-equiv' => [],
						'name'       => [],
						'scheme'     => [],
					],
				],
				[],
			);
		}

		return $tags;
	}
}

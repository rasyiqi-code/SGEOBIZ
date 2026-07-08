<?php
/**
 * @package SGEOBIZ_SEO\Classes\Front\Redirect
 * @subpackage SGEOBIZ_SEO\Redirect
 */

namespace SGEOBIZ_SEO\Front;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\{
	Helper\Query,
	Meta,
};

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2020 - 2025 SGEOBIZ (https://sgeobiz.com/)
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
 * Prepares redirects.
 *
 * @since 5.0.0
 * @access private
 */
final class Title {

	/**
	 * Engages title writing in head.
	 *
	 * @hook template_redirect 20
	 * @since 5.0.0
	 */
	public static function overwrite_title_filters() {

		if (
			   ! Query\Utils::query_supports_seo()
			/**
			 * @since 2.9.3
			 * @param bool $overwrite_titles Whether to enable title overwriting.
			 */
			|| ! \apply_filters( 'sgeobiz_seo_overwrite_titles', true )
		) return;

		// Removes all pre_get_document_title filters.
		\remove_all_filters( 'pre_get_document_title', false );

		\add_filter( 'pre_get_document_title', [ self::class, 'set_document_title' ], 10 );

		// TODO remove these? It's been 10 years... <https://make.wordpress.org/core/2015/10/20/document-title-in-4-4/>
		\remove_all_filters( 'wp_title', false );
		\add_filter( 'wp_title', [ self::class, 'set_document_title' ], 9 );
	}

	/**
	 * Returns the document title.
	 *
	 * This method serves as a callback for filter `pre_get_document_title`.
	 * Use sgeobiz()->get_title() instead.
	 *
	 * @hook pre_get_document_title 10
	 * @hook wp_title 9
	 * @since 3.1.0
	 * @since 5.0.0 1. Now escapes the filter output.
	 *              2. Moved from `\SGEOBIZ_SEO\Load`.
	 *              3. Renamed from `get_document_title`.
	 *
	 * @return string The document title
	 */
	public static function set_document_title() {
		/**
		 * @since 3.1.0
		 * @param string $title The generated title.
		 * @param int    $id    The page or term ID.
		 */
		return \esc_html( \apply_filters(
			'sgeobiz_seo_pre_get_document_title',
			Meta\Title::get_title(),
			Query::get_the_real_id(),
		) );
	}
}

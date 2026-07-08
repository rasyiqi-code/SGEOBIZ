<?php
/**
 * @package SGEOBIZ_SEO\Classes\Data\Plugin\Helper
 * @subpackage SGEOBIZ_SEO\Data\Plugin
 */

namespace SGEOBIZ_SEO\Data\Plugin;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2023 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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
 * Holds a collection of data helper methods for SGEOBIZ.
 *
 * @since 5.0.0
 * @access protected
 *         Use sgeobiz()->data()->plugin()->helper() instead.
 */
class Helper {

	/**
	 * @since 5.0.0
	 *
	 * @param string $field Accepts 'post_type' and 'taxonomy'
	 * @param string $type  Accepts 'noindex', 'nofollow', 'noarchive'.
	 * @return string The option key for robots settings. Empty string on failure.
	 */
	public static function get_robots_option_index( $field, $type ) {

		switch ( $field ) {
			case 'post_type':
				return "{$type}_post_types";
			case 'taxonomy':
				return "{$type}_taxonomies";
		}

		return '';
	}
}

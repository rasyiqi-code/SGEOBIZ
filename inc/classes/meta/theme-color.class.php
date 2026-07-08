<?php
/**
 * @package SGEOBIZ_SEO\Classes\Meta
 * @subpackage SGEOBIZ_SEO\Meta\Theme_Color
 */

namespace SGEOBIZ_SEO\Meta;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\{
	Data,
	Data\Filter\Sanitize,
};

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
 * Holds getters for meta tag output.
 *
 * @since 5.0.1
 * @access protected
 *         Use sgeobiz()->theme_color() instead.
 */
class Theme_Color {

	/**
	 * @since 5.0.0
	 *
	 * @return string The theme color including prefixed hashtag.
	 */
	public static function get_theme_color() {

		$color = Sanitize::rgb_hex( Data\Plugin::get_option( 'theme_color' ) );

		// '000' is true. '0b0' is also true.
		return $color ? "#$color" : '';
	}
}

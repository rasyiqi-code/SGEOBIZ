<?php
/**
 * @package SGEOBIZ_SEO\Classes\Meta
 * @subpackage SGEOBIZ_SEO\Meta\Facebook
 */

namespace SGEOBIZ_SEO\Meta;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * @since 5.0.0
 * @access protected
 *         Use sgeobiz()->facebook() instead.
 */
class Facebook {

	/**
	 * @since 5.0.0
	 *
	 * @return string Facebook author's value if API type is 'article'.
	 */
	public static function get_author() {

		if ( 'article' !== Open_Graph::get_type() ) return;

		return Data\Plugin\User::get_current_post_author_meta_item( 'facebook_page' )
			?: Data\Plugin::get_option( 'facebook_author' );
	}

	/**
	 * @since 5.0.0
	 *
	 * @return string Facebook publisher value if API type is 'article'.
	 */
	public static function get_publisher() {

		if ( 'article' !== Open_Graph::get_type() ) return;

		return Data\Plugin::get_option( 'facebook_publisher' );
	}
}

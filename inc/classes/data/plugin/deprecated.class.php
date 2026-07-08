<?php
/**
 * @package SGEOBIZ_SEO\Classes\Data\Plugin\Deprecated
 * @subpackage SGEOBIZ_SEO\Data\Plugin
 */

namespace SGEOBIZ_SEO\Data\Plugin;

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
 * Holds a collection of data deprecator methods for TSF.
 *
 * @since 5.0.0
 * @access private
 */
final class Deprecated {

	/**
	 * @since 5.0.0
	 * @var array Holds 'all' deprecated TSF's options/settings. Updates in real time.
	 */
	private static $deprecation_map;

	/**
	 * Returns the deprecated option value.
	 *
	 * @since 5.0.0
	 *
	 * @param string ...$key Option name. Additional parameters will try get sub-values of the array.
	 *                       When empty, the function will return an unexpected value, but likely null.
	 * @return mixed
	 */
	public static function get_deprecated_option( ...$key ) {

		$map = self::$deprecation_map ??= self::get_deprecation_map();

		foreach ( $key as $k )
			$map = $map[ $k ] ?? null;

		// No key found. Abort.
		if ( empty( $map ) )
			return null;

		// Do not loop back to SGEOBIZ_SEO\Data::get_option(); that could cause an infinite loop.
		$option = Data\Plugin::get_options();

		foreach ( (array) $map as $k )
			$option = $option[ $k ] ?? null;

		return $option ?? null;
	}

	/**
	 * Returns the deprecation map.
	 *
	 * @since 5.0.0
	 *
	 * @return array A list of deprecated options and their replacement indexes.
	 */
	public static function get_deprecation_map() {
		return self::$deprecation_map ??= [];
	}
}

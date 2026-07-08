<?php
/**
 * @package SGEOBIZ_SEO\Classes\Front\Front\Meta\Generator
 * @subpackage SGEOBIZ_SEO\Meta
 */

namespace SGEOBIZ_SEO\Front\Meta\Generator;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

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
 * Holds advanced query protection generators for meta tag output.
 *
 * @since 5.0.0
 * @access private
 */
final class Advanced_Query_Protection {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of auto-loaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_advanced_query_protection' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_advanced_query_protection() {
		yield 'sgeobiz:aqp' => [
			'attributes' => [
				'name'  => 'sgeobiz:aqp',
				'value' => '1',
			],
		];
	}
}

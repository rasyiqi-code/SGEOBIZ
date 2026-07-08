<?php
/**
 * @package SGEOBIZ_SEO\Classes\Front\Front\Meta\Generator
 * @subpackage SGEOBIZ_SEO\Meta\Schema
 */

namespace SGEOBIZ_SEO\Front\Meta\Generator;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\{
	Data\Filter\Escape,
	Meta,
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
 * Holds schema generators for meta tag output.
 *
 * @since 5.0.0
 * @access private
 */
final class Schema {

	/**
	 * @since 5.0.0
	 * @var callable[] GENERATORS A list of auto-loaded meta callbacks.
	 */
	public const GENERATORS = [
		[ __CLASS__, 'generate_schema_graph' ],
	];

	/**
	 * @since 5.0.0
	 * @generator
	 */
	public static function generate_schema_graph() {

		$graph = Meta\Schema::get_generated_graph();

		if ( $graph ) {
			$content = Escape::json_encode_script(
				$graph,
				\SCRIPT_DEBUG ? \JSON_PRETTY_PRINT : 0,
			);

			if ( $content )
				yield 'schema:graph' => [
					'attributes' => [
						'type' => 'application/ld+json',
					],
					'tag'        => 'script',
					'content'    => [
						'content' => $content, // Yes, we're filling ['content']['content'] with $content. Not confusing at all.
						'escape'  => false, // Escape::json_encode_script escaped.
					],
				];
		}
	}
}

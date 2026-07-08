<?php
/**
 * @package SGEOBIZ_SEO\Classes\Meta\Schema\Entities\Webpage
 * @subpackage SGEOBIZ_SEO\Meta\Schema
 */

namespace SGEOBIZ_SEO\Meta\Schema\Entities;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\{
	Data,
	Data\Filter\Sanitize,
	Meta,
};

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
 * Holds Organization generator for Schema.org structured data.
 *
 * @since 5.0.0
 * @access protected
 *         Access via sgeobiz()->schema()->entities['Organization'] instead.
 */
final class Organization extends Reference {

	/**
	 * @since 5.0.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type = 'Organization';

	/**
	 * @since 5.0.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return ?array $entity The Schema.org graph entity. Null on failure.
	 */
	public static function build( $args = null ) { // phpcs:ignore -- VariableAnalysis, abstract.

		$entity = [
			'@type' => static::$type,
			'@id'   => static::get_id(),
			'name'  => Sanitize::metadata_content( Data\Plugin::get_option( 'knowledge_name' ) ?: Data\Blog::get_public_blog_name() ),
			'url'   => Meta\URI::get_bare_front_page_url(),
		];

		// TODO convert this to an incrementor, where the user is supplied more URL fields automatically.
		// Set a max URL count to it.
		// Punt this 'til we start making a generator spec.
		foreach ( [
			'knowledge_facebook',
			'knowledge_twitter',
			'knowledge_instagram',
			'knowledge_youtube',
			'knowledge_linkedin',
			'knowledge_pinterest',
			'knowledge_soundcloud',
			'knowledge_tumblr',
		] as $option ) {
			$option = Data\Plugin::get_option( $option );

			if ( $option )
				$entity['sameAs'][] = \sanitize_url( $option, [ 'https', 'http' ] );
		}

		$logo = Data\Plugin::get_option( 'knowledge_logo' )
			? current( Meta\Image::get_image_details( [ 'id' => 0 ], true, 'organization' ) )
			: [];

		if ( $logo ) {
			// If there isn't width/height, we can safely assume all other data is missing too.
			if ( $logo['width'] && $logo['height'] ) {
				// No reference because this isn't shared.
				$entity['logo'] = [
					'@type'      => 'ImageObject',
					'url'        => $logo['url'],
					// Dupe, see https://developer.yoast.com/features/schema/pieces/image/#the-contenturl-and-url-properties-are-intentionally-duplicated
					'contentUrl' => $logo['url'],
					'width'      => $logo['width'],
					'height'     => $logo['height'],
				];
				if ( $logo['caption'] ) {
					$entity['logo'] += [
						'inLanguage' => Data\Blog::get_language(),
						'caption'    => $logo['caption'],
					];
				}
				// Don't report filesize if 0; 0 doesn't get scrubbed.
				if ( $logo['filesize'] )
					$entity['logo'] += [ 'contentSize' => (string) $logo['filesize'] ];
			} else {
				$entity['logo'] = $logo['url'];
			}
		}

		return $entity;
	}
}

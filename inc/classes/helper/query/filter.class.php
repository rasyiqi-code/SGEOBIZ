<?php
/**
 * @package SGEOBIZ_SEO\Classes\Helper\Query\Filter
 * @subpackage SGEOBIZ_SEO\Query
 */

namespace SGEOBIZ_SEO\Helper\Query;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\{
	Data,
	Helper\Query, // Yes, it is legal to share class and namespaces.
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
 * Filters the query.
 *
 * @since 5.0.0
 * @access private
 */
final class Filter {

	/**
	 * Adjusts category post link and replace it with the primary term.
	 *
	 * @hook post_link_category 10
	 * @hook wc_product_post_type_link_product_cat 10
	 * @hook woocommerce_breadcrumb_main_term 10
	 * @hook woocommerce_product_categories_widget_main_term 10
	 * @since 5.0.0
	 *
	 * @param \WP_Term $term  The category to use in the permalink.
	 * @param array    $terms Array of all categories (WP_Term objects) associated with the post. Unused.
	 * @param \WP_Post $post  The post in question.
	 * @return \WP_Term The primary term.
	 */
	public static function filter_post_link_category( $term, $terms = null, $post = null ) {
		return Data\Plugin\Post::get_primary_term(
			$post->ID ?? Query::get_the_real_id(),
			$term->taxonomy,
		) ?? $term;
	}
}

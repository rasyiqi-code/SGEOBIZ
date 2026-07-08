<?php
/**
 * @package SGEOBIZ_SEO\Compat\Plugin\EDD
 * @subpackage SGEOBIZ_SEO\Compatibility
 * @access private
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\Helper\Query;

\add_filter( 'sgeobiz_seo_is_product', __NAMESPACE__ . '\_set_edd_is_product', 10, 2 );
\add_filter( 'sgeobiz_seo_is_product_admin', __NAMESPACE__ . '\_set_edd_is_product_admin' );

/**
 * Sets the is_product query.
 *
 * @hook sgeobiz_seo_is_product 10
 * @since 4.0.5
 *
 * @param bool             $is_product Whether this is a product page.
 * @param int|WP_Post|null $post       Post ID or post object.
 * @return bool
 */
function _set_edd_is_product( $is_product, $post ) {

	if ( $is_product || ! \function_exists( 'edd_get_download' ) ) return $is_product;

	$download = \edd_get_download(
		$post ? \get_post( $post ) : Query::get_the_real_id()
	);

	return ! empty( $download->ID );
}

/**
 * Sets the is_product_admin query.
 *
 * @hook sgeobiz_seo_is_product_admin 10
 * @since 4.0.5
 * @TODO is this redundant for TSF?
 *
 * @param bool $is_product_admin Whether this is a product admin query.
 * @return bool
 */
function _set_edd_is_product_admin( $is_product_admin ) {

	if ( $is_product_admin ) return $is_product_admin;

	// Checks for "is_singular_admin()" because the post type is non-hierarchical.
	return Query::is_singular_admin() && 'download' === Query::get_admin_post_type();
}

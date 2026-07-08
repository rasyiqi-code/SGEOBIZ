<?php
/**
 * @package SGEOBIZ_SEO\Compat\Plugin\Jetpack
 * @subpackage SGEOBIZ_SEO\Compatibility
 * @access private
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

if ( Data\Plugin::get_option( 'og_tags' ) )
	\add_filter( 'jetpack_enable_open_graph', '__return_false' );

if ( Data\Plugin::get_option( 'twitter_tags' ) )
	\add_filter( 'jetpack_disable_twitter_cards', '__return_true' );

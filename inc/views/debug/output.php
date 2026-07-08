<?php
/**
 * @package SGEOBIZ_SEO\Views\Debug
 * @subpackage SGEOBIZ_SEO\Debug
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use SGEOBIZ_SEO\Internal\Debug;

use SGEOBIZ_SEO\Helper\{
	Post_Type,
	Query,
	Taxonomy,
};

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2017 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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

$id        = Query::get_the_real_id();
$mdash     = ' &mdash; ';
$taxonomy  = Query::get_current_taxonomy();
$post_type = Query::get_current_post_type();

// This will return 'Page' on all non-archive types (except the homepage)
if ( Query::is_real_front_page() ) {
	$type = 'Front Page';
} elseif ( $taxonomy ) {
	$type = Taxonomy::get_label( $taxonomy );
} elseif ( $post_type ) {
	$type = Post_Type::get_label( $post_type );
} else {
	$type = 'Unknown';
}

if ( \is_admin() ) {
	$bstyle = \is_rtl()
		? 'direction:ltr;color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 200px );min-height:700px;padding:0;margin:20px 180px 40px 20px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18pxfont-feature-settings:normal;font-variant:normal'
		: 'direction:ltr;color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 200px );min-height:700px;padding:0;margin:20px 20px 40px 180px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18pxfont-feature-settings:normal;font-variant:normal';
	?>
	<div style="<?= $bstyle // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<h3 style="font-family:unset;font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom:2px solid #aaa;position:absolute;z-index:9002;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px;">
			SEO Debug Information
			<?php
			if ( Query::is_post_edit() || Query::is_term_edit() ) {
				echo ' :: ';
				echo \esc_html( "Type: $type" );
				echo \esc_html( $mdash . 'ID: ' . $id );
				echo \esc_html( $mdash . 'Plugin version: ' . SGEOBIZ_SEO_VERSION );
				echo \esc_html( $mdash . 'Plugin DB version: c' . \get_option( 'sgeobiz_seo_upgraded_db_version' ) . ' | e' . SGEOBIZ_SEO_DB_VERSION );
			}
			?>
		</h3>
		<div style="position:absolute;bottom:0;right:0;left:0;top:39px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;z-index:9001">
			<?php
			Debug::_output_debug_header();
			Debug::_output_debug_query();
			?>
		</div>
	</div>
	<?php
} else {
	?>
	<style>.wp-ui-notification{color:#fff;background-color:#d54e21}.code.highlight{font-family:Consolas,Monaco,monospace;font-size:14px;}.sgeobiz-debug h3{font-size:18px;margin:18px 0}</style>
	<div class=sgeobiz-debug style="direction:ltr;color:#444;font-family:Georgio,sans-serif;font-size:14px;clear:both;float:left;position:relative;width:calc( 100% - 80px );min-height:700px;padding:0;margin:40px;overflow:hidden;border:1px solid #ccc;border-radius:3px;line-height:18px;font-feature-settings:normal;font-variant:normal">
		<h3 style="font-family:unset;font-size:14px;padding:0 12px;margin:0;line-height:39px;border-bottom:2px solid #aaa;position:absolute;z-index:9002;width:100%;right:0;left:0;top:0;background:#fff;border-radius:3px 3px 0 0;height:39px">
			SEO Debug Information
			<?php
			echo ' :: ';
			echo 'Type: ' . \esc_html( $type );
			echo \esc_html( $mdash . 'ID: ' . $id );
			echo \esc_html( $mdash . 'Plugin version: ' . SGEOBIZ_SEO_VERSION );
			echo \esc_html( $mdash . 'Plugin DB version: c' . \get_option( 'sgeobiz_seo_upgraded_db_version' ) . ' | e' . SGEOBIZ_SEO_DB_VERSION );
			?>
		</h3>
		<div style="position:absolute;bottom:0;right:0;left:0;top:39px;margin:0;padding:0;background:#fff;border-radius:3px;overflow-x:hidden;z-index:9001">
			<?php
			Debug::_output_debug_header();
			?>
			<div style="width:50%;float:left;">
				<?php
				Debug::_output_debug_query_from_cache();
				?>
			</div><div style="width:50%;float:right;">
				<?php
				Debug::_output_debug_query();
				?>
			</div>
		</div>
	</div>
	<?php
}

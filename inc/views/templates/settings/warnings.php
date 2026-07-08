<?php
/**
 * @package SGEOBIZ_SEO\Templates\Settings
 * @subpackage SGEOBIZ_SEO\Admin\Settings
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use SGEOBIZ_SEO\Admin\Settings\Layout\{
	HTML,
	Input,
};

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

?>
<script type=text/html id=tmpl-sgeobiz-disabled-post-type-help>
	<span class=sgeobiz-post-type-warning>
		<?php
		HTML::make_info(
			\esc_html__( "This post type is excluded, so this option won't work.", 'sgeobiz-seo' ),
		);
		?>
	</span>
</script>

<script type=text/html id=tmpl-sgeobiz-disabled-taxonomy-help>
	<span class=sgeobiz-taxonomy-warning>
		<?php
		HTML::make_info(
			\esc_html__( "This taxonomy is excluded, so this option won't work.", 'sgeobiz-seo' ),
		);
		?>
	</span>
</script>

<script type=text/html id=tmpl-sgeobiz-disabled-taxonomy-from-pt-help>
	<span class=sgeobiz-taxonomy-from-pt-warning>
		<?php
		HTML::make_info(
			\esc_html__( "This taxonomy's post types are also excluded, so this option won't have any effect.", 'sgeobiz-seo' ),
		);
		?>
	</span>
</script>

<script type=text/html id=tmpl-sgeobiz-disabled-title-additions-help-social>
	<span class=sgeobiz-title-additions-warning-social>
		<?php
		HTML::make_info(
			\esc_html__( 'The site title is already removed from meta titles, so this option only affects the homepage.', 'sgeobiz-seo' ),
		);
		?>
	</span>
</script>

<script type=text/html id=tmpl-sgeobiz-robots-pt-help>
	<span class=sgeobiz-taxonomy-from-pt-robots-warning>
		<?php
		HTML::make_info(
			\esc_html__( "This taxonomy inherited the state from the post type, so this option won't have any effect.", 'sgeobiz-seo' ),
		);
		?>
	</span>
</script>

<script type=text/html id=tmpl-sgeobiz-disabled-title-additions-help>
	<span class=sgeobiz-title-additions-warning>
		<?php
		HTML::make_info(
			\esc_html__( "Site titles are removed globally, so this option won't work.", 'sgeobiz-seo' ),
		);
		?>
	</span>
</script>
<?php

<?php
/**
 * @package SGEOBIZ_SEO\Templates\Inpost
 * @subpackage SGEOBIZ_SEO\Admin\Edit\Inpost
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use SGEOBIZ_SEO\Admin\Settings\Layout\HTML;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

?>
<script type=text/html id=tmpl-tsf-primary-term-selector>
	<input type=hidden id="autodescription[_primary_term_{{data.taxonomy.name}}]" name="autodescription[_primary_term_{{data.taxonomy.name}}]" value="{{data.taxonomy.primary}}">
	<?php
	\wp_nonce_field(
		Data\Admin\Post::SAVE_NONCES['post-edit']['action'] . '_pt',
		Data\Admin\Post::SAVE_NONCES['post-edit']['name'] . '_pt_{{data.taxonomy.name}}',
	);
	?>
</script>

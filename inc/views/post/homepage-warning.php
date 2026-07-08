<?php
/**
 * @package SGEOBIZ_SEO\Views\Post
 * @subpackage SGEOBIZ_SEO\Admin\Post
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

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

?>
<div class="sgeobiz-flex-setting sgeobiz-flex" id=sgeobiz-is-homepage-warning>
	<div class="sgeobiz-flex-setting-input sgeobiz-flex">
		<div class="sgeobiz-flex-setting-input-inner-wrap sgeobiz-flex">
			<div class="sgeobiz-flex-setting-input-item sgeobiz-flex">
				<span>
					<?php
					\esc_html_e( 'The fields below may be overwritten by the Homepage Settings found on the SEO Settings page.', 'sgeobiz-seo' );
					if ( \current_user_can( \SGEOBIZ_SEO_SETTINGS_CAP ) ) {
						echo ' &mdash; ';
						printf(
							'<a href="%s" target=_blank>%s</a>',
							// phpcs:ignore WordPress.Security.EscapeOutput -- menu_page_url() escapes
							\menu_page_url( \SGEOBIZ_SEO_SITE_OPTIONS_SLUG, false ) . '#autodescription-homepage-settings',
							\esc_html__( 'Edit those settings instead.', 'sgeobiz-seo' ),
						);
					}
					?>
				</span>
			</div>
		</div>
	</div>
</div>

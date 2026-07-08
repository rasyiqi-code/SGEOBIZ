<?php
/**
 * @package SGEOBIZ_SEO\Views\Admin
 * @subpackage SGEOBIZ_SEO\Admin\Settings
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2019 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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

// See nav_tab_wrapper
[ $id, $tabs ] = $view_args;

/**
 * Start navigational tabs.
 * Don't output navigation if the number of tabs is 1 or lower.
 */
if ( \count( $tabs ) > 1 ) {
	?>
	<div class="sgeobiz-nav-tab-wrapper hide-if-no-sgeobiz-js" id="<?= \esc_attr( "$id-tabs-wrapper" ) ?>">
		<?php
		$tab_index = 1;

		foreach ( $tabs as $tab => $args ) {
			$dashicon = $args['dashicon'] ?? '';
			$name     = $args['name'] ?? '';

			printf(
				'<div class=sgeobiz-tab>%s</div>',
				vsprintf(
					'<input type=radio class="sgeobiz-nav-tab-radio sgeobiz-input-not-saved" id=%1$s name="%2$s" %3$s><label for=%1$s class=sgeobiz-nav-tab-label>%4$s</label>',
					[
						\esc_attr( "sgeobiz-$id-tab-$tab" ),
						\esc_attr( "sgeobiz-$id-tabs" ),
						1 === $tab_index ? 'checked' : '', // phpcs:ignore WordPress.Security.EscapeOutput -- plaintext.
						\sprintf(
							'%s%s',
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bug in EscapeOutputSniff
							$dashicon ? '<span class="dashicons dashicons-' . \esc_attr( $dashicon ) . ' sgeobiz-dashicons-tabs"></span>' : '',
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bug in EscapeOutputSniff
							$name ? '<span class=sgeobiz-nav-desktop>' . \esc_attr( $name ) . '</span>' : '',
						),
					],
				),
			);
			++$tab_index;
		}
		?>
	</div>
	<?php
}

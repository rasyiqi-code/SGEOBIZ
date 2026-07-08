<?php
/**
 * @package SGEOBIZ_SEO\Views\Profile
 * @subpackage SGEOBIZ_SEO\Admin\User
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use const SGEOBIZ_SEO\ROBOTS_IGNORE_SETTINGS;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2017 - 2025 SGEOBIZ (https://sgeobiz.com/)
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

// See output_setting_fields et al.
[ $user ] = $view_args;

$fields = [
	'tsf-user-meta[facebook_page]' => [
		'name'        => \__( 'Facebook profile page', 'sgeobiz-seo' ),
		'type'        => 'url',
		'placeholder' => \_x( 'https://www.facebook.com/YourPersonalProfile', 'Example Facebook Personal URL', 'sgeobiz-seo' ),
		'value'       => Data\Plugin\User::get_meta_item( 'facebook_page', $user->ID ),
		'class'       => '',
	],
	'tsf-user-meta[twitter_page]'  => [
		'name'        => \__( 'X profile handle', 'sgeobiz-seo' ),
		'type'        => 'text',
		'placeholder' => \_x( '@your-personal-username', 'X @username', 'sgeobiz-seo' ),
		'value'       => Data\Plugin\User::get_meta_item( 'twitter_page', $user->ID ),
		'class'       => 'ltr',
	],
];

?>
<h2><?php \esc_html_e( 'Authorial Info', 'sgeobiz-seo' ); ?></h2>
<table class=form-table>
<?php
foreach ( $fields as $field => $labels ) {
	?>
	<tr class="user-<?= \esc_attr( $field ) ?>-wrap">
		<th><label for="<?= \esc_attr( $field ) ?>">
			<?= \esc_html( $labels['name'] ) ?>
		</label></th>
		<td>
			<input
				type="<?= \esc_attr( $labels['type'] ) ?>"
				name="<?= \esc_attr( $field ) ?>"
				id="<?= \esc_attr( $field ) ?>"
				value="<?= \esc_attr( $labels['value'] ) ?>"
				placeholder="<?= \esc_attr( $labels['placeholder'] ) ?>"
				class="regular-text <?= \esc_attr( $labels['class'] ) ?>" />
			<p class=description><?php \esc_html_e( 'This may be shown publicly.', 'sgeobiz-seo' ); ?></p>
		</td>
	</tr>
	<?php
}
?>
</table>
<?php

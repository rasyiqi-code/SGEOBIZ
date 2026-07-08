<?php
/**
 * @package SGEOBIZ_SEO\Views\Notice
 * @subpackage SGEOBIZ_SEO\Admin\Notice
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use SGEOBIZ_SEO\Admin\Settings\Layout\HTML;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2021 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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

// See _output_dismissible_persistent_notices
[ $message, $key, $args ] = $view_args;

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2015 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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

if ( ! $message ) return;

$sanitized_key = \sanitize_key( $key );

// Make sure the scripts are loaded. Persistent notices may be registered "too late" but can still be outputted.
Admin\Script\Registry::register_scripts_and_hooks();

switch ( $args['type'] ) {
	case 'warning':
	case 'info':
		$args['type'] = "notice-{$args['type']}";
}

$dismiss_title_i18n = \__( 'Dismiss this notice', 'default' );

$nonce_action = Admin\Notice\Persistent::_get_dismiss_nonce_action( $sanitized_key );

$button_js = \sprintf(
	'<a class="hide-if-no-sgeobiz-js sgeobiz-dismiss" href="javascript:;" title="%s" %s></a>',
	\esc_attr( $dismiss_title_i18n ),
	HTML::make_data_attributes( [
		'key'   => $sanitized_key,
		// Is this the best nonce key key? Capability validation already happened. See `output_dismissible_persistent_notices()`.
		'nonce' => \wp_create_nonce( $nonce_action ),
	] ),
);
// We'll display this button even if this notice no longer repeats. This aligns with the user's expectation and offers control.
$button_nojs = vsprintf(
	'<form action="%s" method=post id="sgeobiz-dismiss-notice[%s]" class=hide-if-sgeobiz-js>%s</form>',
	[
		// Register this at removable_query_args? Ignore? No one cares, literally? Does anyone even read this? Hello!? HELLO!?!?
		\esc_attr( \add_query_arg( [ 'sgeobiz-dismissed-notice' => $sanitized_key ] ) ),
		$sanitized_key,
		implode(
			'',
			[
				\wp_nonce_field( $nonce_action, 'sgeobiz_notice_nonce', true, false ),
				vsprintf(
					'<button class=sgeobiz-dismiss type=submit name=sgeobiz-notice-submit id=sgeobiz-notice-submit[%s] value=%s title="%s">%s</button>',
					[
						$sanitized_key,
						$sanitized_key,
						\esc_attr( $dismiss_title_i18n ),
						\sprintf( '<span class=screen-reader-text>%s</span>', \esc_html( $dismiss_title_i18n ) ),
					],
				),
			],
		),
	],
);

vprintf(
	'<div class="notice %s sgeobiz-notice %s">%s%s</div>',
	[
		\esc_attr( $args['type'] ),
		( $args['icon'] ? 'sgeobiz-show-icon' : '' ),
		\sprintf(
			! $args['escape'] && 0 === stripos( $message, '<p' )
				? '%s'
				: '<p>%s</p>',
			$args['escape']
				? \esc_html( $message )
				: $message, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the invoker should be mindful.
		),
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- they are.
		$button_js . $button_nojs,
	],
);

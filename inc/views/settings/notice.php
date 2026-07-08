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

$notice = Data\Plugin::get_site_cache( 'settings_notice' );

if ( ! $notice ) return;

$message = '';
$type    = '';

switch ( $notice ) {
	case 'updated':
		$message = \__( 'SEO settings are saved, and the caches have been flushed.', 'sgeobiz-seo' );
		$type    = 'updated';
		break;

	case 'unchanged':
		$message = \__( 'No SEO settings were changed, but the caches have been flushed.', 'sgeobiz-seo' );
		$type    = 'info';
		break;

	case 'reset':
		$message = \__( 'SEO settings are reset, and the caches have been flushed.', 'sgeobiz-seo' );
		$type    = 'warning';
		break;

	case 'error':
		$message = \__( 'An unknown error occurred saving SEO settings.', 'sgeobiz-seo' );
		$type    = 'error';
}

Data\Plugin::update_site_cache( 'settings_notice', '' );

$message and Admin\Notice::output_notice( $message, [ 'type' => $type ] );

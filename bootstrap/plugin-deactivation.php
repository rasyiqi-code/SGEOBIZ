<?php
/**
 * @package SGEOBIZ_SEO/Bootstrap\Install
 */

namespace SGEOBIZ_SEO\Bootstrap;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use function SGEOBIZ_SEO\is_headless;

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2015 - 2025 SGEOBIZ (https://sgeobiz.com/)
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

turn_off_autoloading: if ( ! is_headless( 'settings' ) ) {
	$options = [];

	if ( false !== \get_option( \SGEOBIZ_SEO_SITE_OPTIONS ) )
		$options[] = \SGEOBIZ_SEO_SITE_OPTIONS;

	if ( false !== \get_option( \SGEOBIZ_SEO_SITE_CACHE ) )
		$options[] = \SGEOBIZ_SEO_SITE_CACHE;

	\wp_set_options_autoload( $options, false );
}

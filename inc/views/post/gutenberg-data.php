<?php
/**
 * @package SGEOBIZ_SEO\Views\Post
 * @subpackage SGEOBIZ_SEO\Admin\Post
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use SGEOBIZ_SEO\Helper\Query;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2025 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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

printf(
	'<div id=%s data-post-id=%d class=hidden></div>',
	'tsf-gutenberg-data-holder',
	Query::get_the_real_id(), // phpcs:ignore WordPress.Security.EscapeOutput -- printf casts to int.
);

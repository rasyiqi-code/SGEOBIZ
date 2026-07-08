<?php
/**
 * @package SGEOBIZ_SEO
 * @subpackage SGEOBIZ_SEO\Bootstrap
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\Helper\{
	Headers,
	Query,
};

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2023 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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

// Load the plugin's text domain first.
\load_plugin_textdomain(
	'sgeobiz-seo',
	false,
	\dirname( \SGEOBIZ_SEO_PLUGIN_BASENAME ) . \DIRECTORY_SEPARATOR . 'language',
);

// Output noindex headers when an XMLRPC request is detected. There are no hooks, test inline.
if ( \defined( 'XMLRPC_REQUEST' ) && \XMLRPC_REQUEST )
	Headers::output_robots_noindex_headers();

// Adjust category link to accommodate primary term.
\add_filter( 'post_link_category', [ Query\Filter::class, 'filter_post_link_category' ], 10, 3 );

// Overwrite the robots.txt output.
\add_filter( 'robots_txt', [ RobotsTXT\Main::class, 'get_robots_txt' ], 10, 2 );

// Register the SGEOBIZ breadcrumb shortcode.
\add_shortcode( 'sgeobiz_breadcrumb', 'sgeobiz_breadcrumb' );

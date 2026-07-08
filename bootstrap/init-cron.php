<?php
/**
 * @package SGEOBIZ_SEO
 * @subpackage SGEOBIZ_SEO\Bootstrap
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

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

// Prerender sitemap.
if (
	   Data\Plugin::get_option( 'sitemap_cron_prerender' ) // Less likely to be true.
	&& Data\Plugin::get_option( 'sitemaps_output' )
) {
	\add_action(
		'tsf_sitemap_cron_hook_before',
		[ Sitemap\Optimized\Base::class, 'prerender_sitemap' ],
	);
}

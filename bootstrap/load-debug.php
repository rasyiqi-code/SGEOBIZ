<?php
/**
 * @package SGEOBIZ_SEO
 * @subpackage SGEOBIZ_SEO\Bootstrap
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\Internal\Debug;

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

\add_action( 'sgeobiz_seo_do_before_output', [ Debug::class, '_set_debug_query_output_cache' ] );
\add_action( 'admin_footer', [ Debug::class, '_do_debug_output' ] );
\add_action( 'wp_footer', [ Debug::class, '_do_debug_output' ] );

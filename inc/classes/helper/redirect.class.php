<?php
/**
 * @package SGEOBIZ_SEO\Classes\Helper\Redirect
 * @subpackage SGEOBIZ_SEO\Query
 */

namespace SGEOBIZ_SEO\Helper;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use function SGEOBIZ_SEO\memo;

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

/**
 * Holds a collection of helper methods for HTTP Redirects.
 *
 * @since 5.0.0
 * @access private
 */
final class Redirect {

	/**
	 * Whether to allow external redirect through the 301 redirect option.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 5.0.0 Moved from `\SGEOBIZ_SEO\Load`.
	 *
	 * @return bool Whether external redirect is allowed.
	 */
	public static function allow_external_redirect() {
		/**
		 * @since 2.1.0
		 * @param bool $allowed Whether external redirect is allowed.
		 */
		return memo() ?? memo( (bool) \apply_filters( 'sgeobiz_seo_allow_external_redirect', true ) );
	}
}

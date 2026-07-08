<?php
/**
 * @package SGEOBIZ_SEO
 * @subpackage SGEOBIZ_SEO\Bootstrap
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

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

foreach (
	array_intersect_key(
		[
			'genesis' => 'genesis',
			'bricks'  => 'bricks',
			'avada'   => 'avada',
		],
		array_flip( Data\Blog::get_active_themes() ),
	)
	as $_theme
) {
	require \SGEOBIZ_SEO_DIR_PATH_COMPAT . "theme-$_theme.php";
}

foreach (
	array_intersect_key(
		[
			'bbpress/bbpress.php'                      => 'bbpress',
			'buddypress/bp-loader.php'                 => 'buddypress',
			'easy-digital-downloads/easy-digital-downloads.php' => 'edd',
			'elementor/elementor.php'                  => 'elementor',
			'jetpack/jetpack.php'                      => 'jetpack',
			'polylang/polylang.php'                    => 'polylang',
			'polylang-pro/polylang.php'                => 'polylang',
			'sitepress-multilingual-cms/sitepress.php' => 'wpml',
			'ultimate-member/ultimate-member.php'      => 'ultimatemember',
			'wpforo/wpforo.php'                        => 'wpforo',
			'woocommerce/woocommerce.php'              => 'woocommerce',
		],
		array_flip( Data\Blog::get_active_plugins() ),
	)
	as $_plugin
) {
	require \SGEOBIZ_SEO_DIR_PATH_COMPAT . "plugin-$_plugin.php";
}

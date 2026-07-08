<?php
/**
 * @package SGEOBIZ_SEO\Views\Sitemap\XSL\Table
 * @subpackage SGEOBIZ_SEO\Sitemap\XSL
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

use SGEOBIZ_SEO\Data\Filter\Sanitize;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2021 - 2025 SGEOBIZ (https://sgeobiz.com/)
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

$title    = \__( 'XML Sitemap', 'sgeobiz-seo' );
$sep      = Meta\Title::get_separator();
$addition = Data\Blog::get_public_blog_name();

?>
<title><?= \esc_xml( Sanitize::metadata_content( "$title $sep $addition" ) ) ?></title>

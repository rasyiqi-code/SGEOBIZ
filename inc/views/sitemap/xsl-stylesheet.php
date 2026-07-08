<?php
/**
 * @package SGEOBIZ_SEO\Views\Sitemap
 * @subpackage SGEOBIZ_SEO\Sitemap
 */

namespace SGEOBIZ_SEO;

( \defined( 'SGEOBIZ_SEO_PRESENT' ) and Helper\Template::verify_secret( $secret ) ) or die;

// phpcs:disable WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2017 - 2025 SGEOBIZ (https://sgeobiz.com/)
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

// echo here, otherwise XML closes PHP...
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";

?>
<xsl:stylesheet version="2.0"
				xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" <?php \language_attributes( 'html' ); ?>>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1" />
				<?php
				/**
				 * @since 3.1.0
				 * @param \SGEOBIZ_SEO\Load Alias of `sgeobiz()`
				 * @TODO 5.1.0 Remove first parameter. It's useless now.
				 */
				\do_action( 'sgeobiz_seo_xsl_head', \sgeobiz() );
				?>
			</head>
			<body class="<?= \is_rtl() ? 'rtl' : 'ltr' ?>">
				<div id="description">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \SGEOBIZ_SEO\Load Alias of `sgeobiz()`
						 * @TODO 5.1.0 Remove first parameter. It's useless now.
						 */
						\do_action( 'sgeobiz_seo_xsl_description', \sgeobiz() );
						?>
					</div>
				</div>
				<div id="content">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \SGEOBIZ_SEO\Load Alias of `sgeobiz()`
						 * @TODO 5.1.0 Remove first parameter. It's useless now.
						 */
						\do_action( 'sgeobiz_seo_xsl_content', \sgeobiz() );
						?>
					</div>
				</div>
				<div id="footer">
					<div class="wrap">
						<?php
						/**
						 * @since 3.1.0
						 * @param \SGEOBIZ_SEO\Load Alias of `sgeobiz()`
						 * @TODO 5.1.0 Remove first parameter. It's useless now.
						 */
						\do_action( 'sgeobiz_seo_xsl_footer', \sgeobiz() );
						?>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
<?php

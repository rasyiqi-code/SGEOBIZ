<?php
/**
 * @package SGEOBIZ_SEO\Classes\Admin\PluginTable
 */

namespace SGEOBIZ_SEO\Admin;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use function SGEOBIZ_SEO\is_headless;

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

/**
 * Prepares the Plugin Table view interface.
 *
 * @since 4.1.4
 * @since 5.0.0 Moved from `\SGEOBIZ_SEO\Bridges`
 * @access private
 */
final class PluginTable {

	/**
	 * Adds various links to the plugin row on the plugin's screen.
	 *
	 * @hook plugin_action_links_the-seo-framework/the-seo-framework.php 10
	 * @since 3.1.0
	 * @since 4.1.4 Moved to PluginTable.
	 * @access private
	 *
	 * @param array $links The current links.
	 * @return array The plugin links.
	 */
	public static function add_plugin_action_links( $links = [] ) {

		$sgeobiz_links = [];

		if ( ! is_headless( 'settings' ) ) {
			$sgeobiz_links['settings'] = \sprintf(
				'<a href="%s">%s</a>',
				\esc_url( \admin_url( 'admin.php?page=' . \SGEOBIZ_SEO_SITE_OPTIONS_SLUG ) ),
				\esc_html__( 'Settings', 'sgeobiz-seo' ),
			);
		}

		$sgeobiz_links['sgeobizem']   = \sprintf(
			'<a href="%s" rel="noreferrer noopener" target=_blank>%s</a>',
			'https://sgeobiz.com/extensions/',
			\esc_html_x( 'Extensions', 'Plugin extensions', 'sgeobiz-seo' ),
		);
		$sgeobiz_links['pricing'] = \sprintf(
			'<a href="%s" rel="noreferrer noopener" target=_blank>%s</a>',
			'https://sgeobiz.com/pricing/',
			\esc_html_x( 'Pricing', 'Plugin pricing', 'sgeobiz-seo' ),
		);

		return array_merge( $sgeobiz_links, $links );
	}

	/**
	 * Adds more row meta on the plugin screen.
	 *
	 * @hook plugin_row_meta 10
	 * @since 3.2.4
	 * @since 4.1.4 Moved to PluginTable.
	 * @since 5.0.0 Exchanged API docs for GitHub link. Simplified translations.
	 * @access private
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata,
	 *                              including the version, author,
	 *                              author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return array $plugin_meta
	 */
	public static function add_plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( \SGEOBIZ_SEO_PLUGIN_BASENAME !== $plugin_file )
			return $plugin_meta;

		return array_merge(
			$plugin_meta,
			[
				'support' => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://sgeobiz.com/support/',
					\esc_html__( 'Support', 'sgeobiz-seo' ),
				),
				'docs'    => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://sgeobiz.com/docs/',
					\esc_html__( 'Documentation', 'sgeobiz-seo' ),
				),
				'Git'     => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://github.com/sgeobiz',
					'GitHub',
				),
				'EM'      => \sprintf(
					'<a href="%s" rel="noreferrer noopener nofollow" target=_blank>%s</a>',
					'https://sgeobiz.com/extensions/',
					'Extension Manager',
				),
			],
		);
	}
}

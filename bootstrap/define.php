<?php
/**
 * @package SGEOBIZ_SEO\Bootstrap
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_DB_VERSION' ) or die;

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2018 - 2025 SGEOBIZ (https://sgeobiz.com/)
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
 * Tells the world the plugin is present and to be used.
 *
 * @since 3.1.0
 */
\define( 'SGEOBIZ_SEO_PRESENT', true );

/**
 * The user capability required to access the extension overview page.
 *
 * == WARNING ==
 * When this constant is used incorrectly, you can expose your site to unforeseen
 * security risks. We assume the role supplied here is lower than the webmaster's;
 * for example, in a WPMU environment. However, proceed with caution.
 *
 * @since 4.1.0
 * @param string
 */
\defined( 'SGEOBIZ_SEO_SETTINGS_CAP' )
	or \define( 'SGEOBIZ_SEO_SETTINGS_CAP', 'manage_options' );

/**
 * The user capability required to have SEO-fields on their profiles.
 *
 * == WARNING ==
 * When this constant is used incorrectly, you can expose your site to unforeseen
 * security risks. We assume the role supplied here is lower than the webmaster's;
 * for example, in a WPMU environment. However, proceed with caution.
 *
 * @since 4.1.0
 * @param string
 */
\defined( 'SGEOBIZ_SEO_AUTHOR_INFO_CAP' )
	or \define( 'SGEOBIZ_SEO_AUTHOR_INFO_CAP', 'edit_posts' );

/**
 * Enables the site-wide SEO debugging interface.
 *
 * @since 2.3.4
 * @since 5.0.0 Is now registered during plugin load.
 * @param bool
 */
\defined( 'SGEOBIZ_SEO_DEBUG' )
	or \define( 'SGEOBIZ_SEO_DEBUG', false );

/**
 * The plugin's main settings page slug.
 *
 * @since 5.0.0
 * @param bool
 */
\defined( 'SGEOBIZ_SEO_SITE_OPTIONS_SLUG' )
	or \define( 'SGEOBIZ_SEO_SITE_OPTIONS_SLUG', 'sgeobiz-seo-settings' );

/**
 * The plugin options database option_name key.
 *
 * Used for storing the SEO options array.
 *
 * @since 2.2.5
 * @since 5.0.0 Removed its filter.
 */
\define( 'SGEOBIZ_SEO_SITE_OPTIONS', 'sgeobiz-site-settings' );

/**
 * Plugin term options key.
 *
 * @since 2.7.0
 * @since 5.0.0 Removed its filter.
 */
\define( 'SGEOBIZ_SEO_TERM_OPTIONS', 'sgeobiz-term-settings' );

/**
 * Plugin user term options key.
 *
 * @since 2.7.0
 * @since 5.0.0 Removed its filter.
 */
\define( 'SGEOBIZ_SEO_USER_OPTIONS', 'sgeobiz-user-settings' );

/**
 * Plugin updates cache key.
 *
 * @since 3.1.0
 * @since 5.0.0 1. Removed its filter.
 *              2. Changed the default value from 'autodescription-updates-cache'.
 */
\define( 'SGEOBIZ_SEO_SITE_CACHE', 'sgeobiz-site-cache' );

/**
 * The plugin folder URL. Has a trailing slash.
 * Used for calling browser files.
 *
 * @since 2.2.5
 */
\define( 'SGEOBIZ_SEO_DIR_URL', \plugin_dir_url( \SGEOBIZ_SEO_PLUGIN_BASE_FILE ) );

/**
 * The plugin file relative to the plugins dir. Does not have a trailing slash.
 *
 * @since 2.2.8
 */
\define( 'SGEOBIZ_SEO_PLUGIN_BASENAME', \plugin_basename( \SGEOBIZ_SEO_PLUGIN_BASE_FILE ) );

/**
 * The plugin folder absolute path. Used for calling php files.
 *
 * @since 2.2.5
 */
\define( 'SGEOBIZ_SEO_DIR_PATH', \dirname( \SGEOBIZ_SEO_PLUGIN_BASE_FILE ) . \DIRECTORY_SEPARATOR );

/**
 * The plugin views folder absolute path.
 *
 * @since 2.7.0
 */
\define( 'SGEOBIZ_SEO_DIR_PATH_VIEWS', \SGEOBIZ_SEO_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR );

/**
 * The plugin class folder absolute path.
 *
 * @since 2.2.9
 */
\define( 'SGEOBIZ_SEO_DIR_PATH_CLASS', \SGEOBIZ_SEO_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'classes' . \DIRECTORY_SEPARATOR );

/**
 * The plugin trait folder absolute path.
 *
 * @since 3.1.0
 */
\define( 'SGEOBIZ_SEO_DIR_PATH_TRAIT', \SGEOBIZ_SEO_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'traits' . \DIRECTORY_SEPARATOR );

/**
 * The plugin interface folder absolute path.
 *
 * @since 2.8.0
 */
\define( 'SGEOBIZ_SEO_DIR_PATH_INTERFACE', \SGEOBIZ_SEO_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'interfaces' . \DIRECTORY_SEPARATOR );

/**
 * The plugin function folder absolute path.
 *
 * @since 2.2.9
 */
\define( 'SGEOBIZ_SEO_DIR_PATH_FUNCT', \SGEOBIZ_SEO_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'functions' . \DIRECTORY_SEPARATOR );

/**
 * The plugin compatibility folder absolute path.
 *
 * @since 2.8.0
 */
\define( 'SGEOBIZ_SEO_DIR_PATH_COMPAT', \SGEOBIZ_SEO_DIR_PATH . 'inc' . \DIRECTORY_SEPARATOR . 'compat' . \DIRECTORY_SEPARATOR );

/**
 * Robots setting, ignore protection.
 *
 * @since 4.0.0
 * @see \SGEOBIZ_SEO\Generate\robots_meta()
 */
const ROBOTS_IGNORE_PROTECTION = 0b001;

/**
 * Robots setting, ignore settings.
 *
 * @since 4.0.0
 * @see \SGEOBIZ_SEO\Generate\robots_meta()
 */
const ROBOTS_IGNORE_SETTINGS = 0b010;

/**
 * Robots setting, enable asserting.
 *
 * @since 4.2.0
 * @see \SGEOBIZ_SEO\Generate\robots_meta()
 */
const ROBOTS_ASSERT = 0b100;

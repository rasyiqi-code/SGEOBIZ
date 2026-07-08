<?php
/**
 * @package SGEOBIZ_SEO
 * @subpackage SGEOBIZ_SEO\Bootstrap
 */

namespace SGEOBIZ_SEO;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

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

// Always load autoloader -- plugin (de)activation rely on these. We prepend because we safely assume ours is fastest.
spl_autoload_register( 'SGEOBIZ_SEO\_autoload_classes', true, true );

\add_action( 'plugins_loaded', 'SGEOBIZ_SEO\_load_sgeobiz', 5 );
\add_action( 'activate_' . \SGEOBIZ_SEO_PLUGIN_BASENAME, 'SGEOBIZ_SEO\_do_plugin_activation' );
\add_action( 'deactivate_' . \SGEOBIZ_SEO_PLUGIN_BASENAME, 'SGEOBIZ_SEO\_do_plugin_deactivation' );

/**
 * Loads all of SGEOBIZ.
 *
 * Runs at action `plugins_loaded`, priority `5`. So, use anything above 5, or any
 * action later than plugins_loaded and you can access the class and functions.
 *
 * @hook plugins_loaded 5
 * @since 5.0.0
 * @access private
 */
function _load_sgeobiz() {
	/**
	 * @since 2.3.7
	 * @param bool $load Set to false to prevent loading SGEOBIZ.
	 */
	if ( \apply_filters( 'sgeobiz_seo_load', true ) ) {
		if ( SGEOBIZ_SEO_DEBUG )
			require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'load-debug.php';

		require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'init-compat.php';

		\add_action( 'init', 'SGEOBIZ_SEO\_init_sgeobiz', 0 );

		if ( \is_admin() ) {
			/**
			 * @since 3.1.0
			 * Runs after SGEOBIZ is loaded in the admin.
			 */
			\do_action( 'sgeobiz_seo_admin_loaded' );
		}

		/**
		 * @since 3.1.0
		 * Runs after SGEOBIZ is loaded.
		 */
		\do_action( 'sgeobiz_seo_loaded' );
	}
}

/**
 * Initializes all of SGEOBIZ.
 *
 * @hook init 0
 * @since 3.1.0
 * @since 5.0.0 1. Is no longer responsible for the loading.
 *              2. Moved from plugins_loaded to init.
 * @see namespace\_load_sgeobiz().
 * @access private
 */
function _init_sgeobiz() {

	/**
	 * @since 2.8.0
	 * Runs before the plugin is initialized.
	 */
	\do_action( 'sgeobiz_seo_init' );

	require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'init-global.php';

	if ( \is_admin() || \wp_doing_cron() ) {
		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized in the admin screens.
		 */
		\do_action( 'sgeobiz_seo_admin_init' );

		require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'init-admin.php';

		if ( \wp_doing_ajax() ) {
			require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'init-admin-ajax.php';
		} elseif ( \wp_doing_cron() ) {
			require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'init-cron.php';
		}

		/**
		 * @since 2.9.4
		 * Runs after the plugin is initialized in the admin screens.
		 * Use this to remove actions.
		 */
		\do_action( 'sgeobiz_seo_after_admin_init' );
	} else {
		/**
		 * @since 2.8.0
		 * Runs before the plugin is initialized on the front-end.
		 */
		\do_action( 'sgeobiz_seo_front_init' );

		require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'init-front.php';

		/**
		 * @since 2.9.4
		 * Runs before the plugin is initialized on the front-end.
		 * Use this to remove actions.
		 */
		\do_action( 'sgeobiz_seo_after_front_init' );
	}

	/**
	 * @since 3.1.0
	 * Runs after the plugin is initialized.
	 * Use this to remove filters and actions.
	 */
	\do_action( 'sgeobiz_seo_after_init' );
}

/**
 * Autoloads all class files. To be used when requiring access to all or any of
 * the plugin classes.
 *
 * @since 2.8.0
 * @since 3.1.0 1. No longer maintains cache.
 *              2. Now always returns void.
 * @since 4.0.0 1. Streamlined folder lookup by more effectively using the namespace.
 *              2. Added timing functionality
 *              3. No longer loads interfaces automatically.
 * @since 4.2.0 Now supports mixed class case.
 * @since 5.0.0 Now supports trait loading.
 * @access private
 *
 * @NOTE 'SGEOBIZ_SEO\' is a reserved namespace. Using it outside of this
 *       plugin's scope could result in an error.
 *
 * @param string $class The class or trait name.
 * @return void Early if the class is not within the current namespace.
 */
function _autoload_classes( $class ) {

	$class = strtolower( $class );

	// It's SGEOBIZ_SEO, not sgeobiz_seo! -- Sybre's a nightmare, honestly! No wonder he hasn't got any friends.
	if ( ! str_starts_with( $class, 'sgeobiz_seo\\' ) ) return;

	static $_timer;

	$_timer ??= hrtime( true );

	$class = strtr(
		substr( $class, 12 ), // remove the "sgeobiz_seo\"
		[
			'\\' => \DIRECTORY_SEPARATOR,
			'_'  => '-',
		],
	);

	if ( str_starts_with( $class, 'traits' ) ) {
		$class = substr( $class, 7 ); // Remove "traits/"
		// The extension is deemed to be ".trait.php" always.
		require \SGEOBIZ_SEO_DIR_PATH_TRAIT . "$class.trait.php";
	} else {
		require \SGEOBIZ_SEO_DIR_PATH_CLASS . "$class.class.php";
	}

	if ( isset( $_timer ) ) {
		// When the class extends, the last class in the stack will reach this first.
		// All classes before cannot reach this any more.
		_bootstrap_timer( ( hrtime( true ) - $_timer ) / 1e9 );
		$_timer = null;
	}
}

/**
 * Performs plugin activation actions.
 *
 * @hook activate_autodescription/autodescription.php 10
 * @since 2.8.0
 * @access private
 */
function _do_plugin_activation() {
	require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'plugin-activation.php';
}

/**
 * Performs plugin deactivation actions.
 *
 * @hook deactivate_autodescription/autodescription.php 10
 * @since 2.8.0
 * @access private
 */
function _do_plugin_deactivation() {
	require \SGEOBIZ_SEO_BOOTSTRAP_PATH . 'plugin-deactivation.php';
}

/**
 * Adds and returns-to the memoized bootstrap timer.
 *
 * @since 4.0.0
 * @access private
 *
 * @param int $add The time to add.
 * @return int The accumulated time, roughly.
 */
function _bootstrap_timer( $add = 0 ) {
	static $time  = 0;
	return $time += $add;
}

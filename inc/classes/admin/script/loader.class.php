<?php
/**
 * @package SGEOBIZ_SEO\Classes\Admin\Script\Loader
 * @subpackage SGEOBIZ_SEO\Scripts
 */

namespace SGEOBIZ_SEO\Admin\Script;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use function SGEOBIZ_SEO\has_run;

use SGEOBIZ_SEO\{
	Data,
	Meta,
};
use SGEOBIZ_SEO\Helper\{
	Compatibility,
	Guidelines,
	Format\Arrays,
	Query,
	Taxonomy,
	Template,
};

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2019 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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
 * Prepares admin GUI scripts. Auto-invokes everything the moment this file is required.
 * Relies on \SGEOBIZ_SEO\Admin\Script\Registry to register and load scripts.
 *
 * What's a state, and what's a param?
 * - states may and are expected to be changed, like a page title.
 * - params shouldn't change, like the page ID.
 *
 * @since 5.0.0
 * @since 5.1.5 No longer final.
 * @see \SGEOBIZ_SEO\Admin\Script\Registry
 * @access protected
 *         Use sgeobiz()->admin()->scripts()->loader() instead.
 */
class Loader {

	/**
	 * Initializes and enqueues scripts anywhere.
	 *
	 * Great for manual initialization; for example, on the front-end.
	 *
	 * @since 5.1.5
	 * @api Not used internally.
	 */
	public static function mount() {

		self::init();

		$enqueue_hook = \is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts';

		if ( \did_action( $enqueue_hook ) ) {
			Registry::enqueue();
		} else {
			\add_action( $enqueue_hook, [ Registry::class, 'enqueue' ] );
		}
	}

	/**
	 * Initializes scripts based on admin query.
	 *
	 * @hook admin_enqueue_scripts 0
	 * @since 5.0.0
	 * @since 5.1.5 Prevents multiple runs.
	 */
	public static function init() {

		if ( has_run( __METHOD__ ) )
			return;

		$scripts = [
			self::get_common_scripts(),
		];

		if ( Query::is_post_edit() ) {
			self::prepare_media_scripts();

			$scripts[] = self::get_post_edit_scripts();
			$scripts[] = self::get_tabs_scripts();
			$scripts[] = self::get_media_scripts();
			$scripts[] = self::get_title_scripts();
			$scripts[] = self::get_description_scripts();
			$scripts[] = self::get_social_scripts();
			$scripts[] = self::get_canonical_scripts();
			$scripts[] = self::get_primaryterm_scripts();
			$scripts[] = self::get_ays_scripts();

			if ( Data\Plugin::get_option( 'display_pixel_counter' ) || Data\Plugin::get_option( 'display_character_counter' ) )
				$scripts[] = self::get_counter_scripts();

			if ( Query::is_block_editor() )
				$scripts[] = self::get_gutenberg_compat_scripts();
		} elseif ( Query::is_term_edit() ) {
			if ( Data\Plugin::get_option( 'display_term_edit_options' ) ) {
				self::prepare_media_scripts();

				$scripts[] = self::get_term_edit_scripts();
				$scripts[] = self::get_media_scripts();
				$scripts[] = self::get_title_scripts();
				$scripts[] = self::get_description_scripts();
				$scripts[] = self::get_social_scripts();
				$scripts[] = self::get_canonical_scripts();
				$scripts[] = self::get_ays_scripts();

				if ( Data\Plugin::get_option( 'display_pixel_counter' ) || Data\Plugin::get_option( 'display_character_counter' ) )
					$scripts[] = self::get_counter_scripts();
			}
		} elseif ( Query::is_wp_lists_edit() ) {
			if ( Data\Plugin::get_option( 'display_list_edit_options' ) ) {
				$scripts[] = self::get_list_edit_scripts();
				$scripts[] = self::get_title_scripts();
				$scripts[] = self::get_description_scripts();
				$scripts[] = self::get_canonical_scripts();

				if ( Query::is_singular_admin() )
					$scripts[] = self::get_primaryterm_scripts();

				if ( Data\Plugin::get_option( 'display_pixel_counter' ) || Data\Plugin::get_option( 'display_character_counter' ) )
					$scripts[] = self::get_counter_scripts();
			}
		} elseif ( Query::is_seo_settings_page() ) {
			self::prepare_media_scripts();
			self::prepare_metabox_scripts();

			$scripts[] = self::get_seo_settings_scripts();
			$scripts[] = self::get_tabs_scripts();
			$scripts[] = self::get_media_scripts();
			$scripts[] = self::get_title_scripts();
			$scripts[] = self::get_description_scripts();
			$scripts[] = self::get_social_scripts();
			$scripts[] = self::get_canonical_scripts();
			$scripts[] = self::get_ays_scripts();

			// Always load unconditionally, options may enable the counters dynamically.
			$scripts[] = self::get_counter_scripts();
		}

		/**
		 * @since 3.1.0
		 * @since 4.0.0 1. Now holds all scripts.
		 *              2. Added $loader parameter.
		 * @since 4.2.7 Consolidated all input scripts into a list.
		 * @param array  $scripts  The default CSS and JS loader settings.
		 * @param string $registry The \SGEOBIZ_SEO\Admin\Script\Registry registry class name.
		 * @param string $loader   The \SGEOBIZ_SEO\Admin\Script\Loader loader class name.
		 */
		$scripts = \apply_filters(
			'sgeobiz_seo_scripts',
			// Flattening is 3% of this method's total time, we can improve by simplifying the getters above like do_meta_output().
			Arrays::flatten_list( $scripts ),
			Registry::class,
			Loader::class,
		);

		Registry::register( $scripts );
	}

	/**
	 * Prepares WordPress Media scripts.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Resolved PHP notices by not setting the 'post' indexed on new posts.
	 */
	public static function prepare_media_scripts() {

		$args = [];

		if ( Query::is_post_edit() )
			$args['post'] = Query::get_the_real_admin_id();

		\wp_enqueue_media( $args );
	}

	/**
	 * Prepares WordPress meta box scripts.
	 *
	 * @since 4.0.0
	 */
	public static function prepare_metabox_scripts() {

		\wp_enqueue_script( 'common' );
		\wp_enqueue_script( 'wp-lists' );
		\wp_enqueue_script( 'postbox' );
	}

	/**
	 * Returns the common SGEOBIZ scripts.
	 *
	 * @since 5.1.0
	 *
	 * @return array The script params.
	 */
	public static function get_common_scripts() {
		return [
			// Load SGEOBIZ-utils first. TODO split the SGEOBIZ object so that they will no longer become reliant upon eachother.
			[
				'id'       => 'sgeobiz-utils',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => true,
				'name'     => 'utils',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz',
				'type'     => 'css',
				'deps'     => [ 'dashicons' ],
				'autoload' => true,
				'name'     => 'sgeobiz',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz-utils' ],
				'autoload' => true,
				'name'     => 'sgeobiz',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizL10n',
					'data' => [
						'nonces' => [
							/**
							 * Use SGEOBIZ_SEO_SETTINGS_CAP ?... might conflict with other nonces.
							 * -> Just add it to the end, if it matches the existing ones, that's fine (just double work).
							 * If we do this, also add it to "states" or something.
							 */
							'manage_options' => Utils::create_ajax_capability_nonce( 'manage_options' ), // unused
							'upload_files'   => Utils::create_ajax_capability_nonce( 'upload_files' ), // unused
							'edit_posts'     => Utils::create_ajax_capability_nonce( 'edit_posts' ),
						],
						'states' => [
							'debug' => \SCRIPT_DEBUG,
						],
					],
				],
			],
			[
				'id'       => 'sgeobiz-tt',
				'type'     => 'css',
				'deps'     => [],
				'autoload' => true,
				'name'     => 'tt',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'inline'   => [
					'.sgeobiz-tooltip-text-wrap'    => [
						'background-color:{{$bg_accent}}',
						'color:{{$rel_bg_accent}}',
					],
					'.sgeobiz-tooltip-text-wrap *'  => [
						'color:{{$rel_bg_accent}}',
					],
					'.sgeobiz-tooltip-arrow::after' => [
						'border-top-color:{{$bg_accent}}',
					],
					'.sgeobiz-tooltip-down .sgeobiz-tooltip-arrow::after' => [
						'border-bottom-color:{{$bg_accent}}',
					],
					'.sgeobiz-tooltip-text'         => [
						\is_rtl() ? 'direction:rtl' : '',
					],
				],
			],
			[
				'id'       => 'sgeobiz-tt',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz' ],
				'autoload' => true,
				'name'     => 'tt',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-ui',
				'type'     => 'css',
				'deps'     => [ 'sgeobiz', 'dashicons' ],
				'autoload' => true,
				'name'     => 'ui',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-ui',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz', 'sgeobiz-utils', 'jquery' ],
				'autoload' => true,
				'name'     => 'ui',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
		];
	}

	/**
	 * Returns AYS (Are you sure?) scripts params.
	 *
	 * @since 4.0.0
	 *
	 * @return array The script params.
	 */
	public static function get_ays_scripts() {
		return [
			[
				'id'       => 'sgeobiz-ays',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz', 'sgeobiz-utils' ],
				'autoload' => true,
				'name'     => 'ays',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizAysL10n',
					'data' => [
						'i18n' => [
							'saveAlert' => \__( 'The changes you made will be lost if you navigate away from this page.', 'sgeobiz-seo' ),
						],
					],
				],
			],
		];
	}

	/**
	 * Returns LE (List Edit) scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now depends on title and description scripts.
	 * @since 4.2.0 No longer registers l10n (data).
	 * @since 5.1.3 sgeobiz-pt-le dependency is now conditional on singular admin pages.
	 *
	 * @return array The script params.
	 */
	public static function get_list_edit_scripts() {

		$deps = [ 'sgeobiz-title', 'sgeobiz-description', 'sgeobiz-canonical', 'sgeobiz-postslugs', 'sgeobiz-termslugs', 'sgeobiz-authorslugs', 'sgeobiz', 'sgeobiz-tt', 'sgeobiz-utils' ];

		// sgeobiz-pt-le is only registered on singular admin (post list) pages, not term list pages.
		if ( Query::is_singular_admin() )
			$deps[] = 'sgeobiz-pt-le';

		return [
			[
				'id'       => 'sgeobiz-le',
				'type'     => 'css',
				'deps'     => [ 'sgeobiz' ],
				'autoload' => true,
				'name'     => 'le',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-le',
				'type'     => 'js',
				'deps'     => $deps,
				'autoload' => true,
				'name'     => 'le',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
		];
	}

	/**
	 * Returns the SEO Settings page script params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 *
	 * @return array The script params.
	 */
	public static function get_seo_settings_scripts() {

		$front_id = Query::get_the_front_page_id();

		return [
			[
				'id'       => 'sgeobiz-settings',
				'type'     => 'css',
				'deps'     => [ 'sgeobiz', 'sgeobiz-tt', 'wp-color-picker', 'dashicons' ],
				'autoload' => true,
				'name'     => 'settings',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION . '.' . time(),
			],
			[
				'id'       => 'sgeobiz-settings',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'sgeobiz-ays', 'sgeobiz-title', 'sgeobiz-description', 'sgeobiz-social', 'sgeobiz-canonical', 'sgeobiz', 'sgeobiz-tabs', 'sgeobiz-tt', 'wp-color-picker', 'wp-util' ],
				'autoload' => true,
				'name'     => 'settings',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION . '.' . time(),
				'l10n'     => [
					'name' => 'sgeobizSettingsL10n',
					'data' => [
						'states' => [
							'isFrontPrivate'   => $front_id && Data\Post::is_private( $front_id ),
							'isFrontProtected' => $front_id && Data\Post::is_password_protected( $front_id ),
						],
					],
				],
				'tmpl'     => [
					'file' => Template::get_view_location( 'templates/settings/warnings' ),
				],
			],
		];
	}

	/**
	 * Returns Post edit scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 *
	 * @return array The script params.
	 */
	public static function get_post_edit_scripts() {

		$id = Query::get_the_real_id();

		$is_static_front_page = Query::is_static_front_page( $id );
		$is_block_editor      = Query::is_block_editor();

		if ( $is_static_front_page ) {
			$additions_forced_disabled = ! Data\Plugin::get_option( 'homepage_tagline' );
			$additions_forced_enabled  = ! $additions_forced_disabled;
		} else {
			$additions_forced_disabled = (bool) Data\Plugin::get_option( 'title_rem_additions' );
			$additions_forced_enabled  = false;
		}

		return [
			[
				'id'       => 'sgeobiz-post',
				'type'     => 'css',
				'deps'     => [ 'sgeobiz-tt', 'sgeobiz', 'sgeobiz-ui' ],
				'autoload' => true,
				'name'     => 'post',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'inline'   => [
					'.sgeobiz-flex-nav-tab .sgeobiz-flex-nav-tab-radio:checked + .sgeobiz-flex-nav-tab-label' => [
						'box-shadow:0 -2px 0 0 {{$color_accent}} inset, 0 0 0 0 {{$color_accent}} inset',
					],
					'.sgeobiz-flex-nav-tab .sgeobiz-flex-nav-tab-radio:focus + .sgeobiz-flex-nav-tab-label:not(.sgeobiz-no-focus-ring)' => [
						'box-shadow:0 -2px 0 0 {{$color_accent}} inset, 0 0 0 1px {{$color_accent}} inset',
					],
				],
			],
			[
				'id'       => 'sgeobiz-post',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz-ays', 'sgeobiz-title', 'sgeobiz-description', 'sgeobiz-social', 'sgeobiz-canonical', 'sgeobiz-postslugs', 'sgeobiz-termslugs', 'sgeobiz-authorslugs', 'sgeobiz-tabs', 'sgeobiz-tt', 'sgeobiz-utils', 'sgeobiz-ui', 'sgeobiz' ],
				'autoload' => true,
				'name'     => 'post',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizPostL10n',
					'data' => [
						'states' => [
							'isPrivate'       => Data\Post::is_private( $id ),
							'isProtected'     => Data\Post::is_password_protected( $id ),
							'isGutenbergPage' => $is_block_editor, // TODO: Deprecate
							'id'              => $id, // TODO: Deprecate
						],
						'params' => [
							'id'                      => $id,
							'isBlockEditor'           => $is_block_editor,
							'isFront'                 => $is_static_front_page,
							'additionsForcedDisabled' => $additions_forced_disabled,
							'additionsForcedEnabled'  => $additions_forced_enabled,
						],
						'nonces' => [
							'edit_post' => [
								$id => Utils::create_ajax_capability_nonce( 'edit_post', $id ),
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Returns Term scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 * @since 4.2.0 Now properly populates use_generated_archive_prefix() with a \WP_Term object.
	 *
	 * @return array The script params.
	 */
	public static function get_term_edit_scripts() {

		$id       = Query::get_the_real_id();
		$taxonomy = Query::get_current_taxonomy();

		$additions_forced_disabled = (bool) Data\Plugin::get_option( 'title_rem_additions' );

		if ( Meta\Title\Conditions::use_generated_archive_prefix( \get_term( $id, $taxonomy ) ) ) {
			$term_prefix = \sprintf(
				/* translators: %s: Taxonomy singular name. */
				\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
				Taxonomy::get_label( $taxonomy ),
			);
		} else {
			$term_prefix = '';
		}

		return [
			[
				'id'       => 'sgeobiz-term',
				'type'     => 'css',
				'deps'     => [ 'sgeobiz-tt', 'sgeobiz' ],
				'autoload' => true,
				'name'     => 'term',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-term',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz-ays', 'sgeobiz-title', 'sgeobiz-description', 'sgeobiz-social', 'sgeobiz-canonical', 'sgeobiz-termslugs', 'sgeobiz-tt', 'sgeobiz' ],
				'autoload' => true,
				'name'     => 'term',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizTermL10n',
					'data' => [
						'params' => [
							'additionsForcedDisabled' => $additions_forced_disabled,
							'id'                      => $id,
							'taxonomy'                => $taxonomy,
							'termPrefix'              => Utils::decode_entities( $term_prefix ),
						],
						'nonces' => [
							'edit_term' => [
								$id => Utils::create_ajax_capability_nonce( 'edit_term', $id ),
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Returns Gutenberg compatibility scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_gutenberg_compat_scripts() {
		return [
			[
				'id'       => 'sgeobiz-gbc',
				'type'     => 'js',
				'deps'     => [ 'jquery', 'sgeobiz', 'sgeobiz-utils', 'wp-editor', 'wp-data', 'react' ],
				'autoload' => true,
				'name'     => 'gbc',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
		];
	}

	/**
	 * Returns Tabs scripts params.
	 *
	 * @since 4.1.3
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_tabs_scripts() {
		return [
			'id'       => 'sgeobiz-tabs',
			'type'     => 'js',
			'deps'     => [ 'sgeobiz-utils', 'sgeobiz-ui' ],
			'autoload' => true,
			'name'     => 'tabs',
			'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
			'ver'      => \SGEOBIZ_SEO_VERSION,
		];
	}

	/**
	 * Returns Media scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.2 Removed redundant button titles.
	 * @since 5.1.0 Added sgeobiz-media CSS. Added `sgeobizMediaL10n.warning`.
	 *
	 * @return array The script params.
	 */
	public static function get_media_scripts() {
		return [
			[
				'id'       => 'sgeobiz-media',
				'type'     => 'css',
				'deps'     => [],
				'autoload' => true,
				'name'     => 'media',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-media',
				'type'     => 'js',
				'deps'     => [ 'media', 'sgeobiz', 'sgeobiz-utils', 'sgeobiz-tt' ],
				'autoload' => true,
				'name'     => 'media',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizMediaL10n',
					'data' => [
						'labels'  => [
							'social' => [
								'imgSelect'      => \esc_attr__( 'Select Image', 'sgeobiz-seo' ),
								'imgSelectTitle' => '',
								'imgChange'      => \esc_attr__( 'Change Image', 'sgeobiz-seo' ),
								'imgRemove'      => \esc_attr__( 'Remove Image', 'sgeobiz-seo' ),
								'imgRemoveTitle' => '',
								'imgFrameTitle'  => \esc_attr_x( 'Select Social Image', 'Frame title', 'sgeobiz-seo' ),
								'imgFrameButton' => \esc_attr__( 'Use this image', 'sgeobiz-seo' ),
							],
							'logo'   => [
								'imgSelect'      => \esc_attr__( 'Select Logo', 'sgeobiz-seo' ),
								'imgSelectTitle' => '',
								'imgChange'      => \esc_attr__( 'Change Logo', 'sgeobiz-seo' ),
								'imgRemove'      => \esc_attr__( 'Remove Logo', 'sgeobiz-seo' ),
								'imgRemoveTitle' => '',
								'imgFrameTitle'  => \esc_attr_x( 'Select Logo', 'Frame title', 'sgeobiz-seo' ),
								'imgFrameButton' => \esc_attr__( 'Use this image', 'sgeobiz-seo' ),
							],
						],
						'warning' => [
							'warnedTypes'    => [
								'social' => [
									// This is only a short list of increasingly common types.
									'webp' => 'image/webp',
									'heic' => 'image/heic',
								],
							],
							'forbiddenTypes' => [
								'all' => [
									// See SGEOBIZ_SEO\Data\Filter\Sanitize::image_details().
									'apng' => 'image/apng',
									'bmp'  => 'image/bmp',
									'ico'  => 'image/x-icon',
									'cur'  => 'image/x-icon',
									'svg'  => 'image/svg+xml',
									'tif'  => 'image/tiff',
									'tiff' => 'image/tiff',
								],
							],
							'i18n'           => [
								'notLoaded'    => \esc_attr__( 'The image file could not be loaded.', 'sgeobiz-seo' ),
								/* translators: %s is the file extension. */
								'extWarned'    => \esc_attr__( 'The file extension "%s" is not supported on all platforms, which could prevent this image from being displayed.', 'sgeobiz-seo' ),
								/* translators: %s is the file extension. */
								'extForbidden' => \esc_attr__( 'The file extension "%s" is not supported. Choose a different file.', 'sgeobiz-seo' ),
							],
						],
						'nonce'   => Utils::create_ajax_capability_nonce( 'upload_files' ),
					],
				],
			],
		];
	}

	/**
	 * Returns Title scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Updated l10n.data.
	 *
	 * @return array The script params.
	 */
	public static function get_title_scripts() {
		return [
			'id'       => 'sgeobiz-title',
			'type'     => 'js',
			'deps'     => [ 'sgeobiz' ],
			'autoload' => true,
			'name'     => 'title',
			'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
			'ver'      => \SGEOBIZ_SEO_VERSION,
			'l10n'     => [
				'name' => 'sgeobizTitleL10n',
				'data' => [
					'states' => [
						'titleSeparator'  => Utils::decode_entities( Meta\Title::get_separator() ),
						'prefixPlacement' => \is_rtl() ? 'after' : 'before',
					],
					'params' => [
						'untitledTitle'  => Utils::decode_entities( Meta\Title::get_untitled_title() ),
						'stripTitleTags' => (bool) Data\Plugin::get_option( 'title_strip_tags' ),
					],
					'i18n'   => [
						// phpcs:ignore WordPress.WP.I18n -- WordPress doesn't have a comment, either.
						'privateTitle'   => Utils::decode_entities( trim( str_replace( '%s', '', \__( 'Private: %s', 'default' ) ) ) ),
						// phpcs:ignore WordPress.WP.I18n -- WordPress doesn't have a comment, either.
						'protectedTitle' => Utils::decode_entities( trim( str_replace( '%s', '', \__( 'Protected: %s', 'default' ) ) ) ),
					],
				],
			],
		];
	}

	/**
	 * Returns Description scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_description_scripts() {
		return [
			'id'       => 'sgeobiz-description',
			'type'     => 'js',
			'deps'     => [ 'sgeobiz' ],
			'autoload' => true,
			'name'     => 'description',
			'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
			'ver'      => \SGEOBIZ_SEO_VERSION,
		];
	}

	/**
	 * Returns Social scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 No longer registers l10n (data).
	 *
	 * @return array The script params.
	 */
	public static function get_social_scripts() {
		return [
			'id'       => 'sgeobiz-social',
			'type'     => 'js',
			'deps'     => [ 'sgeobiz', 'sgeobiz-utils' ],
			'autoload' => true,
			'name'     => 'social',
			'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
			'ver'      => \SGEOBIZ_SEO_VERSION,
		];
	}

	/**
	 * Returns Canonical scripts params.
	 *
	 * @since 5.1.0
	 *
	 * @return array The script params.
	 */
	public static function get_canonical_scripts() {

		global $wp_rewrite;

		$parsed_home_url = Meta\URI\Utils::get_parsed_front_page_url();

		return [
			[
				'id'       => 'sgeobiz-canonical',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz', 'sgeobiz-utils' ],
				'autoload' => true,
				'name'     => 'canonical',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizCanonicalL10n',
					'data' => [
						'params' => [
							'usingPermalinks' => $wp_rewrite->using_permalinks(),
							'rootUrl'         => [
								// We require separate parts for sanitized URL building.
								'scheme' => $parsed_home_url['scheme'] ?? 'http', // placeholder for completeness; we use preferredScheme.
								'host'   => $parsed_home_url['host'] ?? '',
								'port'   => $parsed_home_url['port'] ?? '',
								'path'   => $parsed_home_url['path'] ?? '/',
							],
							'rewrite'         => [
								'code'         => $wp_rewrite->rewritecode,
								'replace'      => $wp_rewrite->rewritereplace,
								'queryReplace' => $wp_rewrite->queryreplace,
							],
							// TEMP: We still have to figure out how to get the right parameters.
							'allowCanonicalURLNotationTracker' => ! Compatibility::get_active_conflicting_plugin_types()['multilingual'],
						],
					],
				],
			],
			[
				'id'       => 'sgeobiz-postslugs',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => false, // Not all screens require this.
				'name'     => 'postslugs',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-termslugs',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => false, // Not all screens require this.
				'name'     => 'termslugs',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-authorslugs',
				'type'     => 'js',
				'deps'     => [],
				'autoload' => false, // Not all screens require this.
				'name'     => 'authorslugs',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
		];
	}

	/**
	 * Returns Primary Term Selection scripts params.
	 *
	 * @since 4.0.0
	 * @since 4.1.0 Now filters out unsupported taxonomies.
	 * @since 5.1.0 Changed the dependencies for pt, because we now use a select field.
	 * @since 5.1.3 Added list edit support.
	 *
	 * @return array The script params.
	 */
	public static function get_primaryterm_scripts() {

		$is_list_edit = Query::is_wp_lists_edit();

		$post_type   = Query::get_admin_post_type();
		$_taxonomies = $post_type ? Taxonomy::get_hierarchical( 'names', $post_type ) : [];
		$taxonomies  = [];

		foreach ( $_taxonomies as $tax ) {
			if ( ! Taxonomy::is_supported( $tax ) ) continue;

			$singular_name   = Taxonomy::get_label( $tax );
			$primary_term_id = Data\Plugin\Post::get_primary_term_id( Query::get_the_real_admin_id(), $tax );

			$taxonomies[ $tax ] = [
				'name'    => $tax,
				'primary' => $primary_term_id, // if 0, it'll use hints from the interface.
				'i18n'    => [
					/* translators: %s = term name */
					'selectPrimary' => \sprintf( \esc_html__( 'Select primary %s', 'sgeobiz-seo' ), $singular_name ),
				],
			];
		}

		if ( $is_list_edit ) {
			$vars = [
				'id'   => 'sgeobiz-pt-le',
				'name' => 'pt-le',
			];
			$deps = [ 'sgeobiz', 'wp-util' ];
		} else {
			// If not list edit, we're in the post editor.
			if ( Query::is_block_editor() ) {
				$vars = [
					'id'   => 'sgeobiz-pt-gb',
					'name' => 'pt-gb',
				];
				$deps = [ 'sgeobiz', 'sgeobiz-ays', 'wp-hooks', 'wp-element', 'wp-components', 'wp-data', 'wp-util' ];
			} else {
				$vars = [
					'id'   => 'sgeobiz-pt',
					'name' => 'pt',
				];
				$deps = [ 'sgeobiz', 'sgeobiz-ays', 'wp-util' ];
			}
		}

		$tmpl_file = $is_list_edit
			? Template::get_view_location( 'templates/list/primary-term-selector' )
			: Template::get_view_location( 'templates/inpost/primary-term-selector' );

		return [
			[
				'id'       => 'sgeobiz-pt',
				'type'     => 'css',
				'deps'     => [ 'sgeobiz-tt' ],
				'autoload' => true,
				'name'     => 'pt',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => $vars['id'],
				'type'     => 'js',
				'deps'     => $deps,
				'autoload' => true,
				'name'     => $vars['name'],
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizPTL10n',
					'data' => [
						'taxonomies' => $taxonomies,
					],
				],
				'tmpl'     => [
					'file' => $tmpl_file,
				],
			],
		];
	}

	/**
	 * Returns the Pixel and Character counter script params.
	 *
	 * @since 4.0.0
	 *
	 * @return array The script params.
	 */
	public static function get_counter_scripts() {
		return [
			[
				'id'       => 'sgeobiz-c',
				'type'     => 'css',
				'deps'     => [ 'sgeobiz-tt' ],
				'autoload' => true,
				'name'     => 'sgeobizc',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/css/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
			],
			[
				'id'       => 'sgeobiz-c',
				'type'     => 'js',
				'deps'     => [ 'sgeobiz-tt', 'sgeobiz' ],
				'autoload' => true,
				'name'     => 'c',
				'base'     => \SGEOBIZ_SEO_DIR_URL . 'lib/js/',
				'ver'      => \SGEOBIZ_SEO_VERSION,
				'l10n'     => [
					'name' => 'sgeobizCL10n',
					'data' => [
						'guidelines'  => Guidelines::get_text_size_guidelines(),
						'counterType' => \absint( Data\Plugin\User::get_meta_item( 'counter_type' ) ),
						'i18n'        => [
							'guidelines' => Guidelines::get_text_size_guidelines_i18n(),
							/* translators: Pixel counter. 1: number (value), 2: number (guideline) */
							'pixelsUsed' => \esc_attr__( '%1$d out of %2$d pixels are used.', 'sgeobiz-seo' ),
						],
					],
				],
			],
		];
	}
}

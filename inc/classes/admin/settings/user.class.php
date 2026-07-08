<?php
/**
 * @package SGEOBIZ_SEO\Classes\Admin\Settings\User
 * @subpackage SGEOBIZ_SEO\Admin\Edit\User
 */

namespace SGEOBIZ_SEO\Admin\Settings;

\defined( 'SGEOBIZ_SEO_PRESENT' ) or die;

use SGEOBIZ_SEO\{
	Data,
	Helper\Template,
};

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2021 - 2025 SGEOBIZ (https://rasyiqi-code.github.io/SGEOBIZ/)
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
 * Prepares the User Settings view interface.
 *
 * @since 4.1.4
 * @since 5.0.0 1. Renamed from `UserSettings` to `User`.
 *              2. Moved to `\SGEOBIZ_SEO\Admin\Settings`.
 * @access private
 */
final class User {

	/**
	 * Prepares the user setting fields.
	 *
	 * @hook show_user_profile 0
	 * @hook edit_user_profile 0
	 * @since 4.1.4
	 * @since 5.0.0 1. Now asserts if user has capability on any multisite network's blog.
	 *              2. Renamed from `_prepare_setting_fields`.
	 *
	 * @param \WP_User $user WP_User object.
	 */
	public static function prepare_setting_fields( $user ) {

		if ( ! Data\User::user_has_author_info_cap_on_network( $user ) )
			return;

		self::output_setting_fields( $user );
	}

	/**
	 * Outputs user profile fields.
	 *
	 * @since 5.0.0
	 *
	 * @param \WP_User $user WP_User object.
	 */
	private static function output_setting_fields( $user ) {

		\wp_nonce_field(
			Data\Admin\User::SAVE_NONCES['user-edit']['action'],
			Data\Admin\User::SAVE_NONCES['user-edit']['name'],
		);

		/**
		 * @since 4.1.4
		 */
		\do_action( 'sgeobiz_seo_before_author_fields' );

		Template::output_view( 'profile/settings', $user );

		/**
		 * @since 4.1.4
		 */
		\do_action( 'sgeobiz_seo_after_author_fields' );
	}
}

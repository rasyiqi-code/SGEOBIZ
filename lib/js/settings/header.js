/**
 * This file holds SGEOBIZ SEO plugin's JS code for the SEO Settings page.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author SGEOBIZ <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

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

'use strict';

/**
 * Holds sgeobizSettings values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 * TODO split up this file?
 *
 * @constructor
 * @param {!jQuery} $ jQuery object.
 */
window.sgeobizSettings = function( $ ) {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|Boolean|null} l10n Localized strings
	 */
	const l10n = sgeobizSettingsL10n;


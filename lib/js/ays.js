/**
 * This file holds SGEOBIZ SEO plugin's JS code for Are You Sure notifications.
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
 * Holds sgeobizAys values in an object to avoid polluting global namespace.
 *
 * @since 4.0.0
 *
 * @constructor
 */
window.sgeobizAys = function () {

	/**
	 * Data property injected by WordPress l10n handler.
	 *
	 * @since 4.0.0
	 * @access public
	 * @type {(Object<string,*>)|Boolean|null} l10n Localized strings
	 */
	const l10n = sgeobizAysL10n;

	/**
	 * Handles the settings state.
	 *
	 * @since 4.0.0
	 * @access private
	 * @type {Boolean}
	 */
	let _settingsChanged = false;

	/**
	 * Determines the initialization state.
	 *
	 * @since 4.0.0
	 * @access private
	 * @type {Boolean}
	 */
	let _loadedListeners = false;

	/**
	 * Holds the registered change listeners map.
	 *
	 * @since 4.1.1
	 * @access private
	 * @type {Map<Element,string>} string: event Type attached.
	 */
	const _registeredChangeListeners = new Map();

	/**
	 * Returns changed state.
	 *
	 * @since 4.0.0
	 * @since 5.1.0 Deprecated.
	 * @deprecated
	 * @access public
	 *
	 * @return {Boolean}
	 */
	function getChangedState() {
		sgeobiz.deprecatedFunc(
			'sgeobizAys.getChangedState()',
			'5.1.0',
			'sgeobizAys.areSettingsChanged()',
		);
		return areSettingsChanged();
	}

	/**
	 * Returns changed state.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @return {Boolean}
	 */
	function areSettingsChanged() {
		return _settingsChanged;
	}

	/**
	 * Transforms elements and queries to an array from nodelists.
	 *
	 * @since 4.1.1
	 * @access private
	 *
	 * @param {Element|Document|string|string[]} elements
	 * @return {(Element|Document)[]}
	 */
	function _getNodeArray( elements ) {
		return ( elements instanceof Element || elements instanceof Document )
			? [ elements ]
			: [ ...document.querySelectorAll( Array.isArray( elements ) ? elements.join( ', ' ) : elements ) ];
	}

	/**
	 * Registers changed state.
	 *
	 * @since 4.0.0
	 * @access public
	 */
	function registerChange() {
		_settingsChanged = true;
	}

	/**
	 * Deregisters changed state.
	 *
	 * @since 4.0.0
	 * @access public
	 */
	function deregisterChange() {
		_settingsChanged = false;
	}

	// High timeout. Resets should only happen during failures or changing document states; the latter of which is slow.
	const reloadDefaultListenersDebouncer = sgeobizUtils.debounce( () => reloadDefaultListeners(), 1000 );
	/**
	 * Resets all listeners and deregisters changes.
	 *
	 * Deregistration only happens after first initialization.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 debounced reloading of the listeners.
	 * @access public
	 */
	function reset() {
		deregisterChange();

		_loadedListeners && reloadDefaultListenersDebouncer();
	}

	/**
	 * Unloads the change listener, a saving action is expected that causes the user to unload the page.
	 * Also tries to reregister it after 1 second, may other scripts interrupt the unloading.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _triggerUnload() {

		const wereSettingsChanged = areSettingsChanged();

		deregisterChange();

		setTimeout( () => {
			reset();
			wereSettingsChanged && registerChange();
		}, 1000 );
	}

	/**
	 * Triggers registered change listener. Also exempt future change listeners,
	 * for once we registered a change, none is required thereafter.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now only passes trusted events.
	 * @access private
	 *
	 * @param {event} event
	 */
	function _triggerChange( event ) {
		if ( ! event.isTrusted ) return;
		registerChange();
		_exemptFutureChanges();
	}

	/**
	 * Exempts listeners to reduce CPU usage.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now defuncts all registered change listers without relying on event details.
	 * @access private
	 */
	function _exemptFutureChanges() {
		_registeredChangeListeners.forEach( ( eventType, element ) => {
			element.removeEventListener( eventType, _triggerChange );
		} );
		_registeredChangeListeners.clear();
	}

	/**
	 * Registers change listener.
	 * Should be used on input elements that may be saved.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 1. No longer uses jQuery for the events.
	 *              2. Now filters the exceptions in place.
	 * @access public
	 *
	 * @param {(Element|string|string[])} elements  The elements to register.
	 * @param {string}                    eventType The event type to listen to.
	 */
	function registerChangeListener( elements, eventType ) {
		_getNodeArray( elements )
			.filter( el => ! el.classList.contains( 'sgeobiz-input-not-saved' ) && 'hidden' !== el.type )
			.forEach( el => {
				_registeredChangeListeners.set( el, eventType );
				el.addEventListener( eventType, _triggerChange )
			} );
	}

	/**
	 * Registers force reset listener.
	 * Should be used on input elements that save the document, but doesn't reload the document.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param {(Element|string|string[])} elements  The elements to register.
	 * @param {string}                    eventType The event type to listen to.
	 */
	function registerResetListener( elements, eventType ) {
		_getNodeArray( elements ).forEach( el => {
			el.addEventListener( eventType, reset );
		} );
	}

	/**
	 * Registers unload listener.
	 * Should be used on input elements that save the document, and also reloads the document.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param {(Element|string|string[])} elements  The elements to register.
	 * @param {string}                    eventType The event type to listen to.
	 */
	function registerUnloadListener( elements, eventType ) {
		_getNodeArray( elements ).forEach( el => {
			el.addEventListener( eventType, _triggerUnload )
		} );
	}

	/**
	 * Registers default listeners.
	 * Also triggers an event, so other developers may consistently add their listeners.
	 *
	 * @since 4.0.0
	 * @access public
	 */
	function reloadDefaultListeners() {

		_loadedListeners = false;

		// Mouse input
		registerChangeListener(
			[
				'.sgeobiz-metaboxes input[type=radio][name]',
				'.sgeobiz-metaboxes input[type=checkbox][name]',
				'.sgeobiz-metaboxes select[name]',
				'#sgeobiz-inpost-box .inside input[type=radio][name]',
				'#sgeobiz-inpost-box .inside input[type=checkbox][name]',
				'#sgeobiz-inpost-box .inside select[name]',
				'.sgeobiz-term-meta input[type=radio][name]',
				'.sgeobiz-term-meta input[type=checkbox][name]',
				'.sgeobiz-term-meta select[name]',
			],
			'change',
		);

		// Text input
		registerChangeListener(
			[
				'.sgeobiz-metaboxes input:not([type=radio]):not([type=checkbox])[name]',
				'.sgeobiz-metaboxes textarea[name]',
				'#sgeobiz-inpost-box .inside input:not([type=radio]):not([type=checkbox])[name]',
				'#sgeobiz-inpost-box .inside textarea[name]',
				'.sgeobiz-term-meta input:not([type=radio]):not([type=checkbox])[name]',
				'.sgeobiz-term-meta textarea[name]',
			],
			'input',
		);

		// Non-redirect, Gutenberg save action.
		registerResetListener(
			document,
			'sgeobiz-gutenberg-onsave-completed',
		);

		// Redirect-save actions.
		registerUnloadListener(
			[
				'.sgeobiz-metaboxes input[type=submit]',
				'#publishing-action input[type=submit]',
				'#save-action input[type=submit]',
				'a.submitdelete',
				'.edit-tag-actions input[type=submit]',
				'.edit-tag-actions .delete',
			],
			'click',
		);

		document.dispatchEvent( new CustomEvent( 'sgeobiz-registered-ays-listeners' ) );

		_loadedListeners = true;
	}

	/**
	 * Alerts the user that there are unsaved changes before unloading the tab/window.
	 *
	 * @since 4.1.0
	 * @access private
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/WindowEventHandlers/onbeforeunload
	 *
	 * @param {event} event
	 */
	function _alertUserBeforeunload( event ) {
		if ( areSettingsChanged() ) {
			// Every other browser:
			event.preventDefault();
			// Chromium requirement:
			event.returnValue = l10n.i18n['saveAlert'];
		}
	}

	/**
	 * Initializes AYS scripts on sgeobiz-interactive.
	 *
	 * @since 4.0.0
	 * @since 4.1.1 Now binds to the sgeobiz-interactive event, instead of sgeobiz-ready.
	 * @access private
	 */
	function _readyAys() {
		// Initialise form field changing flag.
		reloadDefaultListeners();

		areSettingsChanged() && console.warn( 'sgeobizAys: Doing it wrong! Settings were changed prior ready-state. Debug me.' );
		// Undo accidental load-sequence state changes.
		deregisterChange();

		// Alert onbeforeunload
		window.addEventListener( 'beforeunload', _alertUserBeforeunload );
	}

	return Object.assign( {
		/**
		 * Initialises all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
		 * @access protected
		 */
		load: () => {
			document.body.addEventListener( 'sgeobiz-interactive', _readyAys );
		},
	}, {
		reset,
		getChangedState,
		areSettingsChanged,
		registerChange,
		deregisterChange,
		registerChangeListener,
		registerResetListener,
		registerUnloadListener,
		reloadDefaultListeners,
	}, {
		l10n,
	} );
}();
window.sgeobizAys.load();

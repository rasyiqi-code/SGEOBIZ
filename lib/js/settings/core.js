	/**
	 * Returns settings ID.
	 *
	 * @since 4.1.1
	 * @access private
	 *
	 * @param {string} name
	 * @return {string} The full settings ID/name.
	 */
	function _getSettingsId( name ) {
		return `sgeobiz-site-settings[${name}]`;
	}

	/**
	 * Clone of sgeobiz.dispatchAtInteractive.
	 * Eases programming, trims minified script size.
	 *
	 * @since 4.2.0
	 * @access private
	 * @ignore
	 *
	 * @function
	 * @param {Element} element   The element to dispatch the event upon.
	 * @param {string}  eventName The event name to trigger. Mustn't be custom.
	 */
	const _dispatchAtInteractive = sgeobiz.dispatchAtInteractive;


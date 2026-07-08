	function _loadSettings() {
		// One is not reliant on the other; this way, if one crashes, the rest still works.
		[
			_initSubmit,

			_initGeneralSettings,

			_initTitleSettings,

			_initHomeGeneralListeners,
			_initHomeTitleSettings,
			_initHomeDescriptionSettings,
			_initHomeSocialSettings,
			_initHomeVisibilitySettings,

			_initPtaSettings,

			_initSocialSettings,

			_initSchemaSettings,

			_initRobotsInputs,
			_initRobotsSupport,

			_initWebmastersInputs,

			_initSitemapInputs,

			_initColorPicker,
		].forEach( fn => {
			try {
				fn();
			} catch ( error ) {
				console.error( `Error in ${fn.name}:`, error );
			}
		} );
	}

	/**
	 * Sets a class to the active element which helps excluding focus rings.
	 *
	 * @since 4.0.0
	 * @since 4.1.3 Now offloaded to sgeobizTabs.
	 * @access private
	 */
	function _initTabs() {
		sgeobizTabs.initStack(
			'sgeobizSettings',
			{
				tabToggledEvent: new CustomEvent( 'sgeobiz-tab-toggled' ),
				HTMLClasses:     {
					wrapper:          'sgeobiz-nav-tab-wrapper',
					tabRadio:         'sgeobiz-nav-tab-radio',
					tabLabel:         'sgeobiz-nav-tab-label',
					activeTab:        'sgeobiz-nav-tab-active',
					activeTabContent: 'sgeobiz-nav-tab-content-active',
				},
				fixHistory:      true, // false for flex? Doesn't seem like it was?
			}
		);
	}

	return Object.assign( {
		/**
		 * Initializes all aspects of the scripts.
		 * You shouldn't call this.
		 *
		 * @since 4.0.0
		 * @since 4.0.3 Now also displaces notice-info.
		 * @access protected
		 *
		 * @function
		 */
		load: () => {
			// Execute this ASAP, to prevent late layout shifting. Use same anchor as core--so to prevent subsequent movement.
			const headerEnd = document.querySelector( '.wp-header-end' );
			document.querySelectorAll(
				'div.updated, div.error, div.notice, .notice-error, .notice-warning, .notice-info'
			).forEach( el => { headerEnd.insertAdjacentElement( 'afterend', el ) } );

			document.body.addEventListener( 'sgeobiz-onload', _loadSettings );

			// Initializes tabs early; we rely a fallback event that sgeobiz-onload/sgeobiz-ready uses there.
			_initTabs();
		},
	}, {
		l10n,
	} );
}( jQuery );
window.sgeobizSettings.load();


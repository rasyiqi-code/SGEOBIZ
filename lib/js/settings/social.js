	function _initSocialSettings() {

		const socialTitleRemoveAdditions = document.getElementById( _getSettingsId( 'social_title_rem_additions' ) );
		/**
		 * Changes the useSocialTagline state for dynamic social-title-placeholder updates.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateSocialAdditions = event => {
			if ( event.target.checked ) {
				sgeobizSocial.updateStateAll( 'addAdditions', false );
			} else {
				sgeobizSocial.updateStateAll( 'addAdditions', true );
			}
		}
		if ( socialTitleRemoveAdditions ) {
			socialTitleRemoveAdditions.addEventListener( 'change', updateSocialAdditions );
			_dispatchAtInteractive( socialTitleRemoveAdditions, 'change' );
		}

		const ogTagsToggle = document.getElementById( _getSettingsId( 'og_tags' ) );
		/**
		 * Hides Open Graph fields if Open Graph is disabled.
		 *
		 * @function
		 * @param {Event} event
		 */
		const displayOgFields = event => {
			document.getElementById( 'multi_og_image_wrapper' )
				?.classList
				.toggle( 'hidden', ! event.target.checked );
		}
		if ( ogTagsToggle ) {
			ogTagsToggle.addEventListener( 'change', displayOgFields );
			_dispatchAtInteractive( ogTagsToggle, 'change' );
		}

		/**
		 * Changes the tabs visibility and selectability during option toggles.
		 *
		 * @function
 		 * @param {{id:string,tab:string}} toggleData
		 */
		const registerTagToggle = toggleData => {

			if ( ! toggleData.id ) return;

			const toggle = document.getElementById( _getSettingsId( toggleData.id ) );
			/**
			 * @function
			 * @param {Event} event
			 */
			const hideDisableTab = event => {
				sgeobizTabs.toggleTab( 'sgeobizSettings', `sgeobiz-social-tab-${toggleData.tab}`, event.target.checked );
			}

			if ( toggle ) {
				toggle.addEventListener( 'change', hideDisableTab );
				_dispatchAtInteractive( toggle, 'change' );
			}
		};
		[
			{
				id:  'og_tags', // option ID.
				tab: 'postdates',
			},
			{
				id:  'facebook_tags',
				tab: 'facebook',
			},
			{
				id:  'twitter_tags',
				tab: 'twitter',
			},
			{
				id:  'oembed_scripts',
				tab: 'oembed',
			},
		].forEach( registerTagToggle );

		const toggleCheckRegistry = new Set();
		/**
		 * Changes the settings visibility and selectability during all option toggles.
		 * @function
		 * @param {Event} event
		 */
		const checkAllDisabled = event => {
			if ( event.target.checked ) {
				toggleCheckRegistry.add( event.target.name );
			} else {
				toggleCheckRegistry.delete( event.target.name );
			}

			document.getElementById( 'sgeobiz-social-settings-wrapper' )
				?.classList
				.toggle( 'hidden', ! toggleCheckRegistry.size );
		}
		[ 'og_tags', 'facebook_tags', 'twitter_tags', 'oembed_scripts' ].forEach( id => {
			const toggle = document.getElementById( _getSettingsId( id ) );
			toggle.addEventListener( 'change', checkAllDisabled );
			_dispatchAtInteractive( toggle, 'change' );
		} );

		/**
		 * Emits hooks for when the Twitter Card is toggled.
		 * @function
		 * @param {Event} event
		 */
		const dispatchCardToggleEvent = event => {
			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-update-twitter-card-type',
				{
					detail: {
						cardType: event.target.value,
					}
				}
			) );
		}
		document.querySelectorAll( '#sgeobiz-twitter-cards input' ).forEach( el => {
			el.addEventListener( 'change', dispatchCardToggleEvent );
			if ( el.checked )
				_dispatchAtInteractive( el, 'change' );
		} );
	}

	/**
	 * Initializes Schema settings inputs.
	 *
	 * @since 5.0.0
	 * @access private
	 */
	function _initSchemaSettings() {

		const sdToggle = document.getElementById( _getSettingsId( 'ld_json_enabled' ) );
		/**
		 * @function
		 * @param {Event} event
		 */
		const toggleSettingsDisplay = event => {
			document.getElementById( 'sgeobiz-advanced-structured-data-settings-wrapper' )
				?.classList
				.toggle( 'hidden', ! event.target.checked );

			togglePresenceTab();
		}
		if ( sdToggle ) {
			sdToggle.addEventListener( 'change', toggleSettingsDisplay );
			_dispatchAtInteractive( sdToggle, 'change' );
		}

		const presenceTab = {
			id:  'knowledge_output', // option ID.
			tab: 'presence',
		};
		const presenceToggle   = document.getElementById( _getSettingsId( presenceTab.id ) ),
			  presenceTabRadio = document.getElementById( `sgeobiz-schema-tab-${presenceTab.tab}` );
		const presenceTabLabel = document.getElementById( 'schema-tabs-wrapper' )
			?.querySelector( `[for=sgeobiz-schema-tab-${presenceTab.tab}]` );

		/**
		 * @function
		 */
		const togglePresenceTab = () => {
			let show = sdToggle?.checked && presenceToggle?.checked;

			presenceTabLabel?.classList.toggle( 'hidden', ! show );

			show
				? presenceTabRadio?.removeAttribute( 'disabled' )
				: presenceTabRadio?.setAttribute( 'disabled', '' );
		}
		if ( presenceToggle ) {
			presenceToggle.addEventListener( 'change', togglePresenceTab );
			togglePresenceTab( presenceToggle, 'change' );
		}

		const knowledgeTypeSelect = document.getElementById( _getSettingsId( 'knowledge_type' ) );
		/**
		 * @function
		 * @param {Event} event
		 */
		const toggleKnowledgeType = event => {
			document.getElementById( 'sgeobiz-logo-structured-data-settings-wrapper' )
				?.classList
				.toggle( 'hidden', event.target.value === 'person' );
		}
		if ( knowledgeTypeSelect ) {
			knowledgeTypeSelect.addEventListener( 'change', toggleKnowledgeType );
			_dispatchAtInteractive( knowledgeTypeSelect, 'change' );
		}

		const logoToggle = document.getElementById( _getSettingsId( 'knowledge_logo' ) );
		/**
		 * @function
		 * @param {Event} event
		 */
		const toggleDisplayLogo = event => {
			document.getElementById( 'sgeobiz-logo-upload-structured-data-settings-wrapper' )
				?.classList
				.toggle( 'hidden', ! event.target.checked );
		}
		if ( logoToggle ) {
			logoToggle.addEventListener( 'change', toggleDisplayLogo );
			_dispatchAtInteractive( logoToggle, 'change' );
		}
	}

	/**
	 * Initializes Robots' meta input.
	 *
	 * @since 4.0.2
	 * @since 4.1.1 Now adds taxonomy warnings.
	 * @access private
	 */

	function _initWebmastersInputs() {

		const webmasterNodes = [
			"google_verification",
			"bing_verification",
			"yandex_verification",
			"baidu_verification",
			"pint_verification",
		].map( name => document.getElementById( _getSettingsId( name ) ) );

		/**
		 * @function
		 * @param {Event} event
		 */
		const trimScript = event => {
			let val = event.clipboardData && event.clipboardData.getData( 'text' ) || '';

			if ( val ) {
				// Extrude tag paste's content value and set that as a value.
				let match = /<meta\b[^>]+?\bcontent=(["'])?([^"'>\s]+)\1?[^>]*?>/i.exec( val );
				if ( match?.[2]?.length ) {
					event.stopPropagation();
					event.preventDefault(); // Prevents save listener.. TODO why?
					event.target.value = match[2];
					// Tell change:
					sgeobizAys.registerChange();
				}
			}
		}
		webmasterNodes.forEach( el => el.addEventListener( 'paste', trimScript ) );
	}

	/**
	 * Initializes Sitemap input.
	 *
	 * @since 5.0.5
	 * @access private
	 */
	function _initSitemapInputs() {

		const optimizedSitemapsToggle = document.getElementById( _getSettingsId( 'sitemaps_output' ) ),
			  cacheSitemapsToggle     = document.getElementById( _getSettingsId( 'cache_sitemap' ) );
		/**
		 * @function
		 * @param {Event} event
		 */
		const updateSocialAdditions = event => {

			const sitemapsEnabled = !! event.target.checked;

			sgeobizTabs.toggleTab( 'sgeobizSettings', 'sgeobiz-sitemaps-tab-style', sitemapsEnabled );

			document.getElementById( 'sgeobiz-sitemap-transient-cache-settings' )
				?.classList.toggle( 'hidden', ! sitemapsEnabled );
		}
		if ( optimizedSitemapsToggle ) {
			optimizedSitemapsToggle.addEventListener( 'change', updateSocialAdditions );
			_dispatchAtInteractive( optimizedSitemapsToggle, 'change' );
		}

		const toggleCheckRegistry = new Map();
		/**
		 * Changes the settings visibility and selectability during all option toggles.
		 * @function
		 * @param {Event} event
		 */
		const checkAllEnabled = event => {
			const prerenderingSettings = document.getElementById( 'sgeobiz-sitemap-prerendering-settings' );

			toggleCheckRegistry.set( event.target.name, !! event.target.checked );

			for ( const val of toggleCheckRegistry.values() ) {
				if ( ! val ) {
					prerenderingSettings?.classList.add( 'hidden', ! toggleCheckRegistry.size );
					return;
				}
			}

			prerenderingSettings?.classList.remove( 'hidden' );
		}
		// optimizedSitemapsToggle is a master toggle here -- though, since there's only two... shrug.
		[ optimizedSitemapsToggle, cacheSitemapsToggle ].forEach( toggle => {
			if ( toggle ) {
				toggle.addEventListener( 'change', checkAllEnabled );
				_dispatchAtInteractive( toggle, 'change' );
			}
		} );
	}

	/**
	 * Initializes settings scripts on SGEOBIZ-load.
	 *
	 * @since 4.0.0
	 * @since 5.1.0 Added error handling.
	 * @access private
	 */

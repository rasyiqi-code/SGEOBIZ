	/**
	 * Initializes the settings page.
	 *
	 * @since 5.1.0
	 * @access private
	 */
	function _initSubmit() {

		const form = document.getElementById( 'sgeobiz-settings' );

		// How?
		if ( ! form ) return;

		const submitButtons = form.querySelectorAll( '[name=submit]' );
		// Prevent double-submit.
		form.addEventListener(
			'submit',
			() => {
				submitButtons.forEach( el => el.disabled = true );
				setTimeout( () => submitButtons.forEach( el => el.disabled = false ), 3000 );
			},
		);
	}

	/**
	 * Initializes input helpers for the General Settings.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initGeneralSettings() {

		/**
		 * Triggers displaying/hiding of character counters on the settings page.
		 *
		 * @since 4.1.1
		 * @access private
		 *
		 * @function
		 * @param {Event} event
		 */
		const toggleCharCounterDisplay = event => {
			document.querySelectorAll( '.sgeobiz-counter-wrap' ).forEach( el => {
				el.style.display = event.target.checked ? '' : 'none';
			} );
			event.target.checked && sgeobizC?.triggerCounterUpdate();
		}
		document.getElementById( _getSettingsId( 'display_character_counter' ) )
			?.addEventListener( 'click', toggleCharCounterDisplay );

		/**
		 * Triggers displaying/hiding of pixel counters on the settings page.
		 *
		 * @since 4.0.0
		 * @access private
		 *
		 * @function
		 * @param {Event} event
		 */
		const togglePixelCounterDisplay = event => {
			document.querySelectorAll( '.sgeobiz-pixel-counter-wrap' ).forEach( el => {
				el.style.display = event.target.checked ? '' : 'none';
			} );
			event.target.checked && sgeobizC?.triggerCounterUpdate();
		}
		document.getElementById( _getSettingsId( 'display_pixel_counter' ) )
			?.addEventListener( 'click', togglePixelCounterDisplay );

		/**
		 * Emits a canonical URL scheme change to rewrite canonical URLs on the page.
		 *
		 * @since 5.1.0
		 * @access private
		 *
		 * @function
		 * @param {Event} event
		 */
		const dispatchCanonicalSchemeUpdate = event => {

			const selected = event.target.value,
				  values   = JSON.parse( event.target.dataset?.values || 0 ) || []; // not cached OK.

			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-canonical-scheme-changed',
				{
					detail: {
						scheme: values[ selected ] ?? selected,
					},
				},
			) );
		}
		document.getElementById( _getSettingsId( 'canonical_scheme' ) )
			?.addEventListener( 'change', dispatchCanonicalSchemeUpdate );

		const excludedPostTypes     = new Set(), // Excluded post types.
			  excludedTaxonomies    = new Set(), // Excluded taxonomies.
			  excludedPtTaxonomies  = new Set(), // Excluded taxonomies via post types.
			  excludedTaxonomiesAll = new Set(); // Combined E_Taxonomies + E_PtTaxonomies
		const validateTaxonomyState = () => {
			// We want to show that the taxonomy is excluded, but make that auto-reversible, and somehow still enactable?
			let taxEntries    = document.querySelectorAll( '.sgeobiz-excluded-taxonomies' ),
				triggerChange = false;

			taxEntries.forEach( element => {
				// get taxonomy from last [] entry.
				const taxonomy = element.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );

				const taxPostTypes = JSON.parse( element.dataset?.postTypes || 0 ) || [],
					  isDisabled   = taxPostTypes && taxPostTypes.every( postType => excludedPostTypes.has( postType ) );

				if ( isDisabled ) {
					if ( ! excludedPtTaxonomies.has( taxonomy ) ) {
						// Newly disabled, trigger change.
						triggerChange = true;
					}
					// Filter it out to prevent duplicates. Redundant?
					excludedPtTaxonomies.add( taxonomy );
				} else {
					if ( excludedPtTaxonomies.has( taxonomy ) ) {
						excludedPtTaxonomies.delete( taxonomy );
						// Enabled again, was disabled. Trigger change.
						triggerChange = true;
					}
				}
				refreshTaxonomies();
				triggerChange && dispatchTaxonomySupportChangedEvent( taxonomy );
			} );
		}
		document.body.addEventListener( 'sgeobiz-post-type-support-changed', validateTaxonomyState );

		const refreshTaxonomies = () => {
			// Refresh and concatenate.
			excludedTaxonomiesAll.clear();
			excludedTaxonomies.forEach( taxonomy => excludedTaxonomiesAll.add( taxonomy ) );
			excludedPtTaxonomies.forEach( taxonomy => excludedTaxonomiesAll.add( taxonomy ) );
		}
		const dispatchTaxonomySupportChangedEvent = taxonomy => {
			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-taxonomy-support-changed',
				{
					detail: {
						taxonomy,
						set:    excludedTaxonomies,
						setPt:  excludedPtTaxonomies,
						setAll: excludedTaxonomiesAll,
					}
				}
			) );
		}
		const dispatchPostTypeSupportChangedEvent = postType => {
			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-post-type-support-changed',
				{
					detail: {
						postType,
						set: excludedPostTypes,
					}
				}
			) );
		}

		// This prevents notice-removal checks before they're added.
		let init = false;
		const checkDisabledPT = event => {

			if ( ! event.target.name ) return;

			// get post type from last [] entry.
			let postType = event.target.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );
			if ( event.target.checked ) {
				excludedPostTypes.add( postType );
				dispatchPostTypeSupportChangedEvent( postType );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					excludedPostTypes.delete( postType );
					dispatchPostTypeSupportChangedEvent( postType );
				}
			}
		}
		const checkDisabledTaxonomy = event => {

			if ( ! event.target.name ) return;

			// get taxonomy from last [] entry.
			let taxonomy = event.target.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );
			if ( event.target.checked ) {
				excludedTaxonomies.add( taxonomy );
				refreshTaxonomies();
				dispatchTaxonomySupportChangedEvent( taxonomy );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					excludedTaxonomies.delete( taxonomy );
					refreshTaxonomies();
					dispatchTaxonomySupportChangedEvent( taxonomy );
				}
			}
		}
		document.querySelectorAll( '.sgeobiz-excluded-post-types' ).forEach( el => {
			el.addEventListener( 'change', checkDisabledPT );
			_dispatchAtInteractive( el, 'change' );
		} );
		document.querySelectorAll( '.sgeobiz-excluded-taxonomies' ).forEach( el => {
			el.addEventListener( 'change', checkDisabledTaxonomy );
			_dispatchAtInteractive( el, 'change' );
		} );

		init = true;
	}

	/**
	 * Enables wpColorPicker on input.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initColorPicker() {

		document.querySelectorAll( '.sgeobiz-color-picker' ).forEach( element => {
			// We might as well switch to jQuery instantly since wpColorPicker added its prototype to it.
			let $input       = $( element ),
				currentColor = '',
				defaultColor = $input.data( 'sgeobiz-default-color' );

			$input.wpColorPicker( {
				defaultColor: defaultColor,
				width: 238,
				change: () => {
					currentColor = $input.wpColorPicker( 'color' );

					if ( '' === currentColor )
						currentColor = defaultColor;

					element.value = defaultColor;

					sgeobizAys.registerChange();
				},
				clear: () => {
					// We can't loop this to the change method, as it's not reliable (due to deferring?).
					// So, we just fill it in for the user.
					if ( defaultColor.length ) {
						element.value = defaultColor;
						$input.closest( '.wp-picker-container' ).find( '.wp-color-result' ).css( 'backgroundColor', defaultColor );
					}
					sgeobizAys.registerChange();
				},
				palettes: false,
			} );
		} );
	}

	/**
	 * Initializes Titles' meta input.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 Fixed the additionsToggle getter.
	 * @access private
	 */
	function _initTitleSettings() {

		const additionsToggle            = document.getElementById( _getSettingsId( 'title_rem_additions' ) ),
			  socialAdditionsToggle      = document.getElementById( _getSettingsId( 'social_title_rem_additions' ) ),
			  titleAdditionsHelpTemplate = wp.template( 'sgeobiz-disabled-title-additions-help-social' )();

		/**
		 * Toggles example on Left/Right selection of global title options.
		 *
		 * @function
		 */
		const toggleAdditionsDisplayExample = event => {
			if ( event.target.checked ) {
				document.querySelectorAll( '.sgeobiz-title-additions-js' ).forEach( el => el.style.display = 'none' );
				if ( socialAdditionsToggle ) {
					socialAdditionsToggle.dataset.disabledWarning = 1;
					socialAdditionsToggle.closest( 'label' ).insertAdjacentHTML( 'beforeend', titleAdditionsHelpTemplate );
					sgeobizTT.triggerReset();
				}
			} else {
				document.querySelectorAll( '.sgeobiz-title-additions-js' ).forEach( el => el.style.display = 'inline' );
				// 'sgeobiz-title-additions-warning-social' is defined at `../inc/views/templates/settings/settings.php`
				if ( socialAdditionsToggle?.dataset.disabledWarning )
					socialAdditionsToggle.closest( 'label' ).querySelector( '.sgeobiz-title-additions-warning-social' )?.remove();
			}

			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-update-title-rem-additions',
				{
					detail: {
						removeAdditions: !! event.target.checked,
					}
				}
			) );
		}
		if ( additionsToggle ) {
			additionsToggle.addEventListener( 'change', toggleAdditionsDisplayExample );
			_dispatchAtInteractive( additionsToggle, 'change' );
		}

		/**
		 * Toggles title additions location for the Title examples.
		 * There are two elements, rather than one. One is hidden by default.
		 *
		 * @function
		 * @param {Event} event
		 */
		const toggleAdditionsLocationExample = event => {

			let value;

			document.getElementsByName( event.target.name ).forEach( el => {
				if ( el.checked )
					value = el.value;
			} );

			const showLeft      = 'left' === value,
				  locationClass = 'sgeobiz-title-additions-location-hidden';

			document.querySelectorAll( '.sgeobiz-title-additions-example-left' ).forEach( el => {
				el.classList.toggle( locationClass, ! showLeft );
				el.classList.remove( 'hidden' );
			} );
			document.querySelectorAll( '.sgeobiz-title-additions-example-right' ).forEach( el => {
				el.classList.toggle( locationClass, showLeft );
				el.classList.remove( 'hidden' );
			} );

			sgeobizTitle.updateStateAll(
				'additionPlacement',
				showLeft ? 'before' : 'after',
				_getSettingsId( 'homepage_title' ),
			);
		}
		document.querySelectorAll( '#sgeobiz-title-location input' ).forEach( el => {
			el.addEventListener( 'change', toggleAdditionsLocationExample );
			if ( el.checked )
				_dispatchAtInteractive( el, 'change' );
		} );

		/**
		 * Toggles title prefixes for the Prefix Title example.
		 *
		 * @function
		 * @param {Event} event
		 */
		const adjustPrefixExample = event => {

			const showPrefix  = ! event.target.checked,
				  prefixClass = 'sgeobiz-title-tax-prefix-hidden';

			document.querySelectorAll( '.sgeobiz-title-tax-prefix' ).forEach( el => {
				el.classList.toggle( prefixClass, ! showPrefix );
				el.classList.remove( 'hidden' );
			} );
			document.querySelectorAll( '.sgeobiz-title-tax-noprefix' ).forEach( el => {
				el.classList.toggle( prefixClass, showPrefix );
				el.classList.remove( 'hidden' );
			} );

			sgeobizTitle.updateStateAll( 'showPrefix', showPrefix, _getSettingsId( 'homepage_title' ) );
		}
		const titleRemPrefixes = document.getElementById( _getSettingsId( 'title_rem_prefixes' ) );
		if ( titleRemPrefixes ) {
			titleRemPrefixes.addEventListener( 'change', adjustPrefixExample );
			_dispatchAtInteractive( titleRemPrefixes, 'change' );
		}

		/**
		 * Updates used separator and all examples thereof.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateSeparator = event => {
			const separator   = sgeobiz.decodeEntities( event.target.dataset.entity ),
				  activeClass = 'sgeobiz-title-separator-active';

			document.querySelectorAll( '.sgeobiz-sep-js' ).forEach( el => {
				el.textContent = ` ${separator} `; // two spaces hug it.
			} );

			window.dispatchEvent(
				new CustomEvent(
					'sgeobiz-title-sep-updated',
					{
						detail: { separator }
					}
				)
			);

			let oldActiveLabel = document.querySelector( `.${activeClass}` );
			oldActiveLabel && oldActiveLabel.classList.remove( activeClass, 'sgeobiz-no-focus-ring' );

			let activeLabel = document.querySelector( `label[for="${event.target.id}"]` );
			activeLabel && activeLabel.classList.add( activeClass );
		}
		document.querySelectorAll( '#sgeobiz-title-separator input' ).forEach( el => {
			el.addEventListener( 'click', updateSeparator );
		} );

		/**
		 * Sets a class to the active element which helps excluding focus rings.
		 *
		 * @function
		 * @param {Event} event
		 */
		const addNoFocusClass = event => {
			event.target.classList.add( 'sgeobiz-no-focus-ring' );
		}
		document.querySelectorAll( '#sgeobiz-title-separator label' ).forEach( el => {
			el.addEventListener( 'click', addNoFocusClass );
		} );

		const homeTitleId    = _getSettingsId( 'homepage_title' ),
			  siteTitleInput = document.getElementById( _getSettingsId( 'site_title' ) );
		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {Event} event
		 */
		 const adjustSiteTitleExampleOutput = event => {
			let examples = document.querySelectorAll( '.sgeobiz-site-title-js' ),
				newVal   = sgeobiz.decodeEntities( sgeobiz.sDoubleSpace( event.target.value.trim() ) );

			newVal ||= sgeobiz.decodeEntities( event.target.placeholder );

			// If the home-as-page has a title, don't overwrite.
			if ( ! sgeobizTitle.getStateOf( homeTitleId, '_defaultTitleLocked' ) )
				sgeobizTitle.updateStateOf( homeTitleId, 'defaultTitle', newVal );

			sgeobizTitle.updateStateAll( 'additionValue', newVal, homeTitleId );

			let htmlVal = sgeobiz.escapeString( newVal );
			examples.forEach( el => { el.innerHTML = htmlVal } );
		}
		if ( siteTitleInput ) {
			siteTitleInput.addEventListener( 'input', adjustSiteTitleExampleOutput );
			_dispatchAtInteractive( siteTitleInput, 'input' );
		}
	}

	/**
	 * Initializes home's general tab meta input listeners.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initHomeGeneralListeners() {

		/**
		 * Enqueues meta title and description input triggers.
		 * These triggers force an update for the counters without affecting sgeobizAys.
		 *
		 * @function
		 */
		const enqueueGeneralInputListeners = () => {
			sgeobizTitle.enqueueUnregisteredInputTrigger( _getSettingsId( 'homepage_title' ) );
			sgeobizDescription.enqueueUnregisteredInputTrigger( _getSettingsId( 'homepage_description' ) );
		}

		/**
		 * Enqueues doc-titles input trigger synchronously on postbox collapse or open.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element}       elem
		 */
		const triggerPostboxSynchronousUnregisteredInput = ( event, elem ) => {
			if ( 'autodescription-homepage-settings' === elem.id ) {
				let inside = elem.querySelector( '.inside' );
				if ( inside.offsetHeight > 0 && inside.offsetWidth > 0 ) {
					enqueueGeneralInputListeners();
				}
			}
		}
		// jQuery: WP action.
		$( document ).on( 'postbox-toggled', triggerPostboxSynchronousUnregisteredInput );

		// This also triggers change for the homepage description, which isn't necessary. But, this trims down codebase.
		document.getElementById( 'sgeobiz-homepage-tab-general' )
			?.addEventListener( 'sgeobiz-tab-toggled', enqueueGeneralInputListeners );
	}

	/**
	 * Initializes Homepage's meta title input.
	 *
	 * @since 4.0.0
	 * @since 4.2.8 Now parses custom state _defaultTitleLocked.
	 * @access private
	 */
	function _initHomeTitleSettings() {

		const _titleId = _getSettingsId( 'homepage_title' );

		const titleInput    = document.getElementById( _titleId ),
			  taglineInput  = document.getElementById( _getSettingsId( 'homepage_title_tagline' ) ),
			  taglineToggle = document.getElementById( _getSettingsId( 'homepage_tagline' ) );

		if ( ! titleInput ) return;

		sgeobizTitle.setInputElement( titleInput );

		const state = JSON.parse(
			document.getElementById( `sgeobiz-title-data_${_titleId}` )?.dataset.state || 0,
		);

		sgeobizTitle.updateStateOf( _titleId, 'allowReferenceChange', ! state.refTitleLocked );
		sgeobizTitle.updateStateOf( _titleId, 'defaultTitle', state.defaultTitle );
		sgeobizTitle.updateStateOf( _titleId, 'addAdditions', state.addAdditions );
		sgeobizTitle.updateStateOf( _titleId, 'useSocialTagline', !! ( state.useSocialTagline || false ) );
		sgeobizTitle.updateStateOf( _titleId, 'additionValue', state.additionValue );
		sgeobizTitle.updateStateOf( _titleId, 'additionPlacement', state.additionPlacement );
		sgeobizTitle.updateStateOf( _titleId, '_defaultTitleLocked', !! ( state._defaultTitleLocked || false ) );

		sgeobizTitle.enqueueUnregisteredInputTrigger( _titleId );

		/**
		 * Updates the hover additions placement.
		 *
		 * @since 4.1.1
		 *
		 * @function
		 */
		const toggleHoverAdditionsPlacement = event => {
			sgeobizTitle.updateStateOf(
				_titleId,
				'additionPlacement',
				'left' === event.target.value ? 'before' : 'after',
			);
		}
		document.querySelectorAll( '#sgeobiz-home-title-location input' ).forEach( el => {
			el.addEventListener( 'change', toggleHoverAdditionsPlacement );
			if ( el.checked )
				_dispatchAtInteractive( el, 'change' );
		} );

		/**
		 * Sets private/protected visibility state.
		 *
		 * @function
		 * @param {string} visibility
		 */
		const setTitleVisibilityPrefix = visibility => {
			let oldPrefixValue = sgeobizTitle.getStateOf( _titleId, 'prefixValue' ),
				prefixValue    = '';

			switch ( visibility ) {
				case 'password':
					prefixValue = sgeobizTitle.protectedPrefix;
					break;

				case 'private':
					prefixValue = sgeobizTitle.privatePrefix;
					break;

				default:
				case 'public':
					prefixValue = '';
					break;
			}

			if ( prefixValue !== oldPrefixValue )
				sgeobizTitle.updateStateOf( _titleId, 'prefixValue', prefixValue );
		}
		if ( l10n.states.isFrontPrivate ) {
			setTitleVisibilityPrefix( 'private' );
		} else if ( l10n.states.isFrontProtected ) {
			setTitleVisibilityPrefix( 'password' );
		}

		/**
		 * Adjusts homepage left/right title example part.
		 *
		 * @function
		 * @param {Event} event
		 */
		const adjustHomepageExampleOutput = event => {
			let examples = document.querySelectorAll( '.sgeobiz-custom-title-js' ),
				val      = sgeobiz.decodeEntities( sgeobiz.sDoubleSpace( event.target.value.trim() ) );

			if ( val.length ) {
				val = sgeobiz.escapeString( val );
				examples.forEach( el => el.innerHTML = val );
			} else {
				val = sgeobiz.escapeString( sgeobiz.decodeEntities( sgeobizTitle.getStateOf( _titleId, 'defaultTitle' ) ) );
				examples.forEach( el => el.innerHTML = val );
			}
		}
		titleInput.addEventListener( 'input', adjustHomepageExampleOutput );
		_dispatchAtInteractive( titleInput, 'input' );

		let updateHomePageTaglineExampleOutputBuffer;
		/**
		 * Updates homepage title example output.
		 *
		 * @function
		 */
		const updateHomePageTaglineExampleOutput = () => {
			clearTimeout( updateHomePageTaglineExampleOutputBuffer );
			updateHomePageTaglineExampleOutputBuffer = setTimeout(
				() => {
					let value = sgeobizTitle.getStateOf( _titleId, 'additionValue' );

					value = sgeobiz.decodeEntities( sgeobiz.sDoubleSpace( value.trim() ) );

					if ( value.length && sgeobizTitle.getStateOf( _titleId, 'addAdditions' ) ) {
						document.querySelectorAll( '.sgeobiz-custom-tagline-js' ).forEach( el => {
							el.innerHTML = sgeobiz.escapeString( value );
						} );
						document.querySelectorAll( '.sgeobiz-custom-blogname-js' ).forEach( el => {
							el.style.display = null;
						} );
					} else {
						document.querySelectorAll( '.sgeobiz-custom-blogname-js' ).forEach( el => {
							el.style.display = 'none';
						} );
					}
				},
				1000/60 // 60fps.
			);
		}

		/**
		 * Updates the hover additions value.
		 *
		 * @function
		 */
		const updateHoverAdditionsValue = () => {
			let value = taglineInput.value.trim();

			if ( ! value.length )
				value = taglineInput.placeholder || '';

			value = sgeobiz.escapeString( sgeobiz.decodeEntities( value.trim() ) );

			sgeobizTitle.updateStateOf( _titleId, 'additionValue', value );
			updateHomePageTaglineExampleOutput();
		}
		taglineInput.addEventListener( 'input', updateHoverAdditionsValue );
		_dispatchAtInteractive( taglineInput, 'input' );

		/**
		 * Toggle tagline end examples within the Left/Right example for the homepage titles.
		 * Also disables the input field for extra clarity.
		 *
		 * @function
		 * @param {Event} event
		 */
		const toggleHomePageTaglineExampleDisplay = event => {
			let addAdditions = false;

			if ( event.target.checked ) {
				addAdditions          = true;
				taglineInput.readOnly = false;
			} else {
				addAdditions          = false;
				taglineInput.readOnly = true;
			}

			// A change action implies a change. Don't test for previous; it changed!
			// (also, it defaults to false; which would cause a bug not calling updateHomePageTaglineExampleOutput on-load)
			sgeobizTitle.updateStateOf( _titleId, 'addAdditions', addAdditions );
			updateHomePageTaglineExampleOutput();
		}
		taglineToggle.addEventListener( 'change', toggleHomePageTaglineExampleDisplay );
		_dispatchAtInteractive( taglineToggle, 'change' );

		/**
		 * Updates separator used in the titles.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateSeparator = event => {
			sgeobizTitle.updateStateAll( 'separator', event.detail.separator );
		}
		window.addEventListener( 'sgeobiz-title-sep-updated', updateSeparator );
	}

	/**
	 * Initializes Homepage's meta description input.
	 *
	 * @since 4.0.0
	 * @access private
	 */
	function _initHomeDescriptionSettings() {

		const descId = _getSettingsId( 'homepage_description' );

		sgeobizDescription.setInputElement( document.getElementById( descId ) );

		const state = JSON.parse(
			document.getElementById( `sgeobiz-description-data_${descId}` )?.dataset.state || 0,
		);

		if ( state ) {
			// sgeobizDescription.updateState( 'allowReferenceChange', ! state.refDescriptionLocked );
			sgeobizDescription.updateStateOf( descId, 'defaultDescription', state.defaultDescription.trim() );
		}

		sgeobizDescription.enqueueUnregisteredInputTrigger( descId );
	}

	/**
	 * Initializes Homepage's social meta input.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _initHomeSocialSettings() {

		const _socialGroup = 'homepage_social_settings';

		sgeobizSocial.setInputInstance(
			_socialGroup,
			_getSettingsId( 'homepage_title' ),
			_getSettingsId( 'homepage_description' ),
		);

		const groupData = JSON.parse(
			document.getElementById( `sgeobiz-social-data_${_socialGroup}` )?.dataset.settings || 0,
		);

		if ( ! groupData ) return;

		sgeobizSocial.updateStateOf( _socialGroup, 'addAdditions', groupData.og.state.addAdditions ); // tw Also has one. Maybe future.
		sgeobizSocial.updateStateOf(
			_socialGroup,
			'defaults',
			{
				ogTitle: groupData.og.state.defaultTitle,
				twTitle: groupData.tw.state.defaultTitle,
				ogDesc:  groupData.og.state.defaultDesc,
				twDesc:  groupData.tw.state.defaultDesc,
			}
		);
		sgeobizSocial.updateStateOf(
			_socialGroup,
			'placeholderLocks',
			{
				ogTitle: groupData.og.state?.titlePhLock || false,
				twTitle: groupData.tw.state?.titlePhLock || false,
				ogDesc:  groupData.og.state?.descPhLock || false,
				twDesc:  groupData.tw.state?.descPhLock || false,
			}
		);

		const twitterCardType = document.getElementById( _getSettingsId( 'homepage_twitter_card_type' ) );
		const updateTitleRemoveAdditions = event => {
			const { cardType } = event.detail;

			const _defaultIndexOption = twitterCardType.querySelector( '[value=""]' ),
				  _data               = twitterCardType.dataset || {};

			const newHTML = _data.defaultI18n?.replace(
				'%s',
				_data.defaultLocked
					? _data.defaultValue
					: cardType,
			);

			_defaultIndexOption.innerHTML = newHTML;
			twitterCardType.dispatchEvent( new Event( 'change' ) );
		}
		if ( twitterCardType )
			document.body.addEventListener( 'sgeobiz-update-twitter-card-type', updateTitleRemoveAdditions );
	}

	/**
	 * Initializes Homepage's visibility input.
	 *
	 * @since 5.1.0
	 * @access private
	 */
	function _initHomeVisibilitySettings() {

		const _canonicalId = _getSettingsId( 'homepage_canonical' ),
			  _noindexId   = _getSettingsId( 'homepage_noindex' );

		const canonicalInput = document.getElementById( _canonicalId ),
			  noindexInput   = document.getElementById( _noindexId );

		if ( ! canonicalInput ) return;

		// Prefixed with B because I don't trust using 'protected' (might become reserved).
		const BNOINDEX = 0b10;

		let canonicalPhState = 0b00;

		sgeobizCanonical.setInputElement( canonicalInput );

		const state  = JSON.parse( document.getElementById( `sgeobiz-canonical-data_${_canonicalId}` )?.dataset.state || 0 );

		if ( state ) {
			sgeobizCanonical.updateStateOf( _canonicalId, 'allowReferenceChange', ! state.refCanonicalLocked );
			sgeobizCanonical.updateStateOf( _canonicalId, 'defaultCanonical', state.defaultCanonical.trim() );
			sgeobizCanonical.updateStateOf( _canonicalId, 'preferredScheme', state.preferredScheme.trim() );
			sgeobizCanonical.updateStateOf( _canonicalId, 'urlStructure', state.urlStructure );
		}

		sgeobizCanonical.enqueueTriggerUnregisteredInput( _canonicalId );

		document.body.addEventListener( 'sgeobiz-canonical-scheme-changed', event => {
			sgeobizCanonical.updateStateOf( _canonicalId, 'preferredScheme', event.detail.scheme );
		} );

		/**
		 * @since 5.1.0
		 *
		 * @function
		 */
		const updateCanonicalPlaceholder = () => {
			sgeobizCanonical.updateStateOf(
				_canonicalId,
				'showUrlPlaceholder',
				canonicalPhState & BNOINDEX
					? false
					: true,
			);
		}
		updateCanonicalPlaceholder();

		let pageNoindex = false,
			siteNoindex = false;

		const updateNoindexState = () => {

			let type = 'index';

			switch ( state.noindexQubit ) {
				case 0: // default, unset since unknown.
					if ( noindexInput?.checked || siteNoindex || pageNoindex || state.isProtected ) {
						type = 'noindex';
					} else {
						type = 'index';
					}
					break;
				case -1: // force index
					type = 'index';
					break;
				case 1: // force noindex
					type = 'noindex';
			}

			if ( 'noindex' === type ) {
				canonicalPhState |= BNOINDEX;
			} else {
				canonicalPhState &= ~BNOINDEX;
			}

			updateCanonicalPlaceholder();
		}
		noindexInput?.addEventListener( 'change', updateNoindexState );

		if ( state.isPage ) {
			const checkPTNoindex = event => {
				const { robotsType, set } = event.detail;

				if ( 'noindex' !== robotsType ) return;

				pageNoindex = set.has( 'page' );
				updateNoindexState();
			}
			document.body.addEventListener( 'sgeobiz-post-type-robots-changed', checkPTNoindex );
		}

		const checkSiteNoindex = event => {
			const { checked, robotsType } = event.detail;

			if ( 'noindex' !== robotsType ) return;

			siteNoindex = !! checked;
			updateNoindexState();
		}
		document.body.addEventListener( 'sgeobiz-site-robots-changed', checkSiteNoindex );
	}


/**
 * This file holds SGEOBIZ SEO plugin's JS code for the SEO Settings page.
 * Serve JavaScript as an addition, not as an ends or means.
 *
 * @author SGEOBIZ <https://cyberwire.nl/>
 * @link <https://wordpress.org/plugins/autodescription/>
 */

/**
 * SGEOBIZ SEO plugin
 * Copyright (C) 2019 - 2025 SGEOBIZ (https://sgeobiz.com/)
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

	/**
	 * Returns the option name/id of PTA settings.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {String} postType
	 * @param {String} id
	 * @return {String} The option name/id.
	 */
	function _getPtaInputId( postType, id ) {
		return `${_getSettingsId('pta')}[${postType}][${id}]`;
	}

	let _cachedPtaData = void 0;
	/**
	 * Returns predefined PTA object data.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {string|undefined} postType
	 * @return {{label:string,url:string,hasPosts:Boolean}}
	 */
	function _getPtaData() {
		return _cachedPtaData ||= JSON.parse(
			document.getElementById( 'sgeobiz-post-type-archive-data' )?.dataset.postTypes || 0
		) || {};
	}

	/**
	 * Initializes all Post Type Archive setting fields.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _initPtaSettings() {

		const postTypeData = _getPtaData(),
			  itemLength   = Object.keys( postTypeData ).length;

		switch ( true ) {
			case itemLength > 1:
				_initPtaSelector();
				// fall through;
			case itemLength > 0:
				_initPtaListeners();
				break;
			default:
				break;
		}

		// Yes, this will spawn many event listeners if there are many post type archives.
		// I call those 'Event Horizon cases'. Puns very much intended.
		for ( const postType in postTypeData ) {
			_initPtaTitleSettings( postType );
			_initPtaDescriptionSettings( postType );
			_initPtaSocialSettings( postType );
			_initPtaVisibilitySettings( postType );
			_initPtaMainListeners( postType );
		}
	}

	/**
	 * Initializes the Post Type Archive selector/switcher.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _initPtaSelector() {

		const postTypeData = _getPtaData();

		const select       = document.getElementById( 'sgeobiz-post-type-archive-selector' ),
			  optionOption = document.createElement( 'option' );

		const headerWrap = document.getElementById( 'sgeobiz-post-type-archive-header-wrap' );

		headerWrap && ( headerWrap.style.display = null );

		const populateSelect = () => {
			for ( const postType in postTypeData ) {
				let _option       = optionOption.cloneNode();

				_option.value     = sgeobiz.escapeString( postType );
				_option.innerHTML = sgeobiz.escapeString( postTypeData[ postType ].label );

				select?.appendChild( _option );
			}
		}
		populateSelect();

		// Hide all headers.
		document.querySelectorAll( '.sgeobiz-post-type-header' ).forEach( el => el.classList.add( 'hidden' ) );

		let _debounceSwitch = void 0,
			_detailsEl;
		const switchPostTypeSettingsView = event => {
			clearTimeout( _debounceSwitch );
			_debounceSwitch = setTimeout(
				() => {
					// Remove old details (if any).
					_detailsEl && headerWrap?.removeChild( _detailsEl );

					document.querySelectorAll( '.sgeobiz-post-type-archive-wrap' ).forEach( el => {
						if ( event.target.value === el.dataset.postType ) {
							el.style.display = null;
							_detailsEl = el.querySelector( '.sgeobiz-post-type-archive-details' )?.cloneNode( true );
						} else {
							el.style.display = 'none';
						}
						// This class is redundant now; remove it for it hides permanently.
						el.classList.remove( 'hide-if-sgeobiz-js' );
					} );

					_detailsEl && headerWrap?.appendChild( _detailsEl );

					document.body.dispatchEvent(
						new CustomEvent( 'sgeobiz-post-type-archive-switched', {
							detail: {
								postType:                      event.target.value,
								hasKompaanChocolateBananaBeer: false, // sad day.
							},
						} ),
					);
				},
				1000/60, // 60fps.
			);
		}

		if ( select ) {
			select.addEventListener( 'change', switchPostTypeSettingsView );
			_dispatchAtInteractive( select, 'change' );
		}
	}

	/**
	 * Initializes the global Post Type Archive listeners.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _initPtaListeners() {

		const augmentSwitcher = event => {
			const { postType, set } = event.detail,
				  wrap              = document.querySelector( `.sgeobiz-post-type-archive-wrap[data-post-type="${postType}"]` ),
				  excluded          = set.has( postType );

			wrap?.querySelector( '.sgeobiz-post-type-archive-if-excluded' )?.classList.toggle( 'hidden', ! excluded );
			wrap?.querySelector( '.sgeobiz-post-type-archive-if-not-excluded' )?.classList.toggle( 'hidden', excluded );

			document.body.dispatchEvent(
				// Necessary to trigger input events
				new CustomEvent( 'sgeobiz-post-type-archive-switched', {
					detail: {
						postType: postType,
					}
				} )
			);
		}

		// This also dispatches at Interactive.
		document.body.addEventListener( 'sgeobiz-post-type-support-changed', augmentSwitcher );
	}

	/**
	 * Initializes PTA's meta title input.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {String} postType The post type name.
	 */
	function _initPtaTitleSettings( postType ) {

		const _titleId   = _getPtaInputId( postType, 'doctitle' ),
			  titleInput = document.getElementById( _titleId );

		if ( ! titleInput ) return;

		sgeobizTitle.setInputElement( titleInput );

		const state = JSON.parse(
			document.getElementById( `sgeobiz-title-data_${_titleId}` )?.dataset.state || 0,
		);

		if ( state ) {
			sgeobizTitle.updateStateOf( _titleId, 'defaultTitle', state.defaultTitle );
			sgeobizTitle.updateStateOf( _titleId, 'addAdditions', state.addAdditions );
			sgeobizTitle.updateStateOf( _titleId, 'useSocialTagline', !! ( state.useSocialTagline || false ) );
			sgeobizTitle.updateStateOf( _titleId, 'additionValue', state.additionValue );
			sgeobizTitle.updateStateOf( _titleId, 'additionPlacement', state.additionPlacement );
			sgeobizTitle.updateStateOf( _titleId, 'prefixValue', state.prefixValue );
			sgeobizTitle.updateStateOf( _titleId, 'showPrefix', state.showPrefix );
		}

		/**
		 * Updates title prefix, based on input and global settings.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateTitlePrefix = event => {
			let showPrefix = ! event.target.value.trim().length;

			if ( document.getElementById( _getSettingsId( 'title_rem_prefixes' ) )?.checked )
				showPrefix = false;

			sgeobizTitle.updateStateOf( _titleId, 'showPrefix', showPrefix );
		}

		updateTitlePrefix( { target: titleInput } );
		titleInput.addEventListener( 'input', updateTitlePrefix, { capture: true } );

		/**
		 * Updates title additions, based on singular settings change.
		 *
		 * @function
		 * @param {Event} event
		 */
		const updateTitleAdditions = event => {
			let addAdditions = ! event.target.checked;

			if ( document.getElementById( _getSettingsId( 'title_rem_additions' ) )?.checked )
				addAdditions = false;

			sgeobizTitle.updateStateOf( _titleId, 'addAdditions', addAdditions );
		}

		const disabledTitleAdditionsHelp = wp.template( 'sgeobiz-disabled-title-additions-help' )();

		const blogNameTrigger = document.getElementById( _getPtaInputId( postType, 'title_no_blog_name' ) );
		const updateTitleRemoveAdditions = event => {
			const { removeAdditions } = event.detail;

			blogNameTrigger.disabled = removeAdditions;

			if ( removeAdditions ) {
				blogNameTrigger.closest( 'label' ).insertAdjacentHTML( 'beforeend', disabledTitleAdditionsHelp );
				sgeobizTT.triggerReset();
			} else {
				// 'sgeobiz-title-additions-warning' is defined at `../inc/views/templates/settings/settings.php`
				blogNameTrigger.closest( 'label' ).querySelector( '.sgeobiz-title-additions-warning' )?.remove();
			}

			blogNameTrigger.dispatchEvent( new Event( 'change' ) );
		}
		if ( blogNameTrigger ) {
			document.body.addEventListener( 'sgeobiz-update-title-rem-additions', updateTitleRemoveAdditions );
			blogNameTrigger.addEventListener( 'change', updateTitleAdditions );
			_dispatchAtInteractive( blogNameTrigger, 'change' );
		}

		sgeobizTitle.enqueueUnregisteredInputTrigger( _titleId );
	}

	/**
	 * Initializes PTA's meta description input.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {String} postType The post type name.
	 */
	function _initPtaDescriptionSettings( postType ) {

		const _descId   = _getPtaInputId( postType, 'description' ),
			  descInput = document.getElementById( _descId );

		if ( ! descInput ) return;

		sgeobizDescription.setInputElement( descInput );

		const state = JSON.parse(
			document.getElementById( `sgeobiz-description-data_${_descId}` )?.dataset.state || 0,
		);

		if ( state )
			sgeobizDescription.updateStateOf( _descId, 'defaultDescription', state.defaultDescription.trim() );

		sgeobizDescription.enqueueUnregisteredInputTrigger( _descId );
	}

	/**
	 * Initializes PTA's social meta input.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {String} postType The post type name.
	 */
	function _initPtaSocialSettings( postType ) {

		const _socialGroup = `pta_social_settings_${postType}`;

		const groupData = JSON.parse(
			document.getElementById( `sgeobiz-social-data_${_socialGroup}` )?.dataset.settings || 0,
		);

		sgeobizSocial.setInputInstance(
			_socialGroup,
			_getPtaInputId( postType, 'doctitle' ),
			_getPtaInputId( postType, 'description' ),
		);
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

		const twitterCardType = document.getElementById( _getPtaInputId( postType, 'tw_card_type' ) );
		const updateTitleRemoveAdditions = event => {
			const { cardType } = event.detail;

			const _defaultIndexOption = twitterCardType.querySelector( '[value=""]' ),
				  _data               = twitterCardType.dataset || {};

			const newHTML = _data.defaultI18n?.replace(
				'%s',
				cardType,
			);

			_defaultIndexOption.innerHTML = newHTML;
			twitterCardType.dispatchEvent( new Event( 'change' ) );
		}
		if ( twitterCardType )
			document.body.addEventListener( 'sgeobiz-update-twitter-card-type', updateTitleRemoveAdditions );
	}

	/**
	 * Initializes PTA's Visibility input.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {String} postType The post type name.
	 */
	function _initPtaVisibilitySettings( postType ) {

		const canonicalId    = _getPtaInputId( postType, 'canonical' ),
		      noindexId      = _getPtaInputId( postType, 'noindex' );

		const canonicalInput = document.getElementById( canonicalId ),
			  indexSelect   = document.getElementById( noindexId );

		// Prefixed with B because I don't trust using 'protected' (might become reserved).
		const BNOINDEX = 0b10;

		let canonicalPhState = 0b00;
		/**
		 * @since 5.1.0
		 *
		 * @function
		 */
		const updateCanonicalPlaceholder = () => {
			sgeobizCanonical.updateStateOf(
				canonicalId,
				'showUrlPlaceholder',
				canonicalPhState & BNOINDEX
					? false
					: true,
			);
		}

		if ( canonicalInput ) {

			sgeobizCanonical.setInputElement( canonicalInput );

			const state  = JSON.parse( document.getElementById( `sgeobiz-canonical-data_${canonicalId}` )?.dataset.state || 0 );

			if ( state ) {
				sgeobizCanonical.updateStateOf( canonicalId, 'allowReferenceChange', ! state.refCanonicalLocked );
				sgeobizCanonical.updateStateOf( canonicalId, 'defaultCanonical', state.defaultCanonical.trim() );
				sgeobizCanonical.updateStateOf( canonicalId, 'preferredScheme', state.preferredScheme.trim() );
				sgeobizCanonical.updateStateOf( canonicalId, 'urlStructure', state.urlStructure );
			}

			sgeobizCanonical.enqueueTriggerUnregisteredInput( canonicalId );

			document.body.addEventListener( 'sgeobiz-canonical-scheme-changed', event => {
				sgeobizCanonical.updateStateOf( canonicalId, 'preferredScheme', event.detail.scheme );
			} );
		}

		const robotsData = {
			site: new Map(),
			pt:   new Map(),
		}
		const isNo_Default = robotsType => {
			let off = false;

			if ( 'noindex' === robotsType )
				off = ! _getPtaData()[ postType ].hasPosts;

			return off || robotsData.site.get( robotsType ) || robotsData.pt.get( robotsType );
		}
		const updateRobots = robotsType => {
			const robotsSelect = document.getElementById( _getPtaInputId( postType, robotsType ) );

			if ( ! robotsSelect ) return;

			const _defaultIndexOption = [ ...robotsSelect.options ].find( o => '0' === o.value ),
				  _data               = robotsSelect.dataset || {};

			const newHTML = _data.defaultI18n?.replace(
				'%s',
				sgeobiz.decodeEntities(
					isNo_Default( robotsType ) ? _data.defaultOff : _data.defaultOn,
				)
			);

			if ( newHTML !== _defaultIndexOption?.innerHTML ) {
				_defaultIndexOption.innerHTML = newHTML;
				robotsSelect.dispatchEvent( new Event( 'change' ) );
			}
		}
		const _registerPTDefaultRobotsValue = event => {
			const { postType: pt, robotsType, set } = event.detail;
			// Nothing to see here.
			if ( postType !== pt ) return;
			robotsData.pt.set( robotsType, set.has( postType ) );
			updateRobots( robotsType );
		}
		const _registerSiteDefaultRobotsValue = event => {
			const { checked, robotsType } = event.detail;
			robotsData.site.set( robotsType, !! checked );
			updateRobots( robotsType );
		}
		document.body.addEventListener( 'sgeobiz-post-type-robots-changed', _registerPTDefaultRobotsValue );
		document.body.addEventListener( 'sgeobiz-site-robots-changed', _registerSiteDefaultRobotsValue );

		[ 'noindex', 'nofollow', 'noarchive' ].forEach( type => {
			updateRobots( type )
		} );

		/**
		 * @since 5.1.0
		 *
		 * @function
		 * @param {Number} value
		 */
		const setRobotsIndexingState = value => {
			let type = '';

			switch ( +value ) {
				case 0: // default, unset since unknown.
					type = isNo_Default( 'noindex' ) ? 'noindex' : 'index';
					break;
				case -1: // index
					type = 'index';
					break;
				case 1: // noindex
					type = 'noindex';
					break;
			}
			if ( 'noindex' === type ) {
				canonicalPhState |= BNOINDEX;
			} else {
				canonicalPhState &= ~BNOINDEX;
			}

			updateCanonicalPlaceholder();
		}
		indexSelect.addEventListener( 'change', event => setRobotsIndexingState( event.target.value ) );
		setRobotsIndexingState( indexSelect.value );
	}

	/**
	 * Initializes PTA's main tab meta input listeners.
	 *
	 * @since 4.2.0
	 * @access private
	 *
	 * @param {String} postType The post type name.
	 */
	function _initPtaMainListeners( postType ) {

		/**
		 * Enqueues meta title and description input triggers
		 *
		 * @function
		 */
		const enqueueGeneralInputListeners = () => {
			sgeobizTitle.enqueueUnregisteredInputTrigger( _getPtaInputId( postType, 'doctitle' ) );
			sgeobizDescription.enqueueUnregisteredInputTrigger( _getPtaInputId( postType, 'description' ) );
		}

		/**
		 * Enqueues doc-titles input trigger synchronously on postbox collapse or open.
		 *
		 * @function
		 * @param {!jQuery.Event} event
		 * @param {Element}       elem
		 */
		const triggerPostboxSynchronousUnregisteredInput = ( event, elem ) => {
			if ( 'autodescription-post-type-archive-settings' === elem.id ) {
				let inside = elem.querySelector( '.inside' );
				if ( inside.offsetHeight > 0 && inside.offsetWidth > 0 ) {
					enqueueGeneralInputListeners();
				}
			}
		}
		// jQuery: WP action.
		$( document ).on( 'postbox-toggled', triggerPostboxSynchronousUnregisteredInput );

		/**
		 * Enqueues doc-titles and social input trigger synchronously on post type change.
		 * Triggers for the current post type only.
		 *
		 * @param {Event} event
		 */
		const triggerPtaSynchronousUnregisteredInput = event => {
			if ( event.detail?.postType === postType ) {
				// This also invokes inputs for the Social tabs, which is nice.
				enqueueGeneralInputListeners();
			}
		}
		document.body.addEventListener( 'sgeobiz-post-type-archive-switched', triggerPtaSynchronousUnregisteredInput );

		// This also triggers change for the homepage description, which isn't necessary. But, this trims down codebase.
		document.getElementById( `sgeobiz-post_type_archive_${postType}-tab-general` )
			?.addEventListener( 'sgeobiz-tab-toggled', enqueueGeneralInputListeners );
	}

	/**
	 * Initializes Social meta input.
	 *
	 * @since 4.1.0
	 * @access private
	 */
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
	function _initRobotsInputs() {

		const copyrightToggle = document.getElementById( _getSettingsId( 'set_copyright_directives' ) );

		if ( copyrightToggle ) {
			const controlNodes = [
				"max_snippet_length",
				"max_image_preview",
				"max_video_preview",
			].map( name => document.getElementById( _getSettingsId( name ) ) );

			const surrogateClass = 'sgeobiz-toggle-directives-surrogate';
			/**
			 * Toggles copyright directive option states.
			 *
			 * @function
			 * @param {Event} event
			 */
			const toggleCopyrightControl = event => {
				if ( event.target.checked ) {
					controlNodes.forEach( el => el.disabled = false );
					document.querySelectorAll( `.${surrogateClass}` ).forEach( el => el.remove() );
				} else {
					controlNodes.forEach( el => {
						el.disabled = true;
						let surrogate = document.createElement( 'input' );
						surrogate.type = 'hidden';
						surrogate.name = el.name || '';
						surrogate.value = el.value || 0;
						surrogate.classList.add( surrogateClass );
						el.insertAdjacentElement( 'afterend', surrogate );
					} );
				}
			}
			copyrightToggle.addEventListener( 'change', toggleCopyrightControl );
			_dispatchAtInteractive( copyrightToggle, 'change' );
		}

		const robotsPostTypes     = {},
			  robotsPtTaxonomies  = {};
		[ robotsPostTypes, robotsPtTaxonomies ].forEach( _const => {
			_const.noindex   = new Set();
			_const.nofollow  = new Set();
			_const.noarchive = new Set();
		} );

		const dispatchPosttypeRobotsChangedEvent = ( postType, robotsType ) => {
			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-post-type-robots-changed',
				{
					detail: {
						postType,
						robotsType,
						set: robotsPostTypes[ robotsType ],
					}
				}
			) );
		}
		const dispatchTaxonomyRobotsChangedEvent = ( taxonomy, robotsType ) => {
			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-taxonomy-robots-changed',
				{
					detail: {
						taxonomy,
						robotsType,
						set: robotsPtTaxonomies[ robotsType ],
					}
				}
			) );
		}

		const dispatchSiteRobotsChangedEvent = ( checked, robotsType ) => {
			document.body.dispatchEvent( new CustomEvent(
				'sgeobiz-site-robots-changed',
				{
					detail: {
						checked,
						robotsType,
					}
				}
			) );
		}

		const postTypeRobotsHelp = wp.template( 'sgeobiz-robots-pt-help' )();
		const addTaxRobotsByPtWarning = ( taxonomy, robotsType, disable ) => {
			// Yes, stacked template literals. Sue me :)
			let taxEl = document.getElementById( `${ _getSettingsId( `${robotsType}_taxonomies` ) }[${taxonomy}]` );
			if ( disable ) {
				taxEl.closest( 'label' ).insertAdjacentHTML( 'beforeend', postTypeRobotsHelp );
				sgeobizTT.triggerReset();
			} else {
				// 'sgeobiz-taxonomy-from-pt-robots-warning' is defined at `../inc/views/templates/settings/settings.php`
				taxEl.closest( 'label' ).querySelector( '.sgeobiz-taxonomy-from-pt-robots-warning' )?.remove();
			}

			toggleWarnings( taxonomy );
		}

		const validateTaxonomyState = robotsType => {
			// We want to show that the taxonomy is de-robotsTyped, but make that auto-reversible, and somehow still enactable?

			const taxEntries = document.querySelectorAll( `.sgeobiz-robots-taxonomies[data-robots="${robotsType}"]` );

			let triggerChange = false;

			taxEntries.forEach( element => {
				// get taxonomy from last [] entry.
				let taxonomy = element.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' );

				const taxPostTypes = JSON.parse( element.dataset.postTypes || 0 ),
					  hasRobots    = taxPostTypes && taxPostTypes.every( postType => robotsPostTypes[ robotsType ].has( postType ) );

				if ( hasRobots ) {
					if ( ! robotsPtTaxonomies[ robotsType ].has( taxonomy ) ) {
						// Newly disabled, trigger change.
						triggerChange = true;
					}
					// Filter it out to prevent duplicates. Redundant?
					robotsPtTaxonomies[ robotsType ].add( taxonomy );
				} else {
					if ( robotsPtTaxonomies[ robotsType ].has( taxonomy ) ) {
						robotsPtTaxonomies[ robotsType ].delete( taxonomy );
						// Enabled again, was disabled. Trigger change.
						triggerChange = true;
					}
				}
				// TODO Collect and combine changes, to condense paint stack (perceptive performance, reduce race condition changes)?
				triggerChange && dispatchTaxonomyRobotsChangedEvent( taxonomy, robotsType );
			} );
		}
		const validateTaxonomiesCache = {
			noindex:   new Map(),
			nofollow:  new Map(),
			noarchive: new Map(),
		};
		const getValidateTaxonomiesCache = ( key, robotsType ) => validateTaxonomiesCache[ robotsType ].get( key ) || ( new Set() );
		// TODO trigger new events here, to make it easier to work with for others?
		const validateTaxonomies = event => {
			const { taxonomy, robotsType } = event.detail;

			if ( getValidateTaxonomiesCache( 'robotsPtTaxonomies', robotsType ).size
				!== robotsPtTaxonomies[ robotsType ].size
			) addTaxRobotsByPtWarning( taxonomy, robotsType, robotsPtTaxonomies[ robotsType ].has( taxonomy ) );

			// Create new pointers in the memory by shadow-cloning the object.
			validateTaxonomiesCache[ robotsType ].set( 'robotsPtTaxonomies', new Set( robotsPtTaxonomies[ robotsType ] ) );
		}
		document.body.addEventListener( 'sgeobiz-taxonomy-robots-changed', validateTaxonomies );

		const validatePostTypes = event => {
			validateTaxonomyState( event.detail.robotsType );
		}
		document.body.addEventListener( 'sgeobiz-post-type-robots-changed', validatePostTypes );

		/**
		 * Add exclusions support by removing duplicated warnings.
		 * @param {string} taxonomy
		 */
		const toggleWarnings = taxonomy => {
			for ( let robotsType in robotsPtTaxonomies ) {
				if ( robotsPtTaxonomies[ robotsType ].has( taxonomy ) ) {
					let taxEl   = document.getElementById( `${ _getSettingsId( `${robotsType}_taxonomies` ) }[${taxonomy}]` ),
						warning = taxEl.closest( 'label' ).querySelector( '.sgeobiz-taxonomy-from-pt-robots-warning' );

					if ( taxEl.dataset.disabledWarning ) {
						warning.style.display = 'none';
					} else {
						warning.style.display = '';
					}
				}
			}
		}
		document.body.addEventListener( 'sgeobiz-taxonomy-support-changed', event => toggleWarnings( event.detail.taxonomy ) );

		// This prevents notice-removal checks before they're added.
		let init = false;

		const checkRobotsPT = event => {
			// get post type from last [] entry.
			let postType   = event.target?.name.split( /(?:.+\[)(.+?)(?:])/ ).join( '' ),
				robotsType = event.target?.dataset.robots;

			if ( event.target.checked ) {
				robotsPostTypes[ robotsType ].add( postType );
				dispatchPosttypeRobotsChangedEvent( postType, robotsType );
			} else {
				// No need to filter when it was never registered in the first place.
				if ( init ) {
					robotsPostTypes[ robotsType ].delete( postType );
					dispatchPosttypeRobotsChangedEvent( postType, robotsType );
				}
			}
		}
		document.querySelectorAll( '.sgeobiz-robots-post-types' ).forEach( el => {
			el.addEventListener( 'change', checkRobotsPT );
			_dispatchAtInteractive( el, 'change' );
		} );

		const checkRobotsSite = event => {
			let robotsType = event.target?.dataset.robots,
				checked    = event.target.checked;

			if ( checked ) {
				dispatchSiteRobotsChangedEvent( checked, robotsType );
			} else {
				// Dispatch only when something new is introduced.
				if ( init ) {
					dispatchSiteRobotsChangedEvent( checked, robotsType );
				}
			}
		}
		document.querySelectorAll( '.sgeobiz-robots-site' ).forEach( el => {
			el.addEventListener( 'change', checkRobotsSite );
			_dispatchAtInteractive( el, 'change' );
		} );

		init = true;
	}

	/**
	 * Initializes robots Post Type support.
	 *
	 * @since 4.2.0
	 * @access private
	 */
	function _initRobotsSupport() {

		/**
		 * @param {string} postType
		 * @return {string} The cloned input class used for sending POST data.
		 */
		const getCloneClassPT = postType => sgeobiz.escapeString( `sgeobiz-disabled-post-type-input-clone-${postType}` );
		const postTypeHelpTemplate = wp.template( 'sgeobiz-disabled-post-type-help' )();
		/**
		 * @param {string} postType
		 * @return {array} A list of affected post type settings.
		 */
		const getPostTypeRobotsSettings = postType => [
			document.getElementById( `${ _getSettingsId( 'noindex_post_types' ) }[ ${postType} ]` ),
			document.getElementById( `${ _getSettingsId( 'nofollow_post_types' ) }[ ${postType} ]` ),
			document.getElementById( `${ _getSettingsId( 'noarchive_post_types' ) }[ ${postType} ]` ),
		].filter( el => el );
		const augmentPTRobots = event => {
			const { postType, set } = event.detail;

			if ( set.has( postType ) ) {
				getPostTypeRobotsSettings( postType ).forEach( element => {
					if ( ! element ) return;

					let clone = element.cloneNode( true );
					clone.type = 'hidden';
					// Because the clone is hidden, we must set its value based on the checked state's + value thereof:
					clone.value = element.checked ? element.value : '';
					// Note that this might cause inconsistencies when other JS elements try to amend the data via ID.
					// However, they should use 'getElementsByName', anyway.
					clone.id += '-cloned';
					clone.classList.add( getCloneClassPT( postType ) );

					element.disabled                = true;
					element.dataset.disabledWarning = 1;

					const label = element.closest( 'label' );

					label.insertAdjacentHTML( 'beforeend', postTypeHelpTemplate );
					label.append( clone );
				} );

				sgeobizTT.triggerReset();
			} else {
				getPostTypeRobotsSettings( postType ).forEach( element => {
					if ( ! element ) return;
					if ( ! element.dataset.disabledWarning ) return;

					// 'sgeobiz-post-type-warning' is defined at `../inc/views/templates/settings/settings.php`
					element.closest( 'label' ).querySelector( '.sgeobiz-post-type-warning' ).remove();

					document.querySelectorAll( `.${getCloneClassPT( postType )}` ).forEach(
						clone => { clone.remove() }
					);

					element.disabled               = false;
					element.dataset.disabledWarning = '';
				} );
			}
		}
		document.body.addEventListener( 'sgeobiz-post-type-support-changed', augmentPTRobots );

		const taxonomyHelpTemplate   = wp.template( 'sgeobiz-disabled-taxonomy-help' )();
		const taxonomyPtHelpTemplate = wp.template( 'sgeobiz-disabled-taxonomy-from-pt-help' )();
		/**
		 * @param {string} taxonomy
		 * @return {string} The cloned input class used for sending POST data.
		 */
		const getCloneClassTaxonomy = taxonomy => sgeobiz.escapeString( `sgeobiz-disabled-taxonomy-input-clone-${taxonomy}` );
		/**
		 * @param {string} taxonomy
		 * @return {array} A list of affected post type settings.
		 */
		const getTaxonomyRobotsSettings = taxonomy => [
			document.getElementById( `${ _getSettingsId( 'noindex_taxonomies' ) }[ ${taxonomy} ]` ),
			document.getElementById( `${ _getSettingsId( 'nofollow_taxonomies' ) }[ ${taxonomy} ]` ),
			document.getElementById( `${ _getSettingsId( 'noarchive_taxonomies' ) }[ ${taxonomy} ]` ),
		].filter( el => el );
		const augmentTaxonomyRobots = event => {
			const { taxonomy, set, setPt, setAll } = event.detail;

			if ( setAll.has( taxonomy ) ) {
				getTaxonomyRobotsSettings( taxonomy ).forEach( element => {
					if ( ! element ) return;

					let clone = element.cloneNode( true );
					clone.type = 'hidden';
					// Because the clone is hidden, we must set its value based on the checked state's + value thereof:
					clone.value = element.checked ? element.value : '';
					// Note that this might cause inconsistencies when other JS elements try to amend the data via ID.
					// However, they should use 'getElementsByName', anyway.
					clone.id += '-cloned';
					clone.classList.add( getCloneClassTaxonomy( taxonomy ) );

					element.disabled               = true;
					element.dataset.disabledWarning = 1;

					const label = element.closest( 'label' );

					// 'sgeobiz-taxonomy-warning' is defined at `../inc/views/templates/settings/settings.php`
					if ( ! label.querySelector( '.sgeobiz-taxonomy-warning' ) )
						label.insertAdjacentHTML( 'beforeend', taxonomyHelpTemplate );

					if ( ! label.querySelector( getCloneClassTaxonomy( taxonomy ) ) )
						label.append( clone );
				} );

				sgeobizTT.triggerReset();
			} else {
				getTaxonomyRobotsSettings( taxonomy ).forEach( element => {
					if ( ! element ) return;
					if ( ! element.dataset.disabledWarning ) return;

					// 'sgeobiz-taxonomy-warning' is defined at `../inc/views/templates/settings/settings.php`
					element.closest( 'label' ).querySelector( '.sgeobiz-taxonomy-warning' )?.remove();

					document.querySelectorAll( `.${getCloneClassTaxonomy( taxonomy )}` ).forEach(
						clone => { clone.remove() }
					);

					element.disabled                = false;
					element.dataset.disabledWarning = '';
				} );
			}

			const taxEl = document.getElementById( `${ _getSettingsId( 'disabled_taxonomies' ) }[${taxonomy}]` );

			if ( setPt.has( taxonomy ) ) {
				// 'sgeobiz-taxonomy-from-pt-warning' is defined at `../inc/views/templates/settings/settings.php`
				if ( ! taxEl.closest( 'label' ).querySelector( '.sgeobiz-taxonomy-from-pt-warning' ) ) {
					taxEl.closest( 'label' ).insertAdjacentHTML( 'beforeend', taxonomyPtHelpTemplate );
					sgeobizTT.triggerReset();
				}
			} else {
				// 'sgeobiz-taxonomy-from-pt-warning' is defined at `../inc/views/templates/settings/settings.php`
				taxEl.closest( 'label' ).querySelector( '.sgeobiz-taxonomy-from-pt-warning' )?.remove();
			}
		}
		document.body.addEventListener( 'sgeobiz-taxonomy-support-changed', augmentTaxonomyRobots );
	}

	/**
	 * Initializes Webmasters' meta input.
	 *
	 * @since 4.0.0
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

// Modern Dashboard Layout Transformation
jQuery(document).ready(function($) {
    if (!$('.sgeobiz-metaboxes').length) return;

    // Helper to read query parameter
    var getUrlParameter = function(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    };

    var currentSection = getUrlParameter('section') || 'general';

    // Map section slug to metabox element ID
    var sectionMap = {
        'general': '#autodescription-general-settings',
        'title': '#autodescription-title-settings',
        'description': '#autodescription-description-settings',
        'social': '#autodescription-social-settings',
        'homepage': '#autodescription-homepage-settings',
        'schema': '#autodescription-schema-settings',
        'robots': '#autodescription-robots-settings',
        'webmaster': '#autodescription-webmaster-settings',
        'sitemap': '#autodescription-sitemap-settings',
        'feed': '#autodescription-feed-settings'
    };

    var activeTargetId = sectionMap[currentSection] || '#autodescription-general-settings';

    // Build modern layout wrapper dengan sidebar kiri terintegrasi
    var dashboardHtml = 
        '<div class="sgeobiz-dashboard">' +
        '    <div class="sgeobiz-sidebar">' +
        '        <div class="sgeobiz-sidebar-brand">' +
        '             <span class="dashicons dashicons-location-alt"></span>' +
        '             <h3>SGEOBIZ SEO</h3>' +
        '        </div>' +
        '        <ul class="sgeobiz-sidebar-menu">' +
        '             <li data-slug="general"><a href="admin.php?page=sgeobiz-seo"><span class="dashicons dashicons-admin-generic"></span> Umum</a></li>' +
        '             <li data-slug="title"><a href="admin.php?page=sgeobiz-seo&section=title"><span class="dashicons dashicons-editor-textcolor"></span> Judul</a></li>' +
        '             <li data-slug="description"><a href="admin.php?page=sgeobiz-seo&section=description"><span class="dashicons dashicons-editor-alignleft"></span> Deskripsi Meta</a></li>' +
        '             <li data-slug="social"><a href="admin.php?page=sgeobiz-seo&section=social"><span class="dashicons dashicons-share"></span> Media Sosial</a></li>' +
        '             <li data-slug="homepage"><a href="admin.php?page=sgeobiz-seo&section=homepage"><span class="dashicons dashicons-admin-home"></span> Homepage</a></li>' +
        '             <li data-slug="schema"><a href="admin.php?page=sgeobiz-seo&section=schema"><span class="dashicons dashicons-networking"></span> Schema.org</a></li>' +
        '             <li data-slug="robots"><a href="admin.php?page=sgeobiz-seo&section=robots"><span class="dashicons dashicons-index-card"></span> Robots.txt</a></li>' +
        '             <li data-slug="webmaster"><a href="admin.php?page=sgeobiz-seo&section=webmaster"><span class="dashicons dashicons-translation"></span> Webmaster</a></li>' +
        '             <li data-slug="sitemap"><a href="admin.php?page=sgeobiz-seo&section=sitemap"><span class="dashicons dashicons-category"></span> Peta Situs</a></li>' +
        '             <li data-slug="feed"><a href="admin.php?page=sgeobiz-seo&section=feed"><span class="dashicons dashicons-rss"></span> Feed</a></li>' +
        '             <li data-slug="business-settings"><a href="admin.php?page=sgeobiz-business-settings"><span class="dashicons dashicons-store"></span> Profil Bisnis</a></li>' +
        '        </ul>' +
        '    </div>' +
        '    <div class="sgeobiz-main">' +
        '        <div class="sgeobiz-topbar">' +
        '             <h2 class="sgeobiz-topbar-title">Pengaturan SEO</h2>' +
        '             <div class="sgeobiz-topbar-actions"></div>' +
        '        </div>' +
        '        <div class="sgeobiz-settings-container"></div>' +
        '    </div>' +
        '</div>';

    var $form = $('#sgeobiz-settings');
    var $metaboxes = $('.sgeobiz-metaboxes');

    // Sembunyikan margin margin bawaan WP
    $metaboxes.addClass('sgeobiz-premium-theme');

    // Masukkan wrapper ke dalam form
    $form.prepend(dashboardHtml);

    // Tandai menu sidebar aktif berdasarkan section URL saat ini
    var urlParams = new URLSearchParams(window.location.search);
    var activeSection = urlParams.get('section') || 'general';
    var activePage = urlParams.get('page');
    var activeSlug = activeSection;
    if (activePage === 'sgeobiz-business-settings') {
        activeSlug = 'business-settings';
    }
    $('.sgeobiz-sidebar-menu li').removeClass('active');
    $('.sgeobiz-sidebar-menu li[data-slug="' + activeSlug + '"]').addClass('active');

    var $container = $('.sgeobiz-settings-container');
    var $actions = $('.sgeobiz-topbar-actions');

    // Pindahkan tombol simpan atas ke topbar actions
    $('.sgeobiz-top-buttons').contents().appendTo($actions);
    $('.sgeobiz-top-wrap').hide(); // Sembunyikan header lama

    // Move postboxes
    var activeTitle = '';
    var $postboxes = $('.postbox');
    $postboxes.each(function(index) {
        var $postbox = $(this);
        var title = $postbox.find('.hndle').text() || $postbox.find('h2').text() || 'Settings';
        var id = $postbox.attr('id') || 'postbox-' + index;

        // Clean up title
        title = title.replace(/[▼▲]/g, '').trim();

        // Move to container
        $postbox.appendTo($container);

        var isTabActive = ('#' + id === activeTargetId);
        if (isTabActive) {
            $postbox.removeClass('closed hidden').addClass('sgeobiz-active-postbox').show();
            activeTitle = title;
        } else {
            $postbox.hide();
        }
    });

    if (activeTitle) {
        $('.sgeobiz-topbar-title').text(activeTitle);
    }

    // Hide screen options / help links
    $('#screen-meta-links').hide();

    // Make sure toggle buttons still trigger native change checks
    $('input[type="checkbox"]').on('change', function() {
        if (window.sgeobizAys) {
            window.sgeobizAys.registerChange();
        }
    });
});

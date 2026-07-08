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

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

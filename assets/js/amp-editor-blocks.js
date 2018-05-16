/* exported ampEditorBlocks */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 1, -1, 0 ] } ] */

var __ = wp.i18n.__;

var ampEditorBlocks = ( function() {
	var component = {

		/**
		 * Holds data.
		 */
		data: {
			dynamicBlocks: [],
			ampLayoutOptions: [
				{ value: 'nodisplay', label: __( 'No Display' ) },
				{ value: 'fixed', label: __( 'Fixed' ) }, // Not supported by amp-audio and amp-pixel.
				{ value: 'responsive', label: __( 'Responsive' ) }, // To ensure your AMP element displays, you must specify a width and height for the containing element.
				{ value: 'fixed-height', label: __( 'Fixed height' ) },
				{ value: 'fill', label: __( 'Fill' ) },
				{ value: 'container', label: __( 'Container' ) }, // Not supported by img and video.
				{ value: 'flex-item', label: __( 'Flex Item' ) },
				{ value: 'intrinsic', label: __( 'Intrinsic' ) } // Not supported by video.
			],
			defaultWidth: 608, // Max-width in the editor.
			defaultHeight: 400,
			mediaBlocks: [
				'core/image',
				'core/video',
				'core/audio'
			],
			ampPanelLabel: __( 'AMP Settings' )
		}
	};

	/**
	 * Set data, add filters.
	 *
	 * @param {Array} data Data.
	 */
	component.boot = function boot( data ) {
		_.extend( component.data, data );

		wp.hooks.addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/addAttributes', component.addAMPAttributes );
		wp.hooks.addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/filterSave', component.filterBlocksSave );
		wp.hooks.addFilter( 'blocks.BlockEdit', 'ampEditorBlocks/filterEdit', component.filterBlocksEdit );
		wp.hooks.addFilter( 'blocks.getSaveContent.extraProps', 'ampEditorBlocks/addExtraAttributes', component.addAMPExtraProps );
	};

	/**
	 * Get layout options depending on the block.
	 *
	 * @param {string} blockName Block name.
	 * @return {[*]} Options.
	 */
	component.getLayoutOptions = function getLayoutOptions( blockName ) {
		var layoutOptions = [
				{ value: '', label: 'None' }
			],
			embedBlocks = [
				'core-embed/youtube',
				'core-embed/facebook',
				'core-embed/instagram'
			];

		_.each( component.data.ampLayoutOptions, function( option ) {
			// Exclude options from layout that are not supported.
			if ( 'core/image' === blockName || 'core/video' === blockName || 'core-embed/twitter' === blockName ) {
				if ( 'container' === option.value ) {
					return true;
				}
			} else if ( 'core/audio' === blockName ) {
				if ( -1 !== [ 'responsive', 'fill', 'container', 'flex-item', 'intrinsic' ].indexOf( option.value ) ) {
					return true;
				}
			} else if ( -1 !== embedBlocks.indexOf( blockName ) ) {
				if ( 'container' === option.value || 'intrinsic' === option.value ) {
					return true;
				}
			} else if (
				'core-embed/vimeo' === blockName ||
				'core-embed/dailymotion' === blockName ||
				'core-embed/hulu' === blockName ||
				'core-embed/reddit' === blockName
			) {
				if ( 'container' === option.value || 'intrinsic' === option.value || 'nodisplay' === option.value ) {
					return true;
				}
			} else if ( 'core-embed/soundcloud' === blockName ) {
				if ( 'fixed-height' !== option.value ) {
					return true;
				}
			}

			layoutOptions.push( { value: option.value, label: option.label } );
		} );

		return layoutOptions;
	};

	/**
	 * Add extra data-amp-layout attribute to save to DB.
	 *
	 * @param {Object} props Properties.
	 * @param {string} blockType Block type.
	 * @param {Object} attributes Attributes.
	 * @return {*} Props.
	 */
	component.addAMPExtraProps = function addAMPExtraProps( props, blockType, attributes ) {
		var ampAttributes = {};
		if ( ! attributes.ampLayout && ! attributes.ampNoLoading ) {
			return props;
		}

		if ( attributes.ampLayout ) {
			ampAttributes[ 'data-amp-layout' ] = attributes.ampLayout;
		}
		if ( attributes.ampNoLoading ) {
			ampAttributes[ 'data-amp-noloading' ] = attributes.ampNoLoading;
		}

		return _.assign( ampAttributes, props );
	};

	/**
	 * Add AMP attributes (in this test case just ampLayout) to every core block.
	 *
	 * @param {Object} settings Settings.
	 * @param {string} name Block name.
	 * @return {*} Settings.
	 */
	component.addAMPAttributes = function addAMPAttributes( settings, name ) {
		// AMP Carousel settings.
		if ( 'core/shortcode' === name || 'core/gallery' === name ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampCarousel = {
				type: 'boolean'
			};
			settings.attributes.ampLightbox = {
				type: 'boolean'
			};
		}

		// Add AMP Lightbox settings.
		if ( 'core/image' === name ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampLightbox = {
				type: 'boolean'
			};
		}

		// Layout settings for embeds and media blocks.
		if ( 0 === name.indexOf( 'core-embed' ) || -1 !== component.data.mediaBlocks.indexOf( name ) ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampLayout = {
				type: 'string'
			};
			settings.attributes.ampNoLoading = {
				type: 'boolean'
			};
		}
		return settings;
	};

	/**
	 * Filters blocks edit function of all blocks.
	 *
	 * @param {Function} BlockEdit Edit function.
	 * @return {Function} Edit function.
	 */
	component.filterBlocksEdit = function filterBlocksEdit( BlockEdit ) {
		var el = wp.element.createElement;

		return function( props ) {
			var attributes = props.attributes,
				name = props.name,
				ampLayout,
				inspectorControls;

			ampLayout = attributes.ampLayout;

			if ( 'core/shortcode' === name ) {
				// Lets remove amp-carousel from edit view.
				if ( component.hasGalleryShortcodeCarouselAttribute( attributes.text || '' ) ) {
					props.setAttributes( { text: component.removeAmpCarouselFromShortcodeAtts( attributes.text ) } );
				}
				// Lets remove amp-lightbox from edit view.
				if ( component.hasGalleryShortcodeLightboxAttribute( attributes.text || '' ) ) {
					props.setAttributes( { text: component.removeAmpLightboxFromShortcodeAtts( attributes.text ) } );
				}

				inspectorControls = component.setUpShortcodeInspectorControls( props );
				if ( '' === inspectorControls ) {
					// Return original.
					return [
						el( BlockEdit, _.assign( {
							key: 'original'
						}, props ) )
					];
				}
			} else if ( 'core/gallery' === name ) {
				inspectorControls = component.setUpGalleryInpsectorControls( props );
			} else if ( 'core/image' === name ) {
				inspectorControls = component.setUpImageInpsectorControls( props );
			} else if ( -1 !== component.data.mediaBlocks.indexOf( name ) || 0 === name.indexOf( 'core-embed/' ) ) {
				inspectorControls = component.setUpInspectorControls( props );
			}

			if ( attributes.ampLayout ) {
				if ( 'core/image' === name ) {
					component.setImageBlockLayoutAttributes( props, attributes.ampLayout, inspectorControls );
				} else if ( 'nodisplay' === attributes.ampLayout ) {
					return [
						inspectorControls
					];
				}
			}

			// Return original.
			return [
				inspectorControls,
				el( BlockEdit, _.assign( {
					key: 'original',
					'data-amp-layout': ampLayout,
					style: 'height:100px;'
				}, props ) )
			];
		};
	};

	/**
	 * Set width and height in case of image block.
	 *
	 * @param {Object} props Props.
	 * @param {string} layout Layout.
	 * @param {Object} inspectorControls Inspector controls.
	 * @return {[*]} Void or block edit element.
	 */
	component.setImageBlockLayoutAttributes = function setImageBlockLayoutAttributes( props, layout, inspectorControls ) {
		var attributes = props.attributes;
		switch ( layout ) {
			case 'fixed-height':
				if ( ! attributes.height ) {
					props.setAttributes( { height: component.data.defaultHeight } );
				}
				break;

			case 'fixed':
				if ( ! attributes.height ) {
					props.setAttributes( { height: component.data.defaultHeight } );
				}
				if ( ! attributes.width ) {
					props.setAttributes( { width: component.data.defaultWidth } );
				}
				break;

			case 'nodisplay':
				return [
					inspectorControls
				];
		}
	};

	/**
	 * Get AMP Layout select control.
	 *
	 * @param {Object} props Props.
	 * @return {Object} Element.
	 */
	component.getAmpLayoutControl = function getAmpLayoutControl( props ) {
		var ampLayout = props.attributes.ampLayout,
			el = wp.element.createElement,
			SelectControl = wp.components.SelectControl,
			name = props.name,
			label = __( 'AMP Layout' );

		if ( 'core/image' === name ) {
			label = __( 'AMP Layout (modifies width/height)' );
		}

		return el( SelectControl, {
			label: label,
			value: ampLayout,
			options: component.getLayoutOptions( name ),
			onChange: function( value ) {
				props.setAttributes( { ampLayout: value } );
			}
		} );
	};

	/**
	 * Get AMP Noloading toggle control.
	 *
	 * @param {Object} props Props.
	 * @return {Object} Element.
	 */
	component.getAmpNoloadingToggle = function getAmpNoloadingToggle( props ) {
		var ampNoLoading = props.attributes.ampNoLoading,
			el = wp.element.createElement,
			ToggleControl = wp.components.ToggleControl,
			label = __( 'AMP Noloading' );

		return el( ToggleControl, {
			label: label,
			checked: ampNoLoading,
			onChange: function() {
				props.setAttributes( { ampNoLoading: ! ampNoLoading } );
			}
		} );
	};

	/**
	 * Get AMP Lightbox toggle control.
	 *
	 * @param {Object} props Props.
	 * @return {Object} Element.
	 */
	component.getAmpLightboxToggle = function getAmpLightboxToggle( props ) {
		var ampLightbox = props.attributes.ampLightbox,
			el = wp.element.createElement,
			ToggleControl = wp.components.ToggleControl,
			label = __( 'Add lightbox effect' );

		return el( ToggleControl, {
			label: label,
			checked: ampLightbox,
			onChange: function( nextValue ) {
				props.setAttributes( { ampLightbox: ! ampLightbox } );
				// In case of lightbox set linking images to 'none'.
				if ( nextValue && props.attributes.linkTo && 'none' !== props.attributes.linkTo ) {
					props.setAttributes( { linkTo: 'none' } );
				}
			}
		} );
	};

	/**
	 * Get AMP Carousel toggle control.
	 *
	 * @param {Object} props Props.
	 * @return {Object} Element.
	 */
	component.getAmpCarouselToggle = function getAmpCarouselToggle( props ) {
		var ampCarousel = props.attributes.ampCarousel,
			el = wp.element.createElement,
			ToggleControl = wp.components.ToggleControl,
			label = __( 'Display as AMP carousel' );

		return el( ToggleControl, {
			label: label,
			checked: ampCarousel,
			onChange: function() {
				props.setAttributes( { ampCarousel: ! ampCarousel } );
			}
		} );
	};

	/**
	 * Default setup for inspector controls.
	 *
	 * @param {Object} props Props.
	 * @return {Object|Element|*|{$$typeof, type, key, ref, props, _owner}} Inspector Controls.
	 */
	component.setUpInspectorControls = function setUpInspectorControls( props ) {
		var isSelected = props.isSelected,
			el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			PanelBody = wp.components.PanelBody;

		return isSelected && (
			el( InspectorControls, { key: 'inspector' },
				el( PanelBody, { title: component.data.ampPanelLabel },
					component.getAmpLayoutControl( props ),
					component.getAmpNoloadingToggle( props )
				)
			)
		);
	};

	/**
	 * Set up inspector controls for Image block.
	 *
	 * @param {Object} props Props.
	 * @return {Object} Inspector Controls.
	 */
	component.setUpImageInpsectorControls = function setUpImageInpsectorControls( props ) {
		var isSelected = props.isSelected,
			el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			PanelBody = wp.components.PanelBody;

		return isSelected && (
			el( InspectorControls, { key: 'inspector' },
				el( PanelBody, { title: component.data.ampPanelLabel },
					component.getAmpLayoutControl( props ),
					component.getAmpNoloadingToggle( props ),
					component.getAmpLightboxToggle( props )
				)
			)
		);
	};

	/**
	 * Set up inspector controls for Gallery block.
	 * Adds ampCarousel attribute for displaying the output as amp-carousel.
	 *
	 * @param {Object} props Props.
	 * @return {*} Inspector controls.
	 */
	component.setUpGalleryInpsectorControls = function setUpGalleryInpsectorControls( props ) {
		var isSelected = props.isSelected,
			el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			PanelBody = wp.components.PanelBody;

		return isSelected && (
			el( InspectorControls, { key: 'inspector' },
				el( PanelBody, { title: component.data.ampPanelLabel },
					component.getAmpCarouselToggle( props ),
					component.getAmpLightboxToggle( props )
				)
			)
		);
	};

	/**
	 * Set up inspector controls for shortcode block.
	 * Adds ampCarousel attribute in case of gallery shortcode.
	 *
	 * @param {Object} props Props.
	 * @return {*} Inspector controls.
	 */
	component.setUpShortcodeInspectorControls = function setUpShortcodeInspectorControls( props ) {
		var isSelected = props.isSelected,
			el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			PanelBody = wp.components.PanelBody;

		if ( component.isGalleryShortcode( props.attributes ) ) {
			return isSelected && (
				el( InspectorControls, { key: 'inspector' },
					el( PanelBody, { title: component.data.ampPanelLabel },
						component.getAmpCarouselToggle( props ),
						component.getAmpLightboxToggle( props )
					)
				)
			);
		}

		return '';
	};

	/**
	 * Filters blocks' save function.
	 *
	 * @param {Object} element Element.
	 * @param {string} blockType Block type.
	 * @param {Object} attributes Attributes.
	 * @return {*} Output element.
	 */
	component.filterBlocksSave = function filterBlocksSave( element, blockType, attributes ) {
		var text;
		if ( 'core/shortcode' === blockType.name && component.isGalleryShortcode( attributes ) ) {
			if ( ! attributes.ampLightbox ) {
				if ( component.hasGalleryShortcodeLightboxAttribute( attributes.text || '' ) ) {
					text = component.removeAmpLightboxFromShortcodeAtts( attributes.text );
				}
			} else {
				text = attributes.text || '';
			}
			if ( attributes.ampCarousel ) {
				// If the text contains amp-carousel or amp-lightbox, lets remove it.
				if ( component.hasGalleryShortcodeCarouselAttribute( text ) ) {
					text = component.removeAmpCarouselFromShortcodeAtts( text );
				}

				// If lightbox is not set, we can return here.
				if ( ! attributes.ampLightbox ) {
					if ( attributes.text !== text ) {
						return wp.element.createElement(
							wp.element.RawHTML,
							{},
							text
						);
					}

					// Else lets return original.
					return element;
				}
			} else if ( ! component.hasGalleryShortcodeCarouselAttribute( attributes.text || '' ) ) {
				// Add amp-carousel=false attribut to the shortcode.
				text = attributes.text.replace( '[gallery', '[gallery amp-carousel=false' );
			} else {
				text = attributes.text;
			}

			if ( attributes.ampLightbox && ! component.hasGalleryShortcodeLightboxAttribute( text ) ) {
				text = text.replace( '[gallery', '[gallery amp-lightbox=true' );
			}

			if ( attributes.text !== text ) {
				return wp.element.createElement(
					wp.element.RawHTML,
					{},
					text
				);
			}


		}
		return element;
	};

	/**
	 * Check if AMP Lightbox is set.
	 *
	 * @param {Object} attributes Attributes.
	 * @return {boolean} If is set.
	 */
	component.hasAmpLightboxSet = function hasAmpLightboxSet( attributes ) {
		return attributes.ampLightbox && false !== attributes.ampLightbox;
	};

	/**
	 * Check if AMP Carousel is set.
	 *
	 * @param {Object} attributes Attributes.
	 * @return {boolean} If is set.
	 */
	component.hasAmpCarouselSet = function hasAmpCarouselSet( attributes ) {
		return attributes.ampCarousel && false !== attributes.ampCarousel;
	};

	/**
	 * Check if AMP NoLoading is set.
	 *
	 * @param {Object} attributes Attributes.
	 * @return {boolean} If is set.
	 */
	component.hasAmpNoLoadingSet = function hasAmpNoLoadingSet( attributes ) {
		return attributes.ampNoLoading && false !== attributes.ampNoLoading;
	};

	/**
	 * Check if AMP Layout is set.
	 *
	 * @param {Object} attributes Attributes.
	 * @return {boolean} If AMP Layout is set.
	 */
	component.hasAmpLayoutSet = function hasAmpLayoutSet( attributes ) {
		return attributes.ampLayout && attributes.ampLayout.length;
	};

	/**
	 * Removes amp-carousel=false from attributes.
	 *
	 * @param {string} shortcode Shortcode text.
	 * @return {string} Modified shortcode.
	 */
	component.removeAmpCarouselFromShortcodeAtts = function removeAmpCarouselFromShortcodeAtts( shortcode ) {
		return shortcode.replace( ' amp-carousel=false', '' );
	};

	/**
	 * Removes amp-lightbox=true from attributes.
	 *
	 * @param {string} shortcode Shortcode text.
	 * @return {string} Modified shortcode.
	 */
	component.removeAmpLightboxFromShortcodeAtts = function removeAmpLightboxFromShortcodeAtts( shortcode ) {
		return shortcode.replace( ' amp-lightbox=true', '' );
	};

	/**
	 * Check if shortcode includes amp-carousel attribute.
	 *
	 * @param {string} text Shortcode.
	 * @return {boolean} If has amp-carousel.
	 */
	component.hasGalleryShortcodeCarouselAttribute = function galleryShortcodeHasCarouselAttribute( text ) {
		return -1 !== text.indexOf( 'amp-carousel=false' );
	};

	/**
	 * Check if shortcode includes amp-lightbox attribute.
	 *
	 * @param {string} text Shortcode.
	 * @return {boolean} If has amp-lightbox.
	 */
	component.hasGalleryShortcodeLightboxAttribute = function hasGalleryShortcodeLightboxAttribute( text ) {
		return -1 !== text.indexOf( 'amp-lightbox=true' );
	};

	/**
	 * Check if shortcode is gallery shortcode.
	 *
	 * @param {Object} attributes Attributes.
	 * @return {boolean} If is gallery shortcode.
	 */
	component.isGalleryShortcode = function isGalleryShortcode( attributes ) {
		return attributes.text && -1 !== attributes.text.indexOf( 'gallery' );
	};

	return component;
}() );

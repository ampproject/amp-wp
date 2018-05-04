/* exported ampEditorBlocks */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 1, -1, 0 ] } ] */

var ampEditorBlocks = ( function() {
	var component = {

		/**
		 * Holds data.
		 */
		data: {
			dynamicBlocks: [],
			ampLayoutOptions: [
				{ value: 'nodisplay', label: 'No Display' },
				{ value: 'fixed', label: 'Fixed' }, // Not supported by amp-audio and amp-pixel.
				{ value: 'responsive', label: 'Responsive' }, // To ensure your AMP element displays, you must specify a width and height for the containing element.
				{ value: 'fixed-height', label: 'Fixed height' },
				{ value: 'fill', label: 'Fill' },
				{ value: 'container', label: 'Container' }, // Not supported by img and video.
				{ value: 'flex-item', label: 'Flex Item' },
				{ value: 'intrinsic', label: 'Intrinsic' } // Not supported by video.
			],
			defaultWidth: 608, // Max-width in the editor.
			defaultHeight: 400
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
		wp.hooks.addFilter( 'blocks.getSaveContent.extraProps', 'ampEditorBlocks/addLayoutAttribute', component.addAMPExtraProps );
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
		var mediaBlocks = [
			'core/image',
			'core/video',
			'core/audio'
		];

		// Gallery settings for shortcode.
		if ( 'core/shortcode' === name ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampCarousel = {
				type: 'boolean'
			};
		}

		// Layout settings for embeds and media blocks.
		if ( 0 === name.indexOf( 'core-embed' ) || -1 !== mediaBlocks.indexOf( name ) ) {
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
				// Lets remove amp-carousel from from edit view.
				if ( component.hasGalleryShortcodeCarouselAttribute( attributes.text || '' ) ) {
					props.setAttributes( { text: component.removeAmpCarouselFromShortcodeAtts( attributes.text ) } );
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
			} else {
				inspectorControls = component.setUpInspectorControls( props );
			}

			// For editor view, add a wrapper to any tags except for embeds, these will break due to embedding logic.
			if ( attributes.ampLayout && -1 === name.indexOf( 'core-embed/' ) ) {
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
				if ( '' !== attributes.height ) {
					props.setAttributes( { width: '' } );
				}
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
	 * Default setup for inspector controls.
	 *
	 * @param {Object} props Props.
	 * @return {Object|Element|*|{$$typeof, type, key, ref, props, _owner}} Inspector Controls.
	 */
	component.setUpInspectorControls = function setUpInspectorControls( props ) {
		var ampLayout = props.attributes.ampLayout,
			ampNoLoading = props.attributes.ampNoLoading,
			isSelected = props.isSelected,
			name = props.name,
			el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			SelectControl = wp.components.SelectControl,
			ToggleControl = wp.components.ToggleControl,
			PanelBody = wp.components.PanelBody,
			label = 'AMP Layout';

		if ( 'core/image' === name ) {
			label = 'AMP Layout (modifies width/height)';
		}

		return isSelected && (
			el( InspectorControls, { key: 'inspector' },
				el( PanelBody, { title: 'AMP Settings' },
					el( SelectControl, {
						label: label,
						value: ampLayout,
						options: component.getLayoutOptions( name ),
						onChange: function( value ) {
							props.setAttributes( { ampLayout: value } );
						}
					} ),
					el( ToggleControl, {
						label: 'AMP Noloading',
						checked: ampNoLoading,
						onChange: function() {
							props.setAttributes( { ampNoLoading: ! ampNoLoading } );
						}
					} )
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
		var ampCarousel = props.attributes.ampCarousel,
			isSelected = props.isSelected,
			el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			ToggleControl = wp.components.ToggleControl,
			PanelBody = wp.components.PanelBody,
			toggleControl;

		if ( component.isGalleryShortcode( props.attributes ) ) {
			toggleControl = el( ToggleControl, {
				label: 'Display as AMP carousel',
				checked: ampCarousel,
				onChange: function() {
					props.setAttributes( { ampCarousel: ! ampCarousel } );
				}
			} );
			return isSelected && (
				el( InspectorControls, { key: 'inspector' },
					el( PanelBody, { title: 'AMP Settings' },
						toggleControl
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
			if ( attributes.ampCarousel ) {
				// If the text contains amp-carousel, lets remove it.
				if ( component.hasGalleryShortcodeCarouselAttribute( attributes.text || '' ) ) {
					text = component.removeAmpCarouselFromShortcodeAtts( attributes.text );

					return wp.element.createElement(
						wp.element.RawHTML,
						{},
						text
					);
				}

				// Else lets return original.
				return element;
			}

			// If the text already contains amp-carousel, return original.
			if ( component.hasGalleryShortcodeCarouselAttribute( attributes.text || '' ) ) {
				return element;
			}

			// Add amp-carousel=false attribut to the shortcode.
			text = attributes.text.replace( '[gallery', '[gallery amp-carousel=false' );

			return wp.element.createElement(
				wp.element.RawHTML,
				{},
				text
			);
		}

		return element;
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
	 * Check if shortcode includes amp-carousel attribute.
	 *
	 * @param {string} text Shortcode.
	 * @return {boolean} If has amp-carousel.
	 */
	component.hasGalleryShortcodeCarouselAttribute = function galleryShortcodeHasCarouselAttribute( text ) {
		return -1 !== text.indexOf( 'amp-carousel=false' );
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

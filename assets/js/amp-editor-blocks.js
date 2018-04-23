/* exported ampEditorBlocks */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 1, -1 ] } ] */

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
			]
		}
	};

	/**
	 * Set data, add filters.
	 *
	 * @param {array} data Data.
	 */
	component.boot = function boot( data ) {
		_.extend( component.data, data );

		wp.hooks.addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/addAttributes', component.addAMPAttributes );
		// wp.hooks.addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/filterSave', component.filterBlocksSave );
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
		];

		_.each( component.data.ampLayoutOptions, function( option ) {
			// Exclude options from layout that are not supported.
			if ( 'core/image' === blockName ) {
				if ( 'container' === option.value ) {
					return true;
				}
			} else if ( 'core/audio' === blockName ) {
				if ( -1 !== [ 'responsive', 'fill', 'container', 'flex-item', 'intrinsic' ].indexOf( option.value ) ) {
					return true;
				}
			} else if ( 'core/video' === blockName ) {
				// Don't allow intrinsic / responsive since the core video block doesn't have height/width anyway.
				if ( -1 !== [ 'container', 'intrinsic', 'responsive' ].indexOf( option.value ) ) {
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
	 * @param {object} props Properties.
	 * @param {string} blockType Block type.
	 * @param {object} attributes Attributes.
	 * @return {*}
	 */
	component.addAMPExtraProps = function addAMPExtraProps( props, blockType, attributes ) {
		if ( _.isEmpty( attributes.ampLayout ) ) {
			return props;
		}

		return _.assign( { 'data-amp-layout': attributes.ampLayout }, props );
	};

	/**
	 * Add AMP attributes (in this test case just ampLayout) to every core block.
	 *
	 * @param {object} settings Settings.
	 * @param {string} name Block name.
	 * @return {*} Settings.
	 */
	component.addAMPAttributes = function addAMPAttributes( settings, name ) {

		// Currently adds ampLayout to all core blocks. Not sure if it should.
		if ( -1 !== name.indexOf( 'core/' ) ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampLayout = {
				type: 'string'
			};
		}
		return settings;
	};

	/**
	 * Filters blocks edit function of all blocks.
	 *
	 * @param BlockEdit
	 * @returns {Function}
	 */
	component.filterBlocksEdit = function filterBlocksEdit( BlockEdit ) {
		var el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			SelectControl = wp.components.SelectControl;

		return function( props ) {
			var attributes = props.attributes,
				isSelected = props.isSelected,
				name = props.name,
				ampLayout,
				inspectorControls;

			ampLayout = attributes.ampLayout;
			inspectorControls = isSelected && (
				el( InspectorControls, { key: 'inspector' },
					el ( SelectControl, {
						label: 'AMP Layout',
						value: ampLayout,
						options: component.getLayoutOptions( name ),
						onChange: function( ampLayout ) {
							props.setAttributes( { ampLayout: ampLayout } );
						}
					} )
				)
			);

			// For editor view, add a wrapper to any tags.
			if ( ! _.isEmpty( attributes.ampLayout ) && ! isSelected ) {
				return [
					inspectorControls,
					el( 'amp-layout',
						{ key: 'amp', 'data-amp-layout': attributes.ampLayout, width: 1, height: 1, children: el( BlockEdit, _.assign( {
							key: 'original'
						}, props ) ) }
					)
				];
			}

			// Return original.
			return [
				inspectorControls,
				el( BlockEdit, _.assign( {
					key: 'original',
					'data-amp-layout': ampLayout
				}, props ) )
			];
		};
	};

	/**
	 * Filters blocks save function for core blocks except for dynamic blocks.
	 *
	 * @param element
	 * @param blockType
	 * @param attributes
	 * @returns {*}
	 */
	component.filterBlocksSave = function filterBlocksSave( element, blockType, attributes ) {

		// If the blockType is a dynamic block or if AMP layout isn't return original method.
		if ( -1 !== component.data.dynamicBlocks.indexOf( blockType ) || _.isEmpty( attributes.ampLayout ) ) {
			return element;
		}

		return element;
	};

	return component;
}() );

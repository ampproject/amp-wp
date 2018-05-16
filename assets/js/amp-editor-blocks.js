/* exported ampEditorBlocks */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 1, -1, 0 ] } ] */

var ampEditorBlocks = ( function() {
	var component = {

		/**
		 * Holds data.
		 */
		data: {
			textBlocks: [
				'core/paragraph',
				'core/heading',
				'core/code',
				'core/quote',
				'core/subhead'
			]
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
		wp.hooks.addFilter( 'blocks.BlockEdit', 'ampEditorBlocks/filterEdit', component.filterBlocksEdit );
		wp.hooks.addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/filterSave', component.filterBlocksSave );
	};

	/**
	 * Add AMP attributes to every core block.
	 *
	 * @param {Object} settings Settings.
	 * @param {string} name Block name.
	 * @return {*} Settings.
	 */
	component.addAMPAttributes = function addAMPAttributes( settings, name ) {
		// Fit-text for text blocks.
		if ( -1 !== component.data.textBlocks.indexOf( name ) ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampFitText = {
				type: 'boolean',
				default: false
			};
			settings.attributes.minFont = {
				type: 'number'
			};
			settings.attributes.maxFont = {
				type: 'number'
			};
			settings.attributes.height = {
				type: 'number',
				default: 50
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
			var name = props.name,
				inspectorControls;

			if ( -1 !== component.data.textBlocks.indexOf( name ) ) {
				inspectorControls = component.setUpTextBlocksInspectorControls( props );
			}

			// Return original.
			return [
				inspectorControls,
				el( BlockEdit, _.assign( {
					key: 'original'
				}, props ) )
			];
		};
	};

	/**
	 * Setup inspector controls for text blocks.
	 *
	 * @param {Object} props Props.
	 * @return {Object|Element|*|{$$typeof, type, key, ref, props, _owner}} Inspector Controls.
	 */
	component.setUpTextBlocksInspectorControls = function setUpInspectorControls( props ) {
		var ampFitText = props.attributes.ampFitText,
			minFont = props.attributes.minFont,
			maxFont = props.attributes.maxFont,
			height = props.attributes.height,
			isSelected = props.isSelected,
			el = wp.element.createElement,
			InspectorControls = wp.blocks.InspectorControls,
			TextControl = wp.components.TextControl,
			ToggleControl = wp.components.ToggleControl,
			PanelBody = wp.components.PanelBody,
			label = 'Use AMP Fit Text';

		if ( ampFitText ) {
			return isSelected && (
				el( InspectorControls, { key: 'inspector' },
					el( PanelBody, { title: 'AMP Settings' },
						el( ToggleControl, {
							label: label,
							checked: ampFitText,
							onChange: function() {
								props.setAttributes( { ampFitText: ! ampFitText } );
							}
						} ),
						el( TextControl, {
							label: 'Height (px)',
							value: height,
							onChange: function( nextHeight ) {
								props.setAttributes( { height: nextHeight } );
							}
						} ),
						el( TextControl, {
							label: 'Min font (px)',
							value: minFont,
							onChange: function( nextMinFont ) {
								props.setAttributes( { minFont: nextMinFont } );
							}
						} ),
						el( TextControl, {
							label: 'Max font (px)',
							value: maxFont,
							onChange: function( nextMaxFont ) {
								props.setAttributes( { maxFont: nextMaxFont } );
							}
						} )
					)
				)
			);
		}

		return isSelected && (
			el( InspectorControls, { key: 'inspector' },
				el( PanelBody, { title: 'AMP Settings' },
					el( ToggleControl, {
						label: label,
						checked: ampFitText,
						onChange: function() {
							props.setAttributes( { ampFitText: ! ampFitText } );
						}
					} )
				)
			)
		);
	};

	/**
	 * Filters blocks save function for core blocks except for dynamic blocks.
	 *
	 * @param {Object} element Element.
	 * @param {Object} blockType Block type.
	 * @param {Object} attributes Attributes.
	 * @return {Object} Element.
	 */
	component.filterBlocksSave = function filterBlocksSave( element, blockType, attributes ) {
		var fitTextProps = {
			layout: 'fixed-height',
			children: element
		};
		// If the blockType is a dynamic block or if AMP layout isn't return original method.
		if ( -1 === component.data.textBlocks.indexOf( blockType.name ) || ! attributes.ampFitText ) {
			return element;
		}

		if ( attributes.minFont ) {
			fitTextProps[ 'min-font-size' ] = attributes.minFont;
		}
		if ( attributes.maxFont ) {
			fitTextProps[ 'max-font-size' ] = attributes.maxFont;
		}
		if ( attributes.height ) {
			fitTextProps.height = attributes.height;
		}
		return wp.element.createElement( 'amp-fit-text', fitTextProps );
	};

	return component;
}() );

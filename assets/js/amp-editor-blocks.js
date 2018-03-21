/* exported ampEditorBlocks */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 1 ] } ] */

var ampEditorBlocks = ( function() {

	var component = {

		/**
		 * Holds data.
		 */
		data: {
			dynamicBlocks: [],
			ampLayoutOptions: [
				{ value: '', label: 'None' },
				{ value: 'responsive', label: 'Responsive' },
				{ value: 'fill', label: 'Fill' },
			]
		},
	};

	/**
	 * Set data, add filters.
	 *
	 * @param data
	 */
	component.boot = function boot( data ) {
		_.extend( component.data, data );

		wp.hooks.addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/addAttributes', component.addAMPAttributes );
		// wp.hooks.addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/filterSave', component.filterBlocksSave );
		wp.hooks.addFilter( 'blocks.BlockEdit', 'ampEditorBlocks/filterEdit', component.filterBlocksEdit );
		wp.hooks.addFilter( 'blocks.getSaveContent.extraProps', 'ampEditorBlocks/addLayoutAttribute', component.addAMPExtraProps );
	};
	/**
	 * Add extra data-amp-layout attribute to save to DB.
	 *
	 * @param props
	 * @param blockType
	 * @param attributes
	 * @returns {*}
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
	 * @param settings
	 * @param name
	 * @returns {*}
	 */
	component.addAMPAttributes = function addAMPAttributes( settings, name ) {

		// Currently adds ampLayout to all core blocks. Not sure if it should.
		if ( -1 !== name.indexOf( 'core/' ) ) {
			settings.attributes.ampLayout = {
				type: 'string',
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
			{
				InspectorControls,
			} = wp.blocks,
			{
				SelectControl,
			} = wp.components;

		return function( props ) {
			var { attributes, isSelected } = props,
				ampLayout,
				inspectorControls;

			ampLayout = attributes.ampLayout;
			inspectorControls = isSelected && (
				el( InspectorControls, { key: 'inspector' },
					el ( SelectControl, {
						label: 'AMP Layout',
						value: ampLayout,
						options: component.data.ampLayoutOptions,
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
							key: 'original',
						}, props ) ) }
					)
				];
			}

			// Return original.
			return [
				inspectorControls,
				el( BlockEdit, _.assign( {
					key: 'original',
					'data-amp-layout': ampLayout,
				}, props ) ),
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
} )();

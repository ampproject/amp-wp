/* exported ampStoryEditorBlocks */
/* global lodash */
/* eslint no-magic-numbers: [ "error", { "ignore": [ -1 ] } ] */

var ampStoryEditorBlocks = ( function() { // eslint-disable-line no-unused-vars
	var component, __;

	__ = wp.i18n.__;

	component = {

		/**
		 * Holds data.
		 */
		data: {
			allowedBlocks: [
				'core/code',
				'core/embed',
				'core/image',
				'core/list',
				'core/paragraph',
				'core/preformatted',
				'core/pullquote',
				'core/quote',
				'core/table',
				'core/verse',
				'core/video'
			],
			ampStoryPositionOptions: [
				{
					value: 'upper-third',
					label: __( 'Upper Third', 'amp' )
				},
				{
					value: 'middle-third',
					label: __( 'Middle Third', 'amp' )
				},
				{
					value: 'lower-third',
					label: __( 'Lower Third', 'amp' )
				}
			]
		}
	};

	/**
	 * Add filters.
	 */
	component.boot = function boot() {
		wp.hooks.addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', component.addAMPAttributes );
		wp.hooks.addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/filterEdit', component.filterBlocksEdit );
		wp.hooks.addFilter( 'editor.BlockListBlock', 'my-plugin/with-data-align', component.addWrapperProps );
		wp.hooks.addFilter( 'blocks.getSaveContent.extraProps', 'ampStoryEditorBlocks/addExtraAttributes', component.addAMPExtraProps );
	};

	/**
	 * Add wrapper props to the blocks within AMP Story layers.
	 *
	 * @param {Object} BlockListBlock BlockListBlock element.
	 * @return {Function} Handler.
	 */
	component.addWrapperProps = function( BlockListBlock ) {
		var el = wp.element.createElement,
			select = wp.data.select( 'core/editor' );
		return function( props ) {
			var parentClientId,
				parentBlock,
				ampStoryPosition;
			if ( -1 === component.data.allowedBlocks.indexOf( props.block.name ) || ! props.block.attributes.ampStoryPosition ) {
				return [
					el( BlockListBlock, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			parentClientId = select.getBlockRootClientId( props.block.clientId );
			parentBlock = select.getBlock( parentClientId );
			ampStoryPosition = props.block.attributes.ampStoryPosition;

			if ( 'thirds' !== parentBlock.attributes.template ) {
				ampStoryPosition = null;
			}

			var newProps = lodash.assign(
				{},
				props,
				{
					wrapperProps: lodash.assign(
						{},
						props.wrapperProps,
						{
							'data-amp-position': ampStoryPosition
						}
					)
				}
			);

			return el(
				BlockListBlock,
				newProps
			);
		};
	};
	/**
	 * Add extra attributes to save to DB.
	 *
	 * @param {Object} props Properties.
	 * @param {Object} blockType Block type.
	 * @param {Object} attributes Attributes.
	 * @return {Object} Props.
	 */
	component.addAMPExtraProps = function addAMPExtraProps( props, blockType, attributes ) {
		var ampAttributes = {};
		if ( -1 === component.data.allowedBlocks.indexOf( blockType.name ) ) {
			return props;
		}

		if ( attributes.ampStoryPosition ) {
			ampAttributes[ 'grid-area' ] = attributes.ampStoryPosition;
		}

		return _.extend( ampAttributes, props );
	};

	/**
	 * Add AMP attributes to every allowed AMP Story block.
	 *
	 * @param {Object} settings Settings.
	 * @param {string} name Block name.
	 * @return {Object} Settings.
	 */
	component.addAMPAttributes = function addAMPAttributes( settings, name ) {
		// Add the "thirds" template position option.
		if ( -1 !== component.data.allowedBlocks.indexOf( name ) ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampStoryPosition = {
				type: 'string'
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
		var el = wp.element.createElement,
			select = wp.data.select( 'core/editor' );

		return function( props ) {
			var attributes = props.attributes,
				name = props.name,
				inspectorControls,
				InspectorControls = wp.editor.InspectorControls,
				PanelBody = wp.components.PanelBody,
				SelectControl = wp.components.SelectControl,
				parentClientId = select.getBlockRootClientId( props.clientId ),
				parentBlock;

			if ( -1 === component.data.allowedBlocks.indexOf( name ) ) {
				// Return original.
				return [
					el( BlockEdit, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			parentBlock = select.getBlock( parentClientId );
			if ( 'amp/amp-story-grid-layer' !== parentBlock.name ) {
				// Return original.
				return [
					el( BlockEdit, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			if ( 'thirds' !== parentBlock.attributes.template ) {
				// Return original.
				return [
					el( BlockEdit, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			inspectorControls = el( InspectorControls, { key: 'inspector' },
				el( PanelBody, { title: __( 'AMP Story Settings', 'amp' ) },
					el( SelectControl, {
						label: __( 'Placement', 'amp' ),
						value: attributes.ampStoryPosition,
						options: component.data.ampStoryPositionOptions,
						onChange: function( value ) {
							props.setAttributes( { ampStoryPosition: value } );
						}
					} )
				)
			);

			return [
				inspectorControls,
				el( BlockEdit, _.extend( {
					key: 'original'
				}, props ) )
			];
		};
	};

	return component;
}() );

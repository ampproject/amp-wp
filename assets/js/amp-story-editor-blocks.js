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
			],
			ampAnimationTypeOptions: [
				{
					value: '',
					label: __( 'None', 'amp' )
				},
				{
					value: 'drop',
					label: __( 'Drop', 'amp' )
				},
				{
					value: 'fade-in',
					label: __( 'Fade In', 'amp' )
				},
				{
					value: 'fly-in-bottom',
					label: __( 'Fly In Bottom', 'amp' )
				},
				{
					value: 'fly-in-left',
					label: __( 'Fly In Left', 'amp' )
				},
				{
					value: 'fly-in-right',
					label: __( 'Fly In Right', 'amp' )
				},
				{
					value: 'fly-in-top',
					label: __( 'Fly In Top', 'amp' )
				},
				{
					value: 'pulse',
					label: __( 'Pulse', 'amp' )
				},
				{
					value: 'rotate-in-left',
					label: __( 'Rotate In Left', 'amp' )
				},
				{
					value: 'rotate-in-right',
					label: __( 'Rotate In Right', 'amp' )
				},
				{
					value: 'twirl-in',
					label: __( 'Twirl In', 'amp' )
				},
				{
					value: 'whoosh-in-left',
					label: __( 'Whoosh In Left', 'amp' )
				},
				{
					value: 'whoosh-in-right',
					label: __( 'Whoosh In Right', 'amp' )
				},
				{
					value: 'pan-left',
					label: __( 'Pan Left', 'amp' )
				},
				{
					value: 'pan-right',
					label: __( 'Pan Right', 'amp' )
				},
				{
					value: 'pan-down',
					label: __( 'Pan Down', 'amp' )
				},
				{
					value: 'pan-up',
					label: __( 'Pan Up', 'amp' )
				},
				{
					value: 'zoom-in',
					label: __( 'Zoom In', 'amp' )
				},
				{
					value: 'zoom-out',
					label: __( 'Zoom Out', 'amp' )
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
		if ( attributes.animationType ) {
			ampAttributes[ 'animate-in' ] = attributes.animationType;

			if ( attributes.animationDelay ) {
				ampAttributes[ 'animate-in-delay' ] = attributes.animationDelay + 'ms';
			}
			if ( attributes.animationDuration ) {
				ampAttributes[ 'animate-in-duration' ] = attributes.animationDuration + 'ms';
			}
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
		// Add the "thirds" template position option and animation settings.
		if ( -1 !== component.data.allowedBlocks.indexOf( name ) ) {
			if ( ! settings.attributes ) {
				settings.attributes = {};
			}
			settings.attributes.ampStoryPosition = {
				type: 'string'
			};
			// @todo We could map all the blocks to their tag and use attribute as source instead.
			settings.attributes.animationType = {
				type: 'string'
			};
			settings.attributes.animationDelay = {
				type: 'number',
				default: 0
			};
			settings.attributes.animationDuration = {
				type: 'number',
				default: 0
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
				RangeControl = wp.components.RangeControl,
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
					} ),
					el( SelectControl, {
						label: __( 'Animation type', 'amp' ),
						value: attributes.ampAnimationType,
						options: component.data.ampAnimationTypeOptions,
						onChange: function( value ) {
							props.setAttributes( { ampAnimationType: value } );
						}
					} ),
					el( RangeControl, {
						label: __( 'Animation duration (ms)', 'amp' ),
						value: attributes.ampAnimationDuration,
						min: 0,
						max: 5000,
						onChange: function( value ) {
							props.setAttributes( { ampAnimationDuration: value } );
						}
					} ),
					el( RangeControl, {
						label: __( 'Animation delay (ms)', 'amp' ),
						value: attributes.ampAnimationDelay,
						min: 0,
						max: 5000,
						onChange: function( value ) {
							props.setAttributes( { ampAnimationDelay: value } );
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

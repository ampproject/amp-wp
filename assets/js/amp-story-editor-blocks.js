/* exported ampStoryEditorBlocks */
/* global lodash */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 0, -1 ] } ] */

var ampStoryEditorBlocks = ( function() { // eslint-disable-line no-unused-vars
	var component, __;

	__ = wp.i18n.__;

	component = {

		/**
		 * Holds data.
		 */
		data: {
			allowedBlocks: [
				'core/button',
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
			blockTagMapping: {
				'core/button': 'div.wp-block-button',
				'core/code': 'pre',
				'core/embed': 'figure',
				'core/image': 'figure.wp-block-image',
				'core/paragraph': 'p',
				'core/preformatted': 'pre',
				'core/pullquote': 'blockquote',
				'core/quote': 'blockquote',
				'core/table': 'table',
				'core/verse': 'pre',
				'core/video': 'figure'
			},
			gridBlocks: [
				'amp/amp-story-grid-layer-horizontal',
				'amp/amp-story-grid-layer-vertical',
				'amp/amp-story-grid-layer-thirds',
				'amp/amp-story-grid-layer-background-image',
				'amp/amp-story-grid-layer-background-video'
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
			],
			animationDurationDefaults: {
				drop: 1600,
				'fade-in': 500,
				'fly-in-bottom': 500,
				'fly-in-left': 500,
				'fly-in-right': 500,
				'fly-in-top': 500,
				pulse: 500,
				'rotate-in-left': 700,
				'rotate-in-right': 700,
				'twirl-in': 1000,
				'whoosh-in-left': 500,
				'whoosh-in-right': 500,
				'pan-left': 1000,
				'pan-right': 1000,
				'pan-down': 1000,
				'pan-up': 1000,
				'zoom-in': 1000,
				'zoom-out': 1000
			}
		}
	};

	/**
	 * Add filters.
	 */
	component.boot = function boot() {
		wp.hooks.addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', component.addAMPAttributes );
		wp.hooks.addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/setBlockParent', component.setBlockParent );
		wp.hooks.addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/filterEdit', component.filterBlocksEdit );
		wp.hooks.addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/addWrapperProps', component.addWrapperProps );
		wp.hooks.addFilter( 'blocks.getSaveContent.extraProps', 'ampStoryEditorBlocks/addExtraAttributes', component.addAMPExtraProps );
	};

	/**
	 * Filter layer properties to define the parent block.
	 *
	 * @param {Object} props Block properties.
	 * @return {Object} Properties.
	 */
	component.setBlockParent = function( props ) {
		// Note that `parent` setting gets priority over `allowedBlocks`.
		if ( component.data.allowedBlocks.includes( props.name ) ) {
			// Allow CTA as the parent for all the blocks.
			let parent = [
				'amp/amp-story-cta-layer'
			];

			// In case of other allowed blocks except for button also add other grid layers as parents.
			if ( 'core/button' !== props.name ) {
				parent = parent.concat( [
					'amp/amp-story-grid-layer-horizontal',
					'amp/amp-story-grid-layer-vertical',
					'amp/amp-story-grid-layer-thirds'
				] );
			}
			if ( props.parent ) {
				parent = parent.concat( props.parent );
			}
			return Object.assign(
				{},
				props,
				{ parent: parent }
			);
		} else if ( -1 === props.name.indexOf( 'amp/amp-story-' ) ) {
			// Do not allow inserting any of the blocks if they're not AMP Story blocks.
			return Object.assign(
				{},
				props,
				{ parent: [ '' ] }
			);
		}
		return props;
	};

	/**
	 * Add wrapper props to the blocks within AMP Story Thirds layer.
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
				ampStoryPosition,
				newProps;

			// In case of any grid layer lets add data-amp-type for styling purposes.
			if ( -1 !== component.data.gridBlocks.indexOf( props.block.name ) ) {
				newProps = lodash.assign(
					{},
					props,
					{
						wrapperProps: lodash.assign(
							{},
							props.wrapperProps,
							{
								'data-amp-type': 'grid'
							}
						)
					}
				);

				if ( wp.data.select( 'core/editor' ).hasSelectedInnerBlock( props.clientId, true ) ) {
					newProps.wrapperProps[ 'data-amp-selected' ] = 'parent';
				}

				return el(
					BlockListBlock,
					newProps
				);
			}

			if ( 'amp/amp-story-cta-layer' === props.block.name && wp.data.select( 'core/editor' ).hasSelectedInnerBlock( props.clientId, true ) ) {
				newProps = lodash.assign(
					{},
					props,
					{
						wrapperProps: lodash.assign(
							{},
							props.wrapperProps,
							{
								'data-amp-selected': 'parent'
							}
						)
					}
				);

				return el(
					BlockListBlock,
					newProps
				);
			}

			if ( 'core/image' === props.block.name && ! props.block.attributes.ampShowImageCaption ) {
				newProps = lodash.assign(
					{},
					props,
					{
						wrapperProps: lodash.assign(
							{},
							props.wrapperProps,
							{
								'data-amp-image-caption': 'noCaption'
							}
						)
					}
				);
				return el(
					BlockListBlock,
					newProps
				);
			}

			if ( -1 === component.data.allowedBlocks.indexOf( props.block.name ) || ! props.block.attributes.ampStoryPosition ) {
				return [
					el( BlockListBlock, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			parentClientId = select.getBlockRootClientId( props.block.clientId );
			parentBlock = select.getBlock( parentClientId );
			if ( 'amp/amp-story-grid-layer-thirds' !== parentBlock.name ) {
				return [
					el( BlockListBlock, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			ampStoryPosition = props.block.attributes.ampStoryPosition;

			newProps = lodash.assign(
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
		if ( attributes.ampAnimationType ) {
			ampAttributes[ 'animate-in' ] = attributes.ampAnimationType;

			if ( attributes.ampAnimationDelay ) {
				ampAttributes[ 'animate-in-delay' ] = attributes.ampAnimationDelay;
			}
			if ( attributes.ampAnimationDuration ) {
				ampAttributes[ 'animate-in-duration' ] = attributes.ampAnimationDuration;
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

			// Define selector according to mappings.
			if ( _.has( component.data.blockTagMapping, name ) ) {
				settings.attributes.ampAnimationType = {
					source: 'attribute',
					selector: component.data.blockTagMapping[ name ],
					attribute: 'animate-in'
				};
				settings.attributes.ampAnimationDelay = {
					source: 'attribute',
					selector: component.data.blockTagMapping[ name ],
					attribute: 'animate-in-delay',
					default: '0ms'
				};
				settings.attributes.ampAnimationDuration = {
					source: 'attribute',
					selector: component.data.blockTagMapping[ name ],
					attribute: 'animate-in-duration'
				};
			} else if ( 'core/list' === name ) {
				settings.attributes.ampAnimationType = {
					type: 'string'
				};
				settings.attributes.ampAnimationDelay = {
					type: 'number',
					default: 0
				};
				settings.attributes.ampAnimationDuration = {
					type: 'number',
					default: 0
				};
			}

			if ( 'core/paragraph' === name ) {
				settings.attributes.fontSize.default = 'large';
			}

			if ( 'core/image' === name ) {
				settings.attributes.ampShowImageCaption = {
					type: 'boolean'
				};
			}
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
				parentBlock = select.getBlock( parentClientId );

			if ( -1 === component.data.allowedBlocks.indexOf( name ) ) {
				// Return original.
				return [
					el( BlockEdit, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			if ( ! parentBlock || ( -1 === component.data.gridBlocks.indexOf( parentBlock.name ) && 'amp/amp-story-cta-layer' !== parentBlock.name ) ) {
				// Return original.
				return [
					el( BlockEdit, _.extend( {
						key: 'original'
					}, props ) )
				];
			}

			if ( 'amp/amp-story-grid-layer-thirds' !== parentBlock.name ) {
				inspectorControls = el( InspectorControls, { key: 'inspector' },
					el( PanelBody, { title: __( 'AMP Story Settings', 'amp' ), key: 'amp-story' },
						component.getAmpStorySettings( props )
					)
				);
			} else {
				// In case of children of the thirds grid layer we need to add the placement on the thirds control.
				inspectorControls = el( InspectorControls, { key: 'inspector' },
					el( PanelBody, { title: __( 'AMP Story Settings', 'amp' ), key: 'amp-story' },
						el( SelectControl, {
							key: 'position',
							label: __( 'Placement', 'amp' ),
							value: attributes.ampStoryPosition,
							options: component.data.ampStoryPositionOptions,
							onChange: function( value ) {
								props.setAttributes( { ampStoryPosition: value } );
							}
						} ),
						component.getAmpStorySettings( props )
					)
				);
			}

			return [
				inspectorControls,
				el( BlockEdit, _.extend( {
					key: 'original'
				}, props ) )
			];
		};
	};

	component.getAmpStorySettings = function getAmpStorySettings( props ) {
		const RangeControl = wp.components.RangeControl,
			el = wp.element.createElement,
			SelectControl = wp.components.SelectControl,
			attributes = props.attributes,
			select = wp.data.select( 'core/editor' ),
			parentClientId = select.getBlockRootClientId( props.clientId ),
			parentBlock = select.getBlock( parentClientId );

		let placeHolder,
			ampStorySettings,
			name = props.name;

		placeHolder = component.data.animationDurationDefaults[ attributes.ampAnimationType ] || 0;

		ampStorySettings = [
			el( SelectControl, {
				key: 'animation-type',
				label: __( 'Animation type', 'amp' ),
				value: attributes.ampAnimationType,
				options: component.data.ampAnimationTypeOptions,
				onChange: function( value ) {
					props.setAttributes( { ampAnimationType: value } );
				}
			} ),
			el( RangeControl, {
				key: 'animation-duration',
				label: __( 'Animation duration (ms)', 'amp' ),
				value: attributes.ampAnimationDuration ? parseInt( attributes.ampAnimationDuration ) : '',
				min: 0,
				max: 5000,
				onChange: function( value ) {
					var msValue = value + 'ms';
					props.setAttributes( { ampAnimationDuration: msValue } );
				},
				placeholder: placeHolder,
				initialPosition: placeHolder
			} ),
			el( RangeControl, {
				key: 'animation-delay',
				label: __( 'Animation delay (ms)', 'amp' ),
				value: parseInt( attributes.ampAnimationDelay ),
				min: 0,
				max: 5000,
				onChange: function( value ) {
					var msValue = value + 'ms';
					props.setAttributes( { ampAnimationDelay: msValue } );
				}
			} )
		];
		if ( 'core/image' === name && ( parentBlock && 'amp/amp-story-grid-layer-background-image' !== parentBlock.name ) ) {
			const ToggleControl = wp.components.ToggleControl,
				ampShowImageCaption = !! attributes.ampShowImageCaption;
			ampStorySettings.push( el( ToggleControl, {
				key: 'position',
				label: __( 'Show or hide the caption', 'amp' ),
				checked: ampShowImageCaption,
				onChange: function() {
					const showCaption = ! ampShowImageCaption;
					if ( ! showCaption ) {
						props.setAttributes( { caption: '' } );
					}
					props.setAttributes( { ampShowImageCaption: showCaption } );
				},
				help: __( 'Toggle on to show image caption. If you turn this off the current caption text will be deleted.', 'amp' )
			} ) );
		}
		return ampStorySettings;
	};

	return component;
}() );

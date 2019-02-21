/* exported ampStoryEditorBlocks */
/* global lodash, _ */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 0, -1 ] } ] */

const ampStoryEditorBlocks = ( function() { // eslint-disable-line no-unused-vars
	const __ = wp.i18n.__;

	const component = {

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
				'core/video',
			],
			blockTagMapping: {
				'core/button': 'div.wp-block-button',
				'core/code': 'pre',
				'core/embed': 'figure',
				'core/image': '.wp-block-image',
				'core/paragraph': 'p',
				'core/preformatted': 'pre',
				'core/pullquote': 'blockquote',
				'core/quote': 'blockquote',
				'core/table': 'table',
				'core/verse': 'pre',
				'core/video': 'figure',
			},
			gridBlocks: [
				'amp/amp-story-grid-layer-horizontal',
				'amp/amp-story-grid-layer-vertical',
				'amp/amp-story-grid-layer-thirds',
				'amp/amp-story-grid-layer-background-image',
				'amp/amp-story-grid-layer-background-video',
			],
			ampStoryPositionOptions: [
				{
					value: 'upper-third',
					label: __( 'Upper Third', 'amp' ),
				},
				{
					value: 'middle-third',
					label: __( 'Middle Third', 'amp' ),
				},
				{
					value: 'lower-third',
					label: __( 'Lower Third', 'amp' ),
				},
			],
			ampAnimationTypeOptions: [
				{
					value: '',
					label: __( 'None', 'amp' ),
				},
				{
					value: 'drop',
					label: __( 'Drop', 'amp' ),
				},
				{
					value: 'fade-in',
					label: __( 'Fade In', 'amp' ),
				},
				{
					value: 'fly-in-bottom',
					label: __( 'Fly In Bottom', 'amp' ),
				},
				{
					value: 'fly-in-left',
					label: __( 'Fly In Left', 'amp' ),
				},
				{
					value: 'fly-in-right',
					label: __( 'Fly In Right', 'amp' ),
				},
				{
					value: 'fly-in-top',
					label: __( 'Fly In Top', 'amp' ),
				},
				{
					value: 'pulse',
					label: __( 'Pulse', 'amp' ),
				},
				{
					value: 'rotate-in-left',
					label: __( 'Rotate In Left', 'amp' ),
				},
				{
					value: 'rotate-in-right',
					label: __( 'Rotate In Right', 'amp' ),
				},
				{
					value: 'twirl-in',
					label: __( 'Twirl In', 'amp' ),
				},
				{
					value: 'whoosh-in-left',
					label: __( 'Whoosh In Left', 'amp' ),
				},
				{
					value: 'whoosh-in-right',
					label: __( 'Whoosh In Right', 'amp' ),
				},
				{
					value: 'pan-left',
					label: __( 'Pan Left', 'amp' ),
				},
				{
					value: 'pan-right',
					label: __( 'Pan Right', 'amp' ),
				},
				{
					value: 'pan-down',
					label: __( 'Pan Down', 'amp' ),
				},
				{
					value: 'pan-up',
					label: __( 'Pan Up', 'amp' ),
				},
				{
					value: 'zoom-in',
					label: __( 'Zoom In', 'amp' ),
				},
				{
					value: 'zoom-out',
					label: __( 'Zoom Out', 'amp' ),
				},
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
				'zoom-out': 1000,
			},
			ampStoryFonts: [
				{
					value: '',
					label: __( 'None', 'amp' ),
				},
				{
					value: 'arial',
					label: __( 'Arial', 'amp' ),
				},
				{
					value: 'arial-black',
					label: __( 'Arial Black', 'amp' ),
				},
				{
					value: 'arial-narrow',
					label: __( 'Arial Narrow', 'amp' ),
				},
				{
					value: 'baskerville',
					label: __( 'Baskerville', 'amp' ),
				},
				{
					value: 'brush-script-mt',
					label: __( 'Brush Script MT', 'amp' ),
				},
				{
					value: 'copperplate',
					label: __( 'Copperplate', 'amp' ),
				},
				{
					value: 'courier-new',
					label: __( 'Courier New', 'amp' ),
				},
				{
					value: 'century-gothic',
					label: __( 'Century Gothic', 'amp' ),
				},
				{
					value: 'garamond',
					label: __( 'Garamond', 'amp' ),
				},
				{
					value: 'georgia',
					label: __( 'Georgia', 'amp' ),
				},
				{
					value: 'gill-sans',
					label: __( 'Gill Sans', 'amp' ),
				},
				{
					value: 'lucida-bright',
					label: __( 'Lucida Bright', 'amp' ),
				},
				{
					value: 'lucida-sans-typewriter',
					label: __( 'Lucida Sans Typewriter', 'amp' ),
				},
				{
					value: 'papyrus',
					label: __( 'Papyrus', 'amp' ),
				},
				{
					value: 'palatino',
					label: __( 'Palatino', 'amp' ),
				},
				{
					value: 'tahoma',
					label: __( 'Tahoma', 'amp' ),
				},
				{
					value: 'times-new-roman',
					label: __( 'Times New Roman', 'amp' ),
				},
				{
					value: 'trebuchet-ms',
					label: __( 'Trebuchet MS', 'amp' ),
				},
				{
					value: 'verdana',
					label: __( 'Verdana', 'amp' ),
				},
			],
		},
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
				'amp/amp-story-cta-layer',
			];

			// In case of other allowed blocks except for button also add other grid layers as parents.
			if ( 'core/button' !== props.name ) {
				parent = parent.concat( [
					'amp/amp-story-grid-layer-horizontal',
					'amp/amp-story-grid-layer-vertical',
					'amp/amp-story-grid-layer-thirds',
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

	component.wrapperWithSelect = wp.compose.compose(
		wp.data.withSelect( ( select, props ) => {
			const parentClientId = select( 'core/editor' ).getBlockRootClientId( props.clientId );
			let attributes;
			if ( props.block && props.block.attributes ) {
				attributes = props.block.attributes;
			} else if ( select( 'core/editor' ).getBlockAttributes ) {
				attributes = select( 'core/editor' ).getBlockAttributes( props.clientId );
			}
			return {
				blockName: select( 'core/editor' ).getBlockName( props.clientId ),
				attributes: attributes,
				hasSelectedInnerBlock: select( 'core/editor' ).hasSelectedInnerBlock( props.clientId, true ),
				parentBlock: select( 'core/editor' ).getBlock( parentClientId ),
				props: props,
			};
		} )
	);

	/**
	 * Add wrapper props to the blocks within AMP Story Thirds layer.
	 *
	 * @param {Object} BlockListBlock BlockListBlock element.
	 * @return {Function} Handler.
	 */
	component.addWrapperProps = wp.compose.createHigherOrderComponent( ( BlockListBlock ) => {
		return component.wrapperWithSelect( ( {
			blockName,
			attributes,
			hasSelectedInnerBlock,
			parentBlock,
			props,
		} ) => {
			const newProps = lodash.assign(
					{},
					props,
					{
						wrapperProps: lodash.assign(
							{},
							props.wrapperProps
						),
					}
				),
				el = wp.element.createElement;

			// If we have an inner block selected lets add 'data-amp-selected=parent' to the wrapper.
			if (
				hasSelectedInnerBlock &&
				(
					'amp/amp-story-cta-layer' === blockName ||
					'amp/amp-story-page' === blockName
				)
			) {
				newProps.wrapperProps[ 'data-amp-selected' ] = 'parent';
				return el(
					BlockListBlock,
					newProps
				);
			}

			// In case of any grid layer lets add data-amp-type for styling purposes.
			if ( -1 !== component.data.gridBlocks.indexOf( blockName ) ) {
				newProps.wrapperProps[ 'data-amp-type' ] = 'grid';
				if ( hasSelectedInnerBlock ) {
					newProps.wrapperProps[ 'data-amp-selected' ] = 'parent';
				}
				return el(
					BlockListBlock,
					newProps
				);
			}

			// If we got this far and it's not an allowed inner block then lets return original.
			if ( -1 === component.data.allowedBlocks.indexOf( blockName ) ) {
				return [
					el( BlockListBlock, _.extend( {
						key: 'original',
					}, props ) ),
				];
			}

			// If it's an image block and doesn't have "show image caption" set
			if ( 'core/image' === blockName && ! attributes.ampShowImageCaption ) {
				newProps.wrapperProps[ 'data-amp-image-caption' ] = 'noCaption';
			}

			if ( attributes.ampFontFamily ) {
				newProps.wrapperProps[ 'data-font-family' ] = attributes.ampFontFamily;
			}

			// If we have the thirds layer as parent and the thirds position set.
			if ( 'amp/amp-story-grid-layer-thirds' === parentBlock.name && attributes.ampStoryPosition ) {
				newProps.wrapperProps[ 'data-amp-position' ] = attributes.ampStoryPosition;
			}

			return el(
				BlockListBlock,
				newProps
			);
		} );
	}, 'addWrapperProps' );

	/**
	 * Add extra attributes to save to DB.
	 *
	 * @param {Object} props Properties.
	 * @param {Object} blockType Block type.
	 * @param {Object} attributes Attributes.
	 * @return {Object} Props.
	 */
	component.addAMPExtraProps = function addAMPExtraProps( props, blockType, attributes ) {
		const ampAttributes = {};
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

		if ( attributes.ampFontFamily ) {
			ampAttributes[ 'data-font-family' ] = attributes.ampFontFamily;
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
				type: 'string',
			};

			// Define selector according to mappings.
			if ( _.has( component.data.blockTagMapping, name ) ) {
				settings.attributes.ampAnimationType = {
					source: 'attribute',
					selector: component.data.blockTagMapping[ name ],
					attribute: 'animate-in',
				};
				settings.attributes.ampAnimationDelay = {
					source: 'attribute',
					selector: component.data.blockTagMapping[ name ],
					attribute: 'animate-in-delay',
					default: '0ms',
				};
				settings.attributes.ampAnimationDuration = {
					source: 'attribute',
					selector: component.data.blockTagMapping[ name ],
					attribute: 'animate-in-duration',
				};
			} else if ( 'core/list' === name ) {
				settings.attributes.ampAnimationType = {
					type: 'string',
				};
				settings.attributes.ampAnimationDelay = {
					type: 'number',
					default: 0,
				};
				settings.attributes.ampAnimationDuration = {
					type: 'number',
					default: 0,
				};
			}

			if ( 'core/paragraph' === name ) {
				settings.attributes.fontSize.default = 'large';
			}

			// Lets add font family to the text blocks.
			if ( 'core/paragraph' === name || 'core/heading' === name ) {
				settings.attributes.ampFontFamily = {
					type: 'string',
				};
			}

			if ( 'core/image' === name ) {
				settings.attributes.ampShowImageCaption = {
					type: 'boolean',
					default: false,
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
		const el = wp.element.createElement,
			select = wp.data.select( 'core/editor' );

		return function( props ) {
			const attributes = props.attributes;
			const name = props.name;
			const InspectorControls = wp.editor.InspectorControls;
			const PanelBody = wp.components.PanelBody;
			const SelectControl = wp.components.SelectControl;
			const parentClientId = select.getBlockRootClientId( props.clientId );
			const parentBlock = select.getBlock( parentClientId );

			let inspectorControls;

			if ( -1 === component.data.allowedBlocks.indexOf( name ) ) {
				// Return original.
				return [
					el( BlockEdit, _.extend( {
						key: 'original',
					}, props ) ),
				];
			}

			if ( ! parentBlock || ( -1 === component.data.gridBlocks.indexOf( parentBlock.name ) && 'amp/amp-story-cta-layer' !== parentBlock.name ) ) {
				// Return original.
				return [
					el( BlockEdit, _.extend( {
						key: 'original',
					}, props ) ),
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
							},
						} ),
						component.getAmpStorySettings( props )
					)
				);
			}

			return [
				inspectorControls,
				el( BlockEdit, _.extend( {
					key: 'original',
				}, props ) ),
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

		const name = props.name;

		const placeHolder = component.data.animationDurationDefaults[ attributes.ampAnimationType ] || 0;

		const ampStorySettings = [
			el( SelectControl, {
				key: 'animation-type',
				label: __( 'Animation type', 'amp' ),
				value: attributes.ampAnimationType,
				options: component.data.ampAnimationTypeOptions,
				onChange: function( value ) {
					props.setAttributes( { ampAnimationType: value } );
				},
			} ),
			el( RangeControl, {
				key: 'animation-duration',
				label: __( 'Animation duration (ms)', 'amp' ),
				value: attributes.ampAnimationDuration ? parseInt( attributes.ampAnimationDuration ) : '',
				min: 0,
				max: 5000,
				onChange: function( value ) {
					const msValue = value + 'ms';
					props.setAttributes( { ampAnimationDuration: msValue } );
				},
				placeholder: placeHolder,
				initialPosition: placeHolder,
			} ),
			el( RangeControl, {
				key: 'animation-delay',
				label: __( 'Animation delay (ms)', 'amp' ),
				value: parseInt( attributes.ampAnimationDelay ),
				min: 0,
				max: 5000,
				onChange: function( value ) {
					const msValue = value + 'ms';
					props.setAttributes( { ampAnimationDelay: msValue } );
				},
			} ),
		];

		// Lets add font family select to the text blocks.
		if ( 'core/paragraph' === name || 'core/heading' === name ) {
			ampStorySettings.push(
				el( SelectControl, {
					key: 'font-family',
					label: __( 'Font family', 'amp' ),
					value: attributes.ampFontFamily,
					options: component.data.ampStoryFonts,
					onChange: function( value ) {
						props.setAttributes( { ampFontFamily: value } );
					},
				} )
			);
		}
		if ( 'core/image' === name && ( parentBlock && 'amp/amp-story-grid-layer-background-image' !== parentBlock.name ) ) {
			const ToggleControl = wp.components.ToggleControl;

			ampStorySettings.push( el( ToggleControl, {
				key: 'position',
				label: __( 'Show or hide the caption', 'amp' ),
				checked: attributes.ampShowImageCaption,
				onChange: function() {
					props.setAttributes( { ampShowImageCaption: ! attributes.ampShowImageCaption } );
					if ( ! attributes.ampShowImageCaption ) {
						props.setAttributes( { caption: '' } );
					}
				},
				help: __( 'Toggle on to show image caption. If you turn this off the current caption text will be deleted.', 'amp' ),
			} ) );
		}
		return ampStorySettings;
	};

	return component;
}() );

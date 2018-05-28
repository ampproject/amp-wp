/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { Fragment } = wp.element;
const {
	PanelBody,
	TextControl,
	SelectControl,
	Placeholder
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-reach-player',
	{
		title: __( 'AMP Reach Player' ),
		description: __( 'Displays the Reach Player configured in the Beachfront Reach platform.' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed' ),
			__( 'Beachfront Reach video' )
		],

		attributes: {
			dataEmbedId: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'data-embed-id'
			},
			layout: {
				type: 'string',
				default: 'fixed-height',
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				default: 600,
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'width'
			},
			height: {
				type: 'number',
				default: 400,
				source: 'attribute',
				selector: 'amp-reach-player',
				attribute: 'height'
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataEmbedId, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive' ) },
				{ value: 'fixed-height', label: __( 'Fixed Height' ) },
				{ value: 'fixed', label: __( 'Fixed' ) },
				{ value: 'fill', label: __( 'Fill' ) },
				{ value: 'flex-item', label: __( 'Flex-item' ) }

			];
			let url = false;
			if ( dataEmbedId ) {
				url = 'https://media-cdn.beachfrontreach.com/acct_1/video/';
			}
			return (
				<Fragment>
					{
						isSelected && (
							<InspectorControls key='inspector'>
								<PanelBody title={ __( 'Reach settings' ) }>
									<TextControl
										label={ __( 'The Reach player embed id (required)' ) }
										value={ dataEmbedId }
										onChange={ value => ( setAttributes( { dataEmbedId: value } ) ) }
									/>
									<SelectControl
										label={ __( 'Layout' ) }
										value={ layout }
										options={ ampLayoutOptions }
										onChange={ value => ( setAttributes( { layout: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Width (px)' ) }
										value={ width !== undefined ? width : '' }
										onChange={ value => ( setAttributes( { width: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Height (px)' ) }
										value={ height }
										onChange={ value => ( setAttributes( { height: value } ) ) }
									/>
								</PanelBody>
							</InspectorControls>
						)
					}
					{
						url && (
							<Placeholder label={ __( 'Reach Player' ) }>
								<p className="components-placeholder__error">{ url }</p>
								<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!' ) }</p>
							</Placeholder>
						)
					}
					{
						! url && (
							<Placeholder label={ __( 'Reach Player' ) }>
								<p>{ __( 'Add Reach player embed ID to use the block.' ) }</p>
							</Placeholder>
						)
					}
				</Fragment>
			);
		},

		save( { attributes } ) {
			const { dataEmbedId, layout, height, width } = attributes;

			let reachProps = {
				layout: layout,
				height: height,
				'data-embed-id': dataEmbedId
			};
			if ( 'fixed-height' !== layout && width ) {
				reachProps.width = width;
			}
			return (
				<amp-reach-player { ...reachProps }></amp-reach-player>
			);
		}
	}
);

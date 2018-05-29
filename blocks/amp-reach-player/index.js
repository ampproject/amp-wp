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
		title: __( 'AMP Reach Player', 'amp' ),
		description: __( 'Displays the Reach Player configured in the Beachfront Reach platform.', 'amp' ),
		category: 'common',
		icon: 'embed-generic',
		keywords: [
			__( 'Embed', 'amp' ),
			__( 'Beachfront Reach video', 'amp' )
		],

		attributes: {
			dataEmbedId: {
				type: 'string'
			},
			layout: {
				type: 'string',
				default: 'fixed-height'
			},
			width: {
				type: 'number',
				default: 600
			},
			height: {
				type: 'number',
				default: 400
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataEmbedId, layout, height, width } = attributes;
			const ampLayoutOptions = [
				{ value: 'responsive', label: __( 'Responsive', 'amp' ) },
				{ value: 'fixed-height', label: __( 'Fixed Height', 'amp' ) },
				{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
				{ value: 'fill', label: __( 'Fill', 'amp' ) },
				{ value: 'flex-item', label: __( 'Flex-item', 'amp' ) }

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
								<PanelBody title={ __( 'Reach settings', 'amp' ) }>
									<TextControl
										label={ __( 'The Reach player embed id (required)', 'amp' ) }
										value={ dataEmbedId }
										onChange={ value => ( setAttributes( { dataEmbedId: value } ) ) }
									/>
									<SelectControl
										label={ __( 'Layout', 'amp' ) }
										value={ layout }
										options={ ampLayoutOptions }
										onChange={ value => ( setAttributes( { layout: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Width (px)', 'amp' ) }
										value={ width !== undefined ? width : '' }
										onChange={ value => ( setAttributes( { width: value } ) ) }
									/>
									<TextControl
										type="number"
										label={ __( 'Height (px)', 'amp' ) }
										value={ height }
										onChange={ value => ( setAttributes( { height: value } ) ) }
									/>
								</PanelBody>
							</InspectorControls>
						)
					}
					{
						url && (
							<Placeholder label={ __( 'Reach Player', 'amp' ) }>
								<p className="components-placeholder__error">{ url }</p>
								<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!', 'amp' ) }</p>
							</Placeholder>
						)
					}
					{
						! url && (
							<Placeholder label={ __( 'Reach Player', 'amp' ) }>
								<p>{ __( 'Add Reach player embed ID to use the block.', 'amp' ) }</p>
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

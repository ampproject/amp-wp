
/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InspectorControls,
	PlainText
} = wp.editor;
const {
	PanelBody,
	ToggleControl
} = wp.components;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-mathml',
	{
		title: __( 'AMP MathML' ),
		category: 'common',
		icon: 'welcome-learn-more',
		keywords: [
			__( 'Mathematical formula' ),
			__( 'Scientific content ' )
		],

		attributes: {
			dataFormula: {
				type: 'string'
			},
			displayInline: {
				type: 'boolean'
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { dataFormula, displayInline } = attributes;

			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'AMP MathML Settings' ) }>
							<ToggleControl
								label={ __( 'Display inline' ) }
								checked={ displayInline }
								onChange={ () => ( setAttributes( { displayInline: ! displayInline } ) ) }
							/>
						</PanelBody>
					</InspectorControls>
				),
				<PlainText
					key='formula'
					tagName='div'
					value={ dataFormula }
					placeholder={ __( 'Insert formula' ) }
					onChange={ ( value ) => setAttributes( { dataFormula: value } ) }
				/>
			];
		},

		save( { attributes } ) {
			let mathmlProps = {
				'data-formula': attributes.dataFormula,
				layout: 'container'
			};
			if ( attributes.displayInline ) {
				mathmlProps.inline = '';
			}
			return (
				<amp-mathml { ...mathmlProps }></amp-mathml>
			);
		}
	}
);

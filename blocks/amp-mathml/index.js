
/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	PlainText
} = wp.editor;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-mathml',
	{
		title: __( 'AMP MathML', 'amp' ),
		category: 'common',
		icon: 'welcome-learn-more',
		keywords: [
			__( 'Mathematical formula', 'amp' ),
			__( 'Scientific content ', 'amp' )
		],

		attributes: {
			dataFormula: {
				source: 'attribute',
				selector: 'amp-mathml',
				attribute: 'data-formula'
			}
		},

		edit( { attributes, setAttributes } ) {
			const { dataFormula } = attributes;

			return (
				<PlainText
					key='formula'
					value={ dataFormula }
					placeholder={ __( 'Insert formula', 'amp' ) }
					onChange={ ( value ) => setAttributes( { dataFormula: value } ) }
				/>
			);
		},

		save( { attributes } ) {
			let mathmlProps = {
				'data-formula': attributes.dataFormula,
				layout: 'container'
			};
			return (
				<amp-mathml { ...mathmlProps }></amp-mathml>
			);
		}
	}
);

/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const {
	registerBlockType,
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls
} = wp.blocks;
const { DateTimePicker, PanelBody } = wp.components;
import timeago from 'timeago.js';

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-timeago',
	{
		title: __( 'AMP Timeago' ),
		category: 'common',
		icon: 'wordpress-alt',
		keywords: [
			__( 'Time difference' ),
			__( 'Time ago'),
			__( 'Date' )
		],

		attributes: {
			align: {
				type: 'string',
			},
			dateTime: {
				source: 'children',
				type: 'string',
				selector: 'amp-timeago'
			}
		},

		getEditWrapperProps( attributes ) {
			const { align } = attributes;
			if ( 'left' === align || 'right' === align || 'center' === align ) {
				return { 'data-align': align };
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			var timeAgo,
				align = attributes.align;
			if ( attributes.dateTime ) {
				timeAgo = timeago().format( attributes.dateTime );
			} else {
				timeAgo = timeago().format( new Date() );
				setAttributes( { dateTime: moment( moment(), moment.ISO_8601, true ).format() } );
			}
			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'AMP Timeago Settings' ) }>
							<DateTimePicker
								locale='en'
								currentDate={ attributes.dateTime || moment() }
								onChange={ value => ( setAttributes( { dateTime: moment( value, moment.ISO_8601, true ).format() } ) ) } // eslint-disable-line
							/>
						</PanelBody>
					</InspectorControls>
				),
				<BlockControls>
					<BlockAlignmentToolbar
						value={ align }
						onChange={ ( nextAlign ) => {
							setAttributes( { align: nextAlign } );
						} }
						controls={ [ 'left', 'center', 'right' ] }
					/>
				</BlockControls>,
				<time dateTime={ attributes.dateTime }>{ timeAgo }</time>
			];
		},

		save( { attributes } ) {
			return (
				<amp-timeago layout='responsive' className={ 'align' + ( attributes.align || 'none' ) } datetime={ attributes.dateTime } locale="en">{ attributes.dateTime }</amp-timeago>
			);
		}
	}
);

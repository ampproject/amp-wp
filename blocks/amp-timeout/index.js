/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const { registerBlockType, InspectorControls } = wp.blocks;
const { DateTimePicker, PanelBody } = wp.components;

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
			dateTime: {
				source: 'children',
				type: 'array',
				selector: 'amp-timeago'
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			var timeMoment, timeAgo;
			if ( attributes.dateTime ) {
				timeMoment = moment( attributes.dateTime );
				timeAgo = timeMoment.fromNow();
			} else {
				timeAgo = moment().fromNow();
			}
			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'AMP Timeago Settings' ) }>
							<DateTimePicker
								locale='en'
								currentDate={ attributes.dateTime || moment() }
								onChange={ ( value ) => setAttributes( { dateTime: value } ) } // eslint-disable-line
							/>
						</PanelBody>
					</InspectorControls>
				),
				<time dateTime="2017-04-11T00:37:33.809Z">{ timeAgo }</time>
			];
		},
		save( { attributes } ) {
			return (
				<amp-timeago layout="fixed" width="160"
					height="20"
					dateTime={ attributes.dateTime }
					locale="en">{ attributes.dateTime }</amp-timeago>
			);
		}
	}
);

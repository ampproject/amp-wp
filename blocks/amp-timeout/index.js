/* global moment */

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
const {
	DateTimePicker,
	PanelBody,
	TextControl
} = wp.components;
import timeago from 'timeago.js';

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-timeago',
	{
		title: __( 'AMP Timeago' ),
		category: 'common',
		icon: 'wordpress-alt', // @todo Needs an icon.
		keywords: [
			__( 'Time difference' ),
			__( 'Time ago'),
			__( 'Date' )
		],

		attributes: {
			align: {
				type: 'string',
			},
			cutoff: {
				type: 'number',
			},
			dateTime: {
				type: 'string',
			}
		},

		getEditWrapperProps( attributes ) {
			const { align } = attributes;
			if ( 'left' === align || 'right' === align || 'center' === align ) {
				return { 'data-align': align };
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { align, cutoff } = attributes;
			var timeAgo;
			if ( attributes.dateTime ) {
				if ( attributes.cutoff && attributes.cutoff < Math.abs( moment( attributes.dateTime ).diff( moment(), 'seconds') ) ) {
					timeAgo = moment( attributes.dateTime ).format( 'dddd D MMMM HH:mm');
				} else {
					timeAgo = timeago().format( attributes.dateTime );
				}
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
							<TextControl
								type="number"
								className="blocks-amp-timeout__cutoff"
								label={ __( 'Cutoff (seconds)' ) }
								value={ cutoff !== undefined ? cutoff : '' }
								onChange={ value => ( setAttributes( { cutoff: value } ) ) }
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
			let timeagoProps = {
				layout: 'responsive',
				className: 'align' + ( attributes.align || 'none' ),
				datetime: attributes.dateTime,
				locale: 'en'
			};
			if ( attributes.cutoff ) {
				timeagoProps.cutoff = attributes.cutoff;
			}
			return (
				<amp-timeago { ...timeagoProps }>{ moment( attributes.dateTime ).format( 'dddd D MMMM HH:mm') }</amp-timeago>
			);
		}
	}
);

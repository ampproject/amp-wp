/* global moment */

/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls
} = wp.editor;
const {
	DateTimePicker,
	PanelBody,
	TextControl,
	SelectControl
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
		icon: 'backup',
		keywords: [
			__( 'Time difference' ),
			__( 'Time ago' ),
			__( 'Date' )
		],

		attributes: {
			align: {
				type: 'string'
			},
			cutoff: {
				type: 'number',
				source: 'attribute',
				selector: 'amp-timeago',
				attribute: 'cutoff'
			},
			dateTime: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-timeago',
				attribute: 'datetime'
			},
			ampLayout: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-timeago',
				attribute: 'layout'
			},
			width: {
				type: 'number',
				source: 'attribute',
				selector: 'amp-timeago',
				attribute: 'width'
			},
			height: {
				type: 'number',
				source: 'attribute',
				selector: 'amp-timeago',
				attribute: 'height'
			}
		},

		getEditWrapperProps( attributes ) {
			const { align } = attributes;
			if ( 'left' === align || 'right' === align || 'center' === align ) {
				return { 'data-align': align };
			}
		},

		edit( { attributes, isSelected, setAttributes } ) {
			const { align, ampLayout, cutoff, height, width } = attributes;
			let timeAgo;
			if ( attributes.dateTime ) {
				if ( attributes.cutoff && attributes.cutoff < Math.abs( moment( attributes.dateTime ).diff( moment(), 'seconds' ) ) ) {
					timeAgo = moment( attributes.dateTime ).format( 'dddd D MMMM HH:mm' );
				} else {
					timeAgo = timeago().format( attributes.dateTime );
				}
			} else {
				timeAgo = timeago().format( new Date() );
				setAttributes( { dateTime: moment( moment(), moment.ISO_8601, true ).format() } );
			}

			const ampLayoutOptions = [
				{ value: '', label: 'Responsive' }, // Default for amp-timeago.
				{ value: 'fixed', label: 'Fixed' },
				{ value: 'fixed-height', label: 'Fixed height' }
			];

			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<PanelBody title={ __( 'AMP Timeago Settings' ) }>
							<DateTimePicker
								locale='en'
								currentDate={ attributes.dateTime || moment() }
								onChange={ value => ( setAttributes( { dateTime: moment( value, moment.ISO_8601, true ).format() } ) ) } // eslint-disable-line
							/>
							<SelectControl
								label={ __( 'Layout' ) }
								value={ ampLayout }
								options={ ampLayoutOptions }
								onChange={ value => ( setAttributes( { ampLayout: value } ) ) }
							/>
							<TextControl
								type="number"
								className="blocks-amp-timeout__width"
								label={ __( 'Width (px)' ) }
								value={ width !== undefined ? width : '' }
								onChange={ value => ( setAttributes( { width: value } ) ) }
							/>
							<TextControl
								type="number"
								className="blocks-amp-timeout__height"
								label={ __( 'Height (px)' ) }
								value={ height !== undefined ? height : '' }
								onChange={ value => ( setAttributes( { height: value } ) ) }
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
				<BlockControls key='controls'>
					<BlockAlignmentToolbar
						value={ align }
						onChange={ ( nextAlign ) => {
							setAttributes( { align: nextAlign } );
						} }
						controls={ [ 'left', 'center', 'right' ] }
					/>
				</BlockControls>,
				<time key='timeago' dateTime={ attributes.dateTime }>{ timeAgo }</time>
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
			if ( attributes.ampLayout ) {
				switch ( attributes.ampLayout ) {
					case 'fixed-height':
						if ( attributes.height ) {
							timeagoProps.height = attributes.height;
							timeagoProps.layout = attributes.ampLayout;
						}
						break;
					case 'fixed':
						if ( attributes.height && attributes.width ) {
							timeagoProps.height = attributes.height;
							timeagoProps.width = attributes.width;
							timeagoProps.layout = attributes.ampLayout;
						}
						break;
				}
			}
			return (
				<amp-timeago { ...timeagoProps }>{ moment( attributes.dateTime ).format( 'dddd D MMMM HH:mm' ) }</amp-timeago>
			);
		}
	}
);

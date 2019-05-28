/**
 * External dependencies
 */
import moment from 'moment';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, BlockAlignmentToolbar, BlockControls } from '@wordpress/block-editor';
import {
	DateTimePicker,
	PanelBody,
	TextControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { LayoutControls } from '../../../components';

export const name = 'amp/amp-timeago';

export const settings = {
	title: __( 'AMP Timeago', 'amp' ),
	category: 'common',
	icon: 'backup',
	keywords: [
		__( 'Time difference', 'amp' ),
		__( 'Time ago', 'amp' ),
		__( 'Date', 'amp' ),
	],

	attributes: {
		align: {
			type: 'string',
		},
		cutoff: {
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'cutoff',
		},
		dateTime: {
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'datetime',
		},
		ampLayout: {
			default: 'fixed-height',
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'layout',
		},
		width: {
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'width',
		},
		height: {
			default: 20,
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'height',
		},
	},

	getEditWrapperProps( attributes ) {
		const { align } = attributes;
		if ( 'left' === align || 'right' === align || 'center' === align ) {
			return { 'data-align': align };
		}
	},

	edit( props ) {
		const { attributes, setAttributes } = props;
		const { align, cutoff } = attributes;
		let timeAgo;

		if ( attributes.dateTime ) {
			if ( attributes.cutoff && parseInt( attributes.cutoff ) < Math.abs( moment( attributes.dateTime ).diff( moment(), 'seconds' ) ) ) {
				timeAgo = moment( attributes.dateTime ).format( 'dddd D MMMM HH:mm' );
			} else {
				timeAgo = moment( attributes.dateTime ).fromNow();
			}
		} else {
			timeAgo = moment( Date.now() ).fromNow();
			setAttributes( { dateTime: moment( moment(), moment.ISO_8601, true ).format() } );
		}

		const ampLayoutOptions = [
			{ value: '', label: __( 'Responsive', 'amp' ) },
			{ value: 'fixed', label: __( 'Fixed', 'amp' ) },
			{ value: 'fixed-height', label: __( 'Fixed height', 'amp' ) },
		];

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'AMP Timeago Settings', 'amp' ) }>
						<DateTimePicker
							locale="en"
							currentDate={ attributes.dateTime || moment() }
							onChange={ value => ( setAttributes( { dateTime: moment( value, moment.ISO_8601, true ).format() } ) ) } // eslint-disable-line
						/>
						<LayoutControls { ...props } ampLayoutOptions={ ampLayoutOptions } />
						<TextControl
							type="number"
							className="blocks-amp-timeout__cutoff"
							label={ __( 'Cutoff (seconds)', 'amp' ) }
							value={ cutoff !== undefined ? cutoff : '' }
							onChange={ ( value ) => ( setAttributes( { cutoff: value } ) ) }
						/>
					</PanelBody>
				</InspectorControls>
				<BlockControls>
					<BlockAlignmentToolbar
						value={ align }
						onChange={ ( nextAlign ) => {
							setAttributes( { align: nextAlign } );
						} }
						controls={ [ 'left', 'center', 'right' ] }
					/>
				</BlockControls>
				<time dateTime={ attributes.dateTime }>{ timeAgo }</time>
			</>
		);
	},

	save( { attributes } ) {
		const timeagoProps = {
			layout: 'responsive',
			className: 'align' + ( attributes.align || 'none' ),
			datetime: attributes.dateTime,
			locale: 'en',
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
	},
};

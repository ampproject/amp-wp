/**
 * External dependencies
 */
import moment from 'moment';
import PropTypes from 'prop-types';

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
import { LayoutControls } from '../../components';

const BlockEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const { align, cutoff, dateTime } = attributes;
	let timeAgo;

	if ( dateTime ) {
		if ( cutoff && parseInt( cutoff ) < Math.abs( moment( dateTime ).diff( moment(), 'seconds' ) ) ) {
			timeAgo = moment( dateTime ).format( 'dddd D MMMM HH:mm' );
		} else {
			timeAgo = moment( dateTime ).fromNow();
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
						currentDate={ dateTime || moment() }
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
			<time dateTime={ dateTime }>{ timeAgo }</time>
		</>
	);
};

BlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		align: PropTypes.string,
		cutoff: PropTypes.number,
		dateTime: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BlockEdit;

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { metaToAttributeNames } from '../helpers';

/**
 * WordPress dependencies
 */
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';

import {
	withSelect,
	withDispatch,
} from '@wordpress/data';

import {
	SelectControl,
	RangeControl,
} from '@wordpress/components';

let MetaFields = ( props ) => {
	const {
		meta,
		updateMeta,
	} = props;

	const {
		autoAdvanceAfter,
		autoAdvanceAfterDuration,
	} = metaToAttributeNames( meta );

	const autoAdvanceAfterOptions = [
		{ value: '', label: __( 'Manual', 'amp' ) },
		{ value: 'auto', label: __( 'Automatic', 'amp' ) },
		{ value: 'time', label: __( 'After a certain time', 'amp' ) },
		{ value: 'media', label: __( 'After media has played', 'amp' ) },
	];

	let autoAdvanceAfterHelp;

	if ( 'media' === autoAdvanceAfter ) {
		autoAdvanceAfterHelp = __( 'Based on the first media block encountered on the page', 'amp' );
	} else if ( 'auto' === autoAdvanceAfter ) {
		autoAdvanceAfterHelp = __( 'Based on the duration of all animated blocks on the page', 'amp' );
	}

	return (
		<>
			<SelectControl
				label={ __( 'Advance to next page', 'amp' ) }
				help={ autoAdvanceAfterHelp }
				value={ autoAdvanceAfter }
				options={ autoAdvanceAfterOptions }
				onChange={ ( value ) => updateMeta( { stories_settings_auto_advance_after: value } ) }
			/>
			{ 'time' === autoAdvanceAfter && (
				<RangeControl
					label={ __( 'Time in seconds', 'amp' ) }
					value={ autoAdvanceAfterDuration ? parseInt( autoAdvanceAfterDuration ) : 0 }
					onChange={ ( value ) => updateMeta( { stories_settings_auto_advance_after_duration: value } ) }
				/>
			) }
		</>
	);
};

MetaFields.propTypes = {
	updateMeta: PropTypes.func.isRequired,
	meta: PropTypes.shape( {
		stories_settings_auto_advance_after: PropTypes.string,
		stories_settings_auto_advance_after_duration: PropTypes.number,
	} ),
};

MetaFields = compose(
	withSelect( ( select ) => {
		const { getEditedPostAttribute } = select( 'core/editor' );

		return {
			meta: getEditedPostAttribute( 'meta' ),
		};
	} ),
	withDispatch( ( dispatch, { meta } ) => {
		const { editPost } = dispatch( 'core/editor' );
		return {
			updateMeta( newMeta ) {
				editPost( {
					meta: {
						...meta,
						...newMeta,
					},
				} );
			},
		};
	} ),
)( MetaFields );

export const name = 'amp-story-settings-panel';

export const render = () => {
	return (
		<PluginDocumentSettingPanel
			name={ name }
			title={ __( 'Story Settings', 'amp' ) }
		>
			<MetaFields />
		</PluginDocumentSettingPanel>
	);
};

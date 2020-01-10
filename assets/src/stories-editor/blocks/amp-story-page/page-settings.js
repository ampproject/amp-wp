/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	SelectControl,
	RangeControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getTotalAnimationDuration } from '../../helpers';

/**
 * PageSettings component that displays page-specific controls for advancing to the next page in the story.
 *
 * @param {Object} props Component props.
 * @param {string} props.autoAdvanceAfter The current advancement setting.
 * @param {number} props.autoAdvanceAfterDuration The duration for when advancement should happen after a period of time.
 * @param {Function} props.setAttributes setAttributs callback.
 * @param {string} props.clientId Page client ID.
 *
 * @return {ReactElement} Component.
 */
const PageSettings = ( { autoAdvanceAfter, autoAdvanceAfterDuration, autoAdvanceAfterOptions, setAttributes, clientId } ) => {
	const totalAnimationDuration = useSelect( ( select ) => {
		const { getBlockRootClientId } = select( 'core/block-editor' );
		const { getAnimatedBlocks } = select( 'amp/story' );
		const animatedBlocks = getAnimatedBlocks();
		const animatedBlocksPerPage = ( animatedBlocks[ clientId ] || [] ).filter( ( { id } ) => clientId === getBlockRootClientId( id ) );
		const totalAnimationDurationInMs = getTotalAnimationDuration( animatedBlocksPerPage );
		return Math.ceil( totalAnimationDurationInMs / 1000 );
	}, [ clientId ] );

	const currentOption = autoAdvanceAfterOptions.find( ( i ) => i.value === autoAdvanceAfter ) || {};

	return (
		<PanelBody title={ __( 'Page Settings', 'amp' ) }>
			<SelectControl
				label={ __( 'Advance to next page', 'amp' ) }
				help={ currentOption.description || '' }
				value={ autoAdvanceAfter }
				options={ autoAdvanceAfterOptions }
				onChange={ ( value ) => {
					setAttributes( { autoAdvanceAfter: value } );
					if ( 'auto' === value ) {
						setAttributes( { autoAdvanceAfterDuration: totalAnimationDuration } );
					}
				} }
			/>
			{ 'time' === autoAdvanceAfter && (
				<RangeControl
					label={ __( 'Time in seconds', 'amp' ) }
					value={ autoAdvanceAfterDuration ? parseInt( autoAdvanceAfterDuration ) : 0 }
					onChange={ ( value ) => setAttributes( { autoAdvanceAfterDuration: value } ) }
					min={ Math.max( totalAnimationDuration, 1 ) }
					initialPosition={ totalAnimationDuration }
					help={ totalAnimationDuration > 1 ? __( 'A minimum time is enforced because there are animated blocks on this page.', 'amp' ) : undefined }
				/>
			) }
		</PanelBody>
	);
};

PageSettings.propTypes = {
	clientId: PropTypes.string.isRequired,
	autoAdvanceAfter: PropTypes.string,
	autoAdvanceAfterDuration: PropTypes.number,
	autoAdvanceAfterOptions: PropTypes.array,
	setAttributes: PropTypes.func.isRequired,
};

export default PageSettings;

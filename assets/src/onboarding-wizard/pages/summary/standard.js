/**
 * External dependencies
 */
import PropTypes from 'prop-types';
/**
 * Internal dependencies
 */
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import { Standard as StandardIllustration } from '../../../components/svg/standard';
import { SummaryHeader } from './summary-header';
import { DesktopScreenshot } from './desktop-screenshot';

/**
 * Summary screen when standard mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Standard( { currentTheme } ) {
	return (
		<>
			<SummaryHeader
				illustration={ <StandardIllustration /> }
				title={ __( 'Standard', 'amp' ) }
				text={ __( 'In Standard mode <b>your site will be completely AMP</b> (except in cases where you opt-out of AMP for specific parts of your site), and <b>it will use a single theme</b>. ', 'amp' ) }
			/>

			<DesktopScreenshot { ...currentTheme } />

		</>
	);
}

Standard.propTypes = {
	currentTheme: PropTypes.shape( {
		description: PropTypes.string,
		name: PropTypes.string,
		screenshot: PropTypes.string,
		url: PropTypes.string,
	} ).isRequired,
};

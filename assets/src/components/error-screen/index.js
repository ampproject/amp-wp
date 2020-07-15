/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Panel } from '@wordpress/components';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Screen that shows when an error has broken the application.
 *
 * @param {Object} props Component props.
 * @param {Object} props.error Error object containing a message string.
 * @param {string} props.finishLink The link to return to the admin.
 */
export function ErrorScreen( { error, finishLink } ) {
	return (
		<div className="error-screen-container">
			<Panel className="error-screen">
				<h1>
					{ __( 'The setup wizard has experienced an error.', 'amp' ) }
				</h1>
				<p>
					{ /* dangerouslySetInnerHTML reason: WordPress sometimes sends back HTML in error messages. */ }
					<span
						dangerouslySetInnerHTML={ { __html: error.message || __( 'There was an error loading the setup wizard.', 'amp' ) } }
					/>
					{ ' ' }
					{ finishLink && (
						<a href={ finishLink }>
							{ __( 'Return to AMP settings.', 'amp' ) }
						</a>
					) }
				</p>
			</Panel>
		</div>
	);
}

ErrorScreen.propTypes = {
	error: PropTypes.shape( {
		message: PropTypes.string,
	} ).isRequired,
	finishLink: PropTypes.string.isRequired,
};

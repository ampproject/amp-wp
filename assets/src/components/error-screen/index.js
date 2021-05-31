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
 * @param {Object} props.finishLink The link to return to the admin.
 * @param {string} props.title Custom message title.
 */
export function ErrorScreen( { error, finishLink, title } ) {
	return (
		<div className="error-screen-container">
			<Panel className="error-screen">
				<h1>
					{ title || __( 'Something went wrong.', 'amp' ) }
				</h1>
				<p>
					{ /* dangerouslySetInnerHTML reason: WordPress sometimes sends back HTML in error messages. */ }
					<span
						dangerouslySetInnerHTML={ { __html: error.message || __( 'There was an error loading the page.', 'amp' ) } }
					/>
					{ ' ' }
					{ finishLink?.url && finishLink?.label && (
						<a href={ finishLink.url }>
							{ finishLink.label }
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
	finishLink: PropTypes.shape( {
		label: PropTypes.string.isRequired,
		url: PropTypes.string.isRequired,
	} ),
	title: PropTypes.string,
};

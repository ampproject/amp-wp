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
 * @param {string} props.finishLinkLabel Label of a link to return to the admin.
 * @param {string} props.finishLinkUrl Url of a link to return to the admin.
 * @param {string} props.title Custom message title.
 */
export function ErrorScreen( { error, finishLinkLabel, finishLinkUrl, title } ) {
	return (
		<div className="error-screen-container">
			<Panel className="error-screen">
				<h1>
					{ title || __( 'Something went wrong.', 'amp' ) }
				</h1>

				{ /* dangerouslySetInnerHTML reason: WordPress sometimes sends back HTML in error messages. */ }
				<p dangerouslySetInnerHTML={ {
					__html: error.message || __( 'There was an error loading the page.', 'amp' ),
				} } />

				{ error?.stack && (
					<details>
						<summary>
							{ __( 'Details', 'amp' ) }
						</summary>
						<pre dangerouslySetInnerHTML={ { __html: error.stack } } />
					</details>
				) }

				{ finishLinkUrl && finishLinkLabel && (
					<p>
						<a href={ finishLinkUrl }>
							{ finishLinkLabel }
						</a>
					</p>
				) }
			</Panel>
		</div>
	);
}

ErrorScreen.propTypes = {
	error: PropTypes.shape( {
		message: PropTypes.string,
		stack: PropTypes.string,
	} ).isRequired,
	finishLinkLabel: PropTypes.string,
	finishLinkUrl: PropTypes.string,
	title: PropTypes.string,
};

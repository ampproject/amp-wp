/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Panel } from '@wordpress/components';
import { createInterpolateElement, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import ClipboardButton from '../clipboard-button';
import './style.css';

/**
 * Screen that shows when an error has broken the application.
 *
 * @param {Object} props                 Component props.
 * @param {Object} props.error           Error object containing a message string.
 * @param {string} props.finishLinkLabel Label of a link to return to the admin.
 * @param {string} props.finishLinkUrl   Url of a link to return to the admin.
 * @param {string} props.title           Custom message title.
 */
export function ErrorScreen( { error, finishLinkLabel, finishLinkUrl, title } ) {
	const [ hasCopied, setHasCopied ] = useState( false );
	const { message, stack } = error;

	return (
		<div className="error-screen-container">
			<Panel className="error-screen">
				<h1>
					{ title || __( 'Something went wrong.', 'amp' ) }
				</h1>

				{ /* dangerouslySetInnerHTML reason: WordPress sometimes sends back HTML in error messages. */ }
				<p dangerouslySetInnerHTML={ {
					__html: message || __( 'There was an error loading the page.', 'amp' ),
				} } />

				<p>
					{
						createInterpolateElement(
							__( 'Please submit details to our <a>support forum</a>.', 'amp' ),
							{
								// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
								a: <a href="https://wordpress.org/support/plugin/amp/" target="_blank" rel="noreferrer noopener" />,
							},
						)
					}
				</p>

				{ stack && (
					<details>
						<summary>
							{ __( 'Details', 'amp' ) }
						</summary>
						<pre>
							{ stack }
						</pre>
						<ClipboardButton
							isSmall={ true }
							isSecondary={ true }
							text={ JSON.stringify( { message, stack }, null, 2 ) }
							onCopy={ () => setHasCopied( true ) }
							onFinishCopy={ () => setHasCopied( false ) }
						>
							{ hasCopied ? __( 'Copied!', 'amp' ) : __( 'Copy Error', 'amp' ) }
						</ClipboardButton>
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

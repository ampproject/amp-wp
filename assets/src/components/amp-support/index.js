/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { Button, ExternalLink } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';
import { Selectable } from '../selectable';
import { AMPNotice } from '../amp-notice';
import ClipboardButton from '../clipboard-button';
import { SiteInfo } from './site-info';
import { Theme } from './theme';
import { Plugins } from './plugins';
import { Errors } from './errors';
import { ErrorSources } from './error-sources';
import { ValidatedUrls } from './validated-urls';
import { RawData } from './raw-data';

/**
 * AMP Support component.
 *
 * @class
 * @param {Object} props Props for component.
 * @return {JSX.Element} Makrup for AMP support component
 */
export function AMPSupport( props ) {
	const { data } = props;

	const [ sending, setSending ] = useState( false );
	const [ uuid, setUuid ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ hasCopied, setHasCopied ] = useState( false );

	/**
	 * Event callback for send button.
	 */
	const submitData = () => {
		( async () => {
			setSending( true );
			setUuid( null );
			setError( null );

			try {
				apiFetch.use( apiFetch.createNonceMiddleware( props.nonce ) );

				const response = await apiFetch(
					{
						path: props.restEndpoint,
						method: 'POST',
						data: {
							args: props.args,
						},
					},
				);

				if ( undefined !== response.success && undefined !== response.data ) {
					setUuid( response.data.uuid );
				} else {
					throw new Error( __( 'Failed to send support request. Please try again after some time', 'amp' ) );
				}
			} catch ( exception ) {
				setError( exception.message );
			} finally {
				setSending( false );
			}
		} )();
	};

	return (
		<div className="amp-support">
			<Selectable>
				<h2 className="amp-support__heading">
					{ __( 'AMP Support', 'amp' ) }
				</h2>
				{ /* dangerouslySetInnerHTML reason: Injection of links. */ }
				<p dangerouslySetInnerHTML={
					{
						__html: sprintf(
							/* translators: %s is the URL to create a new support topic */
							__( 'In order to best assist you, please submit the following information to our private database. Once you have done so, copy the the resulting support ID and mention it in a <a href="%s" rel="noreferrer" target="_blank">support forum topic</a>. You do not have to submit data to get support, but our team will be able to help you more effectively if you do so.', 'amp' ),
							'https://wordpress.org/support/plugin/amp/#new-topic-0',
						),
					}
				} />
				<div className="amp-support__body">

					{ data.site_info && <SiteInfo data={ data.site_info } /> }

					{ data.themes && <Theme data={ data.themes } /> }

					{ data.plugins && <Plugins data={ data.plugins } /> }

					{ data.errors && <Errors data={ data.errors } /> }

					{ data.error_sources && <ErrorSources data={ data.error_sources } /> }

					{ data.urls && <ValidatedUrls data={ data.urls } /> }

					{ data && <RawData data={ data } /> }

				</div>
				<div className="amp-support__footer">
					<Button
						disabled={ Boolean( uuid || sending ) }
						className="components-button--send-button is-primary"
						onClick={ submitData }
					>
						{ uuid && __( 'Sent', 'amp' ) }
						{ sending && __( 'Sending…', 'amp' ) }
						{ ( ! uuid && ! sending ) && __( 'Send data', 'amp' ) }
					</Button>
					{
						uuid && (
							<ExternalLink href="https://wordpress.org/support/plugin/amp/#new-topic-0">
								{ __( 'Create support topic', 'amp' ) }
							</ExternalLink>
						)
					}
					{ error && (
						<AMPNotice type="error" size="small">
							{ error }
						</AMPNotice>
					) }
				</div>
				{ uuid && (
					<AMPNotice type="info" size="small">
						{ __( 'Support UUID: ', 'amp' ) }
						<code>
							{ uuid }
						</code>
						<ClipboardButton
							isSmall={ true }
							text={ uuid }
							onCopy={ () => setHasCopied( true ) }
							onFinishCopy={ () => setHasCopied( false ) }
						>
							{ hasCopied ? __( 'Copied!', 'amp' ) : __( 'Copy UUID', 'amp' ) }
						</ClipboardButton>
					</AMPNotice>
				) }
			</Selectable>
		</div>
	);
}

AMPSupport.propTypes = {
	restEndpoint: PropTypes.string.isRequired,
	nonce: PropTypes.string.isRequired,
	args: PropTypes.any,
	data: PropTypes.shape( {
		error_sources: PropTypes.array.isRequired,
		errors: PropTypes.array.isRequired,
		plugins: PropTypes.array,
		site_info: PropTypes.object,
		themes: PropTypes.array,
		urls: PropTypes.array,
	} ),
};

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

/**
 * Internal dependencies
 */
import './style.scss';
import { __, sprintf } from '@wordpress/i18n';
import { Selectable } from '../selectable';
import { AMPNotice } from '../amp-notice';
import { SiteInfo } from './site-info';
import { Theme } from './theme';
import { Plugins } from './plugins';
import { Errors } from './errors';
import { ErrorSources } from './error-sources';
import { ValidatedUrls } from './validated-urls';
import { RawData } from './raw-data';
import { UUID } from './uuid';

/**
 * Event callback for send button.
 *
 * @param {Object}   event    Event Object.
 * @param {Object}   props    Component props.
 * @param {Function} setState Event Object.
 */
function submitData( event, props, setState ) {
	const element = event.target;
	const previousText = element.textContent;
	element.disabled = true;
	element.textContent = __( 'Sending…', 'amp' );

	/**
	 * Ajax callback.
	 */
	( async () => {
		setState( {
			uuid: null,
			error: null,
		} );

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

			element.disabled = false;
			element.textContent = previousText;

			if ( undefined !== response.success && undefined !== response.data ) {
				setState( { uuid: response.data.uuid } );
			} else {
				throw new Error( __( 'Failed to send support request. Please try again after some time', 'amp' ) );
			}
		} catch ( exception ) {
			setState( { error: exception.message() } );
		}
	} )();
}

/**
 * AMP Support component.
 *
 * @class
 * @param {Object} props Props for component.
 * @return {JSX.Element} Makrup for AMP support component
 */
export function AMPSupport( props ) {
	const { data } = props;

	const [ state, _setState ] = useState( {
		uuid: null,
		error: null,
		hasCopied: false,
	} );

	const setState = ( newState ) => {
		_setState( { ...state, ...newState } );
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
						disabled={ Boolean( state.uuid ) }
						className="components-button--send-button is-primary"
						onClick={ ( event ) => {
							submitData( event, props, setState );
						} }
					>
						{ __( 'Send data', 'amp' ) }
					</Button>
					{
						state.uuid && (
							<ExternalLink href="https://wordpress.org/support/plugin/amp/#new-topic-0">
								{ __( 'Create support topic', 'amp' ) }
							</ExternalLink>
						)
					}
					{ state.error && (
						<AMPNotice type="error" size="small">
							{ state.error }
						</AMPNotice>
					) }
				</div>
				{ state.uuid && <UUID state={ state } setState={ setState } /> }
			</Selectable>
		</div>
	);
}

AMPSupport.propTypes = {
	/* eslint-disable react/no-unused-prop-types */
	restEndpoint: PropTypes.string.isRequired,
	nonce: PropTypes.string.isRequired,
	args: PropTypes.any,
	/* eslint-enable react/no-unused-prop-types */
	data: PropTypes.shape( {
		error_sources: PropTypes.array.isRequired,
		errors: PropTypes.array.isRequired,
		plugins: PropTypes.array,
		site_info: PropTypes.object,
		themes: PropTypes.array,
		urls: PropTypes.array,
	} ),
};

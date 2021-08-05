/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { Button, ExternalLink } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';
import { __, sprintf } from '@wordpress/i18n';
import { ListItems } from '../list-items';
import { Selectable } from '../selectable';
import { AMPNotice } from '../amp-notice';
import ClipboardButton from '../clipboard-button';

/**
 * Render site information.
 *
 * @private
 * @param {Object} data Support data.
 * @return {JSX.Element} Site information markup.
 */
function _renderSiteInfo( data ) {
	if ( 'object' !== typeof data.site_info ) {
		return null;
	}

	const siteInfo = data.site_info;

	return (
		<details open={ false }>
			<summary>
				{ __( 'Site Information', 'amp' ) }
			</summary>
			<div className="detail-body">
				<ListItems
					heading={ __( 'Site Information', 'amp' ) }
					items={ [
						{
							label: __( 'Site URL', 'amp' ),
							value: siteInfo?.site_url,
						},
						{
							label: __( 'Site title', 'amp' ),
							value: siteInfo?.site_title,
						},
						{
							label: __( 'PHP version', 'amp' ),
							value: siteInfo?.php_version,
						},
						{
							label: __( 'MySQL version', 'amp' ),
							value: siteInfo?.mysql_version,
						},
						{
							label: __( 'WordPress version', 'amp' ),
							value: siteInfo?.wp_version,
						},
						{
							label: __( 'WordPress language', 'amp' ),
							value: siteInfo?.wp_language,
						},
					] } />
				<ListItems
					heading={ __( 'Site Health', 'amp' ) }
					items={ [
						{
							label: __( 'Https status', 'amp' ),
							value: siteInfo?.wp_https_status ? 'Yes' : 'No',
						},
						{
							label: __( 'Object cache status', 'amp' ),
							value: siteInfo?.object_cache_status ? 'Yes' : 'No',
						},
						{
							label: __( 'Libxml version', 'amp' ),
							value: siteInfo?.libxml_version,
						},
						{
							label: __( 'Is defined curl multi', 'amp' ),
							value: siteInfo?.is_defined_curl_multi ? 'Yes' : 'No',
						},
					] } />
				<ListItems
					heading={ __( 'AMP Information', 'amp' ) }
					items={ [
						{
							label: __( 'AMP mode', 'amp' ),
							value: siteInfo?.amp_mode,
						},
						{
							label: __( 'AMP version', 'amp' ),
							value: siteInfo?.amp_version,
						},
						{
							label: __( 'AMP plugin configured', 'amp' ),
							value: siteInfo?.amp_plugin_configured ? 'Yes' : 'No',
						},
						{
							label: __( 'AMP all templates supported', 'amp' ),
							value: siteInfo?.amp_all_templates_supported ? 'Yes' : 'No',
						},
						{
							label: __( 'AMP supported post types', 'amp' ),
							value: siteInfo?.amp_supported_post_types ? siteInfo.amp_supported_post_types.join( ', ' ) : '',
						},
						{
							label: __( 'AMP supported templates', 'amp' ),
							value: siteInfo?.amp_supported_templates ? siteInfo.amp_supported_templates.join( ', ' ) : '',
						},
						{
							label: __( 'AMP mobile redirect', 'amp' ),
							value: siteInfo?.amp_mobile_redirect ? 'Yes' : 'No',
						},
						{
							label: __( 'AMP reader theme', 'amp' ),
							value: siteInfo?.amp_reader_theme,
						},
					] } />
			</div>
		</details>
	);
}

/**
 * To render theme information.
 *
 * @private
 * @param {Object} data Support data.
 * @return {JSX.Element} Theme markup.
 */
function _renderTheme( data ) {
	if ( 'object' !== typeof data.themes ) {
		return null;
	}

	return (
		<details open={ false }>
			<summary>
				{ __( 'Theme', 'amp' ) }
			</summary>
			<div className="detail-body">
				<ListItems
					className="list-items--list-style-disc"
					items={ data.themes.map( ( item ) => {
						return { value: `${ item.name } ${ item.version ? '(' + item.version + ')' : '' }` };
					} ) }
				/>
			</div>
		</details>
	);
}

/**
 * To render plugins information.
 *
 * @private
 * @param {Object} data Support data.
 * @return {JSX.Element} Plugins markup.
 */
function _renderPlugins( data ) {
	if ( 'object' !== typeof data.plugins ) {
		return null;
	}

	const plugins = Object.values( data.plugins );

	return (
		<details open={ false }>
			<summary>
				{ __( 'Plugins', 'amp' ) }
				{ ` (${ plugins.length || 0 })` }
			</summary>
			<div className="detail-body">
				<ListItems
					className="list-items--list-style-disc"
					items={ plugins.map( ( item ) => {
						return { value: `${ item.name } ${ item.version ? '(' + item.version + ')' : '' }` };
					} ) }
				/>
			</div>
		</details>
	);
}

/**
 * Render error data.
 *
 * @private
 * @param {Object} data Support data.
 * @return {JSX.Element} Error detail markup.
 */
function _renderError( data ) {
	if ( 'object' !== typeof data.errors ) {
		return null;
	}

	return (
		<details open={ false }>
			<summary>
				{ __( 'Errors', 'amp' ) }
				{ ` (${ data.errors.length || 0 })` }
			</summary>
			<div className="detail-body">
				<p>
					<i>
						<small>
							{ __( 'Please check "Raw Data" for all error information.', 'amp' ) }
						</small>
					</i>
				</p>
			</div>
		</details>
	);
}

/**
 * Render error source detail.
 *
 * @private
 * @param {Object} data Support data.
 * @return {JSX.Element} Error source detail markup.
 */
function _renderErrorSource( data ) {
	if ( 'object' !== typeof data.error_sources ) {
		return null;
	}

	return (
		<details open={ false }>
			<summary>
				{ __( 'Error Sources', 'amp' ) }
				{
					( () => {
						return ` (${ data.error_sources.length || 0 })`;
					} )()
				}
			</summary>
			<div className="detail-body">
				<p>
					<i>
						<small>
							{ __( 'Please check "Raw Data" for all error source information.', 'amp' ) }
						</small>
					</i>
				</p>
			</div>
		</details>
	);
}

/**
 * Render validated URls
 *
 * @private
 * @param {Object} data Support data.
 * @return {JSX.Element} Validated URL markup.
 */
function _renderValidatedUrls( data ) {
	if ( 'object' !== typeof data.urls ) {
		return null;
	}

	const urls = data.urls.map( ( item ) => item.url ? item.url : null );

	return (
		<details open={ false }>
			<summary>
				{ __( 'Validated URLs', 'amp' ) }
				{ ` (${ data.urls.length || 0 })` }
			</summary>
			<div className="detail-body">
				<ListItems
					className="list-items--list-style-disc"
					items={ urls.map( ( url ) => {
						return {
							value: (
								<a href={ url } title={ url } target="_blank" rel="noreferrer">
									{ url }
								</a>
							),
						};
					} ) }
				/>
			</div>
		</details>
	);
}

/**
 * To render raw data.
 *
 * @private
 * @param {Object} data Support data.
 * @return {JSX.Element} Raw data markup
 */
function _renderRawData( data ) {
	return (
		<details open={ false }>
			<summary>
				{ __( 'Raw Data', 'amp' ) }
			</summary>
			<pre className="amp-support__raw-data detail-body">
				{ JSON.stringify( data, null, 4 ) }
			</pre>
		</details>
	);
}

/**
 * To render UUID Notice
 *
 * @private
 * @param {Object} state Current state of component.
 * @param {Function} setState Function to update state of component.
 * @return {JSX.Element} UUID notice markup.
 */
function _renderUUID( state, setState ) {
	if ( state.uuid ) {
		return (
			<AMPNotice type="info" size="small">
				{ __( 'Support UUID: ', 'amp' ) }
				<code>
					{ state.uuid }
				</code>
				<ClipboardButton
					isSmall={ true }
					text={ state.uuid }
					onCopy={ () => setState( { hasCopied: true } ) }
					onFinishCopy={ () => setState( { hasCopied: false } ) }
				>
					{ state.hasCopied ? __( 'Copied!', 'amp' ) : __( 'Copy UUID', 'amp' ) }
				</ClipboardButton>
			</AMPNotice>
		);
	}

	return null;
}

/**
 * To render error notice.
 *
 * @private
 * @param {Object} state Current state of component.
 * @return {JSX.Element} Error notice markup.
 */
function _renderErrorNotice( state ) {
	if ( state.error ) {
		return (
			<AMPNotice type="error" size="small">
				{ state.error }
			</AMPNotice>
		);
	}

	return null;
}

/**
 * Event callback for send button.
 *
 * @param {Object} event Event Object.
 * @param {Object} props Component props.
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
			const body = new global.FormData();
			body.append( '_wpnonce', props.nonce );

			for ( const key in props.args ) {
				if ( props.args[ key ] ) {
					props.args[ key ].map( ( value ) => {
						return body.append( `args[${ key }][]`, value );
					} );
				}
			}

			const response = await global.fetch( props.restEndpoint, {
				method: 'POST',
				body,
			} );

			element.disabled = false;
			element.textContent = previousText;

			if ( ! response.ok ) {
				throw new Error( __( 'Failed to send support request. Please try again after some time', 'amp' ) );
			}

			const responseBody = await response.json();

			if ( undefined !== responseBody.success && undefined !== responseBody.data ) {
				setState( { uuid: responseBody.data.uuid } );
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

					{ _renderSiteInfo( data ) }

					{ _renderTheme( data ) }

					{ _renderPlugins( data ) }

					{ _renderError( data ) }

					{ _renderErrorSource( data ) }

					{ _renderValidatedUrls( data ) }

					{ _renderRawData( data ) }

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
					{ state.error ? _renderErrorNotice( state ) : '' }
				</div>
				{ state.uuid ? _renderUUID( state, setState ) : '' }
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

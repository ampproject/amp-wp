/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
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

export class AMPSupport extends Component {
	/**
	 * Construct method.
	 */
	constructor() {
		super();

		this.state = {
			uuid: null,
			error: null,
			hasCopied: false,
		};
	}

	/**
	 * To render component.
	 *
	 * @return {JSX.Element} Component markup.
	 */
	render() {
		const { data } = this.props;

		return (
			<div className="amp-support">
				<Selectable>
					<h2 className="amp-support__heading">
						{ __( 'AMP Support', 'amp' ) }
					</h2>
					{ /* dangerouslySetInnerHTML reason: Injection of links. */ }
					<p dangerouslySetInnerHTML={
						{ __html: sprintf(
							/* translators: %s is the URL to create a new support topic */
							__( 'In order to best assist you, please submit the following information to our private database. Once you have done so, copy the the resulting support ID and mention it in a <a href="%s" rel="noreferrer" target="_blank">support forum topic</a>. You do not have to submit data to get support, but our team will be able to help you more effectively if you do so.', 'amp' ),
							'https://wordpress.org/support/plugin/amp/#new-topic-0',
						) }
					} />
					<div className="amp-support__body">

						{ this._renderSiteInfo() }

						{ this._renderTheme() }

						{ this._renderPlugins() }

						<details open={ false }>
							<summary>
								{ __( 'Errors', 'amp' ) }
								{
									( () => {
										return ` (${ data.errors.length || 0 })`;
									} )()
								}
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

						{ this._renderValidatedUrls() }

						{ this._renderRawData() }
					</div>
					<div className="amp-support__footer">
						<Button
							disabled={ Boolean( this.state.uuid ) }
							className="components-button--send-button is-primary"
							onClick={ this.submitData }
						>
							{ __( 'Send data', 'amp' ) }
						</Button>
						{
							this.state.uuid && (
								<ExternalLink href="https://wordpress.org/support/plugin/amp/#new-topic-0">
									{ __( 'Create support topic', 'amp' ) }
								</ExternalLink>
							)
						}
						{ this.state.error ? this._renderError() : '' }
					</div>
					{ this.state.uuid ? this._renderUUID() : '' }
				</Selectable>
			</div>
		);
	}

	/**
	 * Render site information.
	 *
	 * @private
	 * @return {JSX.Element} Site information markup.
	 */
	_renderSiteInfo() {
		const { data } = this.props;

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
							{ label: 'Site URL', value: siteInfo?.site_url },
							{ label: 'Site title', value: siteInfo?.site_title },
							{ label: 'PHP version', value: siteInfo?.php_version },
							{ label: 'MySQL version', value: siteInfo?.mysql_version },
							{ label: 'WordPress version', value: siteInfo?.wp_version },
							{ label: 'WordPress language', value: siteInfo?.wp_language },
						] } />
					<ListItems
						heading={ __( 'Site Health', 'amp' ) }
						items={ [
							{ label: 'Https status', value: siteInfo?.wp_https_status ? 'Yes' : 'No' },
							{ label: 'Object cache status', value: siteInfo?.object_cache_status ? 'Yes' : 'No' },
							{ label: 'Libxml version', value: siteInfo?.libxml_version },
							{ label: 'Is defined curl multi', value: siteInfo?.is_defined_curl_multi ? 'Yes' : 'No' },
						] } />
					<ListItems
						heading={ __( 'AMP Information', 'amp' ) }
						items={ [
							{ label: 'AMP mode', value: siteInfo?.amp_mode },
							{ label: 'AMP version', value: siteInfo?.amp_version },
							{ label: 'AMP plugin configured', value: siteInfo?.amp_plugin_configured ? 'Yes' : 'No' },
							{
								label: 'AMP all templates supported',
								value: siteInfo?.amp_all_templates_supported ? 'Yes' : 'No',
							},
							{
								label: 'AMP supported post types',
								value: siteInfo?.amp_supported_post_types ? siteInfo.amp_supported_post_types.join( ', ' ) : '',
							},
							{
								label: 'AMP supported templates',
								value: siteInfo?.amp_supported_templates ? siteInfo.amp_supported_templates.join( ', ' ) : '',
							},
							{ label: 'AMP mobile redirect', value: siteInfo?.amp_mobile_redirect ? 'Yes' : 'No' },
							{ label: 'AMP reader theme', value: siteInfo?.amp_reader_theme },
						] } />
				</div>
			</details>
		);
	}

	/**
	 * To render theme information.
	 *
	 * @private
	 * @return {JSX.Element} Theme markup.
	 */
	_renderTheme() {
		const { data } = this.props;

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
	 * @return {JSX.Element} Plugins markup.
	 */
	_renderPlugins() {
		const { data } = this.props;

		if ( 'object' !== typeof data.plugins ) {
			return null;
		}

		const plugins = Object.values( data.plugins );

		return (
			<details open={ false }>
				<summary>
					{ __( 'Plugins', 'amp' ) }
					{
						( () => {
							return ` (${ plugins.length || 0 })`;
						} )()
					}
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
	 * Render validated URls
	 *
	 * @private
	 * @return {JSX.Element} Validated URL markup.
	 */
	_renderValidatedUrls() {
		const { data } = this.props;

		if ( 'object' !== typeof data.urls ) {
			return null;
		}

		const urls = data.urls.map( ( item ) => item.url ? item.url : null );

		return (
			<details open={ false }>
				<summary>
					{ __( 'Validated URLs', 'amp' ) }
					{
						( () => {
							return ` (${ data.urls.length || 0 })`;
						} )()
					}
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
	 * @return {JSX.Element} Raw data markup
	 */
	_renderRawData() {
		const { data } = this.props;
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
	 * @return {JSX.Element} UUID notice markup.
	 */
	_renderUUID() {
		if ( this.state.uuid ) {
			const setHasCopied = ( flag ) => {
				this.setState( {
					hasCopied: Boolean( flag ),
				} );
			};

			return (
				<AMPNotice type="info" size="small">
					{ __( 'Support UUID: ', 'amp' ) }
					<code>
						{ this.state.uuid }
					</code>
					<ClipboardButton
						isSmall={ true }
						text={ this.state.uuid }
						onCopy={ () => setHasCopied( true ) }
						onFinishCopy={ () => setHasCopied( false ) }
					>
						{ this.state.hasCopied ? __( 'Copied!', 'amp' ) : __( 'Copy UUID', 'amp' ) }
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
	 * @return {JSX.Element} Error notice markup.
	 */
	_renderError() {
		if ( this.state.error ) {
			return (
				<AMPNotice type="error" size="small">
					{ this.state.error }
				</AMPNotice>
			);
		}

		return null;
	}

	/**
	 * Event callback for send button.
	 *
	 * @param {Object} event Event Object.
	 */
	submitData = ( event ) => {
		const element = event.target;
		const previousText = element.textContent;
		element.disabled = true;
		element.textContent = __( 'Sending…', 'amp' );

		/**
		 * Ajax callback.
		 */
		( async () => {
			this.setState( { uuid: null, error: null } );

			try {
				const body = new global.FormData();
				body.append( '_wpnonce', this.props.nonce );

				for ( const key in this.props.args ) {
					if ( this.props.args[ key ] ) {
						this.props.args[ key ].map( ( value ) => {
							return body.append( `args[${ key }][]`, value );
						} );
					}
				}

				const response = await global.fetch( this.props.restEndpoint, {
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
					this.setState( {
						uuid: responseBody.data.uuid,
					} );
				} else {
					throw new Error( __( 'Failed to send support request. Please try again after some time', 'amp' ) );
				}
			} catch ( exception ) {
				this.setState( { error: exception.message } );
			}
		} )();
	}
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

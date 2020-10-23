/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useContext, Fragment } from '@wordpress/element';
import { __, sprintf, _n } from '@wordpress/i18n';
import { autop } from '@wordpress/autop';
import { format, dateI18n } from '@wordpress/date';
import { SelectControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { ConditionalDetails } from '../components/conditional-details';
import { SiteSettings } from '../components/site-settings-provider';

/**
 * Renders the formatted date for when a plugin was suppressed.
 *
 * @param {Object} props Component props.
 * @param {Object} props.suppressedPlugin
 */
function SuppressedPluginTime( { suppressedPlugin } ) {
	const { settings } = useContext( SiteSettings );

	const { date_format: dateFormat } = settings;

	if ( ! suppressedPlugin || ! suppressedPlugin.timestamp || ! dateFormat ) {
		return null;
	}

	return (
		<time dateTime={ format( 'c', suppressedPlugin.timestamp ) }>
			{
				/* translators: placeholder is a formatted date. */
				sprintf( __( 'Since %s.', 'amp' ), dateI18n( dateFormat, suppressedPlugin.timestamp * 1000 ) )
			}
		</time>
	);
}
SuppressedPluginTime.propTypes = {
	suppressedPlugin: PropTypes.shape( {
		timestamp: PropTypes.number,
	} ),
};

/**
 * Renders the username of the WP user who suppressed a plugin.
 *
 * @param {Object} props Component props.
 * @param {Object} props.suppressedPlugin
 */
function SuppressedPluginUsername( { suppressedPlugin } ) {
	return (
		<span>
			{
				/* translators: placeholder is the name of the user who suppressed the plugin */
				sprintf( __( 'Done by %s.', 'amp' ), suppressedPlugin.user.name || suppressedPlugin.user.slug )
			}
		</span>
	);
}
SuppressedPluginUsername.propTypes = {
	suppressedPlugin: PropTypes.shape( {
		timestamp: PropTypes.number,
		user: PropTypes.shape( {
			slug: PropTypes.string,
			name: PropTypes.string,
		} ),
	} ),
};

/**
 * Renders information about a suppressed plugin's version.
 *
 * @param {Object} props
 * @param {Object} props.pluginDetails
 * @param {Object} props.suppressedPlugin
 */
function SuppressedPluginVersion( { pluginDetails, suppressedPlugin } ) {
	if ( suppressedPlugin.last_version === pluginDetails.Version ) {
		return null;
	}

	if ( pluginDetails.Version ) {
		return (
			<span>
				{
					sprintf(
						/* translators: both placeholders are plugin version numbers. */
						__( 'Now updated to version %1$s since suppressed at %2$s.', 'amp' ),
						pluginDetails.Version,
						suppressedPlugin.last_version,
					)
				}
			</span>
		);
	}

	return __( 'Plugin updated since last suppressed.', 'amp' );
}
SuppressedPluginVersion.propTypes = {
	pluginDetails: PropTypes.shape( {
		Version: PropTypes.string,
	} ),
	suppressedPlugin: PropTypes.shape( {
		last_version: PropTypes.string,
	} ),
};

/**
 * Renders the validation errors for a plugin that hasn't been suppressed.
 *
 * @param {Object} props
 * @param {Array} props.errors
 */
function ValidationErrorDetails( { errors } ) {
	if ( errors.length === 0 ) {
		return (
			<p>
				{ __( 'No validation errors yet detected.', 'amp' ) }
			</p>
		);
	}

	return (
		<details>
			<summary>
				{
					sprintf(
						/* translators: %s is the error count */
						_n(
							'%s validation error',
							'%s validation errors',
							errors.length,
							'amp',
						),
						errors.length,
					)
				}
			</summary>
			<ul>
				{ errors.map( ( error ) => {
					const WrapperElement = ! error.is_reviewed ? 'strong' : Fragment;

					return (
						<li
							key={ error.term.term_id }
							className={ classnames(
								`error-${ error.is_removed ? 'removed' : 'kept' }`,
								`error-${ error.is_reviewed ? 'reviewed' : 'unreviewed' }`,
							) }>
							<WrapperElement>
								<a href={ error.edit_url } target="_blank" rel="noreferrer" title={ error.tooltip }>
									<span dangerouslySetInnerHTML={ { __html: error.title } } />
								</a>
							</WrapperElement>
						</li>
					);
				} ) }
			</ul>
		</details>
	);
}
ValidationErrorDetails.propTypes = {
	errors: PropTypes.arrayOf( PropTypes.shape( {
		is_removed: PropTypes.bool,
		is_reviewed: PropTypes.bool,
		edit_url: PropTypes.string,
		term: PropTypes.object,
		title: PropTypes.string,
		tooltip: PropTypes.string,
	} ) ),
};

/**
 * Row in the plugin suppression table.
 *
 * @param {Object} props
 * @param {Object} props.pluginDetails Object containing details about the plugin.
 * @param {string} props.pluginKey A plugin key.
 */
function PluginRow( { pluginKey, pluginDetails } ) {
	const { editedOptions, originalOptions, updateOptions } = useContext( Options );

	const { suppressed_plugins: editedSuppressedPlugins } = editedOptions;
	const { suppressed_plugins: originalSuppressedPlugins } = originalOptions;

	const isOriginallySuppressed = pluginKey in originalSuppressedPlugins;
	const isSuppressed = pluginKey in editedSuppressedPlugins && editedSuppressedPlugins[ pluginKey ] !== false;

	const PluginName = () => (
		<strong className="plugin-name">
			{ pluginDetails.Name }
		</strong>
	);

	const errorDetails = (
		<div className="error-details">
			{
				isOriginallySuppressed ? (
					<p>
						<SuppressedPluginTime suppressedPlugin={ originalSuppressedPlugins[ pluginKey ] } />
						{ ' ' }
						<SuppressedPluginUsername suppressedPlugin={ originalSuppressedPlugins[ pluginKey ] } />
						{ ' ' }
						<SuppressedPluginVersion
							pluginDetails={ pluginDetails }
							suppressedPlugin={ originalSuppressedPlugins[ pluginKey ] }
						/>
					</p>
				) : (
					<ValidationErrorDetails errors={ pluginDetails.validation_errors } />
				)
			}
		</div>
	);

	return (
		<tr className={ classnames(
			{
				'has-border-color': isSuppressed || pluginDetails.validation_errors.length,
				'has-validation-errors': ! isSuppressed && pluginDetails.validation_errors.length,
				'is-suppressed': isSuppressed,
			},
		) }>
			<th className="column-status" scope="row">
				<SelectControl
					hideLabelFromVision={ true }
					onChange={ () => {
						const newSuppressedPlugins = { ...editedOptions.suppressed_plugins };

						newSuppressedPlugins[ pluginKey ] = ! isSuppressed;

						updateOptions( { suppressed_plugins: newSuppressedPlugins } );
					} }
					value={ isSuppressed }
					label={ __( 'Plugin status:', 'amp' ) }
					options={ [
						{ value: false, label: __( 'Active', 'amp' ) },
						{ value: true, label: __( 'Suppressed', 'amp' ) },
					] }
				/>
			</th>
			<td className="column-plugin">
				<ConditionalDetails
					summary={ (
						<PluginName />
					) }
				>
					{ [
						pluginDetails.Author && (
							<p className="plugin-author-uri" key={ `${ pluginKey }-details-author` }>
								{ pluginDetails.AuthorURI ? (
									<a href={ pluginDetails.AuthorURI } target="_blank" rel="noreferrer">
										{
											/* translators: placeholder is an author name. */
											sprintf( __( 'By %s' ), pluginDetails.Author )
										}
									</a> )
									: (
										/* translators: placeholder is an author name. */
										sprintf( __( 'By %s' ), pluginDetails.Author )
									)
								}

							</p>
						),
						pluginDetails.Description && (
							<div
								key={ `${ pluginKey }-details-description` }
								className="plugin-description"
								dangerouslySetInnerHTML={ { __html: autop( pluginDetails.Description ) } }
							/>
						),
						pluginDetails.PluginURI && (
							<a href={ pluginDetails.PluginURI } target="_blank" rel="noreferrer" key={ `${ pluginKey }-details-plugin-uri` }>
								{ __( 'More details', 'amp' ) }
							</a>
						),

					].filter( ( child ) => child ) }
				</ConditionalDetails>

				{ errorDetails }
			</td>
			<td className="column-details">
				{ errorDetails }
			</td>
		</tr>
	);
}
PluginRow.propTypes = {
	pluginDetails: PropTypes.shape( {
		Author: PropTypes.string,
		AuthorURI: PropTypes.string,
		Description: PropTypes.string,
		Name: PropTypes.string,
		PluginURI: PropTypes.string,
		validation_errors: PropTypes.array,
	} ).isRequired,
	pluginKey: PropTypes.string.isRequired,
};

/**
 * Component rendering the plugin suppression table.
 */
export function PluginSuppression() {
	const { editedOptions } = useContext( Options );

	const {
		suppressible_plugins: suppressiblePlugins,
	} = editedOptions;

	const Description = () => (
		<p>
			{ __( 'When a plugin adds markup that is not allowed in AMP you may let the AMP plugin remove it, or you may suppress the plugin from running on AMP pages. The following list includes all active plugins on your site, with any of those detected to be generating invalid AMP markup appearing first.', 'amp' ) }
		</p>
	);

	if ( ! suppressiblePlugins || 0 === Object.keys( suppressiblePlugins ).length ) {
		return (
			<>
				<Description />
				<p>
					<em>
						{ __( 'You have no suppressible plugins active.', 'amp' ) }
					</em>

				</p>
			</>
		);
	}

	return (
		<>
			<Description />
			<table id="suppressed-plugins-table" className="wp-list-table widefat fixed">
				<thead>
					<tr>
						<th className="column-status" scope="col">
							{ __( 'Status', 'amp' ) }
						</th>
						<th className="column-plugin" scope="col">
							{ __( 'Plugin', 'amp' ) }
						</th>
						<th className="column-details" scope="col">
							{ __( 'Details', 'amp' ) }
						</th>
					</tr>
				</thead>
				<tbody>
					{ Object.keys( suppressiblePlugins || {} ).map( ( pluginKey ) => (
						<PluginRow
							key={ `plugin-row-${ pluginKey }` }
							pluginDetails={ suppressiblePlugins[ pluginKey ] }
							pluginKey={ pluginKey }
						/>
					) ) }
				</tbody>
			</table>
		</>
	);
}

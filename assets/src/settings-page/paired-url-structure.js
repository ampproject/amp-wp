/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { AMPDrawer } from '../components/amp-drawer';
import { AMPNotice, NOTICE_TYPE_INFO, NOTICE_SIZE_LARGE } from '../components/amp-notice';

/**
 * @typedef {{name: string, slug: string, type: string}} Source
 * @typedef {{query_var: string[], path_suffix: string[], legacy_transitional: string[], legacy_reader: string[], custom: string[]}} PairedUrlExamplesData
 */

/**
 * Paired URL examples.
 *
 * @param {Object} props Component props.
 * @param {?Array} props.pairedUrls Paired URLs.
 */
const PairedUrlExamples = ( { pairedUrls } ) => {
	if ( ! pairedUrls ) {
		return null;
	}

	return (
		<details className="amp-paired-url-examples">
			<summary>
				{ __( 'Examples', 'amp' ) }
			</summary>
			{
				pairedUrls.map( ( pairedUrl ) => {
					return (
						<div className="amp-paired-url-example" key={ pairedUrl }>
							<code>
								{ pairedUrl }
							</code>
						</div>
					);
				} )
			}
		</details>
	);
};
PairedUrlExamples.propTypes = {
	pairedUrls: PropTypes.arrayOf( PropTypes.string ),
};

/**
 * Get custom paired structure sources.
 *
 * @param {Array.<Source>} sources Sources.
 * @return {string} Sources string.
 */
function getCustomPairedStructureSources( sources ) {
	let message = __( 'The custom structure is being introduced by:', 'amp' ) + ' ';
	message += sources.map( ( source ) => {
		let sourceString = source.name || source.slug;
		let typeString;
		switch ( source.type ) {
			case 'plugin':
				typeString = __( 'a plugin', 'amp' );
				break;
			case 'theme':
				typeString = __( 'a theme', 'amp' );
				break;
			case 'mu-plugin':
				typeString = __( 'a must-use plugin', 'amp' );
				break;
			default:
				typeString = null;
		}
		if ( typeString ) {
			sourceString += ` (${ typeString })`;
		}

		return sourceString;
	} ).join( ', ' ) + '.';
	return message;
}

/**
 * Slug conflicts notice.
 *
 * @param {Object} props           Component props.
 * @param {string} props.slug      Slug.
 * @param {Object} props.conflicts Conflicts.
 */
function SlugConflictsNotice( { slug, conflicts } ) {
	return (
		<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
			<p>
				{
					sprintf(
						/* translators: %s is the AMP slug */
						__( 'There are one or more entities that are already using the “%s” URL slug. For this reason, you cannot currently use the path suffix or legacy reader paired URL structures. See below for the source of the slug conflict(s):', 'amp' ),
						slug,
					)
				}
			</p>
			<ul>
				{ conflicts.posts && (
					conflicts.posts.map( ( entity ) => (
						<li key={ `post-${ entity.id }` }>
							{
								entity.edit_link ? (
									<a href={ entity.edit_link } target="_blank" rel="noreferrer">
										{ entity.label || entity.post_type }
									</a>
								) : (
									entity.label || entity.post_type
								)
							}
							{
								entity.title && (
									': ' + entity.title
								)
							}
							{ ' ' }
							<small>
								{
									/* translators: %d is entity ID */
									sprintf( __( '(ID: %d)', 'amp' ), entity.id )
								}
							</small>
						</li>
					) )
				) }

				{ conflicts.terms && (
					conflicts.terms.map( ( entity ) => (
						<li key={ `term-${ entity.id }` }>
							{
								entity.edit_link ? (
									<a href={ entity.edit_link } target="_blank" rel="noreferrer">
										{ entity.label || entity.post_type }
									</a>
								) : (
									entity.label || entity.post_type
								)
							}
							{
								entity.name && (
									': ' + entity.name
								)
							}
							{ ' ' }
							<small>
								{
									/* translators: %d is entity ID */
									sprintf( __( '(ID: %d)', 'amp' ), entity.id )
								}
							</small>
						</li>
					) )
				) }

				{
					conflicts.user && (
						[ conflicts.user ].map( ( entity ) => (
							<li key={ `user-${ entity.id }` }>
								{
									entity.edit_link ? (
										<a href={ entity.edit_link } target="_blank" rel="noreferrer">
											{ __( 'User', 'amp' ) }
										</a>
									) : (
										__( 'User', 'amp' )
									)
								}
								{ ': ' + entity.name }
								{ ' ' }
								<small>
									{
										/* translators: %d is entity ID */
										sprintf( __( '(ID: %d)', 'amp' ), entity.id )
									}
								</small>
							</li>
						) )
					)
				}

				{
					conflicts.post_type && (
						<li>
							{
								sprintf(
									/* translators: %s is post type label */
									__( 'Post type: %s', 'amp' ),
									conflicts.post_type.label,
								)
							}
							{ ' ' }
							<small>
								{
									/* translators: %s is entity name */
									sprintf( __( '(name: %s)', 'amp' ), conflicts.post_type.name )
								}
							</small>
						</li>
					)
				}

				{
					conflicts.taxonomy && (
						<li>
							{
								sprintf(
									/* translators: %s is taxonomy label */
									__( 'Taxonomy: %s', 'amp' ),
									conflicts.taxonomy.label,
								)
							}
							{ ' ' }
							<small>
								{
									/* translators: %s is entity name */
									sprintf( __( '(name: %s)', 'amp' ), conflicts.taxonomy.name )
								}
							</small>
						</li>
					)
				}

				{ conflicts.rewrite && (
					<li>
						{ __( 'Rewrite rules: ', 'amp' ) }
						{
							conflicts.rewrite
								.map( ( entity ) => (
									<code key={ entity }>
										{ entity }
									</code>
								) )
								.reduce( ( prev, curr ) => [ prev, ', ', curr ] )
						}
					</li>
				) }
			</ul>
		</AMPNotice>
	);
}
SlugConflictsNotice.propTypes = {
	slug: PropTypes.string.isRequired,
	conflicts: PropTypes.shape( {
		post_type: PropTypes.object,
		posts: PropTypes.arrayOf( PropTypes.object ),
		rewrite: PropTypes.arrayOf( PropTypes.string ),
		taxonomy: PropTypes.object,
		terms: PropTypes.arrayOf( PropTypes.object ),
		user: PropTypes.object,
	} ).isRequired,
};

/**
 * Component rendering the paired URL structure.
 *
 * @param {Object} props Component props.
 * @param {string} props.focusedSection Focused section.
 */
export function PairedUrlStructure( { focusedSection } ) {
	/** @type {{amp_slug:string, endpoint_path_slug_conflicts:Array, custom_paired_endpoint_sources:Array.<Source>, paired_url_examples: PairedUrlExamplesData, rewrite_using_permalinks: boolean}} editedOptions */
	const { editedOptions, updateOptions } = useContext( Options );

	const { theme_support: themeSupport } = editedOptions || {};

	// Don't show if the mode is standard or the themeSupport is not yet set.
	if ( ! themeSupport || 'standard' === themeSupport ) {
		return null;
	}

	const slug = editedOptions.amp_slug;

	const isCustom = 'custom' === editedOptions.paired_url_structure;

	const endpointSuffixAvailable = editedOptions.endpoint_path_slug_conflicts === null;

	return (
		<AMPDrawer
			className="amp-paired-url-structure"
			heading={ (
				<h3>
					{ __( 'Paired URL Structure', 'amp' ) }
				</h3>
			) }
			hiddenTitle={ __( 'Paired URL Structure', 'amp' ) }
			id="paired-url-structure"
			initialOpen={ 'paired-url-structure' === focusedSection }
		>

			{ isCustom && (
				<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
					<p>
						{ __( 'A custom paired URL structure is being applied so the following options are unavailable.', 'amp' ) }
						{ editedOptions.custom_paired_endpoint_sources.length > 0 &&
							' ' + getCustomPairedStructureSources( editedOptions.custom_paired_endpoint_sources ) }
					</p>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.custom } />
				</AMPNotice>
			) }

			<p dangerouslySetInnerHTML={
				{ __html:
					sprintf(
						/* translators: 1: AMP Paired URL Structure documentation URL */
						__( 'When using the Transitional or Reader template modes, your site is in a “Paired AMP” configuration. Your site\'s canonical URLs are non-AMP, and the separate AMP versions of your pages have AMP-specific URLs. The structure of a paired AMP URL is not important, whether using a query parameter or path suffix. The use of a query parameter is the most compatible across various sites and it has the benefit of not resulting in a 404 if the AMP plugin is deactivated. <em>Note: Changing the paired URL structure can cause AMP pages to temporarily disappear from search results until your site is re-indexed.</em> If you\'re migrating from another AMP plugin with a different paired URL structure, then you may want to change this setting. Otherwise we recommend leaving it as is. <a href="%1$s" target="_blank">Learn more</a>', 'amp' ),
						__( 'https://amp-wp.org/?p=10004', 'amp' ),
					),
				} }
			/>

			{ ! endpointSuffixAvailable && (
				<SlugConflictsNotice slug={ slug } conflicts={ editedOptions.endpoint_path_slug_conflicts } />
			) }

			<ul>
				<li>
					<input
						id="paired_url_structure_query_var"
						type="radio"
						name="paired_url_structure"
						checked={ 'query_var' === editedOptions.paired_url_structure }
						onChange={ () => {
							updateOptions( { paired_url_structure: 'query_var' } );
						} }
						disabled={ isCustom }
					/>
					<label htmlFor="paired_url_structure_query_var">
						{ __( 'Query parameter', 'amp' ) + ': ' }
						<code>
							{ `?${ slug }=1` }
						</code>
						{ ' ' }
						<em>
							{ __( '(default)', 'amp' ) }
						</em>
					</label>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.query_var } />
				</li>
				<li>
					<input
						id="paired_url_structure_path_suffix"
						type="radio"
						name="paired_url_structure"
						checked={ 'path_suffix' === editedOptions.paired_url_structure }
						onChange={ () => {
							updateOptions( { paired_url_structure: 'path_suffix' } );
						} }
						disabled={ isCustom || ! endpointSuffixAvailable || ! editedOptions.rewrite_using_permalinks }
					/>
					<label htmlFor="paired_url_structure_path_suffix">
						{ __( 'Path suffix', 'amp' ) + ': ' }
						<code>
							{ `/${ slug }/` }
						</code>
						{ ! endpointSuffixAvailable && (
							<em>
								{ ' ' + __( '(unavailable due to slug conflict per above)', 'amp' ) }
							</em>
						) }
						{ ! editedOptions.rewrite_using_permalinks && (
							<em>
								{ ' ' + __( '(unavailable due to not using permalinks)', 'amp' ) }
							</em>
						) }
					</label>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.path_suffix } />
				</li>
				<li>
					<input
						id="paired_url_structure_legacy_transitional"
						type="radio"
						name="paired_url_structure"
						checked={ 'legacy_transitional' === editedOptions.paired_url_structure }
						onChange={ () => {
							updateOptions( { paired_url_structure: 'legacy_transitional' } );
						} }
						disabled={ isCustom }
					/>
					<label htmlFor="paired_url_structure_legacy_transitional">
						{ __( 'Legacy transitional', 'amp' ) + ': ' }
						<code>
							{ `?${ slug }` }
						</code>
					</label>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.legacy_transitional } />
				</li>
				<li>
					<input
						id="paired_url_structure_legacy_reader"
						type="radio"
						name="paired_url_structure"
						checked={ 'legacy_reader' === editedOptions.paired_url_structure }
						onChange={ () => {
							updateOptions( { paired_url_structure: 'legacy_reader' } );
						} }
						disabled={ isCustom || ! endpointSuffixAvailable || ! editedOptions.rewrite_using_permalinks }
					/>
					<label htmlFor="paired_url_structure_legacy_reader">
						{ __( 'Legacy reader', 'amp' ) + ': ' }
						<code>
							{ `/${ slug }/` }
						</code>
						{ ' & ' }
						<code>
							{ `?${ slug }` }
						</code>
						{ ! endpointSuffixAvailable && (
							<em>
								{ ' ' + __( '(unavailable due to slug conflict per above)', 'amp' ) }
							</em>
						) }
						{ ! editedOptions.rewrite_using_permalinks && (
							<em>
								{ ' ' + __( '(unavailable due to not using permalinks)', 'amp' ) }
							</em>
						) }
					</label>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.legacy_reader } />
				</li>
			</ul>
		</AMPDrawer>
	);
}
PairedUrlStructure.propTypes = {
	focusedSection: PropTypes.string,
};

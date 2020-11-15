/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { AMPDrawer } from '../components/amp-drawer';
import { AMPNotice, NOTICE_TYPE_INFO, NOTICE_SIZE_LARGE } from '../components/amp-notice';

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
 * @param {string[]} sources Sources.
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
 * Component rendering the paired URL structure.
 *
 * @param {Object} props Component props.
 * @param {string} props.focusedSection Focused section.
 */
export function PairedUrlStructure( { focusedSection } ) {
	const { editedOptions, updateOptions } = useContext( Options );

	const { theme_support: themeSupport } = editedOptions || {};

	// Don't show if the mode is standard or the themeSupport is not yet set.
	if ( ! themeSupport || 'standard' === themeSupport ) {
		return null;
	}

	const slug = editedOptions.amp_slug;

	const isCustom = 'custom' === editedOptions.paired_url_structure;

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
							{ __( '(recommended)', 'amp' ) }
						</em>
					</label>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.query_var } />
				</li>
				<li>
					<input
						id="paired_url_structure_rewrite_endpoint"
						type="radio"
						name="paired_url_structure"
						checked={ 'suffix_endpoint' === editedOptions.paired_url_structure }
						onChange={ () => {
							updateOptions( { paired_url_structure: 'suffix_endpoint' } );
						} }
						disabled={ isCustom }
					/>
					<label htmlFor="paired_url_structure_rewrite_endpoint">
						{ __( 'Rewrite endpoint', 'amp' ) + ': ' }
						<code>
							{ `/${ slug }/` }
						</code>
					</label>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.suffix_endpoint } />
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
						disabled={ isCustom }
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

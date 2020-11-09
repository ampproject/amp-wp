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
						{ __( 'A theme or plugin is customizing the paired URL structure, so the following options are unavailable.', 'amp' ) }
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
						checked={ 'rewrite_endpoint' === editedOptions.paired_url_structure }
						onChange={ () => {
							updateOptions( { paired_url_structure: 'rewrite_endpoint' } );
						} }
						disabled={ isCustom }
					/>
					<label htmlFor="paired_url_structure_rewrite_endpoint">
						{ __( 'Rewrite endpoint', 'amp' ) + ': ' }
						<code>
							{ `/${ slug }/` }
						</code>
					</label>
					<PairedUrlExamples pairedUrls={ editedOptions.paired_url_examples.rewrite_endpoint } />
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

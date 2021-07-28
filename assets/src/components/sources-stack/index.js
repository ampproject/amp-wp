/**
 * WordPress dependencies
 */
import { _n, sprintf } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import SourceDetails from '../source-details';

export default function SourcesStack( { sources } ) {
	if ( ! sources || ! Array.isArray( sources ) || sources.length === 0 ) {
		return null;
	}

	return (
		<details className="validation-error-sources-stack">
			<summary>
				{
					sprintf(
						/* translators: %s: number of sources. */
						_n(
							'Source stack (%s)',
							'Sources stack (%s)',
							sources.length,
							'amp',
						),
						sources.length,
					)
				}
			</summary>
			<ol>
				{ sources.map( ( source, index ) => (
					<li key={ `source-details-${ index }` }>
						<SourceDetails source={ source } />
					</li>
				) ) }
			</ol>
		</details>
	);
}
SourcesStack.propTypes = {
	sources: PropTypes.arrayOf(
		PropTypes.shape( {
			type: PropTypes.string,
			name: PropTypes.string,
			file: PropTypes.string,
			line: PropTypes.number,
			filter: PropTypes.bool,
			function: PropTypes.string,
			hook: PropTypes.string,
			priority: PropTypes.number,
			dependency_type: PropTypes.string,
			handle: PropTypes.string,
			text: PropTypes.string,
			extra_key: PropTypes.string,
			embed: PropTypes.string,
			block_name: PropTypes.string,
		} ),
	).isRequired,
};

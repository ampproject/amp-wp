/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import SourceDetailTitle from './title';
import SourceDetailValue from './value';

export default function SourceDetails( { source } ) {
	if ( ! source || Object.keys( source ).length === 0 ) {
		return null;
	}

	const omitKeys = [ 'dependency_type', 'priority', 'file', 'line', 'filter' ];
	const keys = Object.keys( source ).filter( ( key ) => ! omitKeys.includes( key ) );

	return (
		<table className="validation-error-sources-details">
			<tbody>
				{ keys.map( ( key ) => (
					<tr key={ key }>
						<th scope="row">
							<SourceDetailTitle slug={ key } source={ source } />
							{ ':' }
						</th>
						<td>
							<SourceDetailValue slug={ key } source={ source } />
						</td>
					</tr>
				) ) }
			</tbody>
		</table>
	);
}
SourceDetails.propTypes = {
	source: PropTypes.shape( {
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
	} ).isRequired,
};

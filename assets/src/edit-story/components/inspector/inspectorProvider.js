/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

const DESIGN = 'design';
const DOCUMENT = 'document';
const PREPUBLISH = 'prepublish';

function InspectorProvider( { children } ) {
	const [ tab, setTab ] = useState( DESIGN );

	const state = {
		state: {
			tab,
		},
		actions: {
			setTab,
		},
		data: {
			tabs: {
				DESIGN,
				DOCUMENT,
				PREPUBLISH,
			},
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

InspectorProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default InspectorProvider;

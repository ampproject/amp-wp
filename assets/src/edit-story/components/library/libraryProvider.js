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

const MEDIA = 'media';
const TEXT = 'text';
const SHAPES = 'shapes';
const LINKS = 'links';

function LibraryProvider( { children } ) {
	const [ tab, setTab ] = useState( MEDIA );

	const state = {
		state: {
			tab,
		},
		actions: {
			setTab,
		},
		data: {
			tabs: {
				MEDIA,
				TEXT,
				SHAPES,
				LINKS,
			},
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

LibraryProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default LibraryProvider;

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useAPI } from '../../';

function useLoadFonts( { fonts, setFonts } ) {
	const { actions: { getAllFonts } } = useAPI();

	useEffect( () => {
		if ( fonts.length === 0 ) {
			getAllFonts( {} ).then( ( loadedFont ) => {
				setFonts( loadedFont );
			} );
		}
	}, [ fonts, getAllFonts, setFonts ] );
}

useLoadFonts.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default useLoadFonts;

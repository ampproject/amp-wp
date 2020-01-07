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
			getAllFonts( {} ).then( setFonts );
		}
	}, [ fonts, getAllFonts, setFonts ] );
}

export default useLoadFonts;

/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { shuffle } from 'lodash';

export const SiteScan = createContext();

/**
 * Context provider for site scanning.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 */
export function SiteScanContextProvider( { children } ) {
	const [ recommendedModes, setRecommendedModes ] = useState( null );
	const [ scanningSite, setScanningSite ] = useState( true );

	/**
	 * @todo This temporary code is for development purposes.
	 */
	useEffect( () => {
		if ( ! scanningSite && ! recommendedModes ) {
			setRecommendedModes( shuffle( [ 'standard', 'transitional', 'reader' ] ) );
		}
	}, [ scanningSite, recommendedModes ] );

	/**
	 * @todo This temporary code is for development purposes.
	 */
	useEffect( () => {
		if ( true === scanningSite ) {
			setScanningSite( false );
		}
	}, [ scanningSite ] );

	return (
		<SiteScan.Provider
			value={
				{
					recommendedModes,
					scanningSite,
				}
			}
		>
			{ children }
		</SiteScan.Provider>
	);
}

SiteScanContextProvider.propTypes = {
	children: PropTypes.any,
};

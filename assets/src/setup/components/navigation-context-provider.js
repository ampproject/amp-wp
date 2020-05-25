/**
 * WordPress dependencies
 */
import { createContext, useState, useCallback, useMemo } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export const Navigation = createContext();

/**
 * Context provider for pagination.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 */
export function NavigationContextProvider( { children, pages } ) {
	const [ activePageIndex, setActivePageIndex ] = useState( 0 );
	const [ canGoForward, setCanGoForward ] = useState( false );

	const currentPage = useMemo( () => pages[ activePageIndex ], [ activePageIndex, pages ] );

	const goBack = useCallback( () => {
		setActivePageIndex( activePageIndex - 1 );
	}, [ setActivePageIndex, activePageIndex ] );

	const goForward = useCallback( () => {
		setActivePageIndex( activePageIndex + 1 );
		setCanGoForward( false );
	}, [ setActivePageIndex, setCanGoForward, activePageIndex ] );

	return (
		<Navigation.Provider
			value={
				{
					activePageIndex,
					canGoForward,
					currentPage,
					goBack,
					goForward,
					pages,
					setCanGoForward,
				}
			}
		>
			{ children }
		</Navigation.Provider>
	);
}

NavigationContextProvider.propTypes = {
	children: PropTypes.any,
	pages: PropTypes.arrayOf(
		PropTypes.shape( {
			title: PropTypes.string.isRequired,
		} ),
	).isRequired,
};

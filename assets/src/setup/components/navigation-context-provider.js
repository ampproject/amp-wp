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
	const [ page, setPage ] = useState( 4 );
	const [ canGoForward, setCanGoForward ] = useState( false );

	const currentPage = useMemo( () => pages[ page ], [ page, pages ] );

	const goBack = useCallback( () => {
		setPage( page - 1 );
	}, [ setPage, page ] );

	const goForward = useCallback( () => {
		setPage( page + 1 );
		setCanGoForward( false );
	}, [ setPage, setCanGoForward, page ] );

	return (
		<Navigation.Provider
			value={
				{
					canGoForward,
					currentPage,
					goBack,
					goForward,
					page,
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

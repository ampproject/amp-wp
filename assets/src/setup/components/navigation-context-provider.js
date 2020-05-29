/**
 * WordPress dependencies
 */
import { createContext, useState, useMemo } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export const Navigation = createContext();

/**
 * Context provider for navigating between and keeping track of pages in the app.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 */
export function NavigationContextProvider( { children, pages } ) {
	const [ activePageIndex, setActivePageIndex ] = useState( 0 );
	const [ canGoForward, setCanGoForward ] = useState( false );

	const currentPage = useMemo( () => pages[ activePageIndex ], [ activePageIndex, pages ] );

	/**
	 * Navigates back to the previous page.
	 */
	const goBack = () => {
		setActivePageIndex( activePageIndex - 1 );
	};

	/**
	 * Navigates to the next page. Pages are expected to set canGoForward to true when required actions have been taken.
	 */
	const goForward = () => {
		setActivePageIndex( activePageIndex + 1 );
		setCanGoForward( false );
	};

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

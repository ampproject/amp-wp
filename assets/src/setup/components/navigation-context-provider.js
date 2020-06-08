/**
 * WordPress dependencies
 */
import { createContext, useState } from '@wordpress/element';
import { getQueryArg } from '@wordpress/url';

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
 * @param {Array} props.pages Pages in the app.
 */
export function NavigationContextProvider( { children, pages } ) {
	// Initialize page from URL `amp-setup-screen` parameter. If not set, current page is 0.
	// This is primarily for testing.
	const [ activePageIndex, setActivePageIndex ] = useState( () => {
		const index = pages.findIndex( ( { slug } ) => slug === getQueryArg( global.location.href, 'amp-setup-screen' ) );
		return -1 < index ? index : 0;
	} );
	const [ canGoForward, setCanGoForward ] = useState( false );

	const currentPage = pages[ activePageIndex ];

	/**
	 * Navigates back to the previous page.
	 */
	const moveBack = () => {
		setActivePageIndex( activePageIndex - 1 );
		setCanGoForward( true );
	};

	/**
	 * Navigates to the next page. Pages are expected to set canGoForward to true when required actions have been taken.
	 */
	const moveForward = () => {
		setActivePageIndex( activePageIndex + 1 );
		setCanGoForward( false ); // Each page is responsible for setting this to true.
	};

	return (
		<Navigation.Provider
			value={
				{
					activePageIndex,
					canGoForward,
					currentPage,
					moveBack,
					moveForward,
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
			PageComponent: PropTypes.func.isRequired,
			slug: PropTypes.string.isRequired,
			title: PropTypes.string.isRequired,
		} ),
	).isRequired,
};

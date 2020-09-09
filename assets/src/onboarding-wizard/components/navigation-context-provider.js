/**
 * WordPress dependencies
 */
import { createContext, useState, useContext, useMemo } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
/**
 * Internal dependencies
 */
import { Options } from '../../components/options-context-provider';
import { READER } from '../../common/constants';

export const Navigation = createContext();

/**
 * Context provider for navigating between and keeping track of pages in the app.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 * @param {Array} props.pages Pages in the app.
 */
export function NavigationContextProvider( { children, pages } ) {
	const [ activePageIndex, setActivePageIndex ] = useState( 0 );
	const [ canGoForward, setCanGoForward ] = useState( true ); // Allow immediately moving forward on first page. @todo This may need to change in 2.1.
	const { editedOptions } = useContext( Options );

	const { theme_support: themeSupport } = editedOptions;

	const adaptedPages = useMemo( () => {
		if ( READER === themeSupport ) {
			return pages;
		}

		return pages.filter( ( page ) => 'theme-selection' !== page.slug );
	}, [ pages, themeSupport ] );

	const currentPage = adaptedPages[ activePageIndex ];

	const isLastPage = activePageIndex === adaptedPages.length - 1;

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
		if ( isLastPage ) {
			return;
		}

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
					isLastPage,
					moveBack,
					moveForward,
					pages: adaptedPages,
					setActivePageIndex,
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
			showTitle: PropTypes.bool,
			slug: PropTypes.string.isRequired,
			title: PropTypes.string.isRequired,
		} ),
	).isRequired,
};

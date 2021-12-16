/**
 * WordPress dependencies
 */
import { createContext, useState, useContext, useMemo } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { HAS_DEPENDENCY_SUPPORT } from 'amp-settings'; // From WP inline script.

/**
 * Internal dependencies
 */
import { Options } from '../../components/options-context-provider';
import { SiteScan } from '../../components/site-scan-context-provider';
import { READER } from '../../common/constants';

export const Navigation = createContext();

/**
 * Context provider for navigating between and keeping track of pages in the app.
 *
 * @param {Object} props          Component props.
 * @param {?any}   props.children Component children.
 * @param {Array}  props.pages    Pages in the app.
 */
export function NavigationContextProvider( { children, pages } ) {
	const [ currentPage, setCurrentPage ] = useState( pages[ 0 ] );
	const [ canGoForward, setCanGoForward ] = useState( true ); // Allow immediately moving forward on first page. @todo This may need to change in 2.1.
	const { editedOptions } = useContext( Options );
	const { isSkipped } = useContext( SiteScan );

	const { theme_support: themeSupport } = editedOptions;

	const adaptedPages = useMemo( () => pages.filter( ( page ) => (
		// Do not show the Technical Background step is there is no dependency support.
		! ( 'technical-background' === page.slug && ! HAS_DEPENDENCY_SUPPORT ) &&

		// If Site Scan should be skipped, do not show the relevant step in the Wizard.
		! ( 'site-scan' === page.slug && isSkipped ) &&

		// Theme Selection page should be only accessible for the Reader template mode.
		! ( 'theme-selection' === page.slug && READER !== themeSupport )
	) ), [ isSkipped, pages, themeSupport ] );

	const activePageIndex = adaptedPages.findIndex( ( adaptedPage ) => adaptedPage.slug === currentPage.slug );

	const isLastPage = activePageIndex === adaptedPages.length - 1;

	/**
	 * Navigates back to the previous page.
	 */
	const moveBack = () => {
		setCurrentPage( adaptedPages[ activePageIndex - 1 ] );
		setCanGoForward( true );
	};

	/**
	 * Navigates to the next page. Pages are expected to set canGoForward to true when required actions have been taken.
	 */
	const moveForward = () => {
		if ( isLastPage ) {
			return;
		}

		setCurrentPage( adaptedPages[ activePageIndex + 1 ] );
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

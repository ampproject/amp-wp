/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useState, useEffect, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { ReaderThemes } from '../components/reader-themes-context-provider';
import { Navigation } from './components/navigation-context-provider';

export const ReaderModeOverride = createContext();

/**
 * When reader mode was selected selected and the user chooses the currently active theme as the reader theme,
 * we will override their choice with transitional.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Children to consume the context.
 */
export function ReaderModeOverrideContextProvider( { children } ) {
	const { editedOptions, updateOptions } = useContext( Options );
	const { currentPage: { slug: currentPageSlug } } = useContext( Navigation );
	const { selectedTheme, currentTheme } = useContext( ReaderThemes );

	const { theme_support: themeSupport } = editedOptions || {};

	const [ readerModeWasOverridden, setReaderModeWasOverridden ] = useState( false );

	useEffect( () => {
		if ( 'summary' === currentPageSlug && 'reader' === themeSupport && selectedTheme.name === currentTheme.name ) {
			updateOptions( { theme_support: 'transitional' } );
			setReaderModeWasOverridden( true );
		}
	}, [ selectedTheme.name, currentTheme.name, themeSupport, currentPageSlug, updateOptions ] );

	return (
		<ReaderModeOverride.Provider value={ readerModeWasOverridden }>
			{ children }
		</ReaderModeOverride.Provider>
	);
}

ReaderModeOverrideContextProvider.propTypes = {
	children: PropTypes.any,
};

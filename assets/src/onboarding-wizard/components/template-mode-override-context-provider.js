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
import { Options } from '../../components/options-context-provider';
import { ReaderThemes } from '../../components/reader-themes-context-provider';
import { Navigation } from './navigation-context-provider';
import { User } from './user-context-provider';

export const ReaderModeOverride = createContext();

/**
 * Responds to user selections with overrides to the template mode setting.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Children to consume the context.
 */
export function TemplateModeOverrideContextProvider( { children } ) {
	const { editedOptions, updateOptions } = useContext( Options );
	const { currentPage: { slug: currentPageSlug } } = useContext( Navigation );
	const { selectedTheme, currentTheme } = useContext( ReaderThemes );
	const { developerToolsOption, fetchingUser, originalDeveloperToolsOption } = useContext( User );
	const [ respondedToDeveloperToolsOptionChange, setRespondedToDeveloperToolsOptionChange ] = useState( false );

	const { theme_support: themeSupport } = editedOptions || {};

	const [ readerModeWasOverridden, setReaderModeWasOverridden ] = useState( false );

	/**
	 * Override with transitional if the user has selected reader mode and their currently active theme as reader theme.
	 */
	useEffect( () => {
		if ( 'summary' === currentPageSlug && 'reader' === themeSupport && selectedTheme.name === currentTheme.name ) {
			updateOptions( { theme_support: 'transitional' } );
			setReaderModeWasOverridden( true );
		}
	}, [ selectedTheme.name, currentTheme.name, themeSupport, currentPageSlug, updateOptions ] );

	/**
	 * Unset theme support in current session if the user changes their answer on the technical screen.
	 */
	useEffect( () => {
		if ( fetchingUser ) {
			return;
		}

		if ( developerToolsOption !== originalDeveloperToolsOption && ! respondedToDeveloperToolsOptionChange ) {
			setRespondedToDeveloperToolsOptionChange( true );
			updateOptions( { theme_support: undefined } );
		}
	}, [ developerToolsOption, fetchingUser, originalDeveloperToolsOption, respondedToDeveloperToolsOptionChange, updateOptions ] );

	return (
		<ReaderModeOverride.Provider value={ readerModeWasOverridden }>
			{ children }
		</ReaderModeOverride.Provider>
	);
}

TemplateModeOverrideContextProvider.propTypes = {
	children: PropTypes.any,
};

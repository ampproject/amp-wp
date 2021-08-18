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
import { User } from '../../components/user-context-provider';
import { Navigation } from './navigation-context-provider';

export const TemplateModeOverride = createContext();

/**
 * Responds to user selections with overrides to the template mode setting.
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children Children to consume the context.
 */
export function TemplateModeOverrideContextProvider( { children } ) {
	const { editedOptions, originalOptions, updateOptions, readerModeWasOverridden, setReaderModeWasOverridden } = useContext( Options );
	const { activePageIndex, currentPage } = useContext( Navigation );
	const { selectedTheme, currentTheme } = useContext( ReaderThemes );
	const { developerToolsOption, fetchingUser, originalDeveloperToolsOption } = useContext( User );
	const [ respondedToDeveloperToolsOptionChange, setRespondedToDeveloperToolsOptionChange ] = useState( false );
	const [ mostRecentlySelectedThemeSupport, setMostRecentlySelectedThemeSupport ] = useState( null );
	const [ technicalQuestionChangedAtLeastOnce, setTechnicalQuestionChangedAtLeastOnce ] = useState( false );

	const { slug: currentPageSlug } = currentPage || {};
	const { theme_support: themeSupport } = editedOptions || {};
	const { theme_support: originalThemeSupport } = originalOptions || {};

	const technicalQuestionChanged = ! fetchingUser && developerToolsOption !== originalDeveloperToolsOption;

	/**
	 * Persist the "previously selected" note if the technical question is changed, even if it is subsequently restored.
	 */
	useEffect( () => {
		if ( technicalQuestionChanged ) {
			setTechnicalQuestionChangedAtLeastOnce( true );
		}
	}, [ technicalQuestionChanged ] );

	/**
	 * When a user makes a theme support selection, save it so it can be restored if needed.
	 */
	useEffect( () => {
		if ( themeSupport ) {
			setMostRecentlySelectedThemeSupport( themeSupport );
		}
	}, [ themeSupport ] );

	/**
	 * Override with transitional if the user has selected reader mode and their currently active theme is the same as the selected reader theme.
	 */
	useEffect( () => {
		if ( 'review' === currentPageSlug && 'reader' === themeSupport && selectedTheme.name === currentTheme.name ) {
			if ( ! readerModeWasOverridden ) {
				updateOptions( { theme_support: 'transitional' } );
				setReaderModeWasOverridden( true );
			} else {
				setReaderModeWasOverridden( false );
			}
		}
	}, [
		activePageIndex,
		selectedTheme.name,
		currentTheme.name,
		themeSupport,
		currentPageSlug,
		readerModeWasOverridden,
		updateOptions,
		setReaderModeWasOverridden,
	] );

	/**
	 * Unset theme support in current session if the user changes their answer on the technical screen.
	 */
	useEffect( () => {
		if ( fetchingUser || 'technical-background' !== currentPageSlug ) {
			return;
		}

		// If user has already made a change, don't do anything.
		if ( ! respondedToDeveloperToolsOptionChange && originalThemeSupport !== themeSupport ) {
			setRespondedToDeveloperToolsOptionChange( true );
			return;
		}

		if ( ! respondedToDeveloperToolsOptionChange && developerToolsOption !== originalDeveloperToolsOption ) {
			setRespondedToDeveloperToolsOptionChange( true );
			updateOptions( { theme_support: undefined } );
		}

		if ( respondedToDeveloperToolsOptionChange && developerToolsOption === originalDeveloperToolsOption ) {
			const themeSupportToSelect = mostRecentlySelectedThemeSupport || originalThemeSupport;
			if ( themeSupport !== themeSupportToSelect ) {
				updateOptions( { theme_support: themeSupportToSelect } );
			}
		}
	}, [
		currentPageSlug,
		developerToolsOption,
		fetchingUser,
		mostRecentlySelectedThemeSupport,
		originalDeveloperToolsOption,
		originalThemeSupport,
		respondedToDeveloperToolsOptionChange,
		themeSupport,
		updateOptions,
	] );

	return (
		<TemplateModeOverride.Provider value={ { readerModeWasOverridden, technicalQuestionChangedAtLeastOnce } }>
			{ children }
		</TemplateModeOverride.Provider>
	);
}

TemplateModeOverrideContextProvider.propTypes = {
	children: PropTypes.any,
};

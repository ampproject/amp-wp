/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useMemo, useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ReaderThemes } from '../reader-themes-context-provider';
import { Loading } from '../loading';
import { ThemeCard } from './theme-card';
import './style.css';

/**
 * Component for selecting a reader theme.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.disableCurrentlyActiveTheme Whether the currently active theme should be unselectable.
 * @param {string} props.currentlyActiveThemeNotice The notice to show if the theme is unselectable.
 */
export function ReaderThemeSelection( { disableCurrentlyActiveTheme = false, currentlyActiveThemeNotice } ) {
	const { currentTheme, fetchingThemes, themes } = useContext( ReaderThemes );

	// Separate available themes (both installed and installable) from those that need to be installed manually.
	const { availableThemes, unavailableThemes } = useMemo(
		() => ( themes || [] ).reduce(
			( collections, theme ) => {
				if ( theme.availability === 'non-installable' ) {
					collections.unavailableThemes.push( theme );
				} else {
					collections.availableThemes.push( theme );
				}

				return collections;
			},
			{ availableThemes: [], unavailableThemes: [] },
		),
		[ themes ],
	);

	if ( fetchingThemes ) {
		return <Loading />;
	}

	return (
		<div className="reader-theme-selection">
			<p>
				{
					__( 'Select the theme template for mobile and AMP visitors', 'amp' )
				}
			</p>
			<div>
				{ 0 < availableThemes.length && (
					<ul className="choose-reader-theme__grid">
						{ availableThemes.map( ( theme ) => (
							<ThemeCard
								currentlyActiveThemeNotice={ currentlyActiveThemeNotice }
								disabled={ disableCurrentlyActiveTheme && currentTheme.name === theme.name }
								key={ `theme-card-${ theme.slug }` }
								screenshotUrl={ theme.screenshot_url }
								{ ...theme }
							/>
						) ) }
					</ul>
				) }

				{ 0 < unavailableThemes.length && (
					<div className="choose-reader-theme__unavailable">
						<h3>
							{ __( 'Unavailable themes', 'amp' ) }
						</h3>
						<p>
							{ __( 'The following themes are compatible but cannot be installed automatically. Please install them manually, or contact your host if you are not able to do so.', 'amp' ) }
						</p>
						<ul className="choose-reader-theme__grid">
							{ unavailableThemes.map( ( theme ) => (
								<ThemeCard
									key={ `theme-card-${ theme.slug }` }
									screenshotUrl={ theme.screenshot_url }
									disabled={ true }
									{ ...theme }
								/>
							) ) }
						</ul>
					</div>
				) }
			</div>
		</div>
	);
}

ReaderThemeSelection.propTypes = {
	currentlyActiveThemeNotice: PropTypes.string,
	disableCurrentlyActiveTheme: PropTypes.bool,
};

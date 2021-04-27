/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ReaderThemes } from '../reader-themes-context-provider';
import { Loading } from '../loading';
import './style.css';
import { ThemeCard } from '../theme-card';
import { ThemesAPIError } from '../themes-api-error';

/**
 * Component for selecting a reader theme.
 */
export function ReaderThemeSelection() {
	const { availableThemes, fetchingThemes, unavailableThemes } = useContext( ReaderThemes );

	if ( fetchingThemes ) {
		return <Loading />;
	}

	return (
		<div className="reader-theme-selection">
			<p>
				{
					__( 'Select the theme template for mobile visitors', 'amp' )
				}
			</p>
			<ThemesAPIError />
			<div>
				{ 0 < availableThemes.length && (
					<ul className="choose-reader-theme__grid">
						{ availableThemes.map( ( theme ) => (
							<ThemeCard
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
	hideCurrentlyActiveTheme: PropTypes.bool,
};

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { ThemeCard } from '../theme-card';
import { Carousel } from '../carousel';

export function ReaderThemeCarousel( { availableThemes, currentTheme, hideCurrentlyActiveTheme = true } ) {
	return (
		<Carousel
			items={ availableThemes
				.filter( ( theme ) => ! ( hideCurrentlyActiveTheme && currentTheme.name === theme.name ) )
				.map( ( theme ) => (
					{
						label: theme.name,
						name: theme.slug,
						Item: () => (
							<ThemeCard
								ElementName="div"
								screenshotUrl={ theme.screenshot_url }
								{ ...theme }
							/>
						),
					} ) )
			}
		/>
	);
}

const themeShape = PropTypes.shape( {
	name: PropTypes.string,
} );

ReaderThemeCarousel.propTypes = {
	availableThemes: PropTypes.arrayOf( themeShape ).isRequired,
	currentTheme: themeShape.isRequired,
	hideCurrentlyActiveTheme: PropTypes.bool,
};


/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';
import MobileIcon from '../svg/mobile-icon.svg';
import { Options } from '../options-context-provider';
import { Selectable } from '../selectable';
import { Phone } from '../phone';
import { PXEnhancingMessage } from '../px-enhancing-message';

/**
 * A selectable card showing a theme in a list of themes.
 *
 * @param {Object}  props               Component props.
 * @param {string}  props.description   Theme description.
 * @param {string}  props.homepage      Link to view more information about the theme.
 * @param {string}  props.screenshotUrl URL for screenshot of theme.
 * @param {string}  props.slug          Theme slug.
 * @param {string}  props.name          Theme name.
 * @param {boolean} props.disabled      Whether the theme is not automatically installable in the current environment.
 * @param {Object}  props.style         Style object to pass to the Selectable component.
 * @param {string}  props.ElementName   Name for the wrapper element.
 * @param {boolean} props.isPXEnhanced  Is themes is AMP compatible or not.
 */
export function ThemeCard( { description, ElementName = 'li', homepage, screenshotUrl, slug, name, disabled, style, isPXEnhanced } ) {
	const { editedOptions, updateOptions } = useContext( Options );
	const { reader_theme: readerTheme } = editedOptions;

	const id = `theme-card__${ slug }`;

	return (
		<Selectable
			className={ `theme-card ${ disabled ? 'theme-card--disabled' : '' }` }
			direction="bottom"
			ElementName={ ElementName }
			selected={ readerTheme === slug }
			style={ style }
		>
			<label htmlFor={ id } className="theme-card__label">
				<Phone>
					{
						screenshotUrl ? (
							<img
								src={ screenshotUrl }
								alt={ name || slug }
								height="2165"
								width="1000"
								loading="lazy"
								decoding="async"
							/>
						) : <MobileIcon style={ { width: '100%' } } />
					}
					{ disabled && (
						<div className="theme-card__disabled-overlay">
							{ __( 'Unavailable', 'amp' ) }
						</div>
					) }
				</Phone>
				<div className="theme-card__label-header">
					<input
						disabled={ Boolean( disabled ) }
						type="radio"
						id={ id }
						checked={ readerTheme === slug }
						onChange={ ( ) => {
							updateOptions( { reader_theme: slug } );
						} }
					/>
					<h4 className="theme-card__title">
						{ decodeEntities( name || slug ) }
					</h4>

				</div>

				{
					description && (
						<p className="theme-card__description">
							{ decodeEntities( description ) }
						</p>
					)
				}
			</label>
			{
				homepage && (
					<p className="theme-card__theme-link">
						<a href={ homepage } target="_blank" rel="noreferrer noopener">
							{ __( 'Learn more', 'amp' ) }
						</a>
					</p>
				)
			}
			{ isPXEnhanced && (
				<PXEnhancingMessage />
			) }

		</Selectable>
	);
}

ThemeCard.propTypes = {
	description: PropTypes.string,
	ElementName: PropTypes.string,
	homepage: PropTypes.string,
	screenshotUrl: PropTypes.string,
	slug: PropTypes.string.isRequired,
	name: PropTypes.string,
	disabled: PropTypes.bool,
	style: PropTypes.object,
	isPXEnhanced: PropTypes.bool,
};

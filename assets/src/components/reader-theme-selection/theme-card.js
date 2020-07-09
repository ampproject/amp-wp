
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
import { Options } from '../options-context-provider';
import { Selectable } from '../selectable';
import { Phone } from '../phone';
import { AMPNotice, NOTICE_TYPE_WARNING, NOTICE_SIZE_SMALL } from '../amp-notice';

/**
 * A selectable card showing a theme in a list of themes.
 *
 * @param {Object} props Component props.
 * @param {string} props.description Theme description.
 * @param {string} props.currentlyActiveThemeNotice The notice to show if the theme is unselectable.
 * @param {string} props.homepage Link to view more information about the theme.
 * @param {string} props.screenshotUrl URL for screenshot of theme.
 * @param {string} props.slug Theme slug.
 * @param {string} props.name Theme name.
 * @param {boolean} props.disabled Whether the theme is not automatically installable in the current environment.
 */
export function ThemeCard( { currentlyActiveThemeNotice, description, homepage, screenshotUrl, slug, name, disabled } ) {
	const { editedOptions, updateOptions } = useContext( Options );
	const { reader_theme: readerTheme } = editedOptions;

	const id = `theme-card__${ slug }`;

	return (
		<Selectable className={ `theme-card` } direction="bottom" elementName="li" selected={ readerTheme === slug }>
			<label htmlFor={ id } className="theme-card__label">
				<Phone>
					<img
						src={ screenshotUrl }
						alt={ name }
						height="2165"
						width="1000"
						loading="lazy"
						decoding="async"
					/>
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
					<h3>
						{ decodeEntities( name ) }
					</h3>

				</div>

				{ disabled && currentlyActiveThemeNotice && (
					<AMPNotice type={ NOTICE_TYPE_WARNING } size={ NOTICE_SIZE_SMALL }>
						{ currentlyActiveThemeNotice }
					</AMPNotice>
				) }

				<p className="theme-card__description">
					{ decodeEntities( description ) }

				</p>
			</label>
			<p className="theme-card__theme-link">
				<a href={ homepage } target="_blank" rel="noreferrer noopener">
					{ __( 'Learn more', 'amp' ) }
				</a>
			</p>
		</Selectable>
	);
}

ThemeCard.propTypes = {
	currentlyActiveThemeNotice: PropTypes.string,
	description: PropTypes.string.isRequired,
	homepage: PropTypes.string.isRequired,
	screenshotUrl: PropTypes.string.isRequired,
	slug: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	disabled: PropTypes.bool,
};


/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';
import { __ } from '@wordpress/i18n';
import { Options } from '../../components/options-context-provider';
import { Selectable } from '../../components/selectable';
import { Phone } from '../../components/phone';

/**
 * A selectable card showing a theme in a list of themes.
 *
 * @param {Object} props Component props.
 * @param {string} props.description Theme description.
 * @param {string} props.homepage Link to view more information about the theme.
 * @param {string} props.screenshotUrl URL for screenshot of theme.
 * @param {string} props.slug Theme slug.
 * @param {string} props.name Theme name.
 */
export function ThemeCard( { description, homepage, screenshotUrl, slug, name } ) {
	const { options, updateOptions } = useContext( Options );
	const { reader_theme: readerTheme } = options || {};

	const id = `theme-card__${ slug }`;

	return (
		<Selectable className={ `theme-card` } direction="bottom" HTMLElement="li" selected={ readerTheme === slug }>
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
						type="radio"
						id={ id }
						checked={ readerTheme === slug }
						onChange={ ( ) => {
							updateOptions( { reader_theme: slug } );
						} }
					/>
					<h2>
						{ decodeEntities( name ) }
					</h2>
				</div>

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
	description: PropTypes.string.isRequired,
	homepage: PropTypes.string.isRequired,
	screenshotUrl: PropTypes.string.isRequired,
	slug: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
};


/**
 * WordPress dependencies
 */
import { useContext, useMemo } from '@wordpress/element';
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
 * @param {string} props.name Theme name.
 * @param {string} props.slug Theme slug.
 */
export function ThemeCard( { description, homepage, screenshotUrl, slug, name } ) {
	const { options, updateOptions } = useContext( Options );
	const { reader_theme: readerTheme } = options || {};

	const id = `theme-card__${ slug }`;

	const truncatedDescription = useMemo( () => {
		const splitDescription = description.split( ' ' );
		if ( splitDescription.length < 21 ) {
			return description;
		}

		return splitDescription.slice( 0, 20 ).join( ' ' ) + ' [...]';
	}, [ description ] );

	return (
		<Selectable className={ `amp-wp-theme-card` } direction="bottom" HTMLElement="li" selected={ readerTheme === slug }>
			<label htmlFor={ id } className="amp-wp-theme-card__label">
				<Phone>
					<img
						src={ screenshotUrl }
						alt={ name }
					/>
				</Phone>
				<div className="amp-wp-theme-card__label-header">
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

				<p>
					{ decodeEntities( truncatedDescription ) }
				</p>
			</label>
			<p className="amp-wp-theme-card__theme-link">
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

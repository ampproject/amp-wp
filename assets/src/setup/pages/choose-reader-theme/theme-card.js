
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

export function ThemeCard( { description, homepage, screenshotUrl, slug, name } ) {
	const { options, updateOptions } = useContext( Options );
	const { reader_theme: readerTheme } = options || {};

	const id = `theme-card__${ slug }`;

	return (
		<li className={ `amp-wp-theme-card ${ readerTheme === slug ? 'amp-wp-theme-card--selected' : '' }` }>
			<label htmlFor={ id } className="amp-wp-theme-card__label">
				<img
					src={ screenshotUrl }
					alt={ name }
				/>
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
					{ decodeEntities( description ) }
				</p>
			</label>
			<p className="amp-wp-theme-card__theme-link">
				<a href={ homepage } target="_blank" rel="noreferrer">
					{ __( 'Learn more', 'amp' ) }
				</a>
			</p>
		</li>
	);
}

ThemeCard.propTypes = {
	description: PropTypes.string.isRequired,
	homepage: PropTypes.string.isRequired,
	screenshotUrl: PropTypes.string.isRequired,
	slug: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
};

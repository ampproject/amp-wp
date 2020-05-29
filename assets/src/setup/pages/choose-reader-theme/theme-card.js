
/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';
import { Options } from '../../components/options-context-provider';

export function ThemeCard( { description, screenshotUrl, slug, name } ) {
	const instanceId = useInstanceId( ThemeCard );
	const { options: { reader_theme: readerTheme }, updateOptions } = useContext( Options );

	return (
		<li className={ `amp-wp-theme-card ${ readerTheme === slug ? 'amp-wp-theme-card--selected' : '' }` }>
			<label htmlFor={ instanceId } className="amp-wp-theme-card__label">
				<div className="amp-wp-theme-card__label-header">
					<input
						type="radio"
						id={ instanceId }
						checked={ readerTheme === slug }
						onChange={ ( ) => {
							updateOptions( { reader_theme: slug } );
						} }
					/>
					<h2>
						{ decodeEntities( name ) }
					</h2>
				</div>
				<img
					src={ screenshotUrl }
					alt={ name }
				/>
				<p>
					{ decodeEntities( description ) }
				</p>
			</label>
		</li>
	);
}

ThemeCard.propTypes = {
	description: PropTypes.string.isRequired,
	screenshotUrl: PropTypes.string.isRequired,
	slug: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
};

/**
 * WordPress dependencies
 */
import { useContext, useMemo } from '@wordpress/element';
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

export function ThemeCard( { content, featured_media: featuredMedia, slug, title } ) {
	const instanceId = useInstanceId( ThemeCard );
	const { options: { reader_theme: readerTheme }, updateOptions } = useContext( Options );

	const { imageHeight, imageWidth, imageUrl } = useMemo( () => {
		for ( const size of [ 'large', 'full' ] ) {
			if ( size in featuredMedia.media_details && featuredMedia.media_details[ size ] ) {
				return {
					imageHeight: featuredMedia.media_details[ size ].height,
					imageUrl: featuredMedia.media_details[ size ].source_url,
					imageWidth: featuredMedia.media_details[ size ].width,
				};
			}
		}

		return {};
	}, [ featuredMedia.media_details ] );

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
						{ decodeEntities( title ) }
					</h2>
				</div>
				{ imageUrl && (
					<img
						src={ imageUrl }
						height={ imageHeight }
						with={ imageWidth }
						alt={ featuredMedia.alt_text || featuredMedia.title }
					/> )
				}
				<p>
					{ decodeEntities( content ) }
				</p>
			</label>
		</li>
	);
}

const mediaDetailShape = PropTypes.shape( {
	height: PropTypes.number.isRequired,
	source_url: PropTypes.string.isRequired,
	width: PropTypes.number.isRequired,
} );

ThemeCard.propTypes = {
	content: PropTypes.string.isRequired,
	featured_media: PropTypes.shape(
		{
			id: PropTypes.number.isRequired,
			alt_text: PropTypes.string,
			media_details: PropTypes.shape( {
				large: mediaDetailShape,
				full: mediaDetailShape,
			} ),
			title: PropTypes.string,
		},
	).isRequired,
	slug: PropTypes.string.isRequired,
	title: PropTypes.string.isRequired,
};

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { addBackgroundColorToOverlay } from '../../helpers';
import { IMAGE_BACKGROUND_TYPE, VIDEO_BACKGROUND_TYPE } from '../../constants';

const PageSave = ( { attributes } ) => {
	const {
		anchor,
		focalPoint,
		overlayOpacity,
		mediaId,
		mediaUrl,
		mediaType,
		mediaAlt,
		poster,
		autoAdvanceAfter,
		autoAdvanceAfterDuration,
		autoAdvanceAfterMedia,
	} = attributes;

	const backgroundColors = JSON.parse( attributes.backgroundColors );

	let advanceAfter;

	if ( [ 'auto', 'time' ].includes( autoAdvanceAfter ) && autoAdvanceAfterDuration ) {
		advanceAfter = parseInt( autoAdvanceAfterDuration ) + 's';
	} else if ( 'media' === autoAdvanceAfter ) {
		advanceAfter = autoAdvanceAfterMedia;
	}

	let overlayStyle = {};
	if ( 0 < backgroundColors.length ) {
		overlayStyle = addBackgroundColorToOverlay( overlayStyle, backgroundColors );
		overlayStyle.opacity = overlayOpacity / 100;
	}

	const objectPosition = IMAGE_BACKGROUND_TYPE === mediaType && focalPoint ? `${ focalPoint.x * 100 }% ${ focalPoint.y * 100 }%` : 'initial';

	return (
		<amp-story-page style={ { backgroundColor: '#ffffff' } } id={ anchor } auto-advance-after={ advanceAfter }>
			{
				mediaUrl && (
					<amp-story-grid-layer template="fill">
						{ IMAGE_BACKGROUND_TYPE === mediaType && (
							<img
								layout="fill"
								src={ mediaUrl }
								alt={ mediaAlt }
								className={ mediaId ? `wp-image-${ mediaId }` : null }
								object-position={ objectPosition }
							/>
						) }
						{ VIDEO_BACKGROUND_TYPE === mediaType && (
							<amp-video layout="fill" aria-label={ mediaAlt } src={ mediaUrl } poster={ poster } muted autoplay loop />
						) }
					</amp-story-grid-layer>
				)
			}
			<amp-story-grid-layer template="fill" style={ overlayStyle }></amp-story-grid-layer>
			<InnerBlocks.Content />
		</amp-story-page>
	);
};

PageSave.propTypes = {
	attributes: PropTypes.shape( {
		anchor: PropTypes.string,
		backgroundColors: PropTypes.string,
		mediaId: PropTypes.number,
		mediaType: PropTypes.string,
		mediaAlt: PropTypes.string,
		mediaUrl: PropTypes.string,
		focalPoint: PropTypes.shape( {
			x: PropTypes.number.isRequired,
			y: PropTypes.number.isRequired,
		} ),
		overlayOpacity: PropTypes.number,
		poster: PropTypes.string,
		autoAdvanceAfter: PropTypes.string,
		autoAdvanceAfterDuration: PropTypes.number,
		autoAdvanceAfterMedia: PropTypes.string,
	} ).isRequired,
};

export default PageSave;

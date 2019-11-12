/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { InnerBlocks } from '@wordpress/block-editor';

const PageSave = ( { attributes } ) => {
	const {
		anchor,
		overlayOpacity,
		mediaId,
		mediaUrl,
		mediaAlt,
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

	const overlayStyle = {};
	if ( 0 < backgroundColors.length ) {
		overlayStyle.backgroundColor = backgroundColors[ 0 ].color;
		overlayStyle.opacity = overlayOpacity / 100;
	}

	const objectPosition = 'initial';

	return (
		<amp-story-page style={ { backgroundColor: '#ffffff' } } id={ anchor } auto-advance-after={ advanceAfter }>
			{
				mediaUrl && (
					<amp-story-grid-layer template="fill">
						<img
							layout="fill"
							src={ mediaUrl }
							alt={ mediaAlt }
							className={ mediaId ? `wp-image-${ mediaId }` : null }
							object-position={ objectPosition }
						/>
					</amp-story-grid-layer>
				)
			}
			<amp-story-grid-layer template="fill" style={ overlayStyle } />
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

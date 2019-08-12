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
import {
	addBackgroundColorToOverlay,
} from '../../helpers';
import {
	IMAGE_BACKGROUND_TYPE,
	VIDEO_BACKGROUND_TYPE,
} from '../../constants';

const blockAttributes = {
	anchor: {
		source: 'attribute',
		selector: 'amp-story-page',
		attribute: 'id',
	},
	mediaAlt: {
		type: 'string',
	},
	mediaId: {
		type: 'number',
	},
	mediaUrl: {
		type: 'string',
		source: 'attribute',
		selector: 'amp-story-grid-layer[template="fill"] > amp-img, amp-story-grid-layer[template="fill"] > amp-video',
		attribute: 'src',
	},
	mediaType: {
		type: 'string',
	},
	poster: {
		type: 'string',
	},
	focalPoint: {
		type: 'object',
	},
	autoAdvanceAfter: {
		type: 'string',
	},
	autoAdvanceAfterDuration: {
		type: 'number',
	},
	autoAdvanceAfterMedia: {
		type: 'string',
	},
	backgroundColors: {
		default: '[]',
	},
	overlayOpacity: {
		default: 100,
	},
};

const SaveV120 = ( { attributes } ) => {
	const {
		anchor,
		focalPoint,
		overlayOpacity,
		mediaUrl,
		mediaType,
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

	const imgStyle = {
		objectPosition: IMAGE_BACKGROUND_TYPE === mediaType && focalPoint ? `${ focalPoint.x * 100 }% ${ focalPoint.y * 100 }%` : 'initial',
	};

	return (
		<amp-story-page style={ { backgroundColor: '#ffffff' } } id={ anchor } auto-advance-after={ advanceAfter }>
			{
				mediaUrl && (
					<amp-story-grid-layer template="fill">
						{ IMAGE_BACKGROUND_TYPE === mediaType && (
							<amp-img layout="fill" src={ mediaUrl } style={ imgStyle } />
						) }
						{ VIDEO_BACKGROUND_TYPE === mediaType && (
							<amp-video layout="fill" src={ mediaUrl } poster={ poster } muted autoplay loop />
						) }
					</amp-story-grid-layer>
				)
			}
			<amp-story-grid-layer template="fill" style={ overlayStyle }></amp-story-grid-layer>
			<InnerBlocks.Content />
		</amp-story-page>
	);
};

SaveV120.propTypes = {
	attributes: PropTypes.shape( {
		anchor: PropTypes.string,
		backgroundColors: PropTypes.string,
		mediaAlt: PropTypes.string,
		mediaId: PropTypes.number,
		mediaType: PropTypes.string,
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

export default [
	{
		attributes: {
			...blockAttributes,
			deprecated: {
				default: '1.2.0',
			},
		},
		save: SaveV120,
	},
];

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { addBackgroundColorToOverlay } from '../../helpers';
import { IMAGE_BACKGROUND_TYPE, VIDEO_BACKGROUND_TYPE } from '../../constants';
import EditPage from './edit';
import blockIcon from '../../../images/amp-story-page-icon.svg';

export const name = 'amp/amp-story-page';

const schema = {
	anchor: {
		source: 'attribute',
		selector: 'amp-story-page',
		attribute: 'id',
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
		default: 50,
	},
};

export const settings = {
	title: __( 'Page', 'amp' ),
	category: 'layout',
	icon: blockIcon( { width: 24, height: 24 } ),
	attributes: schema,

	supports: {
		reusable: true,
	},

	/*
     * <amp-story-page>:
     *   mandatory_parent: "AMP-STORY"
     *   mandatory_min_num_child_tags: 1
     *   child_tag_name_oneof: "AMP-ANALYTICS"
     *   child_tag_name_oneof: "AMP-PIXEL"
     *   child_tag_name_oneof: "AMP-STORY-CTA-LAYER"
     *   child_tag_name_oneof: "AMP-STORY-GRID-LAYER"
     *
     * https://github.com/ampproject/amphtml/blob/87fe1d02f902be97b596b36ec3421592c83d241e/extensions/amp-story/validator-amp-story.protoascii#L146-L171
     * */

	edit: EditPage,

	save( { attributes } ) {
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
	},
};

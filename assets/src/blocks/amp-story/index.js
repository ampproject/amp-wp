/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { BLOCK_ICONS, IMAGE_BACKGROUND_TYPE, VIDEO_BACKGROUND_TYPE } from '../../constants';
import EditPage from './edit';

export const name = 'amp/amp-story-page';

const schema = {
	anchor: {
		source: 'attribute',
		selector: 'amp-story-page',
		attribute: 'id',
	},
	backgroundColor: {
		default: '#ffffff',
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
};

export const settings = {
	title: __( 'Page', 'amp' ),
	category: 'layout',
	icon: BLOCK_ICONS[ 'amp/amp-story-page' ],
	attributes: schema,

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
			backgroundColor,
			mediaUrl,
			mediaType,
			poster,
			autoAdvanceAfter,
			autoAdvanceAfterDuration,
			autoAdvanceAfterMedia,
		} = attributes;

		let advanceAfter;

		if ( [ 'auto', 'time' ].includes( autoAdvanceAfter ) && autoAdvanceAfterDuration ) {
			advanceAfter = parseInt( autoAdvanceAfterDuration ) + 's';
		} else if ( 'media' === autoAdvanceAfter ) {
			advanceAfter = autoAdvanceAfterMedia;
		}

		return (
			<amp-story-page style={ { backgroundColor } } id={ anchor } auto-advance-after={ advanceAfter }>
				{
					mediaUrl && (
						<amp-story-grid-layer template="fill">
							{ IMAGE_BACKGROUND_TYPE === mediaType && (
								<amp-img layout="fill" src={ mediaUrl } />
							) }
							{ VIDEO_BACKGROUND_TYPE === mediaType && (
								<amp-video layout="fill" src={ mediaUrl } poster={ poster } muted autoplay loop />
							) }
						</amp-story-grid-layer>
					)
				}
				<amp-story-grid-layer template="vertical">
					<InnerBlocks.Content />
				</amp-story-grid-layer>
				{ /* @todo Add amp-story-cta-layer */ }
			</amp-story-page>
		);
	},
};

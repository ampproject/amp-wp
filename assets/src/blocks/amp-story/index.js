/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { BLOCK_ICONS } from '../../constants';
import EditPage from './edit';
import { IMAGE_BACKGROUND_TYPE, VIDEO_BACKGROUND_TYPE } from './constants';

export const name = 'amp/amp-story-page';

export const settings = {
	title: __( 'Page', 'amp' ),
	category: 'layout',
	icon: BLOCK_ICONS[ 'amp/amp-story-page' ],
	attributes: {
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
		const { anchor, backgroundColor, mediaUrl, mediaType, poster } = attributes;

		return (
			<amp-story-page style={ { backgroundColor } } id={ anchor }>
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

/**
 * Register block.
 */
export default registerBlockType( name,	settings );

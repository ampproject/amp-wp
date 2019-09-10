/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createBlock, getBlockAttributes } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import deprecated from './deprecated';
import edit from './edit';
import save from './save';
import blockIcon from '../../../../images/stories-editor/amp-story-page-icon.svg';

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
		selector: 'amp-story-grid-layer[template="fill"] > img, amp-story-grid-layer[template="fill"] > amp-img, amp-story-grid-layer[template="fill"] > amp-video',
		attribute: 'src',
	},
	mediaType: {
		type: 'string',
	},
	mediaAlt: {
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

	edit,

	save,

	deprecated,

	transforms: {
		from: [
			{
				type: 'raw',
				priority: 20,
				selector: 'amp-story-page',
				transform: ( node ) => {
					const innerHTML = node.outerHTML;
					const blockAttributes = getBlockAttributes( name, innerHTML );

					// @todo: Somehow extract inner blocks.
					return createBlock( name, blockAttributes );
				},
			},
		],
	},
};

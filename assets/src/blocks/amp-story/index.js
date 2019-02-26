/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { BLOCK_ICONS } from '../../helpers';
import EditPage from './edit';

export const name = 'amp/amp-story-page';

export const settings = {
	title: __( 'Page', 'amp' ),
	category: 'layout',
	icon: BLOCK_ICONS[ 'amp/amp-story-page' ],
	attributes: {
		id: {
			source: 'attribute',
			selector: 'amp-story-page',
			attribute: 'id',
		},
		backgroundColor: {
			default: '#ffffff',
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
		return (
			<amp-story-page style={ { backgroundColor: attributes.backgroundColor } } id={ attributes.anchor }>
				{ /* @todo Add fill layer for image/video */ }
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

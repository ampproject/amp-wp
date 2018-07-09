const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InspectorControls,
	InnerBlocks
} = wp.editor;
const {
	TextControl,
	Notice
} = wp.components;

const TEMPLATE = [
	[
		'amp/amp-story-grid-layer',
		[],
		[
			[
				'core/paragraph',
				{
					placeholder: __( 'Add content to layer.', 'amp' )
				}
			]
		]
	]
];

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-page',
	{
		title: __( 'AMP Story Page', 'amp' ),
		category: 'layout',
		icon: 'admin-page',

		// @todo Enforce that the amp-story-page can only be a root-level block; Using `parent: []` does not work, and it causes the inserter to be disabled entirely.
		attributes: {
			id: {
				type: 'string',
				source: 'attribute',
				selector: 'amp-story-page',
				attribute: 'id'
			}
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

		// @todo Show error if no ID is supplied.
		edit( props ) {
			const { isSelected, setAttributes } = props;
			return [
				isSelected && (
					<InspectorControls key='inspector'>
						<TextControl
							type="text"
							className="blocks-amp-story-page__id"
							required={ true }
							label={ __( 'ID', 'amp' ) }
							value={ props.attributes.id }
							onChange={ value => ( setAttributes( { id: value } ) ) }
						/>
					</InspectorControls>
				),
				! props.attributes.id && (
					<Notice status="error" isDismissible={ false }>{ __( 'You must supply an ID for the page.', 'amp' ) }</Notice>
				),
				<InnerBlocks key='contents' template={ TEMPLATE } />
			];
		},

		save( { attributes } ) {
			return (
				<amp-story-page id={ attributes.id }>
					<InnerBlocks.Content />
				</amp-story-page>
			);
		}
	}
);

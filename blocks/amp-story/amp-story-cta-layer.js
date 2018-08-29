const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InnerBlocks
} = wp.editor;
const {
	Notice
} = wp.components;
const { Component } = wp.element;

const {
	select,
	dispatch
} = wp.data;
const {
	moveBlockToPosition,
	removeBlock
} = dispatch( 'core/editor' );

const {
	getBlock,
	getBlockRootClientId,
	getBlockOrder
} = select( 'core/editor' );

const ALLOWED_BLOCKS = [
	'core/button',
	'core/code',
	'core/embed',
	'core/image',
	'core/list',
	'core/paragraph',
	'core/preformatted',
	'core/pullquote',
	'core/quote',
	'core/table',
	'core/verse',
	'core/video'
];

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-story-cta-layer',
	{
		title: __( 'AMP Story CTA Layer' ),
		category: 'layout',
		icon: 'grid-view',
		parent: [ 'amp/amp-story-page' ],
		inserter: false,

		/*
		 * <amp-story-cta-layer>:
		 *   mandatory_ancestor: "AMP-STORY-PAGE"
		 *   descendant_tag_list: "amp-story-cta-layer-allowed-descendants"
		 *
		 * https://github.com/ampproject/amphtml/blob/87fe1d02f902be97b596b36ec3421592c83d241e/extensions/amp-story/validator-amp-story.protoascii#L172-L188
		 */

		edit: class extends Component {
			constructor( props ) {
				super( ...arguments );
				this.props = props;
				this.props.attributes.hasMultipleCtaBlocks = this.hasMoreThanOneCtaBlock();
			}

			shouldComponentUpdate() {
				if ( ! this.props.attributes.hasMultipleCtaBlocks ) {
					this.ensureBeingLastBlock();
				}
				return true;
			}

			componentDidMount() {
				if ( this.props.attributes.hasMultipleCtaBlocks ) {
					removeBlock( this.props.clientId );
					dispatch( 'core/editor' ).createWarningNotice( __( 'Multiple CTA Layers are not allowed, the block was removed.', 'amp' ) );
				}
			}

			ensureBeingLastBlock() {
				// @todo Display notice if the block gets moved.
				const rootClientID = getBlockRootClientId( this.props.clientId );
				const order = getBlockOrder( rootClientID );

				// If the CTA is not the last block, move it there.
				if ( _.last( order ) !== this.props.clientId ) {
					moveBlockToPosition( this.props.clientId, rootClientID, rootClientID, this.props.attributes.layout, order.length - 1 );
				}
			}

			render() {
				// In case of successful block removal this won't be visible.
				if ( this.props.attributes.hasMultipleCtaBlocks ) {
					return (
						<Notice status="error" isDismissible={ false }>{ __( 'Multiple CTA Layers are not allowed. Please remove all but one.', 'amp' ) }</Notice>
					);
				}
				return (
					<InnerBlocks key='contents' allowedBlocks={ ALLOWED_BLOCKS } />
				);
			}

			hasMoreThanOneCtaBlock() {
				const parentBlock = getBlock( getBlockRootClientId( this.props.clientId ) );
				if ( ! parentBlock ) {
					return false;
				}
				let ctaBlocks = 0;
				_.each( parentBlock.innerBlocks, function( child ) {
					if ( 'amp/amp-story-cta-layer' === child.name ) {
						ctaBlocks++;
					}
				} );
				return 1 < ctaBlocks;
			}
		},

		save( { attributes } ) {
			return (
				<amp-story-cta-layer template={ attributes.template }>
					<InnerBlocks.Content />
				</amp-story-cta-layer>
			);
		}
	}
);

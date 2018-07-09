import './amp-story-page';
import './amp-story-grid-layer';

const blockParents = {
	'core/button': 'amp/amp-story-grid-layer',
	'core/code': 'amp/amp-story-grid-layer',
	'core/embed': 'amp/amp-story-grid-layer',
	'core/image': 'amp/amp-story-grid-layer',
	'core/list': 'amp/amp-story-grid-layer',
	'core/paragraph': 'amp/amp-story-grid-layer',
	'core/preformatted': 'amp/amp-story-grid-layer',
	'core/pullquote': 'amp/amp-story-grid-layer',
	'core/quote': 'amp/amp-story-grid-layer',
	'core/table': 'amp/amp-story-grid-layer',
	'core/verse': 'amp/amp-story-grid-layer',
	'core/video': 'amp/amp-story-grid-layer'
};

function setBlockParent( props ) {
	if ( blockParents[ props.name ] ) {
		return Object.assign(
			{},
			props,
			{ parent: [ blockParents[ props.name ] ] }
		);
	}
	return props;
}

wp.hooks.addFilter(
	'blocks.registerBlockType',
	'amp/set-block-parents',
	setBlockParent
);

// Remove all blocks that are not known to be allowed in AMP Stories (ref. amp-story-cta-layer-allowed-descendants).
window.addEventListener( 'load', () => { // @todo Should be better event.
	wp.blocks.getBlockTypes().forEach( function( blockType ) {
		if ( -1 === blockType.name.indexOf( 'amp/amp-story-' ) && ! blockParents[ blockType.name ] ) {
			wp.blocks.unregisterBlockType( blockType.name );
		}
	} );
} );

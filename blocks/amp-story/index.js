import './amp-story-page';

import { ALLOWED_BLOCKS as LAYER_ALLOWED_BLOCKS } from './amp-story-grid-layer';

// Remove all blocks that are not known to be allowed in AMP Stories (ref. amp-story-cta-layer-allowed-descendants).
window.addEventListener( 'load', () => { // @todo Should be better event.
	wp.blocks.getBlockTypes().forEach( function( blockType ) {
		if ( -1 === blockType.name.indexOf( 'amp/amp-story-' ) && ! LAYER_ALLOWED_BLOCKS.includes( blockType.name ) ) {
			wp.blocks.unregisterBlockType( blockType.name );
		}
	} );
} );

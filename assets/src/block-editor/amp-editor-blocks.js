/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { getBlockType, unregisterBlockType } from '@wordpress/blocks';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { addAMPAttributes, filterBlocksEdit, filterBlocksSave, addAMPExtraProps } from './helpers';
import './store';

/*
 * If there's no theme support, unregister blocks that are only meant for AMP.
 * The Latest Stories block is meant for AMP and non-AMP, so don't unregister it here.
 */
const ampDependentBlocks = [
	'amp-brid-player',
	'amp-ima-video',
	'amp-jwplayer',
	'amp-mathml',
	'amp-o2-player',
	'amp-ooyala-player',
	'amp-reach-player',
	'amp-springboard-player',
	'amp-timeago',
];

if ( ! select( 'amp/block-editor' ).isNativeAMP() ) {
	for ( const block of ampDependentBlocks ) {
		const blockName = 'amp/' + block;

		if ( getBlockType( blockName ) ) {
			unregisterBlockType( blockName );
		}
	}
}

addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/filterSave', filterBlocksSave );
addFilter( 'editor.BlockEdit', 'ampEditorBlocks/filterEdit', filterBlocksEdit );
addFilter( 'blocks.getSaveContent.extraProps', 'ampEditorBlocks/addExtraAttributes', addAMPExtraProps );

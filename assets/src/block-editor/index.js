/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';
import { registerBlockType } from '@wordpress/blocks';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { withFeaturedImageNotice } from '../common/components';
import { getMinimumFeaturedImageDimensions } from '../common/helpers';
import { withMediaLibraryNotice } from './components';
import { addAMPAttributes, filterBlocksEdit, removeAmpFitTextFromBlocks, removeClassFromAmpFitTextBlocks } from './helpers';
import './store';

const {
	isStandardMode,
	getAmpBlocksInUse,
} = select( 'amp/block-editor' );

const plugins = require.context( './plugins', true, /.*\.js$/ );

plugins.keys().forEach( ( modulePath ) => {
	const { name, render, icon, onlyPaired = false } = plugins( modulePath );

	if ( onlyPaired && isStandardMode() ) {
		return;
	}

	registerPlugin( name, { icon, render } );
} );

addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/deprecateAmpFitText', removeAmpFitTextFromBlocks );
addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/deprecateAmpFitText/removeMiscAttrs', removeClassFromAmpFitTextBlocks );
addFilter( 'editor.BlockEdit', 'ampEditorBlocks/filterEdit', filterBlocksEdit, 20 );
addFilter( 'editor.PostFeaturedImage', 'ampEditorBlocks/withFeaturedImageNotice', withFeaturedImageNotice );
addFilter( 'editor.MediaUpload', 'ampEditorBlocks/withMediaLibraryNotice', ( InitialMediaUpload ) => withMediaLibraryNotice( InitialMediaUpload, getMinimumFeaturedImageDimensions() ) );

const ampBlocksInUse = getAmpBlocksInUse();
const blocks = require.context( './blocks', true, /(?<!test\/)index\.js$/ );

blocks.keys().forEach( ( modulePath ) => {
	const { name, settings } = blocks( modulePath );

	const shouldRegister = isStandardMode() && ampBlocksInUse.includes( name );

	if ( shouldRegister ) {
		registerBlockType( name, settings );
	}
} );

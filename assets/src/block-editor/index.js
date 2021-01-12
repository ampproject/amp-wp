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
} = select( 'amp/block-editor' );

const plugins = require.context( './plugins', true, /.*\.js$/ );

plugins.keys().forEach( ( modulePath ) => {
	const { name, render, icon } = plugins( modulePath );

	registerPlugin( name, { icon, render } );
} );

addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/addAttributes', addAMPAttributes );
addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/deprecateAmpFitText', removeAmpFitTextFromBlocks );
addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/deprecateAmpFitText/removeMiscAttrs', removeClassFromAmpFitTextBlocks );
addFilter( 'editor.BlockEdit', 'ampEditorBlocks/filterEdit', filterBlocksEdit, 20 );
addFilter( 'editor.PostFeaturedImage', 'ampEditorBlocks/withFeaturedImageNotice', withFeaturedImageNotice );
addFilter( 'editor.MediaUpload', 'ampEditorBlocks/withMediaLibraryNotice', ( InitialMediaUpload ) => withMediaLibraryNotice( InitialMediaUpload, getMinimumFeaturedImageDimensions() ) );

/*
 * If there's no theme support, unregister blocks that are only meant for AMP.
 */
const AMP_DEPENDENT_BLOCKS = [
	'amp/amp-brid-player',
	'amp/amp-ima-video',
	'amp/amp-jwplayer',
	'amp/amp-mathml',
	'amp/amp-o2-player',
	'amp/amp-ooyala-player',
	'amp/amp-reach-player',
	'amp/amp-springboard-player',
	'amp/amp-timeago',
];

const blocks = require.context( './blocks', true, /(?<!test\/)index\.js$/ );

blocks.keys().forEach( ( modulePath ) => {
	const { name, settings } = blocks( modulePath );

	const shouldRegister = isStandardMode() && AMP_DEPENDENT_BLOCKS.includes( name );

	if ( shouldRegister ) {
		registerBlockType( name, settings );
	}
} );

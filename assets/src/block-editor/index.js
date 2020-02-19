/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';
import { registerBlockType } from '@wordpress/blocks';
import { select } from '@wordpress/data';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { withFeaturedImageNotice } from '../common/components';
import { getMinimumFeaturedImageDimensions } from '../common/helpers';
import { withMediaLibraryNotice, AMPPreview } from './components';
import { addAMPAttributes, addAMPExtraProps, filterBlocksEdit, filterBlocksSave, renderPreviewButton } from './helpers';
import './store';

const {
	isWebsiteEnabled,
	isStandardMode,
} = select( 'amp/block-editor' );

// Add filters if AMP for Website experience is enabled.
if ( isWebsiteEnabled() ) {
	const plugins = require.context( './plugins', true, /.*\.js$/ );

	plugins.keys().forEach( ( modulePath ) => {
		const { name, render, icon } = plugins( modulePath );

		registerPlugin( name, { icon, render } );
	} );

	addFilter( 'blocks.registerBlockType', 'ampEditorBlocks/addAttributes', addAMPAttributes );
	addFilter( 'blocks.getSaveElement', 'ampEditorBlocks/filterSave', filterBlocksSave );
	addFilter( 'editor.BlockEdit', 'ampEditorBlocks/filterEdit', filterBlocksEdit, 20 );
	addFilter( 'blocks.getSaveContent.extraProps', 'ampEditorBlocks/addExtraAttributes', addAMPExtraProps );
	addFilter( 'editor.PostFeaturedImage', 'ampEditorBlocks/withFeaturedImageNotice', withFeaturedImageNotice );
	addFilter( 'editor.MediaUpload', 'ampEditorBlocks/withMediaLibraryNotice', ( InitialMediaUpload ) => withMediaLibraryNotice( InitialMediaUpload, getMinimumFeaturedImageDimensions() ) );
}

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

	const shouldRegister = isWebsiteEnabled() && isStandardMode() && AMP_DEPENDENT_BLOCKS.includes( name );

	if ( shouldRegister ) {
		registerBlockType( name, settings );
	}
} );

// Render the 'Preview AMP' button, and move it to after the (non-AMP) 'Preview' button.
if ( isWebsiteEnabled() ) {
	domReady( () => {
		renderPreviewButton( AMPPreview );
	} );
}

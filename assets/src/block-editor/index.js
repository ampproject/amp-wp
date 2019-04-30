/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
// @todo Import from '../components' and use tree shaking in development mode to prevent warnings.
import withCroppedFeaturedImage from '../components/with-cropped-featured-image';
import withFeaturedImageNotice from '../components/with-featured-image-notice';
import { getMinimumFeaturedImageDimensions } from '../common/helpers';

// Display a notice in the Featured Image panel if none exists or its width is too small.
addFilter( 'editor.PostFeaturedImage', 'ampEditorBlocks/withFeaturedImageNotice', withFeaturedImageNotice );

addFilter( 'editor.MediaUpload', 'ampEditorBlocks/addCroppedFeaturedImage', ( InitialMediaUpload ) => withCroppedFeaturedImage( InitialMediaUpload, getMinimumFeaturedImageDimensions() ) );

const plugins = require.context( './plugins', true, /.*\.js$/ );

plugins.keys().forEach( ( modulePath ) => {
	const { name, render, icon } = plugins( modulePath );

	registerPlugin( name, { icon, render } );
} );

const blocks = require.context( './blocks', true, /(?<!test\/)index\.js$/ );

blocks.keys().forEach( ( modulePath ) => {
	const { name, settings } = blocks( modulePath );

	registerBlockType( name, settings );
} );

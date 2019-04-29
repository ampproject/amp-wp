/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { PrePublishPanel, withCroppedFeaturedImage, withFeaturedImageNotice } from '../components';
import { getMinimumFeaturedImageDimensions } from '../stories-editor/helpers';

// Display a notice in the Featured Image panel if none exists or its width is too small.
addFilter( 'editor.PostFeaturedImage', 'ampEditorBlocks/withFeaturedImageNotice', withFeaturedImageNotice );

addFilter( 'editor.MediaUpload', 'ampEditorBlocks/addCroppedFeaturedImage', withCroppedFeaturedImage );

// On clicking 'Publish,' display a notice if no featured image exists or its width is too small.
registerPlugin(
	'amp-post-featured-image-pre-publish',
	{
		render: () => {
			return (
				<PrePublishPanel
					dimensions={ getMinimumFeaturedImageDimensions() }
					required={ false }
				/>
			);
		},
	}
);

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

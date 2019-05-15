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
// @todo Import from '../components' and use tree shaking in development mode to prevent warnings.
import withCroppedFeaturedImage from '../components/with-cropped-featured-image';
import withFeaturedImageNotice from '../components/higher-order/with-featured-image-notice';
import { getMinimumFeaturedImageDimensions } from '../common/helpers';
import './store';

// Display a notice in the Featured Image panel if none exists or its width is too small.
addFilter( 'editor.PostFeaturedImage', 'ampEditorBlocks/withFeaturedImageNotice', withFeaturedImageNotice );
addFilter( 'editor.MediaUpload', 'ampEditorBlocks/addCroppedFeaturedImage', ( InitialMediaUpload ) => withCroppedFeaturedImage( InitialMediaUpload, getMinimumFeaturedImageDimensions() ) );

/*
 * If there's no theme support, unregister blocks that are only meant for AMP.
 * The Latest Stories block is meant for AMP and non-AMP, so don't unregister it here.
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

const plugins = require.context( './plugins', true, /.*\.js$/ );

plugins.keys().forEach( ( modulePath ) => {
	const { name, render, icon } = plugins( modulePath );

	registerPlugin( name, { icon, render } );
} );

const blocks = require.context( './blocks', true, /(?<!test\/)index\.js$/ );

blocks.keys().forEach( ( modulePath ) => {
	const { name, settings } = blocks( modulePath );

	const blockRequiresAmp = AMP_DEPENDENT_BLOCKS.includes( name );

	if ( ! blockRequiresAmp || select( 'amp/block-editor' ).isNativeAMP() ) {
		registerBlockType( name, settings );
	}
} );

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
import { withCroppedFeaturedImage, withFeaturedImageNotice } from '../common/components';
import { addAMPAttributes, addAMPExtraProps, filterBlocksEdit, filterBlocksSave } from './helpers';
import { getMinimumFeaturedImageDimensions, getMinimumPortraitFeaturedImageDimensions } from '../common/helpers';
import './store';

const {
	isWebsiteEnabled,
	isStoriesEnabled,
	isStandardMode,
} = select( 'amp/block-editor' );

const { ampLatestStoriesBlockData } = window;

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
	addFilter( 'editor.MediaUpload', 'ampEditorBlocks/addCroppedFeaturedImage', ( InitialMediaUpload ) => withCroppedFeaturedImage( InitialMediaUpload, getMinimumFeaturedImageDimensions(), getMinimumPortraitFeaturedImageDimensions() ) );
}

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

const blocks = require.context( './blocks', true, /(?<!test\/)index\.js$/ );

blocks.keys().forEach( ( modulePath ) => {
	const { name, settings } = blocks( modulePath );

	const isLatestStoriesBlock = 'amp/amp-latest-stories' === name;

	const shouldRegister = (
		(
			isWebsiteEnabled() && isStandardMode() && AMP_DEPENDENT_BLOCKS.includes( name )
		) ||
		(
			isStoriesEnabled() && isLatestStoriesBlock && typeof ampLatestStoriesBlockData !== 'undefined'
		)
	);

	if ( shouldRegister ) {
		registerBlockType( name, settings );
	}
} );

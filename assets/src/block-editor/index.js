/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
//import { getFeaturedImageNotice, getPrePublishNotice } from '../components';
import getFeaturedImageNotice from '../components/get-featured-image-notice';
import getPrePublishNotice from '../components/get-pre-publish-notice';

/**
 * Whether the image has the minimum width for a featured image.
 *
 * This should have a width of at least 1200 pixels
 * to satisfy the requirement of Google Search for Schema.org metadata.
 *
 * @param {Object} media A media object with width and height values.
 * @return {boolean} Whether the media has the minimum dimensions.
 */
const hasMinimumFeaturedImageWidth = ( media ) => {
	return ( media.width && media.width >= 1200 );
};
const featuredImageMessage = __( 'The featured image should have a width of at least 1200px.', 'amp' );

// Display a notice in the Featured Image panel if none exists or its width is too small.
addFilter(
	'editor.PostFeaturedImage',
	'ampEditorBlocks/addPostFeaturedImageNotice',
	getFeaturedImageNotice(
		hasMinimumFeaturedImageWidth,
		featuredImageMessage
	)
);

// On clicking 'Publish,' display a notice if no featured image exists or its width is too small.
registerPlugin(
	'amp-post-featured-image-pre-publish',
	{
		render: getPrePublishNotice(
			hasMinimumFeaturedImageWidth,
			featuredImageMessage
		),
	}
);

const plugins = require.context( './plugins', true, /.*\.js$/ );

plugins.keys().forEach( ( modulePath ) => {
	const { name, render, icon } = plugins( modulePath );

	registerPlugin( name, { icon, render } );
} );

const blocks = require.context( './blocks', true, /index\.js$/ );

blocks.keys().forEach( ( modulePath ) => {
	const { name, settings } = blocks( modulePath );

	registerBlockType( name, settings );
} );

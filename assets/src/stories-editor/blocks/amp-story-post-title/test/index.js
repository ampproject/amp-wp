/**
 * WordPress dependencies
 */
import { addFilter, removeFilter } from '@wordpress/hooks';
import '@wordpress/editor'; // So the data store is registered.

/**
 * Internal dependencies
 */
import { name, settings } from '../';
import { blockEditRender } from '../../../../test/helpers';
import { addAMPAttributes } from '../../../helpers';
import { withAmpStorySettings } from '../../../components';

describe( 'amp/amp-story-post-title', () => {
	beforeAll( () => {
		addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes', addAMPAttributes );
		addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addStorySettings', withAmpStorySettings );
	} );

	afterAll( () => {
		removeFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/addAttributes' );
		removeFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/addStorySettings' );
	} );

	test( 'block is rendered', () => {
		const wrapper = blockEditRender( name, settings );

		expect( wrapper.render().find( '.wp-block-amp-amp-story-post-title' ) ).toHaveLength( 1 );
	} );
} );

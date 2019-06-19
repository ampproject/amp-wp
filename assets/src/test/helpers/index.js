/**
 * External dependencies
 */
import { shallow } from 'enzyme';

/**
 * WordPress dependencies
 */
import {
	createBlock,
	getBlockType,
	registerBlockType,
} from '@wordpress/blocks';
import { BlockEdit } from '@wordpress/block-editor';
import { withFilters } from '@wordpress/components';

export const blockEditRender = ( name, settings ) => {
	if ( ! getBlockType( name ) ) {
		registerBlockType( name, settings );
	}

	const block = createBlock( name );

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const Component = withFilters( 'editor.BlockEdit' )( BlockEdit ); // @todo: Figure out why this filter is necessary here.

	return shallow(
		<Component
			clientId={ block.clientId }
			name={ name }
			attributes={ block.attributes }
			isSelected={ false }
			setAttributes={ jest.fn() }
		/>
	);
};

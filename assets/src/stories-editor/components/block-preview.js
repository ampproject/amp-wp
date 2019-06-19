/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { Disabled } from '@wordpress/components';
import { BlockEdit } from '@wordpress/block-editor';

/**
 * Block Preview Component: It renders a preview given a block name and attributes.
 *
 * @param {Object} props Component props.
 *
 * @return {WPElement} Rendered element.
 */
const BlockPreview = ( { clientId, name, attributes, innerBlocks = [] } ) => {
	const block = createBlock( name, attributes, innerBlocks );
	return (
		<Disabled className="block-editor-block-preview__content editor-styles-wrapper" aria-hidden>
			<BlockEdit
				name={ name }
				focus={ false }
				clientId={ clientId }
				isLocked={ true }
				insertBlocksAfter={ false }
				attributes={ block.attributes }
				setAttributes={ () => {} }
			/>
		</Disabled>
	);
};

BlockPreview.propTypes = {
	clientId: PropTypes.string,
	name: PropTypes.string.isRequired,
	attributes: PropTypes.object.isRequired,
	innerBlocks: PropTypes.arrayOf( PropTypes.object ),
};

export default BlockPreview;

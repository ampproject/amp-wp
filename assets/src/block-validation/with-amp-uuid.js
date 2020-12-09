/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { v4 as uuid } from 'uuid';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { useEffect } from '@wordpress/element';

/**
 * Sets each block's AMP UUID.
 *
 * @param {Object} props
 */
function BlockEditorWithAMPUuid( props ) {
	const { attributes: { amp_uuid: ampUuid }, BlockEdit, setAttributes } = props;

	// Set the block's AMP UUID if it has not been set yet.
	useEffect( () => {
		if ( ! ampUuid ) {
			setAttributes( { amp_uuid: uuid() } );
		}
	}, [ ampUuid, setAttributes ] );

	return (
		<BlockEdit { ...props } />
	);
}
BlockEditorWithAMPUuid.propTypes = {
	attributes: {
		amp_uuid: PropTypes.oneOf( [ false, PropTypes.string ] ),
	},
	BlockEdit: PropTypes.func.isRequired,
	setAttributes: PropTypes.func.isRequired,
};

/**
 * Filters the block edit function of all blocks.
 */
export const withAMPUuid = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => <BlockEditorWithAMPUuid { ...props } BlockEdit={ BlockEdit } />,
	'BlockEditorWithAMPUuid',
);

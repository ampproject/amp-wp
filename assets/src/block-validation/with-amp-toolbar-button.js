/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from './store';
import { AMPToolbarButton } from './amp-toolbar-button';

/**
 * Adds the AMPToolbarButton to blocks that have one or more unreviewed validation errors.
 *
 * @param {Object} props
 * @param {Function} props.BlockEdit Block edit function.
 * @param {string} props.clientId Client ID.
 */
function BlockEditWithToolbar( props ) {
	const { BlockEdit, clientId } = props;

	const count = useSelect(
		( select ) => ( select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors() || [] )
			.filter( ( { clientId: validationErrorClientId } ) => clientId === validationErrorClientId )
			.length || 0,
		[ clientId ],
	);

	return (
		<>
			{ 0 < count &&
				<AMPToolbarButton clientId={ clientId } count={ count } />
			}
			<BlockEdit { ...props } />

		</>
	);
}
BlockEditWithToolbar.propTypes = {
	BlockEdit: PropTypes.func.isRequired,
	clientId: PropTypes.string.isRequired,
};

/**
 * Filters the block edit function of all blocks.
 */
export const withAMPToolbarButton = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => <BlockEditWithToolbar { ...props } BlockEdit={ BlockEdit } />,
	'BlockEditWithAMPToolbar',
);

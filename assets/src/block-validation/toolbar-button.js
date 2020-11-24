/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { BlockControls } from '@wordpress/block-editor';
import { ToolbarButton } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ToolbarIcon } from './icon';
import { BLOCK_VALIDATION_STORE_KEY } from './store';
import { PLUGIN_NAME, SIDEBAR_NAME } from '.';

/**
 * AMP button displaying in the block toolbar.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId Block client ID.
 */
function AMPBlockToolBarButton( { clientId } ) {
	const count = useSelect(
		( select ) => select( BLOCK_VALIDATION_STORE_KEY ).getBlockValidationErrors( clientId )?.length || 0,
		[ clientId ],
	);

	const { openGeneralSidebar } = useDispatch( 'core/edit-post' );

	return (
		<BlockControls>
			<ToolbarButton onClick={ () => {
				openGeneralSidebar( `${ PLUGIN_NAME }/${ SIDEBAR_NAME }` );
			} }>
				<ToolbarIcon count={ count } />
			</ToolbarButton>
		</BlockControls>
	);
}
AMPBlockToolBarButton.propTypes = {
	clientId: PropTypes.string,
};

/**
 * Filters blocks edit function of all blocks.
 *
 * @param {Function} BlockEdit function.
 *
 * @return {Function} Edit function.
 */
export function filterBlocksEdit( BlockEdit ) {
	// Filter a class component.
	if ( BlockEdit.prototype.render ) {
		return class BlockEditWithAmpToolBarButtonClass extends BlockEdit {
			render() {
				return (
					<>
						<AMPBlockToolBarButton clientId={ this.props.clientId } />
						{ super.render() }
					</>
				);
			}
		};
	}

	// Filter a function component.
	const BlockEditWithAmpToolBarButtonFunction = ( props ) => {
		return (
			<>
				<AMPBlockToolBarButton clientId={ props.clientId } />
				<BlockEdit { ...props } />

			</>
		);
	};
	BlockEditWithAmpToolBarButtonFunction.propTypes = {
		clientId: PropTypes.string,
	};

	return BlockEditWithAmpToolBarButtonFunction;
}

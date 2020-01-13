/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { PageInserter } from '../';
import reorderIcon from '../../../../images/stories-editor/reorder.svg';
import './edit.css';

/**
 * Component that adds additional controls in the top of the editor
 * to add new pages and start/stop reordering pages.
 */
function StoryControls() {
	const {
		isReordering,
		blockOrder,
	} = useSelect( ( select ) => {
		return {
			isReordering: select( 'amp/story' ).isReordering(),
			blockOrder: select( 'core/block-editor' ).getBlockOrder(),
		};
	}, [] );

	const { clearSelectedBlock } = useDispatch( 'core/block-editor' );
	const { startReordering, saveOrder, resetOrder } = useDispatch( 'amp/story' );

	if ( isReordering ) {
		return (
			<>
				<Button
					className="amp-story-controls-reorder-cancel"
					onClick={ () => resetOrder( blockOrder ) }
					icon="no-alt"
				>
					{ __( 'Cancel', 'amp' ) }
				</Button>
				<Button
					className="amp-story-controls-reorder-save"
					onClick={ saveOrder }
					isLarge
					isPrimary
				>
					{ __( 'Save Changes', 'amp' ) }
				</Button>
			</>
		);
	}

	return (
		<>
			<PageInserter />
			<Button
				className="amp-story-controls-reorder"
				icon={ reorderIcon( { width: 24, height: 19 } ) }
				label={ __( 'Reorder Pages', 'amp' ) }
				onClick={ () => {
					clearSelectedBlock();
					startReordering( blockOrder );
				} }
			/>
		</>
	);
}

export default StoryControls;

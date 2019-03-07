/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton, Button } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * @todo Use Inserter component from @wordpress/editor or similar for the "Add New Page" functionality.
 *
 * @return {Object} Story controls component.
 */
function StoryControls( { isReordering, startReordering, stopReordering } ) {
	if ( isReordering ) {
		return (
			<Fragment>
				<IconButton
					className="amp-story-controls-reorder-cancel"
					onClick={ stopReordering }
					icon="no-alt"
				>
					{ __( 'Cancel', 'amp' ) }
				</IconButton>
				<Button
					className="amp-story-controls-reorder-save"
					onClick={ stopReordering }
					isLarge
					isPrimary
				>
					{ __( 'Save Changes', 'amp' ) }
				</Button>
			</Fragment>
		);
	}

	return (
		<Fragment>
			<IconButton
				className="amp-story-controls-add"
				icon="insert"
				label={ __( 'Add New Page', 'amp' ) }
				onClick={ ( e ) => {
					e.preventDefault();
				} }
			/>
			<IconButton
				className="amp-story-controls-reorder"
				icon="sort"
				label={ __( 'Reorder Pages', 'amp' ) }
				onClick={ startReordering }
			/>
		</Fragment>
	);
}

// Todo: Move pages and moveBlockToPosition to separate reorder component.
export default compose(
	withSelect( ( select ) => {
		const {	getBlocksByClientId	} = select( 'core/editor' );
		const { getBlockOrder, isReordering } = select( 'amp/story' );

		const pages = getBlocksByClientId( getBlockOrder() );

		return {
			pages,
			isReordering: isReordering(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { moveBlockToPosition } = dispatch( 'core/editor' );
		const { setCurrentPage, startReordering, stopReordering } = dispatch( 'amp/story' );

		return {
			onChangePage: ( pageClientId ) => {
				setCurrentPage( pageClientId );
			},
			startReordering,
			stopReordering,
			moveBlockToPosition: ( clientId, index ) => moveBlockToPosition( clientId, '', '', index ),
		};
	} )
)( StoryControls );

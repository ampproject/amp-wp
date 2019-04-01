/**
 * Component that adds additional controls in the top of the editor
 * to add new pages and start/stop reordering pages.
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton, Button } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { TemplateInserter } from './';
import reorderIcon from '../../images/reorder.svg';

function StoryControls( { isReordering, startReordering, saveOrder, resetOrder } ) {
	if ( isReordering ) {
		return (
			<Fragment>
				<IconButton
					className="amp-story-controls-reorder-cancel"
					onClick={ resetOrder }
					icon="no-alt"
				>
					{ __( 'Cancel', 'amp' ) }
				</IconButton>
				<Button
					className="amp-story-controls-reorder-save"
					onClick={ saveOrder }
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
			<TemplateInserter />
			<IconButton
				className="amp-story-controls-reorder"
				icon={ reorderIcon( { width: 24, height: 19 } ) }
				label={ __( 'Reorder Pages', 'amp' ) }
				onClick={ startReordering }
			/>
		</Fragment>
	);
}

export default compose(
	withSelect( ( select ) => {
		const { isReordering } = select( 'amp/story' );

		return {
			isReordering: isReordering(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { startReordering, saveOrder, resetOrder } = dispatch( 'amp/story' );

		return {
			startReordering,
			saveOrder,
			resetOrder,
		};
	} )
)( StoryControls );

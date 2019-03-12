/**
 * Custom component for BlockDropZone for being able to position inner blocks via drag and drop.
 * Parts of this are taken from the original BlockDropZone component.
 */

/**
 * WordPress dependencies
 */
import {
	DropZone,
} from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {
	MediaUploadCheck,
} from '@wordpress/editor';

class BlockDropZone extends Component {
	constructor() {
		super( ...arguments );

		this.onDrop = this.onDrop.bind( this );
	}

	onDrop( event ) {
		const { getBlockAttributes, updateBlockAttributes, srcClientId } = this.props;

		const elementId = `block-${ srcClientId }`;
		const cloneElementId = `clone-block-${ srcClientId }`;
		const element = document.getElementById( elementId );
		const clone = document.getElementById( cloneElementId );
		if ( ! element || ! clone ) {
			event.preventDefault();
			return;
		}

		const srcAttributes = getBlockAttributes( srcClientId );
		// Get the original position of the block that was dragged
		const srcPosition = element.getBoundingClientRect();
		// Get the current position of the clone.
		const clonePosition = clone.getBoundingClientRect();

		// We will set the new position based on where the clone was moved to.
		// The new attributes are set based on the difference between the original component and the clone added to the previous attributes.
		let positionLeft = srcAttributes.positionLeft + clonePosition.left - srcPosition.left;
		let positionTop = srcAttributes.positionTop + clonePosition.top - srcPosition.top;
		positionLeft = positionLeft < 0 ? 0 : positionLeft;
		positionTop = positionTop < 0 ? 0 : positionTop;

		updateBlockAttributes( srcClientId, {
			positionLeft,
			positionTop
		} );
	}

	render() {
		return (
			<MediaUploadCheck>
				<DropZone
					className='editor-block-drop-zone'
					onDrop={ this.onDrop }
				/>
			</MediaUploadCheck>
		);
	}
}

export default compose(
	withDispatch( ( dispatch ) => {
		const { updateBlockAttributes } = dispatch( 'core/block-editor' );

		return {
			updateBlockAttributes( ...args ) {
				updateBlockAttributes( ...args );
			},
		};
	} ),
	withSelect( ( select ) => {
		const { getBlockAttributes } = select( 'core/block-editor' );
		return {
			getBlockAttributes,
		};
	} )
)( BlockDropZone );

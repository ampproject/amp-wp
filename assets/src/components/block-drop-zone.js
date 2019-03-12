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
import { withDispatch } from '@wordpress/data';

const wrapperElSelector = 'div[data-amp-selected="parent"] .editor-inner-blocks';

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
		const { updateBlockAttributes, srcClientId } = this.props;

		const elementId = `block-${ srcClientId }`;
		const cloneElementId = `clone-block-${ srcClientId }`;
		const element = document.getElementById( elementId );
		const clone = document.getElementById( cloneElementId );

		// Get the editor wrapper element for calculating the width and height.
		const wrapperEl = document.querySelector( wrapperElSelector );
		if ( ! element || ! clone || ! wrapperEl || ! wrapperEl.clientHeight || ! wrapperEl.clientWidth ) {
			event.preventDefault();
			return;
		}

		// Get the current position of the clone.
		const clonePosition = clone.getBoundingClientRect();

		const wrapperPosition = wrapperEl.getBoundingClientRect();

		// We will set the new position based on where the clone was moved to, with reference being the wrapper element.
		// Lets take the % based on the wrapper for top and left.
		const positionLeftInPx = clonePosition.left - wrapperPosition.left;
		const positionTopInPx = clonePosition.top - wrapperPosition.top;

		const positionLeft = Math.round( ( positionLeftInPx / wrapperEl.clientWidth ) * 100 );
		const positionTop = Math.round( ( positionTopInPx / wrapperEl.clientHeight ) * 100 );

		updateBlockAttributes( srcClientId, {
			positionLeft,
			positionTop,
		} );
	}

	render() {
		return (
			<MediaUploadCheck>
				<DropZone
					className="editor-block-drop-zone"
					onDrop={ this.onDrop }
				/>
			</MediaUploadCheck>
		);
	}
}
export default withDispatch( ( dispatch ) => {
	const { updateBlockAttributes } = dispatch( 'core/block-editor' );

	return {
		updateBlockAttributes( ...args ) {
			updateBlockAttributes( ...args );
		},
	};
} )( BlockDropZone );

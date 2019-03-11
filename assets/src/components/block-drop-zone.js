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

const parseDropEvent = ( event ) => {
	let result = {
		srcRootClientId: null,
		srcClientId: null,
		srcIndex: null,
		type: null,
	};

	if ( ! event.dataTransfer ) {
		return result;
	}

	try {
		result = Object.assign( result, JSON.parse( event.dataTransfer.getData( 'text' ) ) );
	} catch ( err ) {
		return result;
	}

	return result;
};

class BlockDropZone extends Component {
	constructor() {
		super( ...arguments );

		this.onDrop = this.onDrop.bind( this );
	}

	onDrop( event ) {
		const { clientId: dstClientId, getBlockAttributes, updateBlockAttributes } = this.props;
		const { srcClientId } = parseDropEvent( event );

		const elementId = `block-${ dstClientId }`;
		const element = document.getElementById( elementId );
		if ( ! element ) {
			event.preventDefault();
			return;
		}

		const srcAttributes = getBlockAttributes( srcClientId );
		// Get the original position of the block that was dragged
		const srcPosition = element.getBoundingClientRect();

		// Base on this we can calculate the change of the original position and add this to the previous value.
		const positionLeft = srcAttributes.positionLeft + ( event.clientX - srcPosition.left );
		const positionTop = srcAttributes.positionTop + ( event.clientY - srcPosition.top );
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

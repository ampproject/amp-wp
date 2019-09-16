/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { pasteHandler, serialize } from '@wordpress/blocks';
import { documentHasSelection } from '@wordpress/dom';
import { withDispatch, useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ensureAllowedBlocksOnPaste } from '../../helpers';

function CopyPasteHandler( { children, onCopy, onCut, clientId, isSelected } ) {
	const {
		isFirstPage,
		canUserUseUnfilteredHTML,
	} = useSelect(
		( select ) => {
			const {
				getBlockOrder,
				getSettings,
			} = select( 'core/block-editor' );
			const { __experimentalCanUserUseUnfilteredHTML } = getSettings();
			return {
				isFirstPage: getBlockOrder().indexOf( clientId ) === 0,
				canUserUseUnfilteredHTML: __experimentalCanUserUseUnfilteredHTML,
			};
		}, [ clientId ]
	);

	const { insertBlocks } = useDispatch( 'core/block-editor' );

	const onPaste = ( event ) => {
		// Ignore if the Page is not the selected page.
		if ( ! isSelected ) {
			return;
		}
		const clipboardData = event.clipboardData;

		let plainText = '';
		let html = '';

		// IE11 only supports `Text` as an argument for `getData` and will
		// otherwise throw an invalid argument error, so we try the standard
		// arguments first, then fallback to `Text` if they fail.
		try {
			plainText = clipboardData.getData( 'text/plain' );
			html = clipboardData.getData( 'text/html' );
		} catch ( error1 ) {
			try {
				html = clipboardData.getData( 'Text' );
			} catch ( error2 ) {
				// Some browsers like UC Browser paste plain text by default and
				// don't support clipboardData at all, so allow default
				// behaviour.
				return;
			}
		}

		event.preventDefault();

		const mode = 'BLOCKS';

		const content = pasteHandler( {
			HTML: html,
			plainText,
			mode,
			canUserUseUnfilteredHTML,
		} );

		if ( content.length > 0 ) {
			insertBlocks( ensureAllowedBlocksOnPaste( content, clientId, isFirstPage ), null, clientId );
		}
	};

	return (
		<div onCopy={ onCopy } onPaste={ onPaste } onCut={ onCut }>
			{ children }
		</div>
	);
}

CopyPasteHandler.propTypes = {
	children: PropTypes.object.isRequired,
	clientId: PropTypes.string.isRequired,
	isSelected: PropTypes.bool.isRequired,
	onCopy: PropTypes.func.isRequired,
	onCut: PropTypes.func.isRequired,
};

export default withDispatch( ( dispatch, ownProps, { select } ) => {
	const {
		getBlocksByClientId,
		getSelectedBlockClientIds,
		hasMultiSelection,
	} = select( 'core/block-editor' );
	const { clearCopiedMarkup, setCopiedMarkup } = dispatch( 'amp/story' );

	/**
	 * Creates cut/copy handler for ensuring that the store's copiedMarkup is in sync with what's actually in clipBoard.
	 * If it's not a block that's being copied, let's clear the copiedMarkup.
	 * Otherwise, let's set the copied markup.
	 * If it's a cut handler, finally remove the currently selected block.
	 *
	 * @param {boolean} isCut  Set to true if this is a cut handler, false if copy handler
	 * @return {Function} Returns an event handler for the desired action
	 */
	const createCutCopyHandler = ( isCut ) => () => {
		const selectedBlockClientIds = getSelectedBlockClientIds();

		if ( selectedBlockClientIds.length === 0 ) {
			clearCopiedMarkup();
			return;
		}

		// Let native copy behaviour take over in input fields.
		if ( ! hasMultiSelection() && documentHasSelection() ) {
			clearCopiedMarkup();
			return;
		}
		const serialized = serialize( getBlocksByClientId( selectedBlockClientIds ) );
		setCopiedMarkup( serialized );

		if ( isCut ) {
			// TODO: Remove selected Blocks.
			// The code below works most of the time, but sometimes (unable to tell when and why) another element on page is selected when the block is removed, and then the browser copies this other element in stead of the previously selected element (that has now been removed).
			/* for ( const clientId of selectedBlockClientIds ) {
				removeBlock( clientId );
			} */
			// wrapping the call in setTimeout fixes the case where another element is selected on cut, but throws an error in the cases, where the above code works fine.
		}
	};

	const onCopy = createCutCopyHandler( false );
	const onCut = createCutCopyHandler( true );

	return {
		onCopy,
		onCut,
	};
} )( CopyPasteHandler );

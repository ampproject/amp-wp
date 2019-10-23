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
import { copyTextToClipBoard, isPageBlock, useIsBlockAllowedOnPage, displayPasteError } from '../../helpers';

function CopyPasteHandler( { children, onCopy, clientId, isSelected } ) {
	const {
		canUserUseUnfilteredHTML,
		getCopiedMarkupState,
		blocksOnPage,
	} = useSelect(
		( select ) => {
			const { getSettings, getBlockOrder } = select( 'core/block-editor' );
			const { __experimentalCanUserUseUnfilteredHTML } = getSettings();
			const { getCopiedMarkup } = select( 'amp/story' );
			return {
				canUserUseUnfilteredHTML: __experimentalCanUserUseUnfilteredHTML,
				getCopiedMarkupState: getCopiedMarkup,
				blocksOnPage: getBlockOrder( clientId ),
			};
		},
		[ clientId ]
	);

	const { insertBlock } = useDispatch( 'core/block-editor' );
	const { createErrorNotice } = useDispatch( 'core/notices' );
	const isBlockAllowedOnPage = useIsBlockAllowedOnPage();

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
				// If everything goes wrong, fall back to state based clipboard.
				plainText = getCopiedMarkupState();
				html = getCopiedMarkupState();
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

		if ( ! clientId || ! content.length ) {
			return;
		}

		content.forEach( ( pastedBlock ) => {
			if ( isBlockAllowedOnPage( pastedBlock.name, clientId ) ) {
				insertBlock( pastedBlock, blocksOnPage.length, clientId );
			} else {
				displayPasteError( pastedBlock.name, createErrorNotice );
			}
		} );
	};

	return (
		<div onCopy={ onCopy } onPaste={ onPaste } onCut={ onCopy }>
			{ children }
		</div>
	);
}

CopyPasteHandler.propTypes = {
	children: PropTypes.node.isRequired,
	clientId: PropTypes.string.isRequired,
	isSelected: PropTypes.bool,
	onCopy: PropTypes.func.isRequired,
};

export default withDispatch( ( dispatch, ownProps, { select } ) => {
	const {
		getBlocksByClientId,
		getSelectedBlockClientIds,
		hasMultiSelection,
	} = select( 'core/block-editor' );
	const { getCurrentPage } = select( 'amp/story' );
	const { clearCopiedMarkup, setCopiedMarkup } = dispatch( 'amp/story' );
	const { removeBlock, selectBlock } = dispatch( 'core/block-editor' );

	/**
	 * Creates cut/copy handler for ensuring that the store's copiedMarkup is in sync with what's actually in clipBoard.
	 * If it's not a block that's being copied, let's clear the copiedMarkup.
	 * Otherwise, let's set the copied markup.
	 * If it's a cut handler, finally remove the currently selected block.
	 *
	 * @param  {Event} event Event object.
	 */
	const onCopy = ( event ) => {
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

		// Don't allow story blocks to be copyied.
		for ( const selectedBlockClientId of selectedBlockClientIds ) {
			if ( isPageBlock( selectedBlockClientId ) ) {
				clearCopiedMarkup();
				return;
			}
		}

		const copyBlocks = getBlocksByClientId( selectedBlockClientIds );
		const serialized = serialize( copyBlocks );
		// Workout what type of event, from event object passed to this function.
		const isCut = ( event.type === 'cut' );

		// Make sure that setCopiedMarkup finishes before doing anything else.
		setCopiedMarkup( serialized ).then( () => {
			copyTextToClipBoard( serialized );

			if ( isCut ) {
				const pageClientId = getCurrentPage();
				for ( const clientId of selectedBlockClientIds ) {
					// On removing block, change focus to the page, to make sure that editor doesn't get confused and tries to select an already removed block.
					selectBlock( pageClientId );
					removeBlock( clientId );
				}
			}
		} );
	};

	return {
		onCopy,
	};
} )( CopyPasteHandler );

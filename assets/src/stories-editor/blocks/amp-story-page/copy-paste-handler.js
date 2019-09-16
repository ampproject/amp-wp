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
import { copyTextToClipBoard, ensureAllowedBlocksOnPaste } from '../../helpers';

function CopyPasteHandler( { children, onCopy, onCut, clientId, isSelected } ) {
	const {
		isFirstPage,
		canUserUseUnfilteredHTML,
		getCopiedMarkupState,
	} = useSelect(
		( select ) => {
			const {
				getBlockOrder,
				getSettings,
			} = select( 'core/block-editor' );
			const { __experimentalCanUserUseUnfilteredHTML } = getSettings();
			const { getCopiedMarkup } = select( 'amp/story' );
			return {
				isFirstPage: getBlockOrder().indexOf( clientId ) === 0,
				canUserUseUnfilteredHTML: __experimentalCanUserUseUnfilteredHTML,
				getCopiedMarkupState: getCopiedMarkup,
			};
		}, [ clientId ]
	);

	const { insertBlocks } = useDispatch( 'core/block-editor' );
	const { clearCopiedMarkup } = useDispatch( 'amp/story' );

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
	const { removeBlock } = dispatch( 'core/block-editor' );

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

		const copyBlocks = getBlocksByClientId( selectedBlockClientIds );
		for ( const copyBlock of copyBlocks ) {
			if ( copyBlock && copyBlock.name && 'amp/amp-story-page' === copyBlock.name ) {
				return;
			}
		}

		const serialized = serialize( copyBlocks );
		setCopiedMarkup( serialized ).then( () => {
			copyTextToClipBoard( serialized );

			if ( isCut ) {
				for ( const clientId of selectedBlockClientIds ) {
					removeBlock( clientId );
				}
			}
		} );
	};

	const onCopy = createCutCopyHandler( false );
	const onCut = createCutCopyHandler( true );

	return {
		onCopy,
		onCut,
	};
} )( CopyPasteHandler );

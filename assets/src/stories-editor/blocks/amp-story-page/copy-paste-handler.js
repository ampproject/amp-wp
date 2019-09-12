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
	const { removeBlock } = dispatch( 'core/block-editor' );
	const { clearCopiedMarkup, setCopiedMarkup } = dispatch( 'amp/story' );

	/**
	 * Copy handler for ensuring that the store's copiedMarkup is in sync with what's actually in clipBoard.
	 * If it's not a block that's being copied, let's clear the copiedMarkup.
	 * Otherwise, let's set the copied markup.
	 */
	const onCopy = () => {
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
	};

	/**
	 * Cut handler for ensuring that the store's cutMarkup is in sync with what's actually in clipBoard.
	 * If it's not a block that's being cut, let's clear the cutMarkup.
	 * Otherwise, let's set the cut markup.
	 */
	const onCut = () => {
		const selectedBlockClientIds = getSelectedBlockClientIds();

		if ( selectedBlockClientIds.length === 0 ) {
			return;
		}
		// Reuse code in onCode.
		onCopy();
		// Remove selected Blocks.
		for ( const clientId of selectedBlockClientIds ) {
			removeBlock( clientId );
		}
	};

	return {
		onCopy,
		onCut,
	};
} )( CopyPasteHandler );

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

const BLACKLIST_CLIPBOARD_ELEMENTS = [ 'INPUT', 'TEXTAREA' ];

/**
 * @param {?Element} container
 * @param {function(!ClipboardEvent)} copyCutHandler
 * @param {function(!ClipboardEvent)} pasteHandler
 */
function useClipboardHandlers( container, copyCutHandler, pasteHandler ) {
	useEffect( () => {
		if ( ! container ) {
			return undefined;
		}

		const copyCutHandlerWrapper = ( evt ) => {
			const { target, clipboardData } = evt;

			// Elements that either handle their own clipboard or use platform.
			if ( ! target ||
          BLACKLIST_CLIPBOARD_ELEMENTS.includes( target.tagName ) ||
          target.closest( '[contenteditable="true"]' ) ) {
				return;
			}

			// A target can be anywhere in the container's full subtree, but not
			// in its siblings.
			if ( ! container.contains( target ) && ! target.contains( container ) ) {
				return;
			}

			// Someone has already put something in the clipboard. Do not override.
			if ( clipboardData.types.length !== 0 ) {
				return;
			}

			copyCutHandler( evt );
		};

		const pasteHandlerWrapper = ( evt ) => {
			const { target } = evt;

			// Elements that either handle their own clipboard or use platform.
			if ( ! target ||
          BLACKLIST_CLIPBOARD_ELEMENTS.includes( target.tagName ) ||
          target.closest( '[contenteditable="true"]' ) ) {
				return;
			}

			// A target can be anywhere in the container's full subtree, but not
			// in its siblings.
			if ( ! container.contains( target ) && ! target.contains( container ) ) {
				return;
			}

			pasteHandler( evt );
		};

		document.addEventListener( 'copy', copyCutHandlerWrapper );
		document.addEventListener( 'cut', copyCutHandlerWrapper );
		document.addEventListener( 'paste', pasteHandlerWrapper );
		return () => {
			document.removeEventListener( 'copy', copyCutHandlerWrapper );
			document.removeEventListener( 'cut', copyCutHandlerWrapper );
			document.removeEventListener( 'paste', pasteHandlerWrapper );
		};
	}, [ container, copyCutHandler, pasteHandler ] );
}

export default useClipboardHandlers;

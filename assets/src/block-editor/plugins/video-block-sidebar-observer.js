/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';
import { select, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export const name = 'amp-video-block-sidebar-observer';

const messageClassName = 'amp-video-autoplay-notice';

/**
 * Plugin for observing mutations done to the `core/video` block sidebar.
 */
function VideoBlockSidebarObserver() {
	const pluginRef = useRef();
	const sidebarRef = useRef();
	const observerRef = useRef( null );

	const isEditorSidebarOpened = useSelect( ( _select ) => _select( 'core/edit-post' ).isEditorSidebarOpened(), [] );

	useEffect( () => {
		if ( pluginRef.current && ! sidebarRef.current ) {
			sidebarRef.current = pluginRef.current.closest( '#editor' ).querySelector( '.interface-interface-skeleton__sidebar' );
		}
	}, [] );

	useEffect( () => {
		if ( isEditorSidebarOpened && sidebarRef.current && ! observerRef.current ) {
			observerRef.current = new MutationObserver( () => {
				toggleVideoMutedMessage( sidebarRef.current );
			} );

			observerRef.current.observe( sidebarRef.current, {
				attributes: true,
				subtree: true,
				childList: true,
			} );
		}

		return () => {
			if ( observerRef.current ) {
				observerRef.current.disconnect();
				observerRef.current = null;
			}
		};
	}, [ isEditorSidebarOpened ] );

	return (
		<div ref={ pluginRef } />
	);
}

/**
 * Toggle muted message for the Video block.
 *
 * @param {HTMLElement} sidebar Block editor sidebar element.
 */
function toggleVideoMutedMessage( sidebar ) {
	let block;

	// First check if there are multiple blocks selected.
	const multiSelectedBlocks = select( 'core/block-editor' ).getMultiSelectedBlocks();

	if ( multiSelectedBlocks.length > 0 ) {
		if ( multiSelectedBlocks.find( ( item ) => item.name !== 'core/video' ) ) {
			return;
		}

		block = multiSelectedBlocks[ 0 ];
	} else {
		block = select( 'core/block-editor' ).getSelectedBlock();

		if ( block?.name !== 'core/video' ) {
			return;
		}
	}

	const { attributes: { autoplay, muted } } = block;
	const shouldShowMessage = true === autoplay && true !== muted;
	const message = sidebar.querySelector( `.${ messageClassName }` );

	if ( shouldShowMessage && ! message ) {
		/**
		 * Index of the "Muted" toggle in the "Video settings" panel.
		 *
		 * Block editor doesn't add any designators to the toggles in the Video
		 * block inspector controls. Because of that, we first locate the video
		 * poster image control; from there we move up the DOM tree and search
		 * for the "Muted" toggle container.
		 *
		 * @type {number}
		 */
		const mutedToggleContainerIndex = 3;
		const mutedToggleContainer = sidebar.querySelector( '.editor-video-poster-control' )?.parentElement.children[ mutedToggleContainerIndex ];

		if ( ! mutedToggleContainer ) {
			return;
		}

		const notice = document.createElement( 'p' );
		notice.classList.add( messageClassName );
		notice.textContent = __( 'Autoplay will cause the video to be muted in many browsers to prevent a poor user experience. It will be muted in AMP for this reason as well.', 'amp' );

		mutedToggleContainer.append( notice );
	} else if ( ! shouldShowMessage && message ) {
		message.remove();
	}
}

export const render = VideoBlockSidebarObserver;

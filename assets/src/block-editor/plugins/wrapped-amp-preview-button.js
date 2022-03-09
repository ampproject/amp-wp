/**
 * WordPress dependencies
 */
import { createPortal, useLayoutEffect, useRef } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import AmpPreviewButton from '../components/amp-preview-button';

/**
 * A wrapper for the AMP preview button that renders it immediately after the 'Post' preview button, when present.
 */
function WrappedAmpPreviewButton() {
	const root = useRef( null );
	const referenceNode = useRef( null );

	const { isEditedPostSaveable, isViewable } = useSelect( ( select ) => ( {
		isEditedPostSaveable: select( 'core/editor' ).isEditedPostSaveable(),
		isViewable: select( 'core' ).getPostType( select( 'core/editor' ).getEditedPostAttribute( 'type' ) )?.viewable,
	} ), [] );

	useLayoutEffect( () => {
		// The AMP preview button should always be inserted right before the publish/update button.
		referenceNode.current = document.querySelector( '.editor-post-publish-button__button' );

		if ( referenceNode.current?.parentNode ) {
			if ( ! root.current ) {
				root.current = document.createElement( 'div' );
				root.current.className = 'amp-wrapper-post-preview';
			}

			referenceNode.current.parentNode.insertBefore( root.current, referenceNode.current );
		}

		return () => {
			if ( referenceNode.current && root.current ) {
				referenceNode.current.parentNode.removeChild( root.current );
				referenceNode.current = null;
			}
		};
		// AMP Preview button should be "refreshed" whenever settings in the post editor header are re-rendered.
		// The following properties may indicate a change in the toolbar layout:
		// - Viewable property gets defined once the toolbar has been rendered.
		// - When saveable property changes, the toolbar is reshuffled heavily.
	}, [ isEditedPostSaveable, isViewable ] );

	return root.current ? createPortal( <AmpPreviewButton />, root.current ) : null;
}

export const name = 'amp-preview-button-wrapper';

export const onlyPaired = true;

export const render = WrappedAmpPreviewButton;

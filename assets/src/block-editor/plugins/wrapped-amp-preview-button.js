/**
 * WordPress dependencies
 */
import { createPortal, useEffect, useRef } from '@wordpress/element';
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

	const isViewable = useSelect(
		( select ) => select( 'core' ).getPostType( select( 'core/editor' ).getEditedPostAttribute( 'type' ) )?.viewable,
		[],
	);

	useEffect( () => {
		if ( ! root.current && ! referenceNode.current ) {
			// At first, we try finding the post preview button that is visible only on small screens.
			// If found, we will use its next sibling so that `insertBefore` gets us to the exact location
			// we are looking for.
			referenceNode.current = document.querySelector( '.editor-post-preview' )?.nextSibling;

			// Since the mobile post preview button is rendered with a delay, we are using the post publish/update
			// button as a fallback. Because it is rendered early, our AMP preview button will be visible immediately.
			if ( ! referenceNode.current ) {
				referenceNode.current = document.querySelector( '.editor-post-publish-button' );
			}

			if ( referenceNode.current ) {
				root.current = document.createElement( 'div' );
				root.current.className = 'amp-wrapper-post-preview';
				referenceNode.current.parentNode.insertBefore( root.current, referenceNode.current );
			}
		}

		return () => {
			if ( referenceNode.current && root.current ) {
				referenceNode.current.parentNode.removeChild( root.current );
				root.current = null;
				referenceNode.current = null;
			}
		};
	// We use `isViewable` as a dependency in order to reposition the preview button once the block editor is fully loaded.
	}, [ isViewable ] );

	// It is unlikely that AMP would be enabled for a non-viewable post type. This is why the Preview button will
	// always be displayed initially (when `isViewable` is undefined), preventing horizontal layout shift.
	// Once the `isViewable` value is defined (which is after the initial block editor load) and it is `false`,
	// the Preview button will be hidden causing a minor layout shift.
	if ( ! root.current || isViewable === false ) {
		return null;
	}

	return createPortal( <AmpPreviewButton />, root.current );
}

export const name = 'amp-preview-button-wrapper';

export const onlyPaired = true;

export const render = WrappedAmpPreviewButton;

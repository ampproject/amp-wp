/**
 * WordPress dependencies
 */
import { createPortal, useEffect, useRef } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { POST_PREVIEW_CLASS } from '../constants';
import AmpPreviewButton from '../components/amp-preview-button';

/**
 * A wrapper for the AMP preview button that renders it immediately after the 'Post' preview button, when present.
 */
function WrappedAmpPreviewButton() {
	const root = useRef();
	const postPreviewButton = useRef();

	const isViewable = useSelect(
		( select ) => select( 'core' ).getPostType( select( 'core/editor' ).getEditedPostAttribute( 'type' ) )?.viewable,
		[],
	);

	useEffect( () => {
		if ( isViewable && ! root.current && ! postPreviewButton.current ) {
			postPreviewButton.current = document.querySelector( `.${ POST_PREVIEW_CLASS }` );

			// Insert the AMP preview button immediately after the post preview button.
			if ( postPreviewButton.current ) {
				root.current = document.createElement( 'div' );
				root.current.className = 'amp-wrapper-post-preview';
				postPreviewButton.current.parentNode.insertBefore( root.current, postPreviewButton.current.nextSibling );
			}
		}

		return () => {
			if ( postPreviewButton.current && root.current ) {
				postPreviewButton.current.parentNode.removeChild( root.current );
			}
		};
	}, [ isViewable ] );

	if ( ! isViewable || ! root.current ) {
		return null;
	}

	return createPortal( <AmpPreviewButton />, root.current );
}

export const name = 'amp-preview-button-wrapper';

export const onlyPaired = true;

export const render = WrappedAmpPreviewButton;

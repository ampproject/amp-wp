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
	const postPublishButton = useRef( null );

	const isViewable = useSelect(
		( select ) => select( 'core' ).getPostType( select( 'core/editor' ).getEditedPostAttribute( 'type' ) )?.viewable,
		[],
	);

	useEffect( () => {
		if ( ! root.current && ! postPublishButton.current ) {
			postPublishButton.current = document.querySelector( '.editor-post-publish-button' );

			// Insert the AMP preview button immediately after the post preview button.
			if ( postPublishButton.current ) {
				root.current = document.createElement( 'div' );
				root.current.className = 'amp-wrapper-post-preview';
				postPublishButton.current.parentNode.insertBefore( root.current, postPublishButton.current );
			}
		}

		return () => {
			if ( postPublishButton.current && root.current ) {
				postPublishButton.current.parentNode.removeChild( root.current );
				root.current = null;
				postPublishButton.current = null;
			}
		};
	}, [] );

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

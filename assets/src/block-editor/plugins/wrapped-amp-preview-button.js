/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component, createPortal } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { ifCondition, compose, pure } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { POST_PREVIEW_CLASS } from '../constants';
import AmpPreviewButton from '../components/amp-preview-button';

/**
 * A wrapper for the AMP preview button that renders it immediately after the 'Post' preview button, when present.
 */
class WrappedAmpPreviewButton extends Component {
	/**
	 * Constructs the class.
	 *
	 * @param {*} args Constructor arguments.
	 */
	constructor( ...args ) {
		super( ...args );

		this.root = document.createElement( 'div' );
		this.root.className = 'amp-wrapper-post-preview';

		this.postPreviewButton = document.querySelector( `.${ POST_PREVIEW_CLASS }` );
	}

	/**
	 * Invoked immediately after a component is mounted (inserted into the tree).
	 */
	componentDidMount() {
		if ( ! this.postPreviewButton ) {
			return;
		}

		// Insert the AMP preview button immediately after the post preview button.
		this.postPreviewButton.parentNode.insertBefore( this.root, this.postPreviewButton.nextSibling );
	}

	/**
	 * Invoked immediately before a component is unmounted and destroyed.
	 */
	componentWillUnmount() {
		if ( ! this.postPreviewButton ) {
			return;
		}

		this.postPreviewButton.parentNode.removeChild( this.root );
	}

	/**
	 * Renders the component.
	 */
	render() {
		if ( ! this.postPreviewButton ) {
			return null;
		}

		return createPortal( <AmpPreviewButton />, this.root );
	}
}

export const name = 'amp-preview-button-wrapper';

export const onlyPaired = true;

export const render = pure(
	compose( [
		withSelect( ( select ) => {
			const { getPostType } = select( 'core' );
			const { getEditedPostAttribute } = select( 'core/editor' );

			const postType = getPostType( getEditedPostAttribute( 'type' ) );

			return {
				isViewable: get( postType, [ 'viewable' ], false ),
			};
		} ),
		// This HOC creator renders the component only when the condition is true. At that point the 'Post' preview
		// button should have already been rendered (since it also relies on the same condition for rendering).
		ifCondition( ( { isViewable } ) => isViewable ),
	] )( WrappedAmpPreviewButton ),
);

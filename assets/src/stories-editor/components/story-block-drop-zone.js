/**
 * Custom component for BlockDropZone for being able to position inner blocks via drag and drop.
 * Parts of this are taken from the original BlockDropZone component.
 */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	DropZone,
} from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getPercentageFromPixels } from '../helpers';
import {
	STORY_PAGE_INNER_HEIGHT,
} from '../constants';

const wrapperElSelector = 'div[data-amp-selected="parent"] .editor-inner-blocks';

class BlockDropZone extends Component {
	constructor( ...args ) {
		super( ...args );

		this.onDrop = this.onDrop.bind( this );
	}

	/**
	 * Handles the drop event for blocks within a page.
	 * Separate handling for CTA block.
	 *
	 * @param {Object} event Drop event.
	 */
	onDrop( event ) {
		const { srcBlockName, updateBlockAttributes, srcClientId } = this.props;
		const isCTABlock = 'amp/amp-story-cta' === srcBlockName;

		let elementId,
			cloneElementId,
			wrapperEl;

		// In case of the CTA block we are not moving the block itself but just the `a` within.
		if ( isCTABlock ) {
			elementId = `amp-story-cta-button-${ srcClientId }`;
			cloneElementId = `clone-amp-story-cta-button-${ srcClientId }`;
			const btnWrapperSelector = `#block-${ srcClientId } .editor-block-list__block-edit`;

			// Get the editor wrapper element for calculating the width and height.
			wrapperEl = document.querySelector( btnWrapperSelector );
		} else {
			elementId = `block-${ srcClientId }`;
			cloneElementId = `clone-block-${ srcClientId }`;
			wrapperEl = document.querySelector( wrapperElSelector );
		}

		const element = document.getElementById( elementId );
		const clone = document.getElementById( cloneElementId );

		if ( ! element || ! clone || ! wrapperEl ) {
			event.preventDefault();
			return;
		}

		// CTA block can't be rotated.
		if ( ! isCTABlock ) {
			// We have to remove the rotation for getting accurate position.
			clone.parentNode.style.visibility = 'hidden';
			clone.parentNode.style.transform = 'none';
		}

		const clonePosition = clone.getBoundingClientRect();
		const wrapperPosition = wrapperEl.getBoundingClientRect();

		// We will set the new position based on where the clone was moved to, with reference being the wrapper element.
		// Lets take the % based on the wrapper for top and left.
		const leftPosKey = isCTABlock ? 'btnPositionLeft' : 'positionLeft';
		const topPosKey = isCTABlock ? 'btnPositionTop' : 'positionTop';

		// Let's get the base value to measure the top percentage from.
		let baseHeight = STORY_PAGE_INNER_HEIGHT;
		if ( isCTABlock ) {
			baseHeight = STORY_PAGE_INNER_HEIGHT / 5;
		}
		updateBlockAttributes( srcClientId, {
			[ leftPosKey ]: getPercentageFromPixels( 'x', clonePosition.left - wrapperPosition.left ),
			[ topPosKey ]: getPercentageFromPixels( 'y', clonePosition.top - wrapperPosition.top, baseHeight ),
		} );
	}

	render() {
		return (
			<DropZone
				className="editor-block-drop-zone"
				onDrop={ this.onDrop }
			/>
		);
	}
}

BlockDropZone.propTypes = {
	updateBlockAttributes: PropTypes.func,
	srcClientId: PropTypes.string,
	srcBlockName: PropTypes.string,
};

export default withDispatch( ( dispatch ) => {
	const { updateBlockAttributes } = dispatch( 'core/block-editor' );

	return {
		updateBlockAttributes( ...args ) {
			updateBlockAttributes( ...args );
		},
	};
} )( BlockDropZone );

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
import { DropZone } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getPercentageFromPixels, isCTABlock } from '../helpers';
import {
	STORY_PAGE_INNER_HEIGHT,
	STORY_PAGE_INNER_HEIGHT_FOR_CTA,
} from '../constants';

const wrapperElSelector = 'div[data-amp-selected="parent"] .block-editor-inner-blocks';

const BlockDropZone = ( { srcBlockName, srcClientId } ) => {
	const blockIsCTA = isCTABlock( srcBlockName );

	const { updateBlockAttributes } = useDispatch( 'core/block-editor' );

	/**
	 * Handles the drop event for blocks within a page.
	 * Separate handling for CTA block.
	 *
	 * @param {Object} event Drop event.
	 */
	const onDrop = ( event ) => {
		let elementId,
			cloneElementId,
			wrapperEl;

		// In case of the CTA block we are not moving the block itself but just the `a` within.
		if ( blockIsCTA ) {
			elementId = `amp-story-cta-button-${ srcClientId }`;
			cloneElementId = `clone-amp-story-cta-button-${ srcClientId }`;
			const btnWrapperSelector = `#block-${ srcClientId } .block-editor-block-list__block-edit`;

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
		if ( ! blockIsCTA ) {
			// We have to remove the rotation for getting accurate position.
			clone.parentNode.style.visibility = 'hidden';
			clone.parentNode.style.transform = 'none';
		}

		const clonePosition = clone.getBoundingClientRect();
		const wrapperPosition = wrapperEl.getBoundingClientRect();

		// We will set the new position based on where the clone was moved to, with reference being the wrapper element.
		// Lets take the % based on the wrapper for top and left.
		const leftPosKey = blockIsCTA ? 'btnPositionLeft' : 'positionLeft';
		const topPosKey = blockIsCTA ? 'btnPositionTop' : 'positionTop';

		// Let's get the base value to measure the top percentage from.
		let baseHeight = STORY_PAGE_INNER_HEIGHT;
		if ( blockIsCTA ) {
			baseHeight = STORY_PAGE_INNER_HEIGHT_FOR_CTA;
		}
		updateBlockAttributes( srcClientId, {
			[ leftPosKey ]: getPercentageFromPixels( 'x', clonePosition.left - wrapperPosition.left ),
			[ topPosKey ]: getPercentageFromPixels( 'y', clonePosition.top - wrapperPosition.top, baseHeight ),
		} );
	};

	return (
		<DropZone
			className="editor-block-drop-zone"
			key={ srcClientId }
			onDrop={ onDrop }
		/>
	);
};

BlockDropZone.propTypes = {
	srcClientId: PropTypes.string,
	srcBlockName: PropTypes.string,
};

export default BlockDropZone;

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../constants';
import getBlockWrapperElement from './getBlockWrapperElement';
import getBlockInnerElement from './getBlockInnerElement';
import resetAnimationProperties from './resetAnimationProperties';
import getRelativeElementPosition from './getRelativeElementPosition';
import getBlockDOMNode from './getBlockDOMNode';

const { getBlockRootClientId } = select( 'core/block-editor' );

/**
 * Calculate target scaling factor so that it is at least 25% larger than the
 * page.
 *
 * A copy of the same method in the AMP framework.
 *
 * @see https://github.com/ampproject/amphtml/blob/13b3b6d92ee0565c54ec34732e88f01847aa8a91/extensions/amp-story/1.0/animation-presets-utils.js#L91-L111
 *
 * @param {number} width Target width.
 * @param {number} height Target height.
 *
 * @return {number} Target scaling factor.
 */
export const calculateTargetScalingFactor = ( width, height ) => {
	const targetFitsWithinPage = width <= STORY_PAGE_INNER_WIDTH || height <= STORY_PAGE_INNER_HEIGHT;

	if ( targetFitsWithinPage ) {
		const scalingFactor = 1.25;

		const widthFactor = STORY_PAGE_INNER_WIDTH > width ? STORY_PAGE_INNER_WIDTH / width : 1;
		const heightFactor = STORY_PAGE_INNER_HEIGHT > height ? STORY_PAGE_INNER_HEIGHT / height : 1;

		return Math.max( widthFactor, heightFactor ) * scalingFactor;
	}

	return 1;
};

/**
 * Calculates the offsets and scaling factors for animation playback.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 * @return {{offsetX: number, offsetY: number, scalingFactor: number}} Animation transform parameters.
 */
const getAnimationTransformParams = ( block, animationType ) => {
	const blockElement = getBlockWrapperElement( block );
	const innerElement = getBlockInnerElement( block );
	const parentBlock = getBlockRootClientId( block.clientId );
	const parentBlockElement = getBlockDOMNode( parentBlock );

	const width = innerElement.offsetWidth;
	const height = innerElement.offsetHeight;
	const { top, left } = getRelativeElementPosition( blockElement, parentBlockElement );

	let offsetX;
	let offsetY;
	let scalingFactor;

	switch ( animationType ) {
		case 'fly-in-left':
		case 'rotate-in-left':
		case 'whoosh-in-left':
			offsetX = -( left + width );
			break;
		case 'fly-in-right':
		case 'rotate-in-right':
		case 'whoosh-in-right':
			offsetX = STORY_PAGE_INNER_WIDTH + left + width;
			break;
		case 'fly-in-top':
			offsetY = -( top + height );
			break;
		case 'fly-in-bottom':
			offsetY = STORY_PAGE_INNER_HEIGHT + top + height;
			break;
		case 'drop':
			offsetY = Math.max( 160, ( top + height ) );
			break;
		case 'pan-left':
		case 'pan-right':
			scalingFactor = calculateTargetScalingFactor( width, height );
			offsetX = STORY_PAGE_INNER_WIDTH - ( width * scalingFactor );
			offsetY = ( STORY_PAGE_INNER_HEIGHT - ( height * scalingFactor ) ) / 2;
			break;
		case 'pan-down':
		case 'pan-up':
			scalingFactor = calculateTargetScalingFactor( width, height );
			offsetX = -( width * scalingFactor ) / 2;
			offsetY = STORY_PAGE_INNER_HEIGHT - ( height * scalingFactor );
			break;
		default:
			offsetX = 0;
	}

	return {
		offsetX,
		offsetY,
		scalingFactor,
	};
};

/**
 * Sets the needed CSS custom properties and class name for animation playback.
 *
 * This way the initial animation state can be displayed without having to actually
 * start the animation.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 */
const setAnimationTransformProperties = ( block, animationType ) => {
	const blockElement = getBlockWrapperElement( block );

	if ( ! blockElement || ! animationType ) {
		return;
	}

	resetAnimationProperties( block, animationType );

	const { offsetX, offsetY, scalingFactor } = getAnimationTransformParams( block, animationType );

	if ( offsetX ) {
		blockElement.style.setProperty( '--animation-offset-x', `${ offsetX }px` );
	}

	if ( offsetY ) {
		blockElement.style.setProperty( '--animation-offset-y', `${ offsetY }px` );
	}

	if ( scalingFactor ) {
		blockElement.style.setProperty( '--animation-scale-start', scalingFactor );
		blockElement.style.setProperty( '--animation-scale-end', scalingFactor );
	}

	blockElement.classList.add( `story-animation-init-${ animationType }` );
};

export default setAnimationTransformProperties;

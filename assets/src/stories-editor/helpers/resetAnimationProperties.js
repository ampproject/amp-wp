/**
 * Internal dependencies
 */
import getBlockWrapperElement from './getBlockWrapperElement';

/**
 * Removes all inline styles and class name previously set for animation playback.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 */
const resetAnimationProperties = ( block, animationType ) => {
	const blockElement = getBlockWrapperElement( block );

	if ( ! blockElement || ! animationType ) {
		return;
	}

	blockElement.classList.remove( `story-animation-init-${ animationType }` );
	blockElement.classList.remove( `story-animation-${ animationType }` );
	blockElement.style.removeProperty( '--animation-offset-x' );
	blockElement.style.removeProperty( '--animation-offset-y' );
	blockElement.style.removeProperty( '--animation-scale-start' );
	blockElement.style.removeProperty( '--animation-scale-end' );
	blockElement.style.removeProperty( '--animation-duration' );
	blockElement.style.removeProperty( '--animation-delay' );
};

export default resetAnimationProperties;

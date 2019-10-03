/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS } from '../constants';
import getBlockWrapperElement from './getBlockWrapperElement';

/**
 * Plays the block's animation in the editor.
 *
 * Assumes that setAnimationTransformProperties() has been called before.
 *
 * @param {Object} block Block object.
 * @param {string} animationType Animation type.
 * @param {number} animationDuration Animation duration.
 * @param {number} animationDelay Animation delay.
 * @param {Function} callback Callback for when animation has stopped.
 */
const startAnimation = ( block, animationType, animationDuration, animationDelay, callback = () => {} ) => {
	const blockElement = getBlockWrapperElement( block );

	if ( ! blockElement || ! animationType ) {
		callback();

		return;
	}

	const DEFAULT_ANIMATION_DURATION = ANIMATION_DURATION_DEFAULTS[ animationType ] || 0;

	blockElement.classList.remove( `story-animation-init-${ animationType }` );

	blockElement.style.setProperty( '--animation-duration', `${ animationDuration || DEFAULT_ANIMATION_DURATION }ms` );
	blockElement.style.setProperty( '--animation-delay', `${ animationDelay || 0 }ms` );

	blockElement.classList.add( `story-animation-${ animationType }` );

	blockElement.addEventListener( 'animationend', callback, { once: true } );
};

export default startAnimation;

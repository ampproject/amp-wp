/**
 * Given a list of animated blocks, calculates the total duration
 * of all animations based on the durations and the delays.
 *
 * @param {Object[]} animatedBlocks               List of animated blocks.
 * @param {string} animatedBlocks[].id            The block's client ID.
 * @param {string} animatedBlocks[].parent        The block's parent client ID.
 * @param {string} animatedBlocks[].animationType The block's animation type.
 * @param {string} animatedBlocks[].duration      The block's animation duration.
 * @param {string} animatedBlocks[].delay         The block's animation delay.
 *
 * @return {number} Total animation duration time.
 */
const getTotalAnimationDuration = ( animatedBlocks ) => {
	const getLongestAnimation = ( parentBlockId ) => {
		return animatedBlocks
			.filter( ( { parent, animationType } ) => parent === parentBlockId && animationType )
			.map( ( { duration, delay } ) => {
				const animationDelay = delay ? parseInt( delay ) : 0;
				const animationDuration = duration ? parseInt( duration ) : 0;

				return animationDelay + animationDuration;
			} )
			.reduce( ( max, current ) => Math.max( max, current ), 0 );
	};

	const levels = [ ...new Set( animatedBlocks.map( ( { parent } ) => parent ) ) ];

	return levels.map( getLongestAnimation ).reduce( ( sum, duration ) => sum + duration, 0 );
};

export default getTotalAnimationDuration;

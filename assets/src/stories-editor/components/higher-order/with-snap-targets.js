/**
 * WordPress dependencies
 */
import { withDispatch, withSelect } from '@wordpress/data';
import { compose, createHigherOrderComponent } from '@wordpress/compose';
import isShallowEqual from '@wordpress/is-shallow-equal';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';
import { getPixelsFromPercentage } from '../../helpers';

const applyWithSelect = withSelect( ( select, { clientId, angle } ) => {
	const {
		getBlocksByClientId,
		getBlockRootClientId,
		getBlockOrder,
	} = select( 'core/block-editor' );
	const { getSnapLines } = select( 'amp/story' );

	const parentBlock = getBlockRootClientId( clientId );
	const siblings = getBlocksByClientId( getBlockOrder( parentBlock ) )
		.filter( ( { clientId: blockId } ) => blockId !== clientId )
		.filter( ( { attributes } ) => ! attributes.rotationAngle );

	const snapLines = getSnapLines();
	const hasSnapLine = ( item ) => snapLines.find( ( snapLine ) => isShallowEqual( item[ 0 ], snapLine[ 0 ] ) && isShallowEqual( item[ 1 ], snapLine[ 1 ] ) );

	return {
		horizontalSnaps: () => {
			// @todo: Support snapping for a rotated block that is being resized.
			if ( angle ) {
				return [];
			}

			const pageSnaps = [
				0,
				STORY_PAGE_INNER_WIDTH / 2,
				STORY_PAGE_INNER_WIDTH,
			];

			const blockSnaps = siblings
				.map( ( { attributes } ) => {
					const { positionLeft, width } = attributes;
					const positionInPx = getPixelsFromPercentage( 'x', positionLeft );
					return [ positionInPx, positionInPx + width ];
				} )
				.reduce( ( result, snaps ) => {
					for ( const snap of snaps ) {
						if ( ! result.includes( snap ) && ! pageSnaps.includes( snap ) ) {
							result.push( snap );
						}
					}

					return result;
				}, [] );

			return [ ...pageSnaps, ...blockSnaps ];
		},
		verticalSnaps: () => {
			// @todo: Support snapping for a rotated block that is being resized.
			if ( angle ) {
				return [];
			}

			const pageSnaps = [
				0,
				STORY_PAGE_INNER_HEIGHT / 2,
				STORY_PAGE_INNER_HEIGHT,
			];

			const blockSnaps = siblings
				.map( ( { attributes } ) => {
					const { positionTop, height } = attributes;
					const positionInPx = getPixelsFromPercentage( 'y', positionTop );
					return [ positionInPx, positionInPx + height ];
				} )
				.reduce( ( result, snaps ) => {
					for ( const snap of snaps ) {
						if ( ! result.includes( snap ) && ! pageSnaps.includes( snap ) ) {
							result.push( snap );
						}
					}

					return result;
				}, [] );

			return [ ...pageSnaps, ...blockSnaps ];
		},
		snapLines,
		hasSnapLine,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch ) => {
	const {
		showSnapLines,
		hideSnapLines,
		setSnapLines,
		clearSnapLines,
	} = dispatch( 'amp/story' );

	return {
		showSnapLines,
		hideSnapLines,
		setSnapLines,
		clearSnapLines,
	};
} );

const enhance = compose(
	applyWithSelect,
	applyWithDispatch,
);

/**
 * Higher-order component that provides snap targets.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	enhance,
	'withSnapTargets'
);
